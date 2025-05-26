<?php
include 'conn.php';
require_once 'config.php';

// First, add event_status column to event_table if it doesn't exist
$sql = "ALTER TABLE event_table ADD COLUMN IF NOT EXISTS event_status ENUM('Open', 'Closed', 'Cancelled') DEFAULT 'Open'";

if ($conn->query($sql) === TRUE) {
    echo "Event status column added successfully\n";
} else {
    echo "Error adding event status column: " . $conn->error . "\n";
}

// Drop existing foreign key constraint
$sql = "ALTER TABLE event_attendance 
        DROP FOREIGN KEY IF EXISTS event_attendance_ibfk_3";

if ($conn->query($sql) === TRUE) {
    echo "Dropped existing foreign key constraint\n";
} else {
    echo "Error dropping foreign key constraint: " . $conn->error . "\n";
}

// Add missing columns to event_attendance
$sql = "ALTER TABLE event_attendance 
        ADD COLUMN IF NOT EXISTS attendance_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ADD FOREIGN KEY IF NOT EXISTS (event_id) REFERENCES event_table(number),
        ADD FOREIGN KEY IF NOT EXISTS (student_id) REFERENCES student_info(student_id),
        ADD FOREIGN KEY IF NOT EXISTS (registered_by) REFERENCES users(id),
        ADD UNIQUE KEY IF NOT EXISTS unique_attendance (event_id, student_id)";

if ($conn->query($sql) === TRUE) {
    echo "Event attendance table updated successfully\n";
} else {
    echo "Error updating event attendance table: " . $conn->error . "\n";
}

// Drop event_coordinators table if it exists
$sql = "DROP TABLE IF EXISTS event_coordinators";
if ($conn->query($sql) === TRUE) {
    echo "Dropped existing event_coordinators table\n";
} else {
    echo "Error dropping event_coordinators table: " . $conn->error . "\n";
}

// Create event_coordinators table without foreign keys first
$sql = "CREATE TABLE event_coordinators (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id INT UNSIGNED NOT NULL,
    coordinator_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "Event coordinators table created successfully\n";
} else {
    echo "Error creating event coordinators table: " . $conn->error . "\n";
}

// Add foreign keys separately
$sql = "ALTER TABLE event_coordinators
        ADD CONSTRAINT fk_event_id FOREIGN KEY (event_id) REFERENCES event_table(number) ON DELETE CASCADE,
        ADD CONSTRAINT fk_coordinator_id FOREIGN KEY (coordinator_id) REFERENCES users(id) ON DELETE CASCADE";

if ($conn->query($sql) === TRUE) {
    echo "Foreign keys added to event_coordinators table\n";
} else {
    echo "Error adding foreign keys: " . $conn->error . "\n";
}

// Update all existing events to have 'Open' status if they don't have one
$sql = "UPDATE event_table SET event_status = 'Open' WHERE event_status IS NULL";
if ($conn->query($sql) === TRUE) {
    echo "Updated existing events to have Open status\n";
} else {
    echo "Error updating existing events: " . $conn->error . "\n";
}

$conn->close();
?> 