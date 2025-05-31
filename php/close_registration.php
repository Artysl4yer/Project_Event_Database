<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Please log in to continue']);
    exit();
}

// Check if event_id is provided
if (!isset($_POST['event_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Event ID is required']);
    exit();
}

$event_id = intval($_POST['event_id']);

try {
    // Start transaction
    $conn->begin_transaction();

    // Get user ID from session email
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $_SESSION['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('User not found');
    }
    
    $user = $result->fetch_assoc();
    $coordinator_id = $user['id'];

    // Update event status to Closed
    $stmt = $conn->prepare("UPDATE event_table SET event_status = 'Closed' WHERE number = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        throw new Exception('Event not found or already closed');
    }

    // Add coordinator record
    $stmt = $conn->prepare("INSERT INTO event_coordinators (event_id, coordinator_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $event_id, $coordinator_id);
    $stmt->execute();

    // Commit transaction
    $conn->commit();

    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Event registration closed successfully'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?> 