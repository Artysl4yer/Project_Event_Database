<?php
session_start();

// Check email, student_id, and role
if (!isset($_SESSION['email'], $_SESSION['student_id'], $_SESSION['role'])) {
    header("Location: ../pages/1_Login.php");
    exit();
}


?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="../styles/style1.css">
        <link rel="stylesheet" href="../styles/style6.css">
        <link rel="stylesheet" href="../styles/attendance.css">
        <link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css">
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
                <a href="8_archive.php" class="active"> <i class="fa-solid fa-bars"></i> <span class="label"> Logs </span> </a>
            </div>
            <div class="logout">
                <a href="../php/1logout.php" onclick="return confirm('Are you sure you want to logout?');"> <i class="fa-solid fa-right-from-bracket"></i> <span class="label"> Logout </span> </a>
            </div>
        </div>
        <div class = "main-container">
            <?php
                    include '../php/conn.php';

                    // Get event details from URL parameter (either event number or event title)
                    $event_id = isset($_GET['event']) ? $_GET['event'] : null;
                    $event_title = isset($_GET['event_title']) ? urldecode($_GET['event_title']) : null;

                    // Query to get event details based on either event number or title
                    if ($event_id !== null) {
                        $event_query = "SELECT * FROM event_table WHERE number = ?";
                        $stmt = $conn->prepare($event_query);
                        $stmt->bind_param("i", $event_id);
                    } else if ($event_title !== null) {
                        $event_query = "SELECT * FROM event_table WHERE event_title = ?";
                        $stmt = $conn->prepare($event_query);
                        $stmt->bind_param("s", $event_title);
                    } else {
                        echo "<div class='error-message'>No event selected or event not found.</div>";
                        exit;
                    }

                    $stmt->execute();
                    $event_result = $stmt->get_result();
                    $event_row = $event_result->fetch_assoc();

                    if (!$event_row) {
                        echo "<div class='error-message'>No event selected or event not found.</div>";
                        exit;
                    }

                    // Get the event number for attendance records
                    $event_number = $event_row['number'];
            ?>
            <div class="attendance-top">
                <div class="event-title">
                    <h2>Event: <?php echo htmlspecialchars($event_row['event_title']); ?></h2>
                </div>
                <form class="attendance-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="studentName">Student Name</label>
                            <input type="text" id="studentName" name="studentName" placeholder="Enter Student Name" required>
                        </div>
                        <div class="form-group">
                            <label for="studentId">Student ID</label>
                            <input type="text" id="studentId" name="studentId" placeholder="Enter Student ID" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <button type="submit" class="submit-btn">Mark Attendance</button>
                    </div>
                </form>
            </div>
            <div class="attendance-below">
                <h3>Attendance List</h3>
                <div class="table-container">
                    <table class="attendance-table">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Student ID</th>
                                <th>Time In</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Query to fetch attendance records for this specific event
                            $attendance_query = "SELECT *
                                               FROM participants_table p 
                                               INNER JOIN attendance_table a ON p.id = a.student_id 
                                               WHERE a.event_number = ? 
                                               ORDER BY a.date DESC, a.time_in DESC";
                            $stmt = $conn->prepare($attendance_query);
                            $stmt->bind_param("i", $event_number);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['first_name'] . " " . $row['lastname']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['time_in']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                                    echo "<td>Present</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>No attendance records found for this event</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="statisical-report">
                <h3>Attendance List</h3>
                <div class="chart">
                    <canvas id="myChart"></canvas>
                </div>
            </div>
        </div>


        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="../Javascript/stats-script.js"></script>
    </body>
</html>