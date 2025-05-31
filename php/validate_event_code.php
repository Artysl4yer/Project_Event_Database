<?php
session_start();
header('Content-Type: application/json');

// Include database connection
require_once 'conn.php';

// Get the event code from the request
$code = isset($_GET['code']) ? trim($_GET['code']) : '';

if (empty($code)) {
    echo json_encode(['success' => false, 'message' => 'No code provided']);
    exit;
}

try {
    // Check if the event code exists and is valid
    $stmt = $conn->prepare("SELECT number, event_status, registration_status FROM event_table WHERE event_code = ? AND event_status != 'cancelled'");
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    $stmt->bind_param("s", $code);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute query: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid event code']);
        exit;
    }
    
    $event = $result->fetch_assoc();
    
    // Check if registration is open
    if ($event['registration_status'] !== 'open') {
        echo json_encode(['success' => false, 'message' => 'Registration is currently closed for this event']);
        exit;
    }
    
    // Check if the event is active/ongoing
    if ($event['event_status'] !== 'ongoing' && $event['event_status'] !== 'scheduled') {
        echo json_encode(['success' => false, 'message' => 'This event is not currently active']);
        exit;
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Valid event code',
        'event_id' => $event['number']
    ]);
    
} catch (Exception $e) {
    error_log("Error in validate_event_code.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while validating the code']);
}

// Close the database connection
if ($stmt) {
    $stmt->close();
}
$conn->close();
?> 