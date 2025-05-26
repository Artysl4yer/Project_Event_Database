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

        // First, check if there are any participants
        $check_sql = "SELECT COUNT(*) as participant_count FROM participants_table WHERE event_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $event_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['participant_count'] > 0) {
            // If there are participants, archive the event instead of deleting
            $archive_sql = "UPDATE event_table SET event_status = 'archived' WHERE number = ?";
            $archive_stmt = $conn->prepare($archive_sql);
            $archive_stmt->bind_param("i", $event_id);
            
            if (!$archive_stmt->execute()) {
                throw new Exception("Failed to archive event: " . $archive_stmt->error);
            }

            // Log the archive action
            $log_sql = "INSERT INTO event_logs (event_id, action, performed_by, action_time) 
                       VALUES (?, 'archived', ?, NOW())";
            $log_stmt = $conn->prepare($log_sql);
            $log_stmt->bind_param("ii", $event_id, $_SESSION['client_id']);
            $log_stmt->execute();

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Event archived successfully (has participants)'
            ]);
        } else {
            // If no participants, delete the event
            $delete_sql = "DELETE FROM event_table WHERE number = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $event_id);
            
            if (!$delete_stmt->execute()) {
                throw new Exception("Failed to delete event: " . $delete_stmt->error);
            }

            // Log the deletion
            $log_sql = "INSERT INTO event_logs (event_id, action, performed_by, action_time) 
                       VALUES (?, 'deleted', ?, NOW())";
            $log_stmt = $conn->prepare($log_sql);
            $log_stmt->bind_param("ii", $event_id, $_SESSION['client_id']);
            $log_stmt->execute();

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Event deleted successfully'
            ]);
        }

    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Error processing event: ' . $e->getMessage()
        ]);
    }
}
?>
