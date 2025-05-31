<?php
ob_start();
header('Content-Type: application/json');
include 'conn.php';
$response = ['success' => false, 'message' => 'Unknown error'];
try {
    if (!isset($_FILES['attendance_csv']) || $_FILES['attendance_csv']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error');
    }
    $file = $_FILES['attendance_csv']['tmp_name'];
    $handle = fopen($file, 'r');
    if (!$handle) {
        throw new Exception('Failed to open uploaded file');
    }
    $header = fgetcsv($handle);
    if (!$header) {
        throw new Exception('CSV file is empty or invalid');
    }
    $rowCount = 0;
    while (($row = fgetcsv($handle)) !== false) {
        $data = array_combine($header, $row);
        // Example: expect columns student_id, event_id, attendance_time
        if (!isset($data['student_id']) || !isset($data['event_id'])) continue;
        $student_id = $data['student_id'];
        $event_id = $data['event_id'];
        $attendance_time = isset($data['attendance_time']) ? $data['attendance_time'] : date('Y-m-d H:i:s');
        // Insert or update student_table if needed (add more fields as needed)
        $check = $conn->prepare('SELECT 1 FROM student_table WHERE ID = ?');
        $check->bind_param('s', $student_id);
        $check->execute();
        $check->store_result();
        if ($check->num_rows == 0) {
            $insertStudent = $conn->prepare('INSERT INTO student_table (ID) VALUES (?)');
            $insertStudent->bind_param('s', $student_id);
            $insertStudent->execute();
            $insertStudent->close();
        }
        $check->close();
        // Insert attendance
        $insertAttendance = $conn->prepare('INSERT INTO event_attendance (event_id, student_id, attendance_time) VALUES (?, ?, ?)');
        $insertAttendance->bind_param('iss', $event_id, $student_id, $attendance_time);
        $insertAttendance->execute();
        $insertAttendance->close();
        $rowCount++;
    }
    fclose($handle);
    $response = ['success' => true, 'message' => "Imported $rowCount attendance records."];
} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}
ob_clean();
echo json_encode($response);
$conn->close(); 