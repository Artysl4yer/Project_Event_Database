<?php
session_start();
require_once '../php/conn.php';

if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $student_id = str_replace('-', '', $_POST['student_id']);
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    //  email or student_id exists
    $checkEmail = $conn->query("SELECT * FROM users WHERE email = '$email' OR student_id = '$student_id'");
    if ($checkEmail->num_rows > 0) {
        $_SESSION['register_error'] = "Email or Student ID already exists.";
        $_SESSION['active_form'] = 'register';
    } else {
        $conn->query("INSERT INTO users (name, email, student_id, password, role) VALUES ('$name', '$email', '$student_id', '$password', 'student')");
        $_SESSION['register_success'] = "Registration successful! Please login.";
        $_SESSION['active_form'] = 'login';
    }

    header("Location: ../pages/1_Login.php");
    exit();
}

if (isset($_POST['login'])) {
    $identifier = str_replace('-', '', $_POST['identifier']); // student_id or email
    $password = $_POST['password'];

    // Debug log the login attempt
    error_log("Login attempt - Identifier: " . $identifier);

    // check student_id or email
    $stmt = $conn->prepare("SELECT * FROM users WHERE student_id = ? OR email = ?");
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        error_log("User found in database - Email: " . $user['email'] . ", Student ID: " . $user['student_id']);
        
        if (password_verify($password, $user['password'])) {
            error_log("Password verification successful");
            
            // Clear any existing session data
            session_unset();
            
            // Set session variables
            $_SESSION['name'] = $user['name'];
            $_SESSION['student_id'] = $user['student_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'] ?? 'student'; // Use role from database, default to 'student' if not set
            
            // Ensure session is written
            session_write_close();
            
            // Debug output
            error_log("Login successful for user: " . $user['email']);
            error_log("Session role set to: " . $_SESSION['role']);
            
            // Redirect based on role
            if ($_SESSION['role'] === 'coordinator' || $_SESSION['role'] === 'admin') {
                header("Location: ../pages/4_Event.php");
            } else {
                header("Location: ../pages/student-home.php");
            }
            exit();
        } else {
            error_log("Password verification failed for user: " . $user['email']);
            error_log("Provided password: " . $password);
            error_log("Stored hash: " . $user['password']);
        }
    } else {
        error_log("No user found with identifier: " . $identifier);
    }

    $_SESSION['login_error'] = "Invalid Student ID/Email or password.";
    $_SESSION['active_form'] = 'login';
    header("Location: ../pages/1_Login.php");
    exit();
}

?>
