<?php
header('Content-Type: application/json');
include 'conn.php';

$sql = "SELECT e.event_title, COUNT(a.id) as attendance_count
        FROM event_table e
        LEFT JOIN event_attendance a ON e.number = a.event_id
        GROUP BY e.number, e.event_title
        ORDER BY attendance_count DESC, e.event_title ASC";

$result = $conn->query($sql);
$data = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'event_title' => $row['event_title'],
            'attendance_count' => (int)$row['attendance_count']
        ];
    }
}
echo json_encode($data);
$conn->close(); 