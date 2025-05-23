<?php
session_start();
require_once '../php/conn.php';

// Set header to return JSON
header('Content-Type: application/json');

if (isset($_POST['register']) && ($_POST['register'] === '1' || $_POST['register'] === 'Register')) {
    // Validate required fields
    $required_fields = ['name', 'student_id', 'email', 'password'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']);
            exit();
        }
    }

    $name = $_POST['name'];
    $student_id = str_replace('-', '', $_POST['student_id']);
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        // Check database connection first
        if ($conn->connect_error) {
            echo json_encode(['success' => false, 'message' => 'Database connection failed. Please try again later.']);
            exit();
        }

        // Check if email or student_id exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR student_id = ?");
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'An error occurred during registration. Please try again.']);
            exit();
        }

        $stmt->bind_param("ss", $email, $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Email or Student ID already exists.']);
            exit();
        }

        // Insert the new user
        $stmt = $conn->prepare("INSERT INTO users (name, email, student_id, password, role) VALUES (?, ?, ?, ?, 'student')");
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'An error occurred during registration. Please try again.']);
            exit();
        }

        $stmt->bind_param("ssss", $name, $email, $student_id, $password);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Registration successful! Please login.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $stmt->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
    }
    exit();
}

if (isset($_POST['login']) || isset($_POST['identifier'])) {
    $identifier = str_replace('-', '', $_POST['identifier']); // student_id or email
    $password = $_POST['password'];

    try {
        // Check database connection first
        if ($conn->connect_error) {
            echo json_encode(['success' => false, 'message' => 'Database connection failed. Please try again later.']);
            exit();
        }

        // check student_id or email
        $stmt = $conn->prepare("SELECT * FROM users WHERE student_id = ? OR email = ?");
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'An error occurred during login. Please try again.']);
            exit();
        }

        $stmt->bind_param("ss", $identifier, $identifier);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['name'] = $user['name'];
                $_SESSION['student_id'] = $user['student_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                $redirect = '';
                if ($user['role'] == 'admin') {
                    $redirect = '../pages/admin-home.php';
                } elseif ($user['role'] == 'student') {
                    $redirect = '../pages/student-home.php';
                } elseif ($user['role'] == 'coordinator') {
                    $redirect = '../pages/4_Event.php';
                }
                
                echo json_encode(['success' => true, 'redirect' => $redirect]);
                exit();
            }
        }

        echo json_encode(['success' => false, 'message' => 'Invalid Student ID/Email or password.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
    }
    exit();
}

// If no valid action was specified
echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>
