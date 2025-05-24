<?php
include 'conn.php';

// Get event ID from request
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

if ($event_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid event ID']);
    exit;
}

// Check if event exists
$stmt = $conn->prepare("SELECT event_id, event_name, event_date, event_time, event_location FROM event_table WHERE event_id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Event not found']);
    exit;
}

$event = $result->fetch_assoc();
echo json_encode(['success' => true, 'event' => $event]);

$stmt->close();
$conn->close();
?> 