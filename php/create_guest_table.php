<?php
include 'conn.php';

// Create guest_info table
$sql = "CREATE TABLE IF NOT EXISTS guest_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    contact_number VARCHAR(20),
    organization VARCHAR(255),
    position VARCHAR(255),
    event_id INT NOT NULL,
    registration_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES event_table(number)
)";

if ($conn->query($sql) === TRUE) {
    echo "Guest info table created successfully";
} else {
    echo "Error creating guest info table: " . $conn->error;
}

$conn->close();
?> 