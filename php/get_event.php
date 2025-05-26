<?php
session_start();
require_once 'config.php';
require_once 'conn.php';

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['email'], $_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'coordinator'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if (isset($_GET['id'])) {
    try {
        $event_id = $_GET['id'];
        
        // Get event data
        $sql = "SELECT * FROM event_table WHERE number = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $event_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to fetch event: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $event = $result->fetch_assoc();
        
        if ($event) {
            // Calculate duration in hours
            $start = new DateTime($event['event_start']);
            $end = new DateTime($event['event_end']);
            $duration = $end->diff($start);
            $event['event_duration'] = $duration->h + ($duration->days * 24);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'event' => $event
            ]);
        } else {
            throw new Exception("Event not found");
        }
        
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching event: ' . $e->getMessage()
        ]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Event ID not provided'
    ]);
}
?> 