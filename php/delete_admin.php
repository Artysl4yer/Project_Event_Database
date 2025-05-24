<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_id'])) {
    $admin_id = $_POST['admin_id'];

    $query = "DELETE FROM clients WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $admin_id);

    if ($stmt->execute()) {
        header("Location: ../pages/10_Admin.php");
        exit();
    } else {
        echo "Error deleting admin: " . $conn->error;
    }

    $stmt->close();
}
?>
