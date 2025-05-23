<?php
session_start();

// Check student id and email
if (!isset($_SESSION['email']) || !isset($_SESSION['student_id'])) {
    header("Location: ../pages/1_Login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>PLP: Event</title>
        <link rel="stylesheet" href="/styles/style7.css">
        <link rel="stylesheet" href="/styles/style1.css">
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
                <a href="10_Admin.php" class="active"> <i class="fa-regular fa-circle-user"></i> <span class="label"> Admins </span> </a>
                <a href="7_StudentTable.php" class="active"> <i class="fa-solid fa-address-card"></i> <span class="label"> Participants </span> </a>
                <a href="5_About.php" class="active"> <i class="fa-solid fa-circle-info"></i> <span class="label"> About </span> </a>
                <a href="8_archive.php" class="active"> <i class="fa-solid fa-bars"></i> <span class="label"> Logs </span> </a>
                <a href="1_Login.php" class="active"> <i class="fa-solid fa-circle-info"></i> <span class="label"> Login </span> </a>
            </div>
            <div class="logout">
                <a href="../php/1logout.php" onclick="return confirm('Are you sure you want to logout?');"> <i class="fa-solid fa-gear"></i> <span class="label"> Logout </span> </a>
            </div>
        </div>
        <div class="Top">
            <img src="/images-icon/plm_courtyard.png" alt="plpback" height="100">
            <h1>About</h1>
        </div>
        <div class="Intro">
            <div class="intro-flex">
                <div class="intro-title">
                    <h1><span class="green">Introduction </span> on <br> Event Attendance!</h1>
                </div>
                <div class="intro-text1">
                    <p>Welcome to the official Event Attendance portal of Pamantasan ng Lungsod ng Pasig. This system is designed to efficiently track and manage student and participant attendance across university events.</p>
                </div>
                <div class="intro-text2">
                    <p> It promotes accountability, streamlines record-keeping, and supports PLP's commitment to excellence in student engagement and campus activities.</p>
                </div>
            </div>
        <div class="Traits">
            <div class="par1">
                <div class="logotraits">
                    <img src="/images-icon/regis.png" alt="1">
                </div>
                <div class="texttrait">
                    <h2>Accurate Record</h2>
                    <p>Ensures reliable tracking of participant attendance for all events.</p>
                </div>
            </div>
            <div class="par2">
                <div class="logotraits">
                    <img src="/images-icon/clock.png" alt="1">
                </div>
                <div class="texttrait">
                    <h2>Time Efficiency</h2>
                    <p>Speeds up the check-in process with organized digital logs.</p>
                </div>
            </div>
            <div class="par3">
                <div class="texttrait">
                    <img src="/images-icon/clipboard.png" alt="1">
                </div>
                <div class="traitstxt">
                    <h2>Student Engagement</h2>
                    <p>Encourages active participation in university activities.</p>
                </div>
            </div>
        </div>
            <div>
                <!-- Wag tanggalin -->
            </div>
        </div>
        <!--
        <div class="member-list">
            <div class="mtitle">
                <h1>Team Members</h1>
            </div>
    <p>A dedicated team of PLP students and staff collaboratively manages the Event<br> Attendance System to ensure smooth and efficient event tracking.</p>

            <div class="memberli">
                <div class="member">
                    <div class="mem-img">
                        <img src="/images-icon/members-img/lark.jpg" alt="member"> 
                    </div>
                    <div class="member-info">
                        <h2>Lark</h2>
                        <h3>Role</h3>
                    </div>
                    
                </div>

                <div class="member">
                    <div class="mem-img">
                        <img src="/images-icon/members-img/madi.jpg" alt="member"> 
                    </div>
                    <div class="member-info">
                        <h2>Madi</h2>
                        <h3>Role</h3>
                    </div>
                    
                </div>

                <div class="member">
                    <div class="mem-img">
                        <img src="/images-icon/members-img/darryl.jpg" alt="member"> 
                    </div>
                    <div class="member-info">
                        <h2>Darryl</h2>
                        <h3>Role</h3>
                    </div>
                    
                </div>

                <div class="member">
                    <div class="mem-img">
                        <img src="/images-icon/members-img/alex.jpg" alt="member"> 
                    </div>
                    <div class="member-info">
                        <h2>Alex</h2>
                        <h3>Role</h3>
                    </div>
                    
                </div>

                <div class="member">
                    <div class="mem-img">
                        <img src="/images-icon/members-img/pasion.jpg" alt="member"> 
                    </div>
                    <div class="member-info">
                        <h2>Pasion</h2>
                        <h3>Role</h3>
                    </div>
                    
                </div>
                -->
    <script src="../Javascript/dynamic.js"></script>
    </body>
</html>








        