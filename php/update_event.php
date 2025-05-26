<?php
session_start();
require_once 'config.php';
require_once 'conn.php';

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['email'], $_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'coordinator'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get event data
        $event_id = $_POST['event_id'];
        $event_title = $_POST['event_title'];
        $event_description = $_POST['event_description'];
        $event_venue = $_POST['event_venue'];
        $organization = $_POST['organization'];
        $event_date = $_POST['event_date'];
        $event_time = $_POST['event_time'];
        $event_duration = $_POST['event_duration'];
        $registration_deadline = $_POST['registration_deadline'];

        // Calculate end time based on duration
        $start_datetime = new DateTime("$event_date $event_time");
        $end_datetime = clone $start_datetime;
        $end_datetime->add(new DateInterval('PT' . $event_duration . 'H'));
        
        // Format dates
        $date_start = $start_datetime->format('Y-m-d');
        $date_end = $end_datetime->format('Y-m-d');
        $event_start = $start_datetime->format('Y-m-d H:i:s');
        $event_end = $end_datetime->format('Y-m-d H:i:s');

        // Update event
        $sql = "UPDATE event_table SET 
                event_title = ?,
                event_description = ?,
                event_location = ?,
                organization = ?,
                date_start = ?,
                event_start = ?,
                date_end = ?,
                event_end = ?,
                registration_deadline = ?
                WHERE number = ?";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }

        $stmt->bind_param(
            "sssssssssi",
            $event_title,
            $event_description,
            $event_venue,
            $organization,
            $date_start,
            $event_start,
            $date_end,
            $event_end,
            $registration_deadline,
            $event_id
        );

        if (!$stmt->execute()) {
            throw new Exception("Failed to update event: " . $stmt->error);
        }

        // Log the update
        $log_sql = "INSERT INTO event_logs (event_id, action, performed_by, action_time) 
                   VALUES (?, 'updated', ?, NOW())";
        $log_stmt = $conn->prepare($log_sql);
        $log_stmt->bind_param("ii", $event_id, $_SESSION['client_id']);
        $log_stmt->execute();

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Event updated successfully'
        ]);

    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Error updating event: ' . $e->getMessage()
        ]);
    }
}
?>