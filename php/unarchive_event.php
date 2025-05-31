<?php
ob_start();
header('Content-Type: application/json');
include '../php/conn.php';
$response = ['success' => false, 'message' => 'Unknown error'];
try {
    if (!isset($_POST['unarchive'])) {
        throw new Exception('Event ID is required');
    }
    $unarchive_id = intval($_POST['unarchive']);

    // First, get the event data from archive
    $select_query = "SELECT * FROM archive_table WHERE number = ?";
    $select_stmt = $conn->prepare($select_query);
    if (!$select_stmt) throw new Exception('Prepare failed: ' . $conn->error);
    $select_stmt->bind_param("i", $unarchive_id);
    $select_stmt->execute();
    $result = $select_stmt->get_result();
    $event_data = $result->fetch_assoc();
    $select_stmt->close();

    if ($event_data) {
        // Insert back into event table
        $insert_query = "INSERT INTO event_table (
            event_title, event_code, event_location, 
            date_start, event_start, date_end, event_end, 
            event_description, organization, event_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $insert_stmt = $conn->prepare($insert_query);
        if (!$insert_stmt) throw new Exception('Prepare failed: ' . $conn->error);
        $insert_stmt->bind_param("ssssssssss", 
            $event_data['event_title'],
            $event_data['event_code'],
            $event_data['event_location'],
            $event_data['date_start'],
            $event_data['event_start'],
            $event_data['date_end'],
            $event_data['event_end'],
            $event_data['event_description'],
            $event_data['organization'],
            $event_data['event_status']
        );
        
        if ($insert_stmt->execute()) {
            // Delete from archive table
            $delete_query = "DELETE FROM archive_table WHERE number = ?";
            $delete_stmt = $conn->prepare($delete_query);
            if (!$delete_stmt) throw new Exception('Prepare failed: ' . $conn->error);
            $delete_stmt->bind_param("i", $unarchive_id);

            if ($delete_stmt->execute()) {
                $response = ['success' => true, 'message' => 'Event unarchived successfully'];
            } else {
                throw new Exception("Error removing from archive: " . $conn->error);
            }
            $delete_stmt->close();
        } else {
            throw new Exception("Error unarchiving event: " . $conn->error);
        }
        $insert_stmt->close();
    } else {
        throw new Exception("Event not found in archive");
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}
ob_clean();
echo json_encode($response);
$conn->close();
?>