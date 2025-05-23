<?php
session_start();

$errors = [
    'login' => $_SESSION['login_error'] ?? '',
    'register' => $_SESSION['register_error'] ?? ''
];
$active_form = $_SESSION['active_form'] ?? 'login';

function showError($error) {
    return !empty($error) ? "<p class='error-message'>{$error}</p>" : "";
}

function isActiveForm($formName, $activeForm) {
    return $formName === $activeForm ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pamantasan ng Lungsod ng Pasig</title>
    <link rel="stylesheet" href="../styles/style4.css">
    <link rel="stylesheet" href="../styles/style1.css">
    <script src="https://kit.fontawesome.com/d78dc5f742.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../Javascript/login.js" defer></script>
    <style>
        .hidden {
            display: none;
        }
        .active {
            display: block;
        }
        .message {
            margin-top: 10px;
            padding: 10px;
            border-radius: 4px;
        }
        .error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }
        .success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }
        .error-message {
            color: #c62828;
            background-color: #ffebee;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
            border: 1px solid #ef9a9a;
        }
    </style>
</head>
<body>
    <div class="title-container">
        <img src="../images-icon/plplogo.png"> <h1> Pamantasan ng Lungsod ng Pasig </h1>
    </div>

    <div class="login-container">
        <div class="loginpage <?= isActiveForm('login', $active_form) ?>">
            <h1>Welcome to PLP's <br> Event Management System</h1>
            <form id="loginForm" action="../php/login_register.php" method="POST" onsubmit="return false;">
                <?= showError($errors['login']); ?>
                <div class="input-field">
                    <label for="identifier">Student ID or Email</label>
                    <input type="text" id="identifier" name="identifier" oninput="formatInput(this)" placeholder="Student ID or Email" required>
                </div>
            
                <div class="input-field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                </div>

                <div class="show-pass">
                    <input type="checkbox" onclick="showPass()"> Show Password
                </div>

                <div class="button-group">
                    <button type="submit" name="login" class="login-btn">Log in</button>
                </div>
                <div id="loginMessage" class="message"></div>
            </form>

            <div class="signup-prompt">
                Don't have an account? <a href="#" onclick="showForm('register-form')">Sign up</a>
            </div>
        </div>  

        <div class="registration-box <?= isActiveForm('register', $active_form) ?>" id="register-form">
            <h2>Registration Form</h2>
            <p class="subtitle">Please fill in the fields below</p>

            <form id="registrationForm" action="../php/login_register.php" method="POST" onsubmit="return validateIdentifier(this)">
                <?= showError($errors['register']); ?>
                <div class="input-field">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" placeholder="Full Name" required>
                </div>

                <div class="input-field">
                    <label for="student_id">Student ID</label>
                    <input type="text" id="student_id" name="student_id" maxlength="8" oninput="formatInput(this)" placeholder="Student ID" required>
                </div>

                <div class="input-field">
                    <label for="email">Email</label>
                    <input type="text" id="email" name="email" placeholder="Email" required>
                </div>

                <div class="input-field">
                    <label for="reg-password">Password</label>
                    <input type="password" id="reg-password" name="password" placeholder="Password" required>
                </div>

                <div class="show-pass">
                    <input type="checkbox" onclick="showPass()"> Show Password
                </div>

                <div class="button-group">
                    <button type="button" class="back-btn" onclick="showForm('login-form')">Back</button>
                    <button type="submit" name="register" class="register-btn">Register</button>
                </div>
                <div id="registerMessage" class="message"></div>
            </form>
        </div>
    </div>
</body>
</html>
