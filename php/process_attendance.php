<?php
// Prevent any output before headers
ob_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/attendance_errors.log');

// Set error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error [$errno]: $errstr in $errfile on line $errline");
    return true;
});

// Set exception handler
set_exception_handler(function($e) {
    error_log("Uncaught Exception: " . $e->getMessage());
    sendResponse(false, "An unexpected error occurred: " . $e->getMessage());
});

session_start();

// Function to send JSON response
function sendResponse($success, $message) {
    // Clear any previous output
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Set headers
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    
    // Log the response
    error_log("Sending response - Success: " . ($success ? 'true' : 'false') . ", Message: " . $message);
    
    // Send response
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// Log the raw input for debugging
error_log("Raw POST data: " . file_get_contents('php://input'));
error_log("POST variables: " . print_r($_POST, true));

// Check session and required parameters
if (!isset($_SESSION['client_id'])) {
    error_log("Session error: client_id not set");
    sendResponse(false, 'Please log in to continue');
}

if (!isset($_POST['event_id']) || !isset($_POST['qr_data'])) {
    error_log("Missing parameters: " . print_r($_POST, true));
    sendResponse(false, 'Missing required parameters');
}

$event_id = intval($_POST['event_id']);
$qr_data = $_POST['qr_data'];

error_log("Processing attendance for event $event_id with data: $qr_data");

// Parse QR code data
$lines = explode("\n", trim($qr_data));
if (count($lines) !== 3) {
    error_log("Invalid QR format: " . print_r($lines, true));
    sendResponse(false, 'Invalid QR code format');
}

$full_name = trim($lines[0]);
$student_id = trim($lines[1]);
$course = trim($lines[2]);

// Remove parentheses from student ID if present
$student_id = trim($student_id, '()');

error_log("Parsed data - Name: $full_name, ID: $student_id, Course: $course");

// Validate student ID format
if (!preg_match('/^\d{2}-\d{5}$/', $student_id)) {
    error_log("Invalid student ID format: $student_id");
    sendResponse(false, 'Invalid student ID format');
}

$conn = null;
$stmt = null;

try {
    // Include database connection
    include 'conn.php';
    
    // Start transaction
    $conn->begin_transaction();
    error_log("Started transaction");

    // First, update event statuses based on current time
    require_once 'update_event_status.php';
    $status_updated = updateEventStatuses($conn);
    if (!$status_updated) {
        error_log("Failed to update event statuses");
    }

    // Check if event exists and is active
    $stmt = $conn->prepare("SELECT number, event_title, event_status, registration_status, event_start, event_end FROM event_table WHERE number = ?");
    if (!$stmt) {
        throw new Exception("Failed to prepare event query: " . $conn->error);
    }
    
    $stmt->bind_param("i", $event_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute event query: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        error_log("Event not found: $event_id");
        throw new Exception('Event not found');
    }
    
    $event = $result->fetch_assoc();
    error_log("Found event: " . print_r($event, true));
    
    // Check if event is in a valid state for attendance
    if (!in_array($event['event_status'], ['scheduled', 'ongoing'])) {
        error_log("Event not in valid state: " . $event['event_status']);
        throw new Exception('Event is not active (must be scheduled or ongoing)');
    }

    // Check if registration is open
    if ($event['registration_status'] !== 'open') {
        error_log("Registration is closed for event: " . $event_id);
        throw new Exception('Registration is closed for this event');
    }

    // Check if current time is within event time range
    $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
    $now_str = $now->format('Y-m-d H:i:s');
    error_log("Current time (Manila): $now_str");
    error_log("Event start: {$event['event_start']}");
    error_log("Event end: {$event['event_end']}");
    
    // Convert times to DateTime objects for comparison
    $start_dt = new DateTime($event['event_start'], new DateTimeZone('Asia/Manila'));
    $end_dt = new DateTime($event['event_end'], new DateTimeZone('Asia/Manila'));
    
    error_log("Current timestamp: " . $now->getTimestamp());
    error_log("Start timestamp: " . $start_dt->getTimestamp());
    error_log("End timestamp: " . $end_dt->getTimestamp());
    
    if ($now < $start_dt) {
        error_log("Time check failed: Current time is before start time");
        throw new Exception('Event has not started yet');
    }
    if ($now > $end_dt) {
        error_log("Time check failed: Current time is after end time");
        throw new Exception('Event has already ended');
    }
    
    error_log("Time check passed: Current time is within event time range");

    // Check if student exists
    $stmt = $conn->prepare("SELECT full_name, course FROM student_info WHERE student_id = ?");
    if (!$stmt) {
        throw new Exception("Failed to prepare student query: " . $conn->error);
    }
    
    $stmt->bind_param("s", $student_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute student query: " . $stmt->error);
    }
    
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        error_log("Adding new student: $student_id");
        // Add new student
        $stmt = $conn->prepare("INSERT INTO student_info (student_id, full_name, course) VALUES (?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Failed to prepare student insert: " . $conn->error);
        }
        
        $stmt->bind_param("sss", $student_id, $full_name, $course);
        if (!$stmt->execute()) {
            error_log("Error adding student: " . $conn->error);
            throw new Exception('Error adding student: ' . $conn->error);
        }
    }

    // Check for existing attendance
    $stmt = $conn->prepare("SELECT id FROM event_attendance WHERE event_id = ? AND student_id = ?");
    if (!$stmt) {
        throw new Exception("Failed to prepare attendance check: " . $conn->error);
    }
    
    $stmt->bind_param("is", $event_id, $student_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute attendance check: " . $stmt->error);
    }
    
    if ($stmt->get_result()->num_rows > 0) {
        error_log("Student already registered: $student_id for event $event_id");
        throw new Exception('Student already registered for this event');
    }

    // Register attendance
    $stmt = $conn->prepare("INSERT INTO event_attendance (event_id, student_id, registered_by) VALUES (?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Failed to prepare attendance insert: " . $conn->error);
    }
    
    $stmt->bind_param("isi", $event_id, $student_id, $_SESSION['client_id']);
    if (!$stmt->execute()) {
        error_log("Error registering attendance: " . $conn->error);
        throw new Exception('Error registering attendance: ' . $conn->error);
    }

    // Commit transaction
    $conn->commit();
    error_log("Successfully registered attendance for $full_name");
    
    // Close database resources
    if ($stmt) {
        $stmt->close();
    }
    
    // Send success response
    sendResponse(true, "Attendance registered for $full_name ($course) in event: " . $event['event_title']);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn) {
        $conn->rollback();
    }
    error_log("Attendance Error: " . $e->getMessage());
    
    // Close database resources
    if ($stmt) {
        $stmt->close();
    }
    
    // Send error response
    sendResponse(false, $e->getMessage());
} finally {
    // Close database connection
    if ($conn) {
        $conn->close();
    }
}
?> 