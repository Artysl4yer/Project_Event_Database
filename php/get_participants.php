<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'conn.php';

if ($conn === false) {
    die(json_encode(['error' => 'Database connection failed']));
}

// Get event ID from query parameter
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

if ($event_id <= 0) {
    die(json_encode(['error' => 'Invalid event ID']));
}

try {
    // First check if event_id column exists
    $check_column = $conn->query("SHOW COLUMNS FROM participants_table LIKE 'event_id'");
    $has_event_id = $check_column && $check_column->num_rows > 0;

    // Query to get participants for the specific event
    if ($has_event_id) {
        $query = "SELECT 
            p.ID,
            p.Name,
            p.Course,
            p.Section,
            p.Gender,
            p.Age,
            p.Year,
            p.Dept,
            p.registration_date
        FROM participants_table p
        WHERE p.event_id = ?
        ORDER BY p.registration_date DESC";
    } else {
        // Fallback to using number column if event_id doesn't exist
        $query = "SELECT 
            p.ID,
            p.Name,
            p.Course,
            p.Section,
            p.Gender,
            p.Age,
            p.Year,
            p.Dept,
            p.registration_date
        FROM participants_table p
        WHERE p.number = ?
        ORDER BY p.registration_date DESC";
    }

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $event_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $participants = [];

    while ($row = $result->fetch_assoc()) {
        $participants[] = $row;
    }

    echo json_encode($participants);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?> 