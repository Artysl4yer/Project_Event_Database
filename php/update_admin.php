<?php
session_start();
include 'conn.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = $_POST['admin_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($admin_id) || empty($name) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'All fields except password are required.']);
        exit;
    }

    if (!empty($password) && $password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
        exit;
    }

    // Check for duplicate email (excluding current admin)
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $admin_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists.']);
        exit;
    }
    $stmt->close();

    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, password=? WHERE id=? AND role='admin'");
        $stmt->bind_param("sssi", $name, $email, $hashed_password, $admin_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name=?, email=? WHERE id=? AND role='admin'");
        $stmt->bind_param("ssi", $name, $email, $admin_id);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Admin updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed.']);
    }
    $stmt->close();
    $conn->close();
    exit;
}
echo json_encode(['success' => false, 'message' => 'Invalid request.']); 