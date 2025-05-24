<?php
include '../php/conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_id = $_POST['event_id'];
    $event_title = $_POST['event-title'];
    $event_location = $_POST['event-location'];
    $date_start = $_POST['event-date-start'] . ' ' . $_POST['event-time-start'];
    $date_end = $_POST['event-date-end'] . ' ' . $_POST['event-time-end'];
    $organization = $_POST['event-orgs'];
    $event_status = $_POST['event-status'];
    $event_description = $_POST['event-description'];

    // Handle image upload
    $file = null; // Will only update if new image is uploaded
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../images-icon/events/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = uniqid('event_') . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_path)) {
                $file = 'images-icon/events/' . $new_filename;
            }
        }
    }

    // Build SQL query based on whether image is being updated
    if ($file !== null) {
        $sql = "UPDATE event_table SET 
                event_title = ?,
                event_location = ?,
                date_start = ?,
                date_end = ?,
                organization = ?,
                event_status = ?,
                event_description = ?,
                file = ?
                WHERE number = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssi", 
            $event_title,
            $event_location,
            $date_start,
            $date_end,
            $organization,
            $event_status,
            $event_description,
            $file,
            $event_id
        );
    } else {
        $sql = "UPDATE event_table SET 
                event_title = ?,
                event_location = ?,
                date_start = ?,
                date_end = ?,
                organization = ?,
                event_status = ?,
                event_description = ?
                WHERE number = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssi", 
            $event_title,
            $event_location,
            $date_start,
            $date_end,
            $organization,
            $event_status,
            $event_description,
            $event_id
        );
    }

    if ($stmt->execute()) {
        header("Location: ../pages/6_NewEvent.php?success=Event updated successfully");
    } else {
        header("Location: ../pages/6_NewEvent.php?error=Error updating event");
    }
    
    $stmt->close();
    $conn->close();
} else {
    header("Location: ../pages/6_NewEvent.php");
}
?>