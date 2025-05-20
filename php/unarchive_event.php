<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    include '../php/conn.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unarchive'])) {
        $unarchive_id = $_POST['unarchive_id'];

        // First, get the event data from archive
        $select_query = "SELECT * FROM archive_table WHERE number = ?";
        $select_stmt = $conn->prepare($select_query);
        $select_stmt->bind_param("i", $unarchive_id);
        $select_stmt->execute();
        $result = $select_stmt->get_result();
        $event_data = $result->fetch_assoc();
        $select_stmt->close();

        if ($event_data) {
            // Insert back into event table
            $insert_query = "INSERT INTO event_table (
                event_title, event_code, event_location, 
                date_start, event_start, date_end, event_end, 
                event_description, organization, event_status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("ssssssssss", 
                $event_data['event_title'],
                $event_data['event_code'],
                $event_data['event_location'],
                $event_data['date_start'],
                $event_data['event_start'],
                $event_data['date_end'],
                $event_data['event_end'],
                $event_data['event_description'],
                $event_data['organization'],
                $event_data['event_status']
            );
            
            if ($insert_stmt->execute()) {
                // Delete from archive table
                $delete_query = "DELETE FROM archive_table WHERE number = ?";
                $delete_stmt = $conn->prepare($delete_query);
                $delete_stmt->bind_param("i", $unarchive_id);

                if ($delete_stmt->execute()) {
                    header("Location: ../pages/8_archive.php");
                    exit();
                } else {
                    echo "Error removing from archive: " . $conn->error;
                }
                $delete_stmt->close();
            } else {
                echo "Error unarchiving event: " . $conn->error;
            }
            $insert_stmt->close();
        } else {
            echo "Event not found in archive";
        }
    }
?>