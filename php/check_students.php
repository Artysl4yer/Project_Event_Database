<?php
include 'conn.php';

// Set header for JSON response
header('Content-Type: application/json');

$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : null;

if (!$student_id) {
    echo json_encode(['success' => false, 'message' => 'No student ID provided']);
    exit;
}

// Query the student_table for the student
$stmt = $conn->prepare("SELECT ID, first_name, last_name, Course FROM student_table WHERE ID = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $student = [
        'full_name' => $row['first_name'] . ' ' . $row['last_name'],
        'student_id' => $row['ID'],
        'course' => $row['Course']
    ];
    echo json_encode(['success' => true, 'student' => $student]);
} else {
    echo json_encode(['success' => false, 'message' => 'Student not found']);
}

$conn->close();
?> 