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
<html>
<head>
    <Title>Login</Title>
    <link rel="icon" type="image/x-icon" href="../images-icon/plplogo.png">
    <link rel="stylesheet" href="../styles/login.css">
    <link rel="stylesheet" href="../styles/style1.css">
    <script src="https://kit.fontawesome.com/d78dc5f742.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="title-container">
        <img src="../images-icon/plplogo.png"> <h1> Pamantasan ng Lungsod ng Pasig </h1>
    </div>

<div class="form-box <?= isActiveForm('login', $active_form) ?>" id="login-form">
    <form action="/php/login_register.php" method="POST" onsubmit="return validateIdentifier(this)">
        <div class="input-field">
            <h2>Welcome to PLP's <br> Event Management System</h2>
            <?= showError($errors['login']); ?>
            <div class="input-container">
                <label for="identifier">Student ID or Email</label>
                <input type="text" id="identifier" name="identifier" oninput="formatInput(this)" placeholder="Student ID or Email" required>

                <label for="login-password">Password</label>
                <input type="password" id="login-password" name="password" placeholder="Password" required>
            </div>
            <div class="show-pass">
                <input type="checkbox" onclick="showPass()"> Show Password
            </div>
            <button type="submit" class="login-btn" name="login">Log in</button>
            <p>Don't have an account? <a href="#" onclick="showForm('register-form')">Sign up</a></p>
        </div>
    </form>
</div>


<div class="form-box <?= isActiveForm('register', $active_form) ?>" id="register-form">
    <form action="/php/login_register.php" method="POST" onsubmit="return validateIdentifier(this)">
        <div class="input-field">
            <h2>Please fill in the fields below</h2>
            <?= showError($errors['register']); ?>
            <div class="input-container">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="Full Name" required>

                <label for="student_id">Student ID</label>
                <input type="text" id="student_id" name="student_id" maxlength="8" oninput="formatInput(this)" placeholder="Student ID" required>

                <label for="email">Email</label>
                <input type="text" id="email" name="email" placeholder="Email" required>

                <label for="reg-password">Password</label>
                <input type="password" id="reg-password" name="password" placeholder="Password" required>
            </div>
            <div class="show-pass">
                <input type="checkbox" onclick="showPass()"> Show Passwords
            </div>
            <button type="submit" class="login-btn" name="register">Register</button>
            <p>Already have an account? <a href="#" onclick="showForm('login-form')">Log in</a></p>
        </div>
    </form>
</div>

    <script src="../Javascript/Login-UI.js"></script>
</body>
</html>

<?php session_unset(); ?>
