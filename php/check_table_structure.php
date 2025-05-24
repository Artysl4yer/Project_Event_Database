<?php
include 'conn.php';

// Echo the CREATE TABLE statements for event_table and participants_table (as provided by the user)
echo ("Event Table (event_table) CREATE TABLE (as provided by you):\n");
echo ("CREATE TABLE event_table (\n");
echo ("  number INT AUTO_INCREMENT PRIMARY KEY,\n");
echo ("  event_code VARCHAR(100) NOT NULL,\n");
echo ("  event_title VARCHAR(100) NOT NULL,\n");
echo ("  event_location VARCHAR(100) NOT NULL,\n");
echo ("  date_start DATE NOT NULL,\n");
echo ("  event_start TIMESTAMP(6) NULL DEFAULT NULL,\n");
echo ("  date_end DATE NOT NULL,\n");
echo ("  event_end TIMESTAMP(6) NULL DEFAULT NULL,\n");
echo ("  event_description VARCHAR(500) NOT NULL,\n");
echo ("  organization VARCHAR(100) NOT NULL,\n");
echo ("  event_status VARCHAR(50) NOT NULL,\n");
echo ("  file VARCHAR(50) NOT NULL,\n");
echo ("  UNIQUE KEY event_code_unique (event_code)\n");
echo (") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\n\n");

echo ("Participants Table (participants_table) CREATE TABLE (as provided by you):\n");
echo ("CREATE TABLE participants_table (\n");
echo ("  ID INT NOT NULL,\n");
echo ("  Name VARCHAR(255) NOT NULL,\n");
echo ("  Course VARCHAR(255) NOT NULL,\n");
echo ("  Section VARCHAR(50) NOT NULL,\n");
echo ("  Gender VARCHAR(10) NOT NULL,\n");
echo ("  Age INT NOT NULL,\n");
echo ("  Year VARCHAR(20) NOT NULL,\n");
echo ("  Dept VARCHAR(255) NOT NULL,\n");
echo ("  number INT NOT NULL,\n");
echo ("  event_code VARCHAR(100) NOT NULL,\n");
echo ("  registration_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,\n");
echo ("  PRIMARY KEY (ID, event_code),\n");
echo ("  KEY number (number),\n");
echo ("  KEY fk_event_code (event_code),\n");
echo ("  CONSTRAINT fk_event_code FOREIGN KEY (event_code) REFERENCES event_table (event_code),\n");
echo ("  CONSTRAINT participants_table_ibfk_1 FOREIGN KEY (number) REFERENCES event_table (number)\n");
echo (") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\n\n");

// Check (and echo) the attendance_table (if it exists) so that the user can verify that the attendance table (created by create_attendance_table.php) is correct.
$result = $conn->query("SHOW TABLES LIKE 'attendance_table'");
if ($result->num_rows > 0) {
  $result = $conn->query("SHOW CREATE TABLE attendance_table");
  $row = $result->fetch_assoc();
  echo ("Attendance Table (attendance_table) CREATE TABLE (as created by create_attendance_table.php):\n");
  echo ($row['Create Table'] . "\n");
} else {
 echo ("Attendance table (attendance_table) does not exist (or was not created).\n");
}

$conn->close();
?> 