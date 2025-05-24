

<!DOCTYPE html>
<html>
    <head>
        <title>PLP: Event</title>
        <link rel="stylesheet" href="/styles/style7.css">
        <link rel="stylesheet" href="/styles/style1.css">
        <script src="https://kit.fontawesome.com/d78dc5f742.js" crossorigin="anonymous"></script>
        <style>
            .back-button {
                position: fixed;
                top: 20px;
                left: 20px;
                background-color: #104911;
                color: white;
                border: none;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
                transition: all 0.3s ease;
                z-index: 1000;
            }

            .back-button:hover {
                background-color: #0d3a0d;
                transform: scale(1.1);
            }
        </style>
    </head>
    <body>
        <button class="back-button" onclick="history.back()">
            <i class="fas fa-arrow-left"></i>
        </button>
        <div class="title-container">
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
    <script src="../Javascript/dynamic.js"></script>
    </body>
</html>








        