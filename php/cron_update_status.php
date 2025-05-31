<?php
// Include database connection
require_once 'conn.php';

// Include the status update function
require_once 'update_event_status.php';

// Run the update
$result = updateEventStatuses($conn);

// Log the result
error_log("Cron job executed at " . date('Y-m-d H:i:s') . " - Update result: " . ($result ? "Success" : "Failed"));

// Close the database connection
$conn->close();
?> 