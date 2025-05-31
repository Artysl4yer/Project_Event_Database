<?php
session_start();
$role = $_SESSION['role'] ?? null;
$page = basename($_SERVER['PHP_SELF']);
$coordinator_allowed = [
    '4_Event.php',
    '5_About.php',
    '6_NewEvent.php',
    '8_archive.php',
    '11_Attendance.php'
];
if (!$role) {
    header("Location: 1_Login.php");
    exit();
}
if ($role === 'admin') {
    // allow
} elseif ($role === 'coordinator') {
    if (!in_array($page, $coordinator_allowed)) {
        header("Location: 4_Event.php");
        exit();
    }
} else {
    header("Location: 1_Login.php");
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
        <style>
            .go-back-btn {
                background: #17692d;
                color: #fff;
                border: none;
                border-radius: 6px;
                padding: 10px 24px;
                font-size: 1rem;
                font-weight: 500;
                cursor: pointer;
                margin-right: 18px;
                transition: background 0.2s, box-shadow 0.2s;
                box-shadow: 0 2px 8px rgba(23,105,45,0.08);
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }
            .go-back-btn:hover {
                background: #145c26;
                box-shadow: 0 4px 16px rgba(23,105,45,0.15);
            }
        </style>
    </head>
    <body>
       <div class="title-container">
            <button class="go-back-btn" onclick="history.back()"><i class="fa fa-arrow-left"></i> Go Back</button>

            <img src="../images-icon/plplogo.png"> <h1> Pamantasan ng Lungsod ng Pasig </h1>
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








        