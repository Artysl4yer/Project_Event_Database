<?php
session_start();
header('Content-Type: application/json');

// Include database connection
require_once 'conn.php';

// Get JSON data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate required fields
if (!isset($data['event_id']) || !isset($data['full_name']) || !isset($data['email']) || !isset($data['contact_number'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Insert guest registration
    $stmt = $conn->prepare("INSERT INTO guest_info (event_id, full_name, email, contact_number, organization, position) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    $stmt->bind_param("isssss", 
        $data['event_id'],
        $data['full_name'],
        $data['email'],
        $data['contact_number'],
        $data['organization'] ?? '',
        $data['position'] ?? ''
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to register guest: " . $stmt->error);
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Guest registration successful']);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    error_log("Error in register_guest.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// Close the database connection
if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?> 