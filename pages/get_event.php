<?php
include '../php/conn.php';

if (isset($_GET['id'])) {
    $event_id = $_GET['id'];
    $query = "SELECT * FROM event_table WHERE number = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();

    echo json_encode($event);  // Return the event data in JSON format
}
?>
