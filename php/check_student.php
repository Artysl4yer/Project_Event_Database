<?php
include 'conn.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start output buffering
ob_start();

// Function to send JSON response
function sendResponse($success, $message, $student = null) {
    ob_clean();
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'student' => $student
    ]);
    ob_end_flush();
    exit;
}

// Check if student_id is provided
if (!isset($_GET['student_id'])) {
    sendResponse(false, 'Student ID is required');
}

$student_id = trim($_GET['student_id']);

// Validate student ID format (YY-XXXXX)
if (!preg_match('/^\d{2}-\d{5}$/', $student_id)) {
    sendResponse(false, 'Invalid student ID format. Use format: YY-XXXXX');
}

// Check if student exists
$stmt = $conn->prepare("SELECT student_id, full_name, course FROM student_info WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    sendResponse(false, 'Student not found in database');
}

$student = $result->fetch_assoc();
sendResponse(true, 'Student found', $student);

$conn->close();
?> 