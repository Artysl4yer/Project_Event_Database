<?php
include 'conn.php';

// Create student_info table with fields matching QR code data
$sql = "CREATE TABLE IF NOT EXISTS student_info (
    student_id VARCHAR(20) PRIMARY KEY,  -- Format: 23-00239
    full_name VARCHAR(100) NOT NULL,     -- Format: MADI, JAMERONE O.
    course VARCHAR(50) NOT NULL,         -- Format: BSIT
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Student info table created successfully<br>";
    
    // Create event_attendance table
    $sql2 = "CREATE TABLE IF NOT EXISTS event_attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        event_id INT NOT NULL,
        student_id VARCHAR(20) NOT NULL,
        registered_by INT NOT NULL,
        attendance_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES event_table(number),
        FOREIGN KEY (student_id) REFERENCES student_info(student_id),
        FOREIGN KEY (registered_by) REFERENCES clients(id),
        UNIQUE KEY unique_attendance (event_id, student_id)
    )";
    
    if ($conn->query($sql2) === TRUE) {
        echo "Event attendance table created successfully";
    } else {
        echo "Error creating event attendance table: " . $conn->error;
    }
} else {
    echo "Error creating student info table: " . $conn->error;
}

$conn->close();
?> 