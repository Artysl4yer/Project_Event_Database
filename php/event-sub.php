<?php
include 'conn.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if($conn == false){
    die(json_encode(['error' => true, 'message' => "Could not connect to database: " . mysqli_connect_error()]));
}

// Get and sanitize input
$event_title = $conn->real_escape_string($_POST["event-title"]);
$event_location = $conn->real_escape_string($_POST["event-location"]);
$date_start = $conn->real_escape_string($_POST["event-date-start"]);
$event_start = $conn->real_escape_string($_POST["event-time-start"]);
$date_end = $conn->real_escape_string($_POST["event-date-end"]);
$event_end = $conn->real_escape_string($_POST["event-time-end"]);
$event_description = $conn->real_escape_string($_POST["event-description"]);
$organization = $conn->real_escape_string($_POST["event-orgs"]);
$event_status = $conn->real_escape_string($_POST['event-status']);
$registration_status = $conn->real_escape_string($_POST['registration-status']);
$auto_close = $conn->real_escape_string($_POST['auto-close']);
$code = $conn->real_escape_string($_POST['code']);

// Validate date/time format
$merge_start = DateTime::createFromFormat('Y-m-d H:i', $date_start . ' ' . $event_start, new DateTimeZone('Asia/Manila'));
$merge_end = DateTime::createFromFormat('Y-m-d H:i', $date_end . ' ' . $event_end, new DateTimeZone('Asia/Manila'));

    if (!$merge_start || !$merge_end) {
        throw new Exception("Invalid date or time format");
    }

    $merge_start = $merge_start->format('Y-m-d H:i:s');
    $merge_end = $merge_end->format('Y-m-d H:i:s');

error_log("Creating event with start time: $merge_start and end time: $merge_end");

// Validate status values
$valid_event_statuses = ['draft', 'scheduled', 'ongoing', 'completed', 'cancelled', 'archived'];
$valid_registration_statuses = ['open', 'closed'];

if (!in_array($event_status, $valid_event_statuses)) {
    die("ERROR: Invalid event status.");
}

if (!in_array($registration_status, $valid_registration_statuses)) {
    die("ERROR: Invalid registration status.");
}

// Start transaction
$conn->begin_transaction();

try {
    $sql = "INSERT INTO event_table (
        event_title, event_code, event_location, 
        date_start, event_start, date_end, event_end, 
        event_description, organization, event_status,
        registration_status, auto_close_registration,
        last_status_update
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP
    )";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssssi", 
        $event_title, $code, $event_location,
        $date_start, $merge_start, $date_end, $merge_end,
        $event_description, $organization, $event_status,
        $registration_status, $auto_close
    );

    if (!$stmt->execute()) {
        throw new Exception("Error executing statement: " . $stmt->error);
    }

    $event_id = $conn->insert_id;

    // Check if event_logs table exists before trying to log
    $table_exists = $conn->query("SHOW TABLES LIKE 'event_logs'");
    if ($table_exists && $table_exists->num_rows > 0) {
        // Log the event creation
        $stmt = $conn->prepare("
            INSERT INTO event_logs (
                event_id, 
                action, 
                details, 
                performed_by
            ) VALUES (?, 'event_created', ?, ?)
        ");
        $details = "Event created with status: $event_status, registration: $registration_status";
        $stmt->bind_param("isi", $event_id, $details, $_SESSION['client_id']);
        $stmt->execute();
    }

    // Commit transaction
    $conn->commit();
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Event created successfully']);
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    error_log("Event Creation Error: " . $e->getMessage());
    
    // Return error response
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error creating event: ' . $e->getMessage()]);
    exit();
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?>