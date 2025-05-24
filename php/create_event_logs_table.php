<?php
include 'conn.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// First, let's check the structure of the referenced tables
$result = $conn->query("SHOW CREATE TABLE event_table");
if ($result) {
    $row = $result->fetch_assoc();
    echo "Event table structure:\n" . $row['Create Table'] . "\n\n";
}

$result = $conn->query("SHOW CREATE TABLE users_table");
if ($result) {
    $row = $result->fetch_assoc();
    echo "Users table structure:\n" . $row['Create Table'] . "\n\n";
}

// SQL to create event_logs table with matching column types
$sql = "CREATE TABLE IF NOT EXISTS event_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT UNSIGNED NOT NULL,
    action VARCHAR(50) NOT NULL,
    details TEXT,
    performed_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES event_table(number) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users_table(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "Table event_logs created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?> 