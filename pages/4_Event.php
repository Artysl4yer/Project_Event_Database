<?php
session_start();

// Debug output
error_log("4_Event.php accessed");
error_log("Session data: " . print_r($_SESSION, true));

// Strict session check - must have all required variables and they must not be empty
if (!isset($_SESSION['email']) || !isset($_SESSION['student_id']) || !isset($_SESSION['role']) ||
    empty($_SESSION['email']) || empty($_SESSION['student_id']) || empty($_SESSION['role'])) {
    
    error_log("Session check failed - missing or empty session variables");
    // Clear session and redirect to login
    session_unset();
    session_destroy();
    header("Location: 1_Login.php");
    exit();
}

// Verify role is valid
$allowed_roles = ['coordinator'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    error_log("Invalid role access attempt: " . $_SESSION['role']);
    session_unset();
    session_destroy();
    header("Location: 1_Login.php");
    exit();
}

// Log the role for debugging
error_log("User role: " . $_SESSION['role']);
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="../styles/style1.css">
        <link rel="stylesheet" href="../styles/style2.css">
        <link rel="stylesheet" href="../styles/style3.css">
        <title> PLP: Events </title>
        <script src="https://kit.fontawesome.com/d78dc5f742.js" crossorigin="anonymous"></script>
    </head>
    <body>
        <div class="title-container">
            <img src="../images-icon/plplogo.png"> <h1> Pamantasan ng Lungsod ng Pasig </h1>
        </div>
        <div class="tab-container">
            <div class="menu-items">
                <a href="4_Event.php" class="active"> <i class="fa-solid fa-home"></i> <span class="label"> Home </span> </a>
                <a href="6_NewEvent.php"> <i class="fa-solid fa-calendar"></i> <span class="label"> Events </span> </a>
                <a href="7_StudentTable.php"> <i class="fa-solid fa-address-card"></i> <span class="label"> Participants </span> </a>
                <a href="8_archive.php"> <i class="fa-solid fa-bars"></i> <span class="label"> Logs </span> </a>
                <a href="5_About.php"> <i class="fa-solid fa-circle-info"></i> <span class="label"> About </span> </a>
            </div>
            <div class="logout">
                <a href="/php/logout.php"> <i class="fa-solid fa-gear"></i> <span class="label"> Logout </span> </a>
            </div>
        </div>
        <div class="image-background">
            <div class="image-background-dim"></div>
            <div class="image-content" id="banner">
                <h1> PLP EVENTS </h1>
                <div class="image-description">
                <p> Welcome to Pamantasan ng Lungsod ng Pasig Updates </p>
                <p> Get Up to date with the latest upcoming Events </p>
            </div>
            </div>
        </div>
        <div class="main-content">
            <div class="first-page">
            <!-- The Event List. The compilation of events, sort to newest to latest -->
            <div class="event-details">
                <div class="event-attendance-top">
                    <p> Event List </p>
                    
                    <div class="search-container">
                        <form class="example" actiion="action_page.php">
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
                                    WHERE event_title LIKE '%$search%' 
                                    OR event_description LIKE '%$search%' 
                                    OR event_location LIKE '%$search%' 
                                    OR organization LIKE '%$search%' 
                                    ORDER BY number DESC";
                    } else {
                        $query = "SELECT * FROM event_table ORDER BY number DESC";
                    }

                    $result = $conn->query($query);

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $dateOnly = (new DateTime($row['date_start']))->format('Y-m-d');
                            $dateTimeStart = (new DateTime($row['event_start']))->format('Y-m-d H:i');
                            $eventNumber = $row['number']; // Get the event number
                            
                            echo "<div class='event-box-details' onclick='window.location.href=\"11_Attendance.php?event=" . $eventNumber . "\"' style='cursor: pointer;'>";
                            echo "  <div class='floating-card'>";
                            echo "      <div class='event-container'>";
                            echo "          <img src='../images-icon/plm_courtyard.png' alt='Event Background' class='eventbg' />";
                            echo "          <div class = 'event-date'>   ";
                            echo "              <p class='day'>" .$dateOnly. "</p>";
                            echo "              <p class='time'>" .$dateTimeStart. "</p>";
                            echo "          </div>  ";
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
                            echo "      <div class='event-actions'>";
                            echo "          <button onclick='event.stopPropagation(); window.location.href=\"11_Attendance.php?event=" . $eventNumber . "\"' class='action-btn'>";
                            echo "              <i class='fas fa-users'></i> View Participants";
                            echo "          </button>";
                            echo "      </div>";
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
   
    
    <script src="../Javascript/dynamic.js"></script>    
   
    </body>
</html>
