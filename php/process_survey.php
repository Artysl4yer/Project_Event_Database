<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in and is a student
if (!isset($_SESSION['student_id'], $_SESSION['role']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

include 'conn.php';

// Get POST data
$event_number = $_POST['event_number'] ?? '';
$student_id = $_POST['student_id'] ?? '';
$overall_rating = $_POST['overall_rating'] ?? '';
$content_rating = $_POST['content_rating'] ?? '';
$speaker_rating = $_POST['speaker_rating'] ?? '';
$venue_rating = $_POST['venue_rating'] ?? '';
$feedback = $_POST['feedback'] ?? '';

// Validate required fields
if (empty($event_number) || empty($student_id) || 
    empty($overall_rating) || empty($content_rating) || 
    empty($speaker_rating) || empty($venue_rating)) {
    echo json_encode(['success' => false, 'message' => 'All ratings are required']);
    exit();
}

try {
    // Check if student is registered for the event
    $stmt = $conn->prepare("SELECT * FROM participants_table WHERE ID = ? AND event_number = ?");
    $stmt->bind_param("si", $student_id, $event_number);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'You are not registered for this event']);
        exit();
    }

    // Check if survey already submitted
    $stmt = $conn->prepare("SELECT * FROM event_surveys WHERE event_number = ? AND student_id = ?");
    $stmt->bind_param("is", $event_number, $student_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'You have already submitted a survey for this event']);
        exit();
    }

    // Insert survey response
    $stmt = $conn->prepare("INSERT INTO event_surveys (event_number, student_id, overall_rating, content_rating, speaker_rating, venue_rating, feedback) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isiiiis", $event_number, $student_id, $overall_rating, $content_rating, $speaker_rating, $venue_rating, $feedback);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Thank you for your feedback!'
        ]);
    } else {
        throw new Exception('Failed to save survey response');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
} 