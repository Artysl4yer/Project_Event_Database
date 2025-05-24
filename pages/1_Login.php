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
        .error-message {
            color: #c62828;
            margin: 5px 0;
        }

        /* Loading Screen Styles */
        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(46, 125, 50, 0.95);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            flex-direction: column;
        }

        .loading-content {
            text-align: center;
            color: white;
        }

        .loading-logo {
            width: 150px;
            height: 150px;
            margin-bottom: 20px;
            animation: pulse 1.5s infinite;
        }

        .loading-text {
            font-size: 24px;
            margin-top: 20px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #104911;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading-screen">
        <div class="loading-content">
            <img src="../images-icon/plplogo.png" alt="PLP Logo" class="loading-logo">
            <div class="loading-spinner"></div>
            <div class="loading-text">Logging in...</div>
        </div>
    </div>

    <div class="title-container">
        <img src="../images-icon/plplogo.png"> <h1> Pamantasan ng Lungsod ng Pasig </h1>
    </div>
    
    <div class="login-container">
        <div class="loginpage active">
            <h1>Welcome to PLP's <br> Event Management System</h1>
            <form id="loginForm" action="../php/login_register.php" method="POST">
                <?= showError($errors['login']); ?>
                <div class="input-field">
                    <label for="identifier">Student ID or Email</label>
                    <input type="text" id="identifier" name="identifier" placeholder="Student ID (XX-XXXXX) or Email" required>
                </div>
            
                <div class="input-field">
                    <label for="login-password">Password</label>
                    <input type="password" id="login-password" name="password" placeholder="Password" required>
                </div>

                <div class="show-pass">
                    <input type="checkbox" onclick="showPass()"> Show Password
                </div>

                <div class="button-group">
                    <button type="submit" class="login-btn" name="login" value="1">Log in</button>
                </div>
                <div id="loginMessage" class="message"></div>
            </form>

            <div class="signup-prompt">
                Don't have an account? <a href="#" class="register-link">Sign up</a>
            </div>
        </div>  

        <div class="registration-box hidden">
            <h2>Create Your Account</h2>
            <p class="subtitle">Fill in your details to register</p>

            <form id="registrationForm" action="../php/login_register.php" method="POST">
                <?= showError($errors['register']); ?>
                <div class="input-field">
                    <label for="firstname">First Name</label>
                    <input type="text" id="firstname" name="firstname" placeholder="First Name" required>
                </div>

                <div class="input-field">
                    <label for="lastname">Last Name</label>
                    <input type="text" id="lastname" name="lastname" placeholder="Last Name" required>
                </div>

                <div class="input-field">
                    <label for="student_id">Student ID</label>
                    <input type="text" id="student_id" name="student_id" maxlength="8" 
                    pattern="\d{2}-\d{5}" 
                    title="Please enter a valid Student ID in XX-XXXXX format" 
                    placeholder="Student ID (XX-XXXXX)" 
                    required>
                </div>

                <div class="input-field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Email" required>
                </div>

                <div class="input-field">
                    <label for="reg-password">Password</label>
                    <input type="password" id="reg-password" name="password" placeholder="Password" required>
                </div>

                <div class="show-pass">
                    <input type="checkbox" onclick="showPass()"> Show Passwords
                </div>

                <div class="button-group">
                    <button type="button" class="back-btn">Back to Login</button>
                    <button type="submit" class="register-btn" name="register" value="1">Create Account</button>
                </div>
                <div id="registerMessage" class="message"></div>
            </form>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // Format student ID input
        function formatStudentId(input) {
            // Remove any non-digit characters
            let value = input.value.replace(/\D/g, '');
            
            // Ensure maximum of 7 digits
            if (value.length > 7) {
                value = value.substr(0, 7);
            }
            
            // Format as XX-XXXXX
            if (value.length > 2) {
                value = value.substr(0, 2) + '-' + value.substr(2);
            }
            
            input.value = value;
        }

        // Apply formatting to student ID fields
        $('#student_id, #identifier').on('input', function() {
            formatStudentId(this);
        });

        // Toggle between login and registration forms
        $('.register-link').click(function(e) {
            e.preventDefault();
            $('.loginpage').removeClass('active').addClass('hidden');
            $('.registration-box').removeClass('hidden').addClass('active');
        });

        $('.back-btn').click(function(e) {
            e.preventDefault();
            $('.registration-box').removeClass('active').addClass('hidden');
            $('.loginpage').removeClass('hidden').addClass('active');
        });

        // Show/hide password
        $('.show-pass input').change(function() {
            var passwordField = $(this).closest('form').find('input[type="password"]');
            passwordField.attr('type', this.checked ? 'text' : 'password');
        });

        // Handle login form submission
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            
            var formData = $(this).serialize();
            formData += '&login=1';
            
            $('.loading-screen').css('display', 'flex').hide().fadeIn(300);
            
            $.ajax({
                type: 'POST',
                url: '../php/login_register.php',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    try {
                        if (response.success) {
                            setTimeout(function() {
                                $('.loading-screen').fadeOut(300, function() {
                                    if (response.redirect) {
                                        window.location.href = response.redirect;
                                    }
                                });
                            }, 2000);
                        } else {
                            $('.loading-screen').fadeOut(300);
                            $('#loginMessage')
                                .removeClass('success')
                                .addClass('error')
                                .text(response.message)
                                .show();
                        }
                    } catch (e) {
                        $('.loading-screen').fadeOut(300);
                        $('#loginMessage')
                            .removeClass('success')
                            .addClass('error')
                            .text('An unexpected error occurred. Please try again.')
                            .show();
                    }
                },
                error: function(xhr, status, error) {
                    $('.loading-screen').fadeOut(300);
                    $('#loginMessage')
                        .removeClass('success')
                        .addClass('error')
                        .text('Server error: ' + (error || 'Unknown error occurred'))
                        .show();
                }
            });
        });

        // Handle registration form submission
        $('#registrationForm').on('submit', function(e) {
            e.preventDefault();
            
            var formData = $(this).serialize();
            formData += '&register=1';
            
            $.ajax({
                type: 'POST',
                url: '../php/login_register.php',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    try {
                        if (response.success) {
                            $('#registerMessage')
                                .removeClass('error')
                                .addClass('success')
                                .text(response.message)
                                .show();
                            
                            setTimeout(function() {
                                window.location.href = '1_Login.php';
                            }, 2000);
                        } else {
                            $('#registerMessage')
                                .removeClass('success')
                                .addClass('error')
                                .text(response.message)
                                .show();
                        }
                    } catch (e) {
                        $('#registerMessage')
                            .removeClass('success')
                            .addClass('error')
                            .text('An unexpected error occurred. Please try again.')
                            .show();
                    }
                },
                error: function(xhr, status, error) {
                    $('#registerMessage')
                        .removeClass('success')
                        .addClass('error')
                        .text('Server error: ' + (error || 'Unknown error occurred'))
                        .show();
                }
            });
        });
    });
    </script>
</body>
</html>

<?php session_unset(); ?> 