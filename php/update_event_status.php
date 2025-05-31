<?php
// Prevent any output before headers
ob_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/event_status_errors.log');

function deleteCompletedEvents($conn) {
    try {
        // Get current timestamp
        $now = date('Y-m-d H:i:s');
        error_log("Checking for events to delete at: $now");

        // First, identify events to delete
        $stmt = $conn->prepare("
            SELECT number FROM event_table 
            WHERE event_status = 'completed' 
            AND event_end <= DATE_SUB(?, INTERVAL 24 HOUR)
        ");
        
        if (!$stmt) {
            error_log("Failed to prepare select statement: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("s", $now);
        if (!$stmt->execute()) {
            error_log("Failed to execute select statement: " . $stmt->error);
            return false;
        }
        
        $result = $stmt->get_result();
        $event_ids = [];
        while ($row = $result->fetch_assoc()) {
            $event_ids[] = $row['number'];
        }
        $stmt->close();
        
        if (empty($event_ids)) {
            error_log("No completed events to delete");
            return true;
        }

        // Delete from archive_table first (foreign key constraint)
        $ids_string = implode(',', $event_ids);
        $archive_delete = $conn->query("DELETE FROM archive_table WHERE number IN ($ids_string)");
        if (!$archive_delete) {
            error_log("Failed to delete from archive_table: " . $conn->error);
            return false;
        }

        // Then delete from event_table
        $event_delete = $conn->query("DELETE FROM event_table WHERE number IN ($ids_string)");
        if (!$event_delete) {
            error_log("Failed to delete from event_table: " . $conn->error);
            return false;
        }

        $deleted_count = count($event_ids);
        error_log("Deleted $deleted_count completed events");

        return true;
    } catch (Exception $e) {
        error_log("Error deleting completed events: " . $e->getMessage());
        return false;
    }
}

function updateEventStatuses($conn) {
    try {
        // Get current timestamp
        $now = date('Y-m-d H:i:s');
        error_log("Updating event statuses at: $now");

        // First, update registration status based on deadline in both tables
        $tables = ['event_table', 'archive_table'];
        foreach ($tables as $table) {
            $stmt = $conn->prepare("
                UPDATE $table 
                SET registration_status = CASE
                    WHEN registration_deadline IS NOT NULL AND registration_deadline <= ? THEN 'closed'
                    WHEN registration_deadline IS NOT NULL AND registration_deadline > ? AND registration_status != 'closed' THEN 'open'
                    ELSE registration_status
                END
                WHERE event_status IN ('scheduled', 'ongoing')
            ");
            
            if (!$stmt) {
                error_log("Failed to prepare registration deadline update for $table: " . $conn->error);
                return false;
            }
            
            $stmt->bind_param("ss", $now, $now);
            if (!$stmt->execute()) {
                error_log("Failed to execute registration deadline update for $table: " . $stmt->error);
                return false;
            }
            $stmt->close();
        }

        // Update events to 'ongoing' if they are scheduled and start time has passed
        foreach ($tables as $table) {
            $stmt = $conn->prepare("
                UPDATE $table 
                SET event_status = 'ongoing'
                WHERE event_status = 'scheduled' 
                AND event_start <= ? 
                AND event_end > ?
            ");
            
            if (!$stmt) {
                error_log("Failed to prepare ongoing update for $table: " . $conn->error);
                return false;
            }
            
            $stmt->bind_param("ss", $now, $now);
            if (!$stmt->execute()) {
                error_log("Failed to execute ongoing update for $table: " . $stmt->error);
                return false;
            }
            $stmt->close();
        }

        // Update events to 'completed' if end time has passed
        foreach ($tables as $table) {
            $stmt = $conn->prepare("
                UPDATE $table 
                SET event_status = 'completed',
                    registration_status = 'closed'
                WHERE event_status IN ('scheduled', 'ongoing')
                AND event_end <= ?
            ");
            
            if (!$stmt) {
                error_log("Failed to prepare completed update for $table: " . $conn->error);
                return false;
            }
            
            $stmt->bind_param("s", $now);
            if (!$stmt->execute()) {
                error_log("Failed to execute completed update for $table: " . $stmt->error);
                return false;
            }
            $stmt->close();
        }

        // Delete completed events that are older than 24 hours
        deleteCompletedEvents($conn);

        error_log("Successfully updated event statuses in all tables");
        return true;

    } catch (Exception $e) {
        error_log("Error updating event statuses: " . $e->getMessage());
        return false;
    }
}

// The following block should only run if the script is called directly, not when included.
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    require_once 'conn.php';
    
    header('Content-Type: application/json');
    
    try {
        if (updateEventStatuses($conn)) {
            echo json_encode(['success' => true, 'message' => 'Event statuses updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update event statuses']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'An unexpected error occurred: ' . $e->getMessage()]);
    } finally {
        if (isset($conn) && $conn instanceof mysqli) {
            $conn->close();
        }
    }
}
?> 