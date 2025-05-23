<?php
require_once 'conn.php';

// Get the user's email from the session
session_start();
if (!isset($_SESSION['email'])) {
    die("No user logged in");
}

$email = $_SESSION['email'];

// Update the user's role to coordinator
$stmt = $conn->prepare("UPDATE users SET role = 'coordinator' WHERE email = ?");
$stmt->bind_param("s", $email);

if ($stmt->execute()) {
    echo "Role updated successfully to coordinator for user: " . htmlspecialchars($email);
    
    // Update session role
    $_SESSION['role'] = 'coordinator';
    
    // Redirect to 4_Event.php
    header("Location: ../pages/4_Event.php");
} else {
    echo "Error updating role: " . $conn->error;
}

$stmt->close();
$conn->close();
?> 