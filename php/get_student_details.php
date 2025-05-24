<?php
require_once 'conn.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering
ob_start();

// Function to send JSON response
function sendResponse($success, $message, $data = null) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'student' => $data
    ]);
    exit;
}

// Check if student_id is provided
if (!isset($_GET['student_id'])) {
    sendResponse(false, 'Student ID is required');
}

$student_id = $_GET['student_id'];

// Validate student ID format
if (!preg_match('/^\d{2}-\d{5}$/', $student_id)) {
    sendResponse(false, 'Invalid student ID format');
}

try {
    // Check if student exists
    $stmt = $conn->prepare("SELECT student_id, full_name, course FROM student_info WHERE student_id = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        sendResponse(true, 'Student found', $student);
    } else {
        sendResponse(false, 'Student not found');
    }
} catch (Exception $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
}
?> 