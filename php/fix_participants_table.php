<?php
include 'conn.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// First, let's check the current table structure
$result = $conn->query("DESCRIBE participants_table");
if (!$result) {
    die("Error checking table structure: " . $conn->error);
}

echo "Current table structure:\n";
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

// Now let's modify the table to add the event_id column if it doesn't exist
$alter_query = "ALTER TABLE participants_table 
    ADD COLUMN IF NOT EXISTS event_id INT,
    ADD COLUMN IF NOT EXISTS registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ADD FOREIGN KEY (event_id) REFERENCES event_table(number) ON DELETE CASCADE";

if ($conn->query($alter_query)) {
    echo "\nTable structure updated successfully!\n";
} else {
    echo "\nError updating table structure: " . $conn->error . "\n";
}

// Let's also check if we need to update any existing records
$update_query = "UPDATE participants_table p 
    INNER JOIN event_table e ON p.number = e.number 
    SET p.event_id = e.number 
    WHERE p.event_id IS NULL";

if ($conn->query($update_query)) {
    echo "Existing records updated successfully!\n";
} else {
    echo "Error updating existing records: " . $conn->error . "\n";
}

$conn->close();
?> 