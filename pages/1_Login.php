<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pamantasan ng Lungsod ng Pasig</title>
    <link rel="stylesheet" href="../styles/style4.css">
    <link rel="stylesheet" href="../styles/style1.css">
    <link rel="stylesheet" href="../styles/style6.css">
    <script src="https://kit.fontawesome.com/d78dc5f742.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="title-container">
        <img src="../images-icon/plplogo.png"> <h1> Pamantasan ng Lungsod ng Pasig </h1>
    </div>
    <div class="tab-container">
        <div class="menu-items">
            <a href="4_Event.php" class="active"> <i class="fa-solid fa-home"></i> <span class="label"> Home </span> </a>
            <a href="6_NewEvent.php" class="active"> <i class="fa-solid fa-calendar"></i> <span class="label"> Events </span> </a>
            <a href="" class="active"> <i class="fa-regular fa-circle-user"></i> <span class="label"> Admins </span> </a>
            <a href="" class="active"> <i class="fa-solid fa-address-card"></i> <span class="label"> Participants </span> </a>
            <a href="#About" class="active"> <i class="fa-solid fa-circle-info"></i> <span class="label"> Aboutn </span> </a>
            <a href="" class="active"> <i class="fa-solid fa-bars"></i> <span class="label"> Logs </span> </a>
            <a href="1_Login.php" class="active"> <i class="fa-solid fa-circle-info"></i> <span class="label"> Login </span> </a>
        </div>
        <div class="logout">
            <a href=""> <i class="fa-solid fa-gear"></i> <span class="label"> Logout </span> </a>
        </div>
    </div>
    <div class="login-container">
        <div class="university-logo">
            <i class="fas fa-university"></i>
        </div>
        
        <div class="university-info">
            <h1>PAMANTASAN NG LUNGSOD NG PASIG</h1>
            <p>On March 15, 1999, the Sangguniang Panlungsod ng Pasig passed Ordinance No. 11, Series of 1999, establishing the Pamantasan ng Lungsod ng Pasig, and appropriated funds for its operations.</p>
            <p>The authority of the Sangguniang Panlungsod ng Pasig to establish the Pamantasan in Article III, Sections 447-455, 469 of the Local Government Code of 1991 which allowed institutions to be established and operated by Local Government Units.</p>
            
            <div class="action-links">
                <a href="#" class="action-link">About <i class="fas fa-arrow-right"></i></a>
                <a href="#" class="action-link">Login <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>

        <div class="login-box">
            <h2>EVENT ATTENDANCE SYSTEM</h2>
            <p class="subtitle">Enter your credentials to continue</p>
            
            <form id="loginForm">
                <div class="input-field">
                    <label for="email">Admin:</label>
                    <input type="text" id="email" placeholder="Enter email" required>
                </div>
                
                <div class="input-field">
                    <label for="password">Password:</label>
                    <!-- Password Field with Eye Icon -->
                    <div class="password-container">
                        <input type="password" id="password" placeholder="Enter password" required>
                        <i id="togglePassword" class="fas fa-eye"></i>
                    </div>
                </div>
                
                <!-- Event Code Field (hidden by default) -->
                <div class="input-field" id="eventCodeField" style="display: none;">
                    <label for="eventCode">Event Code:</label>
                    <input type="text" id="eventCode">
                    <small>Please enter the event code provided by your professor</small>
                </div>
                
                <div class="button-group">  
                    <button type="submit" class="login-btn" id="loginBtn">Log in</button>
                </div>
            </form>
            
            
                </div>
            </div>
        </div>
    </div>

    <script src="../javascript/login.js"></script>

    <!-- JavaScript for password toggle -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const passwordField = document.getElementById('password');

            // Toggle password visibility
            togglePassword.addEventListener('click', function() {
                const type = passwordField.type === 'password' ? 'text' : 'password';
                passwordField.type = type;

                // Toggle the eye icon
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        });
    </script>
</body>
</html>
