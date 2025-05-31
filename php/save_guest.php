<?php
session_start();
require_once 'conn.php';

header('Content-Type: application/json');

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'coordinator'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Validate required fields
$required_fields = ['guest-firstname', 'guest-lastname', 'guest-email', 'guest-event-id', 'guest-gender', 'guest-age'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

// Sanitize input
$guest_number = isset($_POST['guest-number']) ? (int)$_POST['guest-number'] : null;
$first_name = mysqli_real_escape_string($conn, $_POST['guest-firstname']);
$last_name = mysqli_real_escape_string($conn, $_POST['guest-lastname']);
$email = mysqli_real_escape_string($conn, $_POST['guest-email']);
$event_id = mysqli_real_escape_string($conn, $_POST['guest-event-id']);
$gender = mysqli_real_escape_string($conn, $_POST['guest-gender']);
$age = (int)$_POST['guest-age'];

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Validate age
if ($age < 1 || $age > 120) {
    echo json_encode(['success' => false, 'message' => 'Invalid age']);
    exit;
}

// Validate gender
if (!in_array($gender, ['Male', 'Female'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid gender']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    if ($guest_number) {
        // Update existing participant
        $stmt = $conn->prepare("UPDATE participants_table SET first_name=?, last_name=?, email=?, event_id=?, Gender=?, Age=? WHERE number=?");
        $stmt->bind_param("sssssis", $first_name, $last_name, $email, $event_id, $gender, $age, $guest_number);
    } else {
        // Insert new participant
        $stmt = $conn->prepare("INSERT INTO participants_table (first_name, last_name, email, event_id, Gender, Age) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $first_name, $last_name, $email, $event_id, $gender, $age);
    }

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    // Commit transaction
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Participant saved successfully']);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?> 