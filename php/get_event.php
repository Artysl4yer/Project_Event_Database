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
            // Format the response data
            $response = [
                'number' => $event['number'],
                'event_title' => $event['event_title'],
                'event_code' => $event['event_code'],
                'event_description' => $event['event_description'],
                'event_location' => $event['event_location'],
                'organization' => $event['organization'],
                'event_start' => $event['event_start'],
                'event_end' => $event['event_end'],
                'registration_deadline' => $event['registration_deadline'],
                'registration_status' => $event['registration_status'],
                'event_status' => $event['event_status']
            ];

            // Calculate duration in hours
            $start = new DateTime($event['event_start']);
            $end = new DateTime($event['event_end']);
            $duration = $end->diff($start);
            $response['duration'] = $duration->h + ($duration->days * 24);
            
            // Handle event image
            if (!empty($event['event_image'])) {
                $response['event_image'] = '../uploads/events/' . $event['event_image'];
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $response
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