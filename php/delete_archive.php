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
        $event_id = $_POST['event_id'];

        // Start transaction
        $conn->begin_transaction();

        // Get event code before deletion
        $select_sql = "SELECT event_code FROM event_table WHERE number = ?";
        $select_stmt = $conn->prepare($select_sql);
        $select_stmt->bind_param("i", $event_id);
        $select_stmt->execute();
        $result = $select_stmt->get_result();
        $event_data = $result->fetch_assoc();

        if (!$event_data) {
            throw new Exception("Event not found");
        }

        // Delete related records first
        // Delete from event_logs
        $delete_logs_sql = "DELETE FROM event_logs WHERE event_id = ?";
        $delete_logs_stmt = $conn->prepare($delete_logs_sql);
        $delete_logs_stmt->bind_param("i", $event_id);
        $delete_logs_stmt->execute();

        // Delete from event_attendance
        $delete_attendance_sql = "DELETE FROM event_attendance WHERE event_id = ?";
        $delete_attendance_stmt = $conn->prepare($delete_attendance_sql);
        $delete_attendance_stmt->bind_param("i", $event_id);
        $delete_attendance_stmt->execute();

        // Delete from participants_table
        $delete_participants_sql = "DELETE FROM participants_table WHERE number = ?";
        $delete_participants_stmt = $conn->prepare($delete_participants_sql);
        $delete_participants_stmt->bind_param("i", $event_id);
        $delete_participants_stmt->execute();

        // Delete from archive_table
        $delete_archive_sql = "DELETE FROM archive_table WHERE event_code = ?";
        $delete_archive_stmt = $conn->prepare($delete_archive_sql);
        $delete_archive_stmt->bind_param("s", $event_data['event_code']);
        $delete_archive_stmt->execute();

        // Finally, delete from event_table
        $delete_event_sql = "DELETE FROM event_table WHERE number = ?";
        $delete_event_stmt = $conn->prepare($delete_event_sql);
        $delete_event_stmt->bind_param("i", $event_id);
        $delete_event_stmt->execute();

        // Commit transaction
        $conn->commit();

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Event deleted successfully'
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Error deleting event: ' . $e->getMessage()
        ]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>