<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $conn = new mysqli("localhost", "root", "", "event_database");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM event_table WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: 6_NewEvent.php?deleted=1");
        exit();
    } else {
        echo "Error deleting event: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
