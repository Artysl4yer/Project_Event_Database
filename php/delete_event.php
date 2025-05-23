<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    include 'conn.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
        $delete_id = $_POST['delete_id'];

        $query = "DELETE FROM event_table WHERE number = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $delete_id);

        if ($stmt->execute()) {
            header("Location: ../pages/6_NewEvent.php");
            exit();
        } else {
            echo "Error deleting event: " . $conn->error;
        }

        $stmt->close();
    }

    
?>
