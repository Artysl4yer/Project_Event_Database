<?php
ob_start();
header('Content-Type: application/json');
include '../php/conn.php';

$response = ['success' => false, 'message' => 'Unknown error'];

try {
    if (!isset($_POST['event_id'])) {
        throw new Exception('Event ID is required');
    }
    
    $event_id = intval($_POST['event_id']);
    
    // Start transaction
    $conn->begin_transaction();
    
    // First, get the event data
    $select_query = "SELECT * FROM event_table WHERE number = ?";
    $select_stmt = $conn->prepare($select_query);
    if (!$select_stmt) throw new Exception('Prepare failed: ' . $conn->error);
    
    $select_stmt->bind_param("i", $event_id);
    $select_stmt->execute();
    $result = $select_stmt->get_result();
    $event_data = $result->fetch_assoc();
    $select_stmt->close();
    
    if (!$event_data) {
        throw new Exception('Event not found');
    }
    
    // Insert into archive table
    $archive_query = "INSERT INTO archive_table (
        event_title, event_code, event_location,
        date_start, event_start, date_end, event_end,
        event_description, organization, event_status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $archive_stmt = $conn->prepare($archive_query);
    if (!$archive_stmt) throw new Exception('Prepare failed: ' . $conn->error);
    
    $status = 'archived';
    $archive_stmt->bind_param("ssssssssss",
        $event_data['event_title'],
        $event_data['event_code'],
        $event_data['event_location'],
        $event_data['date_start'],
        $event_data['event_start'],
        $event_data['date_end'],
        $event_data['event_end'],
        $event_data['event_description'],
        $event_data['organization'],
        $status
    );
    
    if (!$archive_stmt->execute()) {
        throw new Exception('Failed to archive event: ' . $archive_stmt->error);
    }
    
    // Delete from event table
    $delete_query = "DELETE FROM event_table WHERE number = ?";
    $delete_stmt = $conn->prepare($delete_query);
    if (!$delete_stmt) throw new Exception('Prepare failed: ' . $conn->error);
    
    $delete_stmt->bind_param("i", $event_id);
    if (!$delete_stmt->execute()) {
        throw new Exception('Failed to delete original event: ' . $delete_stmt->error);
    }
    
    // Commit transaction
    $conn->commit();
    $response = ['success' => true, 'message' => 'Event archived successfully'];
    
} catch (Exception $e) {
    // Rollback on error
    if ($conn->connect_errno === 0) {
        $conn->rollback();
    }
    $response = ['success' => false, 'message' => $e->getMessage()];
} finally {
    if (isset($archive_stmt)) $archive_stmt->close();
    if (isset($delete_stmt)) $delete_stmt->close();
    $conn->close();
}

ob_clean();
echo json_encode($response);
?> 