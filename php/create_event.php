<?php
session_start();
require_once 'config.php';
require_once 'conn.php';

// Create logs directory if it doesn't exist
$logsDir = __DIR__ . '/../logs';
if (!file_exists($logsDir)) {
    mkdir($logsDir, 0777, true);
}

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['email'], $_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'coordinator'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', $logsDir . '/event_errors.log');

// Function to log errors
function logError($message) {
    $logFile = __DIR__ . '/../logs/event_errors.log';
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, $logFile);
}

// Validate required fields
$required_fields = [
    'event_title', 'event_description', 'event_venue', 'event_date',
    'event_time', 'event_duration', 'registration_deadline', 'event_code',
    'organization'
];

foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        logError("Missing required field: $field");
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit();
    }
}

try {
    // Format dates and times
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $registration_deadline = $_POST['registration_deadline'];
    
    // Calculate end time based on duration
    $start_datetime = new DateTime("$event_date $event_time");
    $end_datetime = clone $start_datetime;
    $end_datetime->add(new DateInterval('PT' . $_POST['event_duration'] . 'H'));
    
    // Store formatted dates in variables
    $date_start = $start_datetime->format('Y-m-d');
    $date_end = $end_datetime->format('Y-m-d');
    $event_start = $start_datetime->format('Y-m-d H:i:s');
    $event_end = $end_datetime->format('Y-m-d H:i:s');
    
    // Prepare SQL statement with all required fields
    $sql = "INSERT INTO event_table (
        event_title, event_code, event_location, date_start, event_start,
        date_end, event_end, event_description, organization, event_status,
        file, registration_status, auto_close_registration, registration_deadline
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled', '../images-icon/plm_courtyard.png', 'open', 1, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    $stmt->bind_param(
        "ssssssssss",
        $_POST['event_title'],
        $_POST['event_code'],
        $_POST['event_venue'],
        $date_start,
        $event_start,
        $date_end,
        $event_end,
        $_POST['event_description'],
        $_POST['organization'],
        $registration_deadline
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute statement: " . $stmt->error);
    }
    
    $event_id = $conn->insert_id;
    
    // Log successful event creation
    logError("Event created successfully. ID: $event_id");
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Event created successfully',
        'event_id' => $event_id
    ]);
    
} catch (Exception $e) {
    logError("Error creating event: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error creating event: ' . $e->getMessage()
    ]);
} 