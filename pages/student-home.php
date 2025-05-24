<?php
session_start();

// Check email, student_id, and role
if (!isset($_SESSION['email'], $_SESSION['student_id'], $_SESSION['role'])) {
    header("Location: ../pages/1_Login.php");
    exit();
}

// Allowed roles
$allowed_roles = ['student', 'coordinator', 'admin'];

if (!in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: ../pages/1_Login.php");
    exit();
}

include '../php/conn.php';

// Get student's course from the users table
$student_id = $_SESSION['student_id'];
$stmt = $conn->prepare("SELECT course FROM users WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$student_course = $user['course'];
?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Student Home</title>
        <link rel="stylesheet" href="../styles/style1.css">
        <link rel="stylesheet" href="../styles/style11.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    </head>
    <body>
    <div class="title-container">
            <img src="../images-icon/plplogo.png"> <h1> Pamantasan ng Lungsod ng Pasig </h1>
        </div>
        <div class="tab-container">
            <div class="menu-items">
                <a href="../pages/student-profile.php" class="active"> <i class="fa-regular fa-circle-user"></i> <span class="label"> Profile </span> </a>
                <a href="../pages/student-home.php" class="active"> <i class="fa-solid fa-home"></i> <span class="label"> Home </span> </a>
                <a href="../pages/5_About.php" class="active"> <i class="fa-solid fa-circle-info"></i> <span class="label"> About </span> </a>
            </div>
            <div class="logout">
                <a href="../php/1logout.php" onclick="return confirm('Are you sure you want to logout?');"> <i class="fa-solid fa-right-from-bracket"></i> <span class="label"> Logout </span> </a>
            </div>
        </div>
        <div class="image-background">
            <div class="image-background-dim"></div>
            <div class="image-content" id="banner">
                <h1> PLP EVENTS </h1>
            </div>
            
        </div>

        <div class="first-page">
            <div class="event-main">
                <div class="event-details">
                    <div class="event-attendance-top">
                        <p> Event List </p>
                        
                        <div class="search-container">
                            <form class="example" action="action_page.php">
                                <label for="search"> </label>
                                <input type="text" id="search" name="fname" placeholder="Search...">
                                <button type="submit"><i class="fa fa-search"></i></button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="event-list">
                        <?php
                        include '../php/conn.php';

                        $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

                        if (!empty($search)) {
                            $query = "SELECT * FROM event_table 
                                        WHERE (event_title LIKE ? 
                                        OR event_description LIKE ? 
                                        OR event_location LIKE ? 
                                        OR organization LIKE ?) 
                                        AND (organization = ? OR organization = 'All Courses')
                                        ORDER BY number DESC";
                            $search_param = "%$search%";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("sssss", $search_param, $search_param, $search_param, $search_param, $student_course);
                        } else {
                            $query = "SELECT * FROM event_table 
                                     WHERE organization = ? OR organization = 'All Courses'
                                     ORDER BY number DESC";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("s", $student_course);
                        }

                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $dateOnly = (new DateTime($row['date_start']))->format('Y-m-d');
                                $dateTimeStart = (new DateTime($row['event_start']))->format('Y-m-d H:i');
                                
                                echo "<div class='event-box-details'>";
                                echo "  <div class='floating-card'>";
                                echo "      <div class='event-date'>";
                                echo "          <img src='../images-icon/plm_courtyard.png' alt='Event Background' class='eventbg' />";
                                echo "          <p class='day'>" .$dateOnly. "</p>";
                                echo "          <p class='time'>" .$dateTimeStart. "</p>";
                                echo "      </div>";
                                echo "      <div class='event-description'>";
                                echo "          <h3>" .htmlspecialchars($row['event_title']). "</h3>";
                                echo "          <p>" .htmlspecialchars($row['event_description'])."</p>";
                                echo "      </div>";
                                echo "      <div class='status'>";
                                echo "          <p> Status: <b> " . htmlspecialchars($row['event_status']) . " </b></p>";
                                echo "      </div>";
                                echo "  </div>";
                                echo "  <div class='even-more-details'>";
                                echo "      <div class='event-box-row'>";
                                echo "          <p> Location: <b> " .htmlspecialchars($row['event_location']). "</b></p>";
                                echo "          <p> Organization: <b> " .htmlspecialchars($row['organization']). "</b></p>";
                                echo "      </div>";
                                echo "  </div>";
                                echo "</div>";
                            }
                        } else {
                            echo "<p style='grid-column: 1 / -1; margin: 20px; color: red;'>No events found matching your search.</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>