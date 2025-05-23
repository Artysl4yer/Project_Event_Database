<?php
include 'conn.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// First, let's check if the columns already exist
$result = $conn->query("SHOW COLUMNS FROM participants_table LIKE 'first_name'");
$has_first_name = $result && $result->num_rows > 0;

$result = $conn->query("SHOW COLUMNS FROM participants_table LIKE 'last_name'");
$has_last_name = $result && $result->num_rows > 0;

if (!$has_first_name || !$has_last_name) {
    // Add new columns if they don't exist
    $alter_query = "ALTER TABLE participants_table 
        ADD COLUMN IF NOT EXISTS first_name VARCHAR(255) AFTER ID,
        ADD COLUMN IF NOT EXISTS last_name VARCHAR(255) AFTER first_name";
    
    if ($conn->query($alter_query)) {
        echo "Added first_name and last_name columns successfully\n";
        
        // If we have existing data in the Name column, split it and update the new columns
        $update_query = "UPDATE participants_table 
            SET first_name = SUBSTRING_INDEX(Name, ' ', 1),
                last_name = SUBSTRING_INDEX(Name, ' ', -1)
            WHERE Name IS NOT NULL AND Name != ''";
        
        if ($conn->query($update_query)) {
            echo "Updated existing names to first_name and last_name\n";
            
            // After successful migration, we can drop the old Name column
            $drop_query = "ALTER TABLE participants_table DROP COLUMN Name";
            if ($conn->query($drop_query)) {
                echo "Dropped old Name column successfully\n";
            } else {
                echo "Error dropping Name column: " . $conn->error . "\n";
            }
        } else {
            echo "Error updating names: " . $conn->error . "\n";
        }
    } else {
        echo "Error adding columns: " . $conn->error . "\n";
    }
} else {
    echo "Columns first_name and last_name already exist\n";
}

$conn->close();
?> 