<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pamantasan ng Lungsod ng Pasig</title>
    <link rel="stylesheet" href="../styles/style4.css">
    <link rel="stylesheet" href="../styles/style1.css">
    <script src="https://kit.fontawesome.com/d78dc5f742.js" crossorigin="anonymous"></script>
   
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
            <a href="5_About.php" class="active"> <i class="fa-solid fa-circle-info"></i> <span class="label"> About </span> </a>
            <a href="8_archive.php" class="active"> <i class="fa-solid fa-bars"></i> <span class="label"> Logs </span> </a>
            <a href="1_Login.php" class="active"> <i class="fa-solid fa-circle-info"></i> <span class="label"> Login </span> </a>
        </div>
        <div class="logout">
            <a href=""> <i class="fa-solid fa-gear"></i> <span class="label"> Logout </span> </a>
        </div>
    </div>
    <div class="login-container">
        
        <div class="university-info">
            <h1>PAMANTASAN NG LUNGSOD NG PASIG</h1>
            <p>On March 15, 1999, the Sangguniang Panlungsod ng Pasig passed Ordinance No. 11, Series of 1999, establishing the Pamantasan ng Lungsod ng Pasig, and appropriated funds for its operations.</p>
            <p>The authority of the Sangguniang Panlungsod ng Pasig to establish the Pamantasan in Article III, Sections 447-455, 469 of the Local Government Code of 1991 which allowed institutions to be established and operated by Local Government Units.</p>
            
                <div class="action-links">
                    <a href="#" class="action-link register-link">Register <i class="fas fa-arrow-right"></i></a>
                    <a href="#" class="action-link login-link">Login <i class="fas fa-arrow-right"></i></a>
                </div>

                <div class="login-box">
                    <h2>PLP: Event</h2>
                    <p class="subtitle">Enter your credentials to continue</p>
            
                    <form id="loginForm">
                        <div class="input-field">
                            <label for="email">Email:</label>
                            <input type="text" id="email" name="email" placeholder="Enter email" required>
                        </div>
                    
                        <div class="input-field">
                            <label for="password">Password:</label>
                            <input type="password" id="password" name="password" placeholder="Enter Password" required>
                        </div>
                    
                        <div class="button-group">
                            <button type="button" class="back-btn">Back</button>
                            <button type="submit" class="login-btn" id="loginBtn">Log in</button>
                        </div>
                    </form>
                </div>

                <div class="registration-box">
                   <h2>Register</h2>
                    <p class="subtitle">Fill in the form below.</p>

  <div class="input-field">
    <label for="reg-username">Username</label>
    <input type="text" id="reg-username" />
  </div>

  <div class="input-field">
    <label for="reg-email">Email</label>
    <input type="email" id="reg-email" />
  </div>

  <div class="input-field">
    <label for="reg-password">Password</label>
    <input type="password" id="reg-password" />
  </div>

  <div class="button-group">
    <button class="back-btn">Back</button>
    <button class="login-btn">Register</button>
  </div>
                </div>
        </div>
    </div>
    <script src="../javascript/login.js"></script>
    <script src="../Javascript/dynamic.js"></script>
</body>
</html>
