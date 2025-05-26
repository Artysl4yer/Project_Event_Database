<?php
session_start();
header('Content-Type: application/json');

// Include database connection
require_once 'conn.php';

// Get form data
$registration_type = $_POST['registration_type'] ?? '';
$event_id = $_POST['event_id'] ?? '';
$event_code = $_POST['event_code'] ?? '';

// Validate required fields
if (!$event_id || !$event_code) {
    echo json_encode(['success' => false, 'message' => 'Missing event information']);
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();
    
    if ($registration_type === 'student') {
        // Validate student fields
        $student_id = $_POST['student_id'] ?? '';
        $student_name = $_POST['student_name'] ?? '';
        $student_course = $_POST['student_course'] ?? '';
        $student_section = $_POST['student_section'] ?? '';
        $student_gender = $_POST['student_gender'] ?? '';
        
        if (!$student_id || !$student_name || !$student_course || !$student_section || !$student_gender) {
            throw new Exception('Please fill in all required fields');
        }
        
        // Insert student registration
        $stmt = $conn->prepare("INSERT INTO participant_table (event_id, student_id, full_name, course, section, gender) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }
        
        $stmt->bind_param("isssss", 
            $event_id,
            $student_id,
            $student_name,
            $student_course,
            $student_section,
            $student_gender
        );
        
    } else {
        // Validate guest fields
        $guest_name = $_POST['guest_name'] ?? '';
        $guest_email = $_POST['guest_email'] ?? '';
        $guest_contact = $_POST['guest_contact'] ?? '';
        $guest_org = $_POST['guest_org'] ?? '';
        $guest_position = $_POST['guest_position'] ?? '';
        
        if (!$guest_name || !$guest_email || !$guest_contact) {
            throw new Exception('Please fill in all required fields');
        }
        
        // Insert guest registration
        $stmt = $conn->prepare("INSERT INTO guest_info (event_id, full_name, email, contact_number, organization, position) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }
        
        $stmt->bind_param("isssss", 
            $event_id,
            $guest_name,
            $guest_email,
            $guest_contact,
            $guest_org,
            $guest_position
        );
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to register: " . $stmt->error);
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Registration successful']);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    error_log("Error in process_registration.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// Close the database connection
if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?> 