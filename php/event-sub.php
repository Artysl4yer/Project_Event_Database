<?php
include 'conn.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if($conn == false){
    die(json_encode(['error' => true, 'message' => "Could not connect to database: " . mysqli_connect_error()]));
}

try {
    // Get and sanitize input
    $event_title = isset($_POST["event-title"]) ? $conn->real_escape_string($_POST["event-title"]) : '';
    $event_location = isset($_POST["event-location"]) ? $conn->real_escape_string($_POST["event-location"]) : '';
    $date_start = isset($_POST["event-date-start"]) ? $conn->real_escape_string($_POST["event-date-start"]) : '';
    $event_start = isset($_POST["event-time-start"]) ? $conn->real_escape_string($_POST["event-time-start"]) : '';
    $date_end = isset($_POST["event-date-end"]) ? $conn->real_escape_string($_POST["event-date-end"]) : '';
    $event_end = isset($_POST["event-time-end"]) ? $conn->real_escape_string($_POST["event-time-end"]) : '';
    $event_description = isset($_POST["event-description"]) ? $conn->real_escape_string($_POST["event-description"]) : '';
    $organization = isset($_POST["event-orgs"]) ? $conn->real_escape_string($_POST["event-orgs"]) : '';
    $status = isset($_POST['event-status']) ? $conn->real_escape_string($_POST['event-status']) : 'Ongoing';
    $code = isset($_POST['code']) ? $conn->real_escape_string($_POST['code']) : '';

    // Validate required fields
    $required_fields = [
        'event-title' => $event_title,
        'event-location' => $event_location,
        'event-date-start' => $date_start,
        'event-date-end' => $date_end,
        'event-time-start' => $event_start,
        'event-time-end' => $event_end,
        'event-orgs' => $organization,
        'code' => $code
    ];

    $missing_fields = [];
    foreach ($required_fields as $field => $value) {
        if (empty($value)) {
            $missing_fields[] = $field;
        }
    }

    if (!empty($missing_fields)) {
        throw new Exception("Missing required fields: " . implode(", ", $missing_fields));
    }

    // Validate date/time format
    $merge_start = DateTime::createFromFormat('Y-m-d H:i', $date_start . ' ' . $event_start);
    $merge_end = DateTime::createFromFormat('Y-m-d H:i', $date_end . ' ' . $event_end);

    if (!$merge_start || !$merge_end) {
        throw new Exception("Invalid date or time format");
    }

    $merge_start = $merge_start->format('Y-m-d H:i:s');
    $merge_end = $merge_end->format('Y-m-d H:i:s');

    // Insert event
    $stmt = $conn->prepare("INSERT INTO event_table (
        event_title, event_code, event_location, 
        date_start, event_start, date_end, event_end, 
        event_description, organization, event_status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }

    $stmt->bind_param("ssssssssss", 
        $event_title, 
        $code, 
        $event_location, 
        $date_start, 
        $merge_start, 
        $date_end, 
        $merge_end, 
        $event_description, 
        $organization, 
        $status
    );

    if (!$stmt->execute()) {
        throw new Exception("Failed to save event: " . $stmt->error);
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Event created successfully',
        'event_id' => $conn->insert_id
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?>