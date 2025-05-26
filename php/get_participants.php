<?php
session_start();
header('Content-Type: application/json');

// Include database connection
require_once 'conn.php';

// Check if event_id is provided
if (!isset($_POST['event_id'])) {
    echo json_encode(['success' => false, 'message' => 'Event ID is required']);
    exit;
}

$event_id = $_POST['event_id'];

try {
    // Get participants for the event
    $sql = "SELECT s.student_id, s.full_name, s.course, ea.attendance_time as registration_date 
            FROM student_info s 
            INNER JOIN event_attendance ea ON s.student_id = ea.student_id 
            WHERE ea.event_id = ? 
            ORDER BY ea.attendance_time DESC";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }

    $stmt->bind_param("i", $event_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute query: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $participants = [];
    
    while ($row = $result->fetch_assoc()) {
        $participants[] = [
            'student_id' => $row['student_id'],
            'full_name' => $row['full_name'],
            'course' => $row['course'],
            'registration_date' => $row['registration_date']
        ];
    }

    echo json_encode([
        'success' => true,
        'participants' => $participants
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_participants.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching participants'
    ]);
}

// Close statement and connection
if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?> 