<?php
include 'conn.php';

// Check if registration_date column exists
$result = $conn->query("SHOW COLUMNS FROM participants_table LIKE 'registration_date'");
if ($result->num_rows == 0) {
    // Add registration_date column if it doesn't exist
    $sql = "ALTER TABLE participants_table ADD COLUMN registration_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
    
    if ($conn->query($sql) === TRUE) {
        echo "Registration date column added successfully";
    } else {
        echo "Error adding registration date column: " . $conn->error;
    }
} else {
    echo "Registration date column already exists";
}

$conn->close();
?> 