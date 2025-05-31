<?php
function checkAdminAccess() {
    // Check if user is logged in
    if (!isset($_SESSION['client_id'])) {
        header("Location: ../pages/1_Login.php");
        exit();
    }

    // Check if user is an admin
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../pages/1_Login.php");
        exit();
    }
}
?> 