<?php
session_start();

// Check student id and email
if (!isset($_SESSION['email']) || !isset($_SESSION['student_id'])) {
    header("Location: ../pages/Login_v1.php");
    exit();
}
?>


<!DOCTYPE html>
<html>
    <head>
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
                <a href="" class="active"> <i class="fa-solid fa-calendar"></i> <span class="label"> QR Code </span> </a>
            </div>
            <div class="logout">
                <a href="../php/1logout.php"> <i class="fa-solid fa-gear"></i> <span class="label"> Logout </span> </a>
            </div>
        </div>
        <div class="image-background">
            <div class="image-background-dim"></div>
            <div class="image-content" id="banner">
                <h1> PLP EVENTS </h1>
            </div>
            
        </div>

        <div class="first-page">
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
                            echo "      <div class='event-actions'>";
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
    </body>
</html>