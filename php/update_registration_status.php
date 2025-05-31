<?php
session_start();
require_once 'conn.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start output buffering
ob_start();

// Function to send JSON response
function sendResponse($success, $message) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['client_id']) || !in_array($_SESSION['role'], ['admin', 'coordinator'])) {
    sendResponse(false, 'Unauthorized access');
}

// Check required parameters
if (!isset($_POST['event_id']) || !isset($_POST['status'])) {
    sendResponse(false, 'Missing required parameters');
}

$event_id = intval($_POST['event_id']);
$status = $_POST['status'];

// Validate status
if (!in_array($status, ['open', 'closed'])) {
    sendResponse(false, 'Invalid status value');
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Check if user has permission for this event
    $stmt = $conn->prepare("
        SELECT e.number, e.event_status, e.registration_status 
        FROM event_table e
        LEFT JOIN event_coordinators ec ON e.number = ec.event_id
        WHERE e.number = ? AND (
            ? = 'admin' OR 
            ec.coordinator_id = ?
        )
    ");
    $stmt->bind_param("isi", $event_id, $_SESSION['role'], $_SESSION['client_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('You do not have permission to modify this event');
    }

    $event = $result->fetch_assoc();

    // Check if event is in a valid state for registration changes
    // Allow admin to modify registration for any event status
    // Restrict coordinators from modifying completed/cancelled/archived events
    if ($_SESSION['role'] !== 'admin' && in_array($event['event_status'], ['completed', 'cancelled', 'archived'])) {
        throw new Exception('Cannot modify registration for ' . $event['event_status'] . ' events');
    }

    // If admin is opening registration for a completed event, reset the event status and attendance
    if ($_SESSION['role'] === 'admin' && $status === 'open' && $event['event_status'] === 'completed') {
        // Reset event status to scheduled in event_table
        $stmt = $conn->prepare("
            UPDATE event_table 
            SET event_status = 'scheduled',
                registration_status = ?,
                last_status_update = CURRENT_TIMESTAMP
            WHERE number = ?
        ");
        $stmt->bind_param("si", $status, $event_id);
        if (!$stmt->execute()) {
            throw new Exception('Error updating event status');
        }

        // Reset event status to scheduled in archive_table as well
        $stmt = $conn->prepare("
            UPDATE archive_table 
            SET event_status = 'scheduled',
                registration_status = ?,
                last_status_update = CURRENT_TIMESTAMP
            WHERE number = ?
        ");
        $stmt->bind_param("si", $status, $event_id);
        $stmt->execute();

        // Clear attendance records for this event
        $stmt = $conn->prepare("DELETE FROM event_attendance WHERE event_id = ?");
        $stmt->bind_param("i", $event_id);
        if (!$stmt->execute()) {
            throw new Exception('Error clearing attendance records');
        }

        // Log the status change with additional details
        $stmt = $conn->prepare("
            INSERT INTO event_logs (
                event_id, 
                action, 
                performed_by,
                action_time 
            ) VALUES (?, 'event_reset_and_registration_open', ?, CURRENT_TIMESTAMP)
        ");
        $details = "Event reset to scheduled and registration opened by admin";
        $stmt->bind_param("is", $event_id, $_SESSION['client_id']);
        $stmt->execute();

    } else {
        // Normal registration status update
        $stmt = $conn->prepare("
            UPDATE event_table 
            SET registration_status = ?,
                last_status_update = CURRENT_TIMESTAMP
            WHERE number = ?
        ");
        $stmt->bind_param("si", $status, $event_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Error updating registration status');
        }

        // Log the status change
        $stmt = $conn->prepare("
            INSERT INTO event_logs (
                event_id, 
                action, 
                performed_by,
                action_time 
            ) VALUES (?, 'registration_status_change', ?, CURRENT_TIMESTAMP)
        ");
        $details = "Registration status changed to " . $status;
        $stmt->bind_param("is", $event_id, $_SESSION['client_id']);
        $stmt->execute();
    }

    // Commit transaction
    $conn->commit();
    sendResponse(true, 'Registration status updated successfully');

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    error_log("Registration Status Update Error: " . $e->getMessage());
    sendResponse(false, $e->getMessage());
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?> 