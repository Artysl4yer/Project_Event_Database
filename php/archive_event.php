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

        // Get event details first
        $select_sql = "SELECT * FROM event_table WHERE number = ? AND event_status != 'archived'";
        $select_stmt = $conn->prepare($select_sql);
        $select_stmt->bind_param("i", $event_id);
        $select_stmt->execute();
        $result = $select_stmt->get_result();
        $event_data = $result->fetch_assoc();

        if (!$event_data) {
            throw new Exception("Event not found or already archived");
        }

        // Get user ID from session
        $user_sql = "SELECT id FROM users WHERE email = ?";
        $user_stmt = $conn->prepare($user_sql);
        $user_stmt->bind_param("s", $_SESSION['email']);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        $user_data = $user_result->fetch_assoc();

        if (!$user_data) {
            throw new Exception("User not found");
        }

        // Check if event is already in archive_table
        $check_archive_sql = "SELECT COUNT(*) as count FROM archive_table WHERE event_code = ?";
        $check_archive_stmt = $conn->prepare($check_archive_sql);
        $check_archive_stmt->bind_param("s", $event_data['event_code']);
        $check_archive_stmt->execute();
        $archive_result = $check_archive_stmt->get_result();
        $archive_count = $archive_result->fetch_assoc()['count'];

        if ($archive_count > 0) {
            throw new Exception("Event is already archived");
        }

        // Insert into archive_table
        $archive_sql = "INSERT INTO archive_table (
            event_title, event_code, event_location, 
            date_start, event_start, date_end, event_end,
            event_description, organization, event_status,
            archived_by, archived_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $archive_stmt = $conn->prepare($archive_sql);
        $archive_stmt->bind_param(
            "ssssssssssi",
            $event_data['event_title'],
            $event_data['event_code'],
            $event_data['event_location'],
            $event_data['date_start'],
            $event_data['event_start'],
            $event_data['date_end'],
            $event_data['event_end'],
            $event_data['event_description'],
            $event_data['organization'],
            $event_data['event_status'],
            $user_data['id']
        );

        if (!$archive_stmt->execute()) {
            throw new Exception("Failed to archive event: " . $archive_stmt->error);
        }

        // Update event status in event_table
        $update_sql = "UPDATE event_table SET event_status = 'archived' WHERE number = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $event_id);

        if (!$update_stmt->execute()) {
            throw new Exception("Failed to update event status: " . $update_stmt->error);
        }

        // Log the archive action
        $log_sql = "INSERT INTO event_logs (event_id, action, performed_by, action_time) 
                   VALUES (?, 'archived', ?, NOW())";
        $log_stmt = $conn->prepare($log_sql);
        $log_stmt->bind_param("ii", $event_id, $user_data['id']);
        $log_stmt->execute();

        // Commit transaction
        $conn->commit();

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Event archived successfully'
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Error archiving event: ' . $e->getMessage()
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