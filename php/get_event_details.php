<?php
include 'conn.php';

if (!isset($_GET['id'])) {
    die(json_encode(['error' => 'No event ID provided']));
}

$event_id = $_GET['id'];

// Get event details including event_code
$stmt = $conn->prepare("SELECT number, event_title, event_location, date_start, event_start, date_end, event_end, event_description, organization, event_status, event_code FROM event_table WHERE number = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die(json_encode(['error' => 'Event not found']));
}

$event = $result->fetch_assoc();

// Return event details as JSON
header('Content-Type: application/json');
echo json_encode($event);

$stmt->close();
$conn->close();
?> 