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
if (!isset($_SESSION['email'])) {
    error_log("Session error: email not set");
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

// Debug logging for QR code parsing
error_log("DEBUG - QR Code parsing:");
error_log("DEBUG - Full name: '$full_name'");
error_log("DEBUG - Student ID: '$student_id'");
error_log("DEBUG - Course: '$course'");

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

    // Get user ID from session email
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if (!$stmt) {
        throw new Exception("Failed to prepare user query: " . $conn->error);
    }
    
    $stmt->bind_param("s", $_SESSION['email']);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute user query: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception('User not found');
    }
    
    $user = $result->fetch_assoc();
    $user_id = $user['id'];

    // First, update event statuses based on current time
    require_once 'update_event_status.php';
    $status_updated = updateEventStatuses($conn);
    if (!$status_updated) {
        error_log("Failed to update event statuses");
    }

    // Check if event exists and is active
    $stmt = $conn->prepare("SELECT number, event_title, event_status, registration_status, event_start, event_end, organization FROM event_table WHERE number = ?");
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
    error_log("DEBUG - Full event data: " . print_r($event, true));
    
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

    // Debug logging for course restriction check
    error_log("DEBUG - Organization from database: '" . $event['organization'] . "'");
    error_log("DEBUG - Student course: '" . $course . "'");
    error_log("DEBUG - Available organizations: " . print_r($COURSE_RESTRICTIONS, true));

    // Check course restrictions
    require_once 'course_restrictions.php';
    if (!isCourseAllowed($event['organization'], $course)) {
        error_log("Course restriction check failed: Course '$course' not allowed for organization '{$event['organization']}'");
        throw new Exception("This event is restricted to specific courses. Your course ($course) is not eligible for this event.");
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

    // Check if student exists in student_table and has a course (section)
    $stmt = $conn->prepare("SELECT first_name, last_name, Course FROM student_table WHERE ID = ? AND Course != '' AND Course IS NOT NULL");
    if (!$stmt) {
        throw new Exception("Failed to prepare student query: " . $conn->error);
    }
    $stmt->bind_param("s", $student_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute student query: " . $stmt->error);
    }
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        // Student not found, insert if QR has valid ID and course
        // Parse name from QR: expected format 'LASTNAME, FIRSTNAME'
        $name_parts = explode(',', $full_name);
        $last_name = isset($name_parts[0]) ? trim($name_parts[0]) : '';
        $first_name = isset($name_parts[1]) ? trim($name_parts[1]) : '';
        if ($last_name === '' || $first_name === '' || trim($course) === '') {
            error_log("Invalid QR data for new student insert: $full_name, $student_id, $course");
            throw new Exception('Student not found and QR code does not have valid name and course.');
        }
        $insert_stmt = $conn->prepare("INSERT INTO student_table (ID, first_name, last_name, Course) VALUES (?, ?, ?, ?)");
        if (!$insert_stmt) {
            throw new Exception("Failed to prepare student insert: " . $conn->error);
        }
        $insert_stmt->bind_param("ssss", $student_id, $first_name, $last_name, $course);
        if (!$insert_stmt->execute()) {
            error_log("Error inserting new student: " . $conn->error);
            throw new Exception('Error inserting new student: ' . $conn->error);
        }
        $display_name = $last_name . ', ' . $first_name;
        $student_course = $course;
    } else {
        $student = $result->fetch_assoc();
        $display_name = $student['last_name'] . ', ' . $student['first_name'];
        $student_course = $student['Course'];
        if (strcasecmp(trim($student_course), trim($course)) !== 0) {
            throw new Exception('Course in QR does not match student record.');
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
    
    $stmt->bind_param("isi", $event_id, $student_id, $user_id);
    if (!$stmt->execute()) {
        error_log("Error registering attendance: " . $conn->error);
        throw new Exception('Error registering attendance: ' . $conn->error);
    }

    // Commit transaction
    $conn->commit();
    error_log("Successfully registered attendance for $display_name");
    
    // Close database resources
    if ($stmt) {
        $stmt->close();
    }
    
    // Send success response
    sendResponse(true, "Attendance registered for $display_name ($student_course) in event: " . $event['event_title']);

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