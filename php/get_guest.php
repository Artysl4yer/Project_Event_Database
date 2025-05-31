<?php
session_start();
require_once 'conn.php';

header('Content-Type: application/json');

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'coordinator'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Validate guest ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid participant ID']);
    exit;
}

$guest_id = (int)$_GET['id'];

try {
    // Fetch participant data
    $stmt = $conn->prepare("SELECT * FROM participants_table WHERE number = ?");
    $stmt->bind_param("i", $guest_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Participant not found']);
        exit;
    }

    $participant = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'guest' => [
            'first_name' => $participant['first_name'],
            'last_name' => $participant['last_name'],
            'email' => $participant['email'],
            'event_id' => $participant['event_id'],
            'gender' => $participant['Gender'],
            'age' => $participant['Age']
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?> 