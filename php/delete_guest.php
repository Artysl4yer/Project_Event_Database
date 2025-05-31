<?php
session_start();
require_once 'conn.php';

header('Content-Type: application/json');

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'coordinator'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get and validate input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id']) || !is_numeric($input['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid participant ID']);
    exit;
}

$guest_id = (int)$input['id'];

// Start transaction
$conn->begin_transaction();

try {
    // Delete participant
    $stmt = $conn->prepare("DELETE FROM participants_table WHERE number = ?");
    $stmt->bind_param("i", $guest_id);

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception('Participant not found');
    }

    // Commit transaction
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Participant deleted successfully']);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?> 