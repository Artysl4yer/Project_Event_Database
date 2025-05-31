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
    // Your delete logic here (example):
    $sql = "DELETE FROM archive_table WHERE number = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);
    $stmt->bind_param('i', $event_id);
    if (!$stmt->execute()) throw new Exception('Execute failed: ' . $stmt->error);
         $stmt->close();
    $response = ['success' => true, 'message' => 'Archived event deleted successfully'];
} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
     }
ob_clean();
echo json_encode($response);
$conn->close();

?>