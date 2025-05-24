<?php
include 'conn.php';

// Create students table
$sql = "CREATE TABLE IF NOT EXISTS students (
    student_id VARCHAR(20) PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    course VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Students table created successfully<br>";
    
    // Create event_attendance table to track attendance
    $sql2 = "CREATE TABLE IF NOT EXISTS event_attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        event_id INT NOT NULL,
        student_id VARCHAR(20) NOT NULL,
        attendance_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES events(id),
        FOREIGN KEY (student_id) REFERENCES students(student_id),
        UNIQUE KEY unique_attendance (event_id, student_id)
    )";
    
    if ($conn->query($sql2) === TRUE) {
        echo "Event attendance table created successfully";
    } else {
        echo "Error creating event attendance table: " . $conn->error;
    }
} else {
    echo "Error creating students table: " . $conn->error;
}

$conn->close();
?> 