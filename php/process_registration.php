<?php
include 'conn.php';

// Set header to return JSON
header('Content-Type: application/json');

try {
    // Get form data
    $event_id = $_POST['event_id'] ?? '';
    $event_code = $_POST['event_code'] ?? '';
    $student_id = $_POST['ID'] ?? '';
    $firstname = $_POST['FirstName'] ?? '';
    $lastname = $_POST['LastName'] ?? '';
    $name = trim($firstname) . ' ' . trim($lastname);
    $course = $_POST['Course'] ?? '';
    $section = $_POST['Section'] ?? '';
    $gender = $_POST['Gender'] ?? '';
    $age = $_POST['Age'] ?? '';
    $year = $_POST['Year'] ?? '';
    $dept = $_POST['Dept'] ?? '';

    // Validate required fields
    if (empty($event_id) || empty($event_code) || empty($student_id) || empty($firstname) || empty($lastname)) {
        throw new Exception('All fields are required');
    }

    // Verify event exists and code matches
    $stmt = $conn->prepare("SELECT number FROM event_table WHERE number = ? AND event_code = ?");
    $stmt->bind_param("is", $event_id, $event_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Invalid event or event code');
    }

    // Check if student is already registered for this event
    $stmt = $conn->prepare("SELECT ID FROM participants_table WHERE ID = ? AND number = ?");
    $stmt->bind_param("si", $student_id, $event_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        throw new Exception('You are already registered for this event');
    }

    // Insert participant
    $stmt = $conn->prepare("INSERT INTO participants_table (ID, Name, Course, Section, Gender, Age, Year, Dept, number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssi", $student_id, $name, $course, $section, $gender, $age, $year, $dept, $event_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Registration successful']);
    } else {
        throw new Exception('Failed to save registration: ' . $conn->error);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?> 