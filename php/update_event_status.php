<?php
// Prevent any output before headers
ob_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/event_status_errors.log');

function updateEventStatuses($conn) {
    try {
        // Get current timestamp
        $now = date('Y-m-d H:i:s');
        error_log("Updating event statuses at: $now");

        // Update events to 'ongoing' if they are scheduled and start time has passed
        $stmt = $conn->prepare("
            UPDATE event_table 
            SET event_status = 'ongoing',
                registration_status = CASE 
                    WHEN auto_close = 'yes' THEN 'closed'
                    ELSE registration_status 
                END
            WHERE event_status = 'scheduled' 
            AND event_start <= ? 
            AND event_end > ?
        ");
        
        if (!$stmt) {
            error_log("Failed to prepare ongoing update: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("ss", $now, $now);
        if (!$stmt->execute()) {
            error_log("Failed to execute ongoing update: " . $stmt->error);
            return false;
        }
        $stmt->close();

        // Update events to 'completed' if end time has passed
        $stmt = $conn->prepare("
            UPDATE event_table 
            SET event_status = 'completed',
                registration_status = 'closed'
            WHERE event_status IN ('scheduled', 'ongoing')
            AND event_end <= ?
        ");
        
        if (!$stmt) {
            error_log("Failed to prepare completed update: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("s", $now);
        if (!$stmt->execute()) {
            error_log("Failed to execute completed update: " . $stmt->error);
            return false;
        }
        $stmt->close();

        error_log("Successfully updated event statuses");
        return true;

    } catch (Exception $e) {
        error_log("Error updating event statuses: " . $e->getMessage());
        return false;
    }
}

// Clear any previous output
ob_clean();

// Run the update
$result = updateEventStatuses($conn);

// Return JSON response
header('Content-Type: application/json');
echo json_encode(['success' => $result]);

// End output buffering and send
ob_end_flush();
?> 