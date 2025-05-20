<?php
include '../php/conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_id = $_POST['event_id'];
    $event_title = $_POST['event-title'];
    $event_location = $_POST['event-location'];
    $date_start = $_POST['event-date-start'] . ' ' . $_POST['event-time-start'];
    $date_end = $_POST['event-date-end'] . ' ' . $_POST['event-time-end'];
    $organization = $_POST['event-orgs'];
    $event_status = $_POST['event-status'];
    $event_description = $_POST['event-description'];

    $sql = "UPDATE event_table SET 
            event_title = ?,
            event_location = ?,
            date_start = ?,
            date_end = ?,
            organization = ?,
            event_status = ?,
            event_description = ?
            WHERE number = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssi", 
        $event_title,
        $event_location,
        $date_start,
        $date_end,
        $organization,
        $event_status,
        $event_description,
        $event_id
    );

    if ($stmt->execute()) {
        header("Location: ../pages/6_NewEvent.php?success=Event updated successfully");
    } else {
        header("Location: ../pages/6_NewEvent.php?error=Error updating event");
    }
    
    $stmt->close();
    $conn->close();
} else {
    header("Location: ../pages/6_NewEvent.php");
}
?>