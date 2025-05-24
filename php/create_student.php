<?php
include 'conn.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start output buffering
ob_start();

// Function to send JSON response
function sendResponse($success, $message) {
    ob_clean();
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    echo json_encode([
        'success' => $success,
        'message' => $message
    ]);
    ob_end_flush();
    exit;
}

// Get JSON data from request body
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate required fields
if (!isset($data['student_id']) || !isset($data['full_name']) || !isset($data['course'])) {
    sendResponse(false, 'Missing required fields');
}

// Validate student ID format
if (!preg_match('/^\d{2}-\d{5}$/', $data['student_id'])) {
    sendResponse(false, 'Invalid student ID format. Use format: YY-XXXXX');
}

try {
    // Check if student already exists
    $stmt = $conn->prepare("SELECT student_id FROM student_info WHERE student_id = ?");
    $stmt->bind_param("s", $data['student_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        sendResponse(false, 'Student already exists');
    }

    // Insert new student
    $stmt = $conn->prepare("INSERT INTO student_info (student_id, full_name, course) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $data['student_id'], $data['full_name'], $data['course']);
    
    if ($stmt->execute()) {
        sendResponse(true, 'Student created successfully');
    } else {
        throw new Exception($conn->error);
    }
} catch (Exception $e) {
    sendResponse(false, 'Error creating student: ' . $e->getMessage());
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?> 