<?php
// download_attendance_csv.php
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="event_attendance.csv"');
include 'conn.php';

$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
if (!$event_id) {
    echo "Invalid event ID";
    exit;
}

// Query: join event_attendance and student_table for this event
$sql = "SELECT s.ID, s.first_name, s.last_name, s.Course, s.Section, s.Gender, s.Age, s.Year, s.Dept, ea.attendance_time
        FROM event_attendance ea
        JOIN student_table s ON ea.student_id = s.ID
        WHERE ea.event_id = ?
        ORDER BY ea.attendance_time DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $event_id);
$stmt->execute();
$result = $stmt->get_result();

// Output CSV header
$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'First Name', 'Last Name', 'Course', 'Section', 'Gender', 'Age', 'Year', 'Department', 'Attendance Time']);

// Output rows
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['ID'],
        $row['first_name'],
        $row['last_name'],
        $row['Course'],
        $row['Section'],
        $row['Gender'],
        $row['Age'],
        $row['Year'],
        $row['Dept'],
        $row['attendance_time'],
    ]);
}
fclose($output);
$stmt->close();
$conn->close(); 