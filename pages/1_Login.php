<?php
session_start();
include '../php/conn.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $conn->prepare('SELECT * FROM users WHERE student_id = ? OR email = ?');
        $stmt->bind_param('ss', $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['client_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                echo json_encode(['success' => true, 'redirect' => '4_Event.php']);
                exit();
            } else {
                header('Location: 4_Event.php');
                exit();
            }
        } else {
            $error = 'Invalid username or password.';
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                echo json_encode(['success' => false, 'message' => $error]);
                exit();
            }
        }
    } else {
        $error = 'Please enter both username and password.';
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode(['success' => false, 'message' => $error]);
            exit();
        }
    }
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
        .login-container {
            position: relative;
        }
        .loginpage {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            transition: opacity 0.5s ease, visibility 0.5s ease;
            opacity: 0;
            visibility: hidden;
            z-index: 1;
            background: green;
            padding: 50px;
            overflow-y: auto;
        }
        .registration-box {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            transition: opacity 0.5s ease, visibility 0.5s ease;
            opacity: 0;
            visibility: hidden;
            z-index: 1;
            background: white;
            padding: 50px;
            overflow-y: auto;
        }
        .loginpage.active,
        .registration-box.active {
            opacity: 1;
            visibility: visible;
            z-index: 2;
        }
    </style>
</head>
<body>
    <div class="title-container">
        <img src="../images-icon/plplogo.png"> <h1> Pamantasan ng Lungsod ng Pasig </h1>
    </div><!--
    <div class="tab-container">
        <div class="menu-items">
            <a href="4_Event.php" class="active"> <i class="fa-solid fa-home"></i> <span class="label"> Home </span> </a>
            <a href="6_NewEvent.php" class="active"> <i class="fa-solid fa-calendar"></i> <span class="label"> Events </span> </a>
            <a href="" class="active"> <i class="fa-regular fa-circle-user"></i> <span class="label"> Admins </span> </a>
            <a href="7_StudentTable.php" class="active"> <i class="fa-solid fa-address-card"></i> <span class="label"> Participants </span> </a>
            <a href="5_About.php" class="active"> <i class="fa-solid fa-circle-info"></i> <span class="label"> About </span> </a>
            <a href="8_archive.php" class="active"> <i class="fa-solid fa-bars"></i> <span class="label"> Logs </span> </a>
            <a href="1_Login.php" class="active"> <i class="fa-solid fa-circle-info"></i> <span class="label"> Login </span> </a>
        </div>
        <div class="logout">
            <a href="../php/logout.php"> <i class="fa-solid fa-gear"></i> <span class="label"> Logout </span> </a>
        </div>
    </div>-->

    
    <div class="login-container">
        <!--<div class="university-info active">
            <h1>PAMANTASAN NG LUNGSOD NG PASIG</h1>
            <p>On March 15, 1999, the Sangguniang Panlungsod ng Pasig passed Ordinance No. 11, Series of 1999, establishing the Pamantasan ng Lungsod ng Pasig, and appropriated funds for its operations.</p>
            <p>The authority of the Sangguniang Panlungsod ng Pasig to establish the Pamantasan in Article III, Sections 447-455, 469 of the Local Government Code of 1991 which allowed institutions to be established and operated by Local Government Units.</p>
            
        </div>-->

        <!--<div class="action-links">
                <a href="#" class="action-link register-link">New Client Register<i class="fas fa-arrow-right"></i></a>
                <a href="#" class="action-link login-link">Login <i class="fas fa-arrow-right"></i></a>
            </div>-->

    <div class="loginpage active">
            <h1>Welcome to PLP's <br> Event Management System</h1>
            <!--<h2>Enter your credentials to continue</h2>-->
            <form id="loginForm" method="POST">
                <div class="input-field">
                    <label for="login-identifier">ID or Email</label>
                    <input type="text" id="login-identifier" name="identifier" placeholder="Student ID or Email" required>
                </div>
            
                <div class="input-field">
                    <label for="login-password">Password</label>
                    <input type="password" id="login-password" name="password" placeholder="Password" required>
                </div>

                <div class="show-pass">
                    <input type="checkbox" onclick="showPass('login-password')"> Show Password
                </div>

                <div class="button-group">
                    <!--<button type="button" class="back-btn">Back</button>-->
                    <button type="submit" class="login-btn">Log in</button>
                </div>
                <div id="loginMessage" class="message"></div>
            </form>

            <div class="signup-prompt">
                Don't have an account? <a href="#" class="register-link">Sign up</a>
            </div>
        </div>  

        <div class="registration-box">
            <h2>Registration Form</h2>
            <p class="subtitle">Please fill in the fields below</p>

            <form id="registrationForm">
                <div class="input-field">
                    <label for="reg-firstname">First Name</label>
                    <input type="text" id="reg-firstname" name="firstname" placeholder="First Name" required/>
                </div>

                <div class="input-field">
                    <label for="reg-lastname">Last Name</label>
                    <input type="text" id="reg-lastname" name="lastname" placeholder="Last Name" required/>
                </div>

                <div class="input-field">
                    <label for="reg-studentid">ID</label>
                    <input type="text" id="reg-studentid" name="student_id" placeholder="e.g., 23-00992" pattern="[0-9]{2}-[0-9]{5}" maxlength="8" required/>
                </div>

                <div class="input-field">
                    <label for="reg-email">Email</label>
                    <input type="email" id="reg-email" name="email" placeholder="Email" required/>
                </div>

                <div class="input-field">
                    <label for="reg-password">Password</label>
                    <input type="password" id="reg-password" name="password" placeholder="Password" required/>
                </div>

                <div class="input-field">
                    <label for="reg-confirm-password">Confirm Password</label>
                    <input type="password" id="reg-confirm-password" name="confirm_password" placeholder="Confirm Password" required/>
                </div>

                <div class="show-pass">
                    <input type="checkbox" onclick="showPass('reg-password');showPass('reg-confirm-password')"> Show Passwords
                </div>

                <div class="button-group">
                    <button type="button" class="back-btn">Back</button>
                    <button type="submit" class="register-btn">Register</button>
                </div>
                <div id="registerMessage" class="message"></div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../Javascript/show-password.js"></script>
    <script>
    // Toggle between login and registration
    $(document).ready(function() {
        $('.register-link').click(function(e) {
            e.preventDefault();
            $('.loginpage').removeClass('active');
            $('.registration-box').addClass('active');
        });
        $('.back-btn').click(function() {
            $('.registration-box').removeClass('active');
            $('.loginpage').addClass('active');
        });

        // Login AJAX
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            $('#loginMessage').removeClass('error success').text('');
            var identifier = $('#login-identifier').val().trim();
            var password = $('#login-password').val();
            if (!identifier || !password) {
                $('#loginMessage').addClass('error').text('Please enter both Student ID/Email and password.');
                return;
            }
            $.ajax({
                url: '../php/login_register.php',
                method: 'POST',
                data: { login: 1, identifier: identifier, password: password },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        $('#loginMessage').removeClass('error').addClass('success').text('Login successful! Redirecting...');
                        setTimeout(function() { window.location.href = res.redirect || '4_Event.php'; }, 1200);
                    } else {
                        $('#loginMessage').removeClass('success').addClass('error').text(res.message || 'Login failed.');
                    }
                },
                error: function() {
                    $('#loginMessage').removeClass('success').addClass('error').text('An error occurred. Please try again.');
                }
            });
        });

        // Registration AJAX with validation
        $('#registrationForm').on('submit', function(e) {
            e.preventDefault();
            $('#registerMessage').removeClass('error success').text('');
            var firstname = $('#reg-firstname').val().trim();
            var lastname = $('#reg-lastname').val().trim();
            var student_id = $('#reg-studentid').val().trim();
            var email = $('#reg-email').val().trim();
            var password = $('#reg-password').val();
            var confirm_password = $('#reg-confirm-password').val();
            // Validate required fields
            if (!firstname || !lastname || !student_id || !email || !password || !confirm_password) {
                $('#registerMessage').addClass('error').text('All fields are required.');
                return;
            }
            // Validate student ID format
            if (!/^\d{2}-\d{5}$/.test(student_id)) {
                $('#registerMessage').addClass('error').text('Student ID must be in format 23-00992.');
                return;
            }
            // Validate email format
            if (!/^\S+@\S+\.\S+$/.test(email)) {
                $('#registerMessage').addClass('error').text('Please enter a valid email address.');
                return;
            }
            // Validate password match
            if (password !== confirm_password) {
                $('#registerMessage').addClass('error').text('Passwords do not match.');
                return;
            }
            // Submit via AJAX
            $.ajax({
                url: '../php/login_register.php',
                method: 'POST',
                data: {
                    register: 1,
                    firstname: firstname,
                    lastname: lastname,
                    student_id: student_id,
                    email: email,
                    password: password
                },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        $('#registerMessage').removeClass('error').addClass('success').text('Registration successful! Please login.');
                        setTimeout(function() {
                            $('.registration-box').removeClass('active');
                            $('.loginpage').addClass('active');
                            $('#registrationForm')[0].reset();
                        }, 1500);
                    } else {
                        $('#registerMessage').removeClass('success').addClass('error').text(res.message || 'Registration failed.');
                    }
                },
                error: function() {
                    $('#registerMessage').removeClass('success').addClass('error').text('An error occurred. Please try again.');
                }
            });
        });

        // Auto-insert dash in Student ID for registration
        $('#reg-studentid').on('input', function(e) {
            let value = this.value.replace(/[^0-9]/g, '');
            if (value.length > 2) {
                value = value.substring(0, 2) + '-' + value.substring(2, 7);
            }
            this.value = value;
        });
        // Auto-insert dash in Student ID for login (only if input is all digits and not an email)
        $('#login-identifier').on('input', function(e) {
            // Only auto-format if input is all digits (no non-digit except dash)
            let raw = this.value.replace(/-/g, '');
            if (/^\d+$/.test(raw) && raw.length > 2) {
                let value = raw.substring(0, 2) + '-' + raw.substring(2, 7);
                this.value = value;
            }
            // If input contains any non-digit (like @), do not auto-format
        });
    });
    // Show/hide password utility
    function showPass(id) {
        var input = document.getElementById(id);
        if (input.type === 'password') {
            input.type = 'text';
        } else {
            input.type = 'password';
        }
    }
    </script>
    </body>
</html>
