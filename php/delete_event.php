<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    include '../php/conn.php'; // Ensure this path is correct and conn.php establishes $conn

    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => 'An unknown error occurred.'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
        $delete_id = intval($_POST['delete_id']);

        if ($delete_id > 0) {
            $query = "DELETE FROM event_table WHERE number = ?";
            $stmt = $conn->prepare($query);

            if ($stmt) {
                $stmt->bind_param("i", $delete_id);

                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        $response = ['success' => true, 'message' => 'Event deleted successfully.'];
                    } else {
                        $response['message'] = 'Event not found or already deleted.';
                        // Still consider it a "success" in terms of the operation completing without a DB error
                        // but the JS might want to know no rows were affected.
                        // For simplicity, we'll keep success as true if execute didn't fail.
                        // $response = ['success' => false, 'message' => 'Event not found or already deleted.'];
                    }
                } else {
                    $response['message'] = "Error executing deletion: " . $stmt->error;
                    error_log("Error deleting event (execute): " . $stmt->error . " for ID: " . $delete_id);
                }
                $stmt->close();
            } else {
                $response['message'] = "Error preparing statement: " . $conn->error;
                error_log("Error deleting event (prepare): " . $conn->error);
            }
        } else {
            $response['message'] = 'Invalid Event ID provided.';
        }
    } else {
        $response['message'] = 'Invalid request method or missing delete_id.';
    }

    $conn->close();
    echo json_encode($response);
    exit();
?>
