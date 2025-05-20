<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'conn.php';

if ($conn === false) {
    die(json_encode(['error' => 'Database connection failed']));
}

// Get JSON data from request
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    die(json_encode(['error' => 'Invalid JSON data']));
}

// Validate required fields
$required_fields = ['name', 'course', 'section', 'gender', 'age', 'year', 'dept', 'event_id'];
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        die(json_encode(['error' => "Missing required field: $field"]));
    }
}

try {
    // First check if event_id column exists
    $check_column = $conn->query("SHOW COLUMNS FROM participants_table LIKE 'event_id'");
    $has_event_id = $check_column && $check_column->num_rows > 0;

    // Prepare the insert query based on table structure
    if ($has_event_id) {
        $query = "INSERT INTO participants_table 
            (Name, Course, Section, Gender, Age, Year, Dept, event_id, registration_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssssi", 
            $data['name'],
            $data['course'],
            $data['section'],
            $data['gender'],
            $data['age'],
            $data['year'],
            $data['dept'],
            $data['event_id']
        );
    } else {
        // Fallback to using number column if event_id doesn't exist
        $query = "INSERT INTO participants_table 
            (Name, Course, Section, Gender, Age, Year, Dept, number, registration_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssssi", 
            $data['name'],
            $data['course'],
            $data['section'],
            $data['gender'],
            $data['age'],
            $data['year'],
            $data['dept'],
            $data['event_id']  // Using event_id as number
        );
    }

    if (!$stmt->execute()) {
        throw new Exception("Failed to add participant: " . $stmt->error);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Participant added successfully',
        'participant_id' => $conn->insert_id
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?> 