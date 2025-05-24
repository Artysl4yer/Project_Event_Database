<?php
include 'conn.php';

// Check if email column exists
$check_column = $conn->query("SHOW COLUMNS FROM participants_table LIKE 'email'");
if ($check_column->num_rows == 0) {
    // Add email column if it doesn't exist
    $sql = "ALTER TABLE participants_table ADD COLUMN email VARCHAR(255) AFTER participant_id";
    
    if ($conn->query($sql) === TRUE) {
        echo "Email column added successfully";
    } else {
        echo "Error adding email column: " . $conn->error;
    }
} else {
    echo "Email column already exists";
}

$conn->close();
?> 