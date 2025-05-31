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
$required_fields = ['ID', 'first_name', 'last_name', 'Gender', 'Age', 'event_id'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

// Sanitize and validate input
$participant_id = mysqli_real_escape_string($conn, $_POST['ID']);
$first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
$last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
$email = isset($_POST['email']) ? mysqli_real_escape_string($conn, $_POST['email']) : null;
$gender = mysqli_real_escape_string($conn, $_POST['Gender']);
$age = (int)$_POST['Age'];
$event_id = (int)$_POST['event_id'];

// Check event registration status and deadline
$event_query = $conn->prepare("SELECT registration_status, registration_deadline FROM event_table WHERE number = ?");
$event_query->bind_param("i", $event_id);
$event_query->execute();
$event_result = $event_query->get_result();
if ($event_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Event not found']);
    exit;
}
$event = $event_result->fetch_assoc();
date_default_timezone_set('Asia/Manila');
$now = new DateTime('now', new DateTimeZone('Asia/Manila'));
$deadline = new DateTime($event['registration_deadline'], new DateTimeZone('Asia/Manila'));
$is_registration_open = (strtolower(trim($event['registration_status'])) === 'open') && ($now <= $deadline);

if (!$is_registration_open) {
    echo json_encode(['success' => false, 'message' => 'Registration is closed for this event.']);
    exit;
}

// Validate age
if ($age < 1 || $age > 120) {
    echo json_encode(['success' => false, 'message' => 'Invalid age']);
    exit;
}

// Validate email if provided
if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Check if participant already exists
    $check_participant = $conn->prepare("SELECT number FROM participants_table WHERE ID = ?");
    $check_participant->bind_param("s", $participant_id);
    $check_participant->execute();
    $participant_result = $check_participant->get_result();

    if ($participant_result->num_rows === 0) {
        // Insert new participant
        $insert_participant = $conn->prepare("INSERT INTO participants_table (ID, first_name, last_name, email, Gender, Age, event_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insert_participant->bind_param("sssssii", $participant_id, $first_name, $last_name, $email, $gender, $age, $event_id);
        $insert_participant->execute();
    }

    // Commit transaction
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Participant registered successfully']);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?> 