<?php
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['email'], $_SESSION['student_id'], $_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../pages/1_Login.php");
    exit();
}

include '../php/conn.php';

$student_id = $_SESSION['student_id'];
$event_id = isset($_GET['event']) ? $_GET['event'] : null;

// Get event details if event_id is provided
$event_details = null;
if ($event_id) {
    $stmt = $conn->prepare("SELECT * FROM event_table WHERE number = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event_details = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Attendance</title>
    <link rel="stylesheet" href="../styles/style1.css">
    <link rel="stylesheet" href="../styles/style6.css">
    <link rel="stylesheet" href="../styles/attendance.css">
    <script src="https://kit.fontawesome.com/d78dc5f742.js" crossorigin="anonymous"></script>
    <script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
</head>
<body>
    <div class="title-container">
        <img src="../images-icon/plplogo.png" alt="PLP Logo"> 
        <h1>Pamantasan ng Lungsod ng Pasig</h1>
    </div>
    
    <div class="tab-container">
        <div class="menu-items">
            <a href="student-profile.php"><i class="fa-regular fa-circle-user"></i><span class="label">Profile</span></a>
            <a href="student-home.php"><i class="fa-solid fa-home"></i><span class="label">Home</span></a>
            <a href="student-attendance.php" class="active"><i class="fa-solid fa-qrcode"></i><span class="label">Scan QR</span></a>
            <a href="5_About.php"><i class="fa-solid fa-circle-info"></i><span class="label">About</span></a>
        </div>
        <div class="logout">
            <a href="../php/1logout.php" onclick="return confirm('Are you sure you want to logout?');"><i class="fa-solid fa-right-from-bracket"></i><span class="label">Logout</span></a>
        </div>
    </div>

    <div class="main-container">
        <div class="qr-container">
            <h2>Scan QR Code for Attendance</h2>
            <div id="scanner-container">
                <video id="preview"></video>
            </div>
            <div id="manual-input">
                <h3>Or Enter Event Code</h3>
                <form id="attendance-form" method="POST">
                    <input type="text" id="event-code" name="event_code" placeholder="Enter Event Code" required>
                    <button type="submit" class="submit-btn">Mark Attendance</button>
                </form>
            </div>
            <div id="status-message"></div>
        </div>

        <div class="attendance-history">
            <h2>Your Attendance History</h2>
            <div class="table-container">
                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Date</th>
                            <th>Time In</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch student's attendance history
                        $stmt = $conn->prepare("
                            SELECT e.event_title, a.date, a.time_in, a.status
                            FROM attendance_table a
                            JOIN event_table e ON a.event_number = e.number
                            WHERE a.student_id = ?
                            ORDER BY a.date DESC, a.time_in DESC
                        ");
                        $stmt->bind_param("s", $student_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['event_title']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['time_in']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>No attendance records found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        let scanner = new Instascan.Scanner({ video: document.getElementById('preview') });
        
        scanner.addListener('scan', function (content) {
            // Assuming QR content is the event code
            processAttendance(content);
        });
        
        Instascan.Camera.getCameras().then(function (cameras) {
            if (cameras.length > 0) {
                scanner.start(cameras[0]);
            } else {
                console.error('No cameras found.');
                document.getElementById('scanner-container').innerHTML = 'No camera found on this device.';
            }
        }).catch(function (e) {
            console.error(e);
        });

        document.getElementById('attendance-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const eventCode = document.getElementById('event-code').value;
            processAttendance(eventCode);
        });

        function processAttendance(eventCode) {
            fetch('../php/process_student_attendance.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    event_code: eventCode,
                    student_id: '<?php echo $student_id; ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                const statusDiv = document.getElementById('status-message');
                if (data.success) {
                    statusDiv.className = 'success';
                    statusDiv.textContent = data.message;
                    // Reload attendance history after successful marking
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    statusDiv.className = 'error';
                    statusDiv.textContent = data.message;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('status-message').className = 'error';
                document.getElementById('status-message').textContent = 'An error occurred. Please try again.';
            });
        }
    </script>
</body>
</html> 