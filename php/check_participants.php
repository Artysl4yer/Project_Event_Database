<?php
include 'conn.php';

// Get the event ID from the URL parameter
$event_id = isset($_GET['event_id']) ? $_GET['event_id'] : null;

if ($event_id) {
    // Query to check participants for the specific event
    $sql = "SELECT p.*, e.event_title 
            FROM participants_table p 
            LEFT JOIN event_table e ON p.number = e.number 
            WHERE p.number = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "<h2>Debug Information for Event ID: $event_id</h2>";
    
    if ($result->num_rows > 0) {
        echo "<p>Found " . $result->num_rows . " participants</p>";
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>First Name</th><th>Last Name</th><th>Course</th><th>Section</th><th>Gender</th><th>Age</th><th>Year</th><th>Dept</th><th>Number</th><th>Registration Date</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['ID']) . "</td>";
            echo "<td>" . htmlspecialchars($row['first_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['last_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Course']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Section']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Gender']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Age']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Year']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Dept']) . "</td>";
            echo "<td>" . htmlspecialchars($row['number']) . "</td>";
            echo "<td>" . htmlspecialchars($row['registration_date']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No participants found for this event</p>";
    }
    
    // Also check the event_attendance table
    $sql = "SELECT COUNT(*) as count FROM event_attendance WHERE event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    echo "<h3>Event Attendance Count: " . $row['count'] . "</h3>";
} else {
    echo "Please provide an event_id parameter";
}

$conn->close();
?> 