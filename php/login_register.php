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
        $conn->query("INSERT INTO users (name, email, student_id, password) VALUES ('$name', '$email', '$student_id', '$password')");
    }

    header("Location: ../pages/Login_v1.php");
    exit();
}

if (isset($_POST['login'])) {
    $identifier = str_replace('-', '', $_POST['identifier']); // student_id or email
    $password = $_POST['password'];

    // check student_id or email
    $stmt = $conn->prepare("SELECT * FROM users WHERE student_id = ? OR email = ?");
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['name'] = $user['name'];
            $_SESSION['student_id'] = $user['student_id'];
            $_SESSION['email'] = $user['email'];

            if ($user['role'] == 'admin') {
                header("Location: ../pages/admin-home.php");
            } elseif ($user['role'] == 'student') {
                header("Location: ../pages/student-home.php");
            } elseif ($user['role'] == 'coordinator') {
                header("Location: ../pages/4_Event.php");
            }
            exit();
        }
    }

    $_SESSION['login_error'] = "Invalid Student ID/Email or password.";
    $_SESSION['active_form'] = 'login';
    header("Location: ../pages/Login_v1.php");
    exit();
}

?>
