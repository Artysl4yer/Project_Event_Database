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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get event data
        $event_id = $_POST['event_id'];
        $event_title = $_POST['event_title'];
        $event_description = $_POST['event_description'];
        $event_venue = $_POST['event_venue'];
        $organization = $_POST['organization'];
        $event_date = $_POST['event_date'];
        $event_time = $_POST['event_time'];
        $event_duration = $_POST['event_duration'];
        $registration_deadline = $_POST['registration_deadline'];

        // Create DateTime objects for event timing
        $start_datetime_str = "$event_date $event_time";
        $start_datetime = new DateTime($start_datetime_str);
        
        $event_duration_int = intval($event_duration);
        if ($event_duration_int < 1) {
            throw new Exception('Event duration must be at least 1 hour.');
        }

        $end_datetime = clone $start_datetime;
        $end_datetime->add(new DateInterval('PT' . $event_duration_int . 'H'));
        
        // Format dates for database
        $date_start = $start_datetime->format('Y-m-d');
        $date_end = $end_datetime->format('Y-m-d');
        $event_start = $start_datetime->format('Y-m-d H:i:s');
        $event_end = $end_datetime->format('Y-m-d H:i:s');

        // Update event
        $sql = "UPDATE event_table SET 
                event_title = ?,
                event_description = ?,
                event_location = ?,
                organization = ?,
                date_start = ?,
                event_start = ?,
                date_end = ?,
                event_end = ?,
                registration_deadline = ?
                WHERE number = ?";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }

        $stmt->bind_param(
            "sssssssssi",
            $event_title,
            $event_description,
            $event_venue,
            $organization,
            $date_start,
            $event_start,
            $date_end,
            $event_end,
            $registration_deadline,
            $event_id
        );

        if (!$stmt->execute()) {
            throw new Exception("Failed to update event: " . $stmt->error);
        }

        // Handle image upload if provided
        if (isset($_FILES['event-image']) && $_FILES['event-image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['event-image'];
            $fileName = $file['name'];
            $fileTmpName = $file['tmp_name'];
            $fileSize = $file['size'];
            $fileError = $file['error'];
            
            // Validate file
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            
            if (!in_array($fileExt, $allowedExtensions)) {
                throw new Exception('Only JPG, JPEG & PNG files are allowed');
            }
            if ($fileSize > 5000000) { // 5MB limit
                throw new Exception('File size is too large (max 5MB)');
            }
            
            // Generate unique filename
            $newFileName = uniqid('event_', true) . '.' . $fileExt;
            $uploadPath = '../uploads/events/' . $newFileName;
            
            if (!move_uploaded_file($fileTmpName, $uploadPath)) {
                throw new Exception('Failed to upload image');
            }
            
            // Update database with new image
            $sql = "UPDATE event_table SET event_image = ? WHERE number = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $newFileName, $event_id);
            $stmt->execute();
        }

        // Log the update
        $log_sql = "INSERT INTO event_logs (event_id, action, performed_by, action_time) 
                   VALUES (?, 'updated', ?, NOW())";
        $log_stmt = $conn->prepare($log_sql);
        $log_stmt->bind_param("ii", $event_id, $_SESSION['client_id']);
        $log_stmt->execute();

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Event updated successfully'
        ]);

    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Error updating event: ' . $e->getMessage()
        ]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>