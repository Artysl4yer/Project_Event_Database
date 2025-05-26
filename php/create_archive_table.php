<?php
include 'conn.php';

// Create archive table
$sql = "CREATE TABLE IF NOT EXISTS archive_table (
    number INT AUTO_INCREMENT PRIMARY KEY,
    event_title VARCHAR(100) NOT NULL,
    event_code VARCHAR(100) NOT NULL,
    event_location VARCHAR(100) NOT NULL,
    date_start DATE NOT NULL,
    event_start TIMESTAMP(6) NULL DEFAULT NULL,
    date_end DATE NOT NULL,
    event_end TIMESTAMP(6) NULL DEFAULT NULL,
    event_description VARCHAR(500) NOT NULL,
    organization VARCHAR(100) NOT NULL,
    event_status VARCHAR(50) NOT NULL,
    archived_by INT UNSIGNED,
    archived_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (archived_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY event_code_unique (event_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($conn->query($sql) === TRUE) {
    echo "Archive table created successfully";
} else {
    echo "Error creating archive table: " . $conn->error;
}

$conn->close();
?> 