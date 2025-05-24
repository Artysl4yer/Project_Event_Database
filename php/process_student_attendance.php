<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in and is a student
if (!isset($_SESSION['student_id'], $_SESSION['role']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

include 'conn.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$event_code = $input['event_code'] ?? '';
$student_id = $input['student_id'] ?? '';

if (empty($event_code) || empty($student_id)) {
    echo json_encode(['success' => false, 'message' => 'Missing required information']);
    exit();
}

try {
    // Get event details using the event code
    $stmt = $conn->prepare("SELECT number, event_title, event_date FROM event_table WHERE event_code = ?");
    $stmt->bind_param("s", $event_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid event code']);
        exit();
    }

    $event = $result->fetch_assoc();
    $event_number = $event['number'];
    $current_date = date('Y-m-d');
    $current_time = date('H:i:s');

    // Check if student is registered for the event
    $stmt = $conn->prepare("SELECT * FROM participants_table WHERE ID = ? AND event_number = ?");
    $stmt->bind_param("si", $student_id, $event_number);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'You are not registered for this event']);
        exit();
    }

    // Check if attendance already marked
    $stmt = $conn->prepare("SELECT * FROM attendance_table WHERE student_id = ? AND event_number = ? AND date = ?");
    $stmt->bind_param("sis", $student_id, $event_number, $current_date);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Attendance already marked for today']);
        exit();
    }

    // Mark attendance
    $status = 'Present';
    $stmt = $conn->prepare("INSERT INTO attendance_table (student_id, event_number, time_in, date, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sisss", $student_id, $event_number, $current_time, $current_date, $status);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Attendance marked successfully for ' . $event['event_title']
        ]);
    } else {
        throw new Exception('Failed to mark attendance');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
} 