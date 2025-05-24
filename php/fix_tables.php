<?php
include 'conn.php';

// Add missing columns to event_attendance
$sql = "ALTER TABLE event_attendance 
        ADD COLUMN IF NOT EXISTS attendance_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ADD FOREIGN KEY IF NOT EXISTS (event_id) REFERENCES event_table(number),
        ADD FOREIGN KEY IF NOT EXISTS (student_id) REFERENCES student_info(student_id),
        ADD FOREIGN KEY IF NOT EXISTS (registered_by) REFERENCES clients(id),
        ADD UNIQUE KEY IF NOT EXISTS unique_attendance (event_id, student_id)";

if ($conn->query($sql) === TRUE) {
    echo "Event attendance table updated successfully\n";
} else {
    echo "Error updating event attendance table: " . $conn->error . "\n";
}

// Update process_attendance.php to use correct column names
$sql = "UPDATE event_table SET event_status = 'active' WHERE number = 30";
if ($conn->query($sql) === TRUE) {
    echo "Event status updated successfully\n";
} else {
    echo "Error updating event status: " . $conn->error . "\n";
}

$conn->close();
?> 