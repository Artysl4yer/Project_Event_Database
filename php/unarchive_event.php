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

        // Get event details from archive_table
        $select_sql = "SELECT * FROM archive_table WHERE event_code = (SELECT event_code FROM event_table WHERE number = ?)";
        $select_stmt = $conn->prepare($select_sql);
        $select_stmt->bind_param("i", $event_id);
        $select_stmt->execute();
        $result = $select_stmt->get_result();
        $event_data = $result->fetch_assoc();

        if (!$event_data) {
            throw new Exception("Event not found in archive");
        }

        // Update event status in event_table
        $update_sql = "UPDATE event_table SET event_status = 'active' WHERE number = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $event_id);

        if (!$update_stmt->execute()) {
            throw new Exception("Failed to update event status: " . $update_stmt->error);
        }

        // Delete from archive_table
        $delete_sql = "DELETE FROM archive_table WHERE event_code = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("s", $event_data['event_code']);

        if (!$delete_stmt->execute()) {
            throw new Exception("Failed to remove from archive: " . $delete_stmt->error);
        }

        // Log the unarchive action
        $log_sql = "INSERT INTO event_logs (event_id, action, performed_by, action_time) 
                   VALUES (?, 'unarchived', (SELECT id FROM users WHERE email = ?), NOW())";
        $log_stmt = $conn->prepare($log_sql);
        $log_stmt->bind_param("is", $event_id, $_SESSION['email']);
        $log_stmt->execute();

        // Commit transaction
        $conn->commit();

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Event unarchived successfully'
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Error unarchiving event: ' . $e->getMessage()
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