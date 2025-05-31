<?php
header('Content-Type: application/json');
include '../php/conn.php';

$response = ['success' => false, 'message' => 'Unknown error'];

try {
    if (!isset($_GET['event_id'])) {
        throw new Exception('Event ID is required');
    }
    
    $event_id = intval($_GET['event_id']);
    $type = $_GET['type'] ?? 'active';
    
    // Determine which table to query
    $table = $type === 'archive' ? 'archive_table' : 'event_table';
    
    // Get event details
    $query = "SELECT * FROM {$table} WHERE number = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
    
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();
    
    if (!$event) {
        throw new Exception('Event not found');
    }
    
    $response = [
        'success' => true,
        'event' => $event
    ];
    
} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
} finally {
    if (isset($stmt)) $stmt->close();
    $conn->close();
}

echo json_encode($response);
?> 