<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set up error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/event_errors.log');

// Log the start of event creation
error_log("Starting event creation process");

// Include database connection
include 'conn.php';

// Function to log errors
function logError($message) {
    error_log("Error: " . $message);
    return json_encode(['status' => 'error', 'message' => $message]);
}

// Check if it's a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    error_log("POST request received");
    
    // Log POST data
    error_log("POST data: " . print_r($_POST, true));
    
    // Validate required fields
    $required_fields = ['event-title', 'event-location', 'event-date-start', 'event-time-start', 
                       'event-date-end', 'event-time-end', 'event-orgs', 'event-status', 
                       'registration-status', 'auto-close', 'event-description'];
    
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            error_log("Missing required field: " . $field);
            echo logError("Missing required field: " . $field);
            exit;
        }
    }

    // Generate event code
    $event_code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
    error_log("Generated event code: " . $event_code);

    // Combine date and time
    $start_datetime = $_POST['event-date-start'] . ' ' . $_POST['event-time-start'] . ':00';
    $end_datetime = $_POST['event-date-end'] . ' ' . $_POST['event-time-end'] . ':00';
    
    error_log("Start datetime: " . $start_datetime);
    error_log("End datetime: " . $end_datetime);

    // Validate dates
    $start_date = new DateTime($start_datetime, new DateTimeZone('Asia/Manila'));
    $end_date = new DateTime($end_datetime, new DateTimeZone('Asia/Manila'));
    
    if ($end_date <= $start_date) {
        echo logError("End date must be after start date");
        exit;
    }

    // Prepare SQL statement
    $sql = "INSERT INTO event_table (event_title, event_location, date_start, date_end, 
            organization, event_status, registration_status, auto_close_registration, 
            event_description, event_code) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    error_log("SQL Query: " . $sql);

    try {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("ssssssssss", 
            $_POST['event-title'],
            $_POST['event-location'],
            $start_datetime,
            $end_datetime,
            $_POST['event-orgs'],
            $_POST['event-status'],
            $_POST['registration-status'],
            $_POST['auto-close'],
            $_POST['event-description'],
            $event_code
        );

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        error_log("Event created successfully");
        echo json_encode(['status' => 'success', 'message' => 'Event created successfully']);
        
    } catch (Exception $e) {
        error_log("Exception: " . $e->getMessage());
        echo logError("Failed to create event: " . $e->getMessage());
    }

    $stmt->close();
} else {
    error_log("Invalid request method: " . $_SERVER["REQUEST_METHOD"]);
    echo logError("Invalid request method");
}

$conn->close();
?>