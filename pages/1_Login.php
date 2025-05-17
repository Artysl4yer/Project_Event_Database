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
    </style>
</head>
<body>
    <!--<div class="title-container">
        <img src="../images-icon/plplogo.png"> <h1> Pamantasan ng Lungsod ng Pasig </h1>
    </div>
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
            <form id="loginForm">
                <div class="input-field">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
            
                <div class="input-field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
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

        <div class="registration-box hidden">
            <h2>Registration Form</h2>
            <p class="subtitle">Please fill in the fields below</p>

            <form id="registrationForm">
                <div class="input-field">
                    <label for="reg-name">Name</label>
                    <input type="text" id="reg-name" name="name" required/>
                </div>

                <div class="input-field">
                    <label for="reg-organization">Organization</label>
                    <select id="reg-organization" name="organization" required>
                        <option value="">Select an organization</option>
                        <option value="CCS">College of Computer Studies</option>
                        <option value="CBA">College of Business and Accountancy</option>
                        <option value="CON">College of Nursing</option>
                        <option value="COE">College of Education</option>
                        <option value="CIHM">College of International Hospitality Management</option>
                        <option value="COA">College of Arts</option>
                    </select>
                </div>

                <div class="input-field">
                    <label for="reg-username">Username</label>
                    <input type="text" id="reg-username" name="username" required/>
                </div>

                <div class="input-field">
                    <label for="reg-password">Password</label>
                    <input type="password" id="reg-password" name="password" required/>
                </div>

                <div class="input-field">
                    <label for="reg-confirm-password">Confirm Password</label>
                    <input type="password" id="reg-confirm-password" name="confirm_password" required/>
                </div>

                <div class="button-group">
                    <button type="button" class="back-btn">Back</button>
                    <button type="submit" class="register-btn">Register</button>
                </div>
                <div id="registerMessage" class="message"></div>
            </form>
        </div>
    </div>
    
        <script>
            $(document).ready(function() {
                $('.register-link').click(function(e) {
                    e.preventDefault();
                    console.log("Register link clicked");
                    $('.loginpage').removeClass('active').addClass('hidden');
                    $('.registration-box').removeClass('hidden').addClass('active');
                });

                $('.back-btn').click(function(e) {
                    e.preventDefault();
                    $('.registration-box').removeClass('active').addClass('hidden');
                    $('.loginpage').removeClass('hidden').addClass('active');
                });

                $('#registrationForm').on('submit', function(e) {
                    e.preventDefault();
                    console.log('Registration form submitted');
                    
                    var formData = {
                        name: $('#reg-name').val(),
                        organization: $('#reg-organization').val(),
                        username: $('#reg-username').val(),
                        password: $('#reg-password').val(),
                        confirm_password: $('#reg-confirm-password').val()
                    };

                    console.log('Form data:', formData);

                    $.ajax({
                        type: 'POST',
                        url: '../php/register.php',
                        data: formData,
                        dataType: 'json',
                        success: function(response) {
                            console.log('Registration response:', response);
                            if (response.success) {
                                $('#registerMessage').html(response.message).removeClass('error').addClass('success');
                                $('#registrationForm')[0].reset();
                                setTimeout(function() {
                                    $('.registration-box').removeClass('active').addClass('hidden');
                                    $('.loginpage').removeClass('hidden').addClass('active');
                                    $('#loginMessage').html('Registration successful! Please login with your new account.').removeClass('error').addClass('success');
                                }, 2000);
                            } else {
                                $('#registerMessage').html(response.message).removeClass('success').addClass('error');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Registration error:', error);
                            $('#registerMessage').html('An error occurred').addClass('error');
                        }
                    });
                });

                $('#loginForm').on('submit', function(e) {
                    e.preventDefault();
                    console.log('Login form submitted');
                    $.ajax({
                        type: 'POST',
                        url: '../php/login.php',
                        data: $(this).serialize(),
                        dataType: 'json',
                        success: function(response) {
                            console.log('Login response:', response);
                            if (response.success) {
                                window.location.href = '4_Event.php';
                            } else {
                                $('#loginMessage').html(response.message).removeClass('success').addClass('error');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Login error:', error);
                            $('#loginMessage').html('An error occurred').addClass('error');
                        }
                    });
                });
            });
        </script>
    </body>
</html>
