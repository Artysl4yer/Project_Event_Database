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
    // Get attendees for the event with detailed student info
    $sql = "SELECT ea.id AS attendance_id, ea.student_id, ea.registered_by, ea.attendance_time, s.ID, s.first_name, s.last_name, s.Course, s.Section, s.Gender, s.Age, s.Year, s.Dept
            FROM event_attendance ea
            JOIN student_table s ON ea.student_id = s.ID
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
            'attendance_id' => $row['attendance_id'],
            'student_id' => $row['student_id'],
            'registered_by' => $row['registered_by'],
            'attendance_time' => $row['attendance_time'],
            'ID' => $row['ID'],
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'Course' => $row['Course'],
            'Section' => $row['Section'],
            'Gender' => $row['Gender'],
            'Age' => $row['Age'],
            'Year' => $row['Year'],
            'Dept' => $row['Dept']
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