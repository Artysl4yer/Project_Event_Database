<?php
include 'conn.php';

// Drop the attendance table if it exists (to recreate it cleanly)
$conn->query("DROP TABLE IF EXISTS attendance_table");

// Create attendance table matching the user's database structure
$sql = "CREATE TABLE attendance_table (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    number INT NOT NULL COMMENT 'References event_table.number',
    event_code VARCHAR(100) NOT NULL COMMENT 'References event_table.event_code',
    ID INT NOT NULL COMMENT 'References participants_table.ID',
    attendance_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('present', 'absent', 'late') DEFAULT 'present',
    FOREIGN KEY (number) REFERENCES event_table(number) ON DELETE CASCADE,
    FOREIGN KEY (event_code) REFERENCES event_table(event_code) ON DELETE CASCADE,
    FOREIGN KEY (ID, event_code) REFERENCES participants_table(ID, event_code) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (number, event_code, ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

if ($conn->query($sql) === TRUE) {
 echo ("Attendance table (re)created successfully (matching your database).");
} else {
 echo ("Error (re)creating attendance_table: " . $conn->error);
}

$conn->close();
?> 