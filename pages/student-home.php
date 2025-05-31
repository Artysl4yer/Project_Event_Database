<?php
// Start session at the very beginning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '../php/conn.php';

// Check if user is logged in
if (!isset($_SESSION['email']) && !isset($_SESSION['client_id'])) {
    header('Location: 1_Login.php');
    exit();
}

// Check if user has appropriate role
$allowed_roles = ['student', 'coordinator', 'admin'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: 1_Login.php");
    exit();
}

// Get student's course from the users table
$student_id = $_SESSION['student_id'];
$stmt = $conn->prepare("SELECT course FROM users WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$student_course = $user['course'];

// Add debug logging
error_log("Student ID: " . $student_id);
error_log("Student Course: " . $student_course);

// Close the first statement
$stmt->close();

// Debug information
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Student Home - PLP Events</title>
        <link rel="stylesheet" href="../styles/style1.css">
        <link rel="stylesheet" href="../styles/style11.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <link rel="stylesheet" href="../styles/search.css">
        <script>
            // Add base URL to JavaScript
            const BASE_URL = '<?php echo SITE_URL; ?>';
        </script>
        <script src="../Javascript/search.js"></script>
    </head>
    <body>
        <div class="title-container">
            <img src="../images-icon/plplogo.png" alt="PLP Logo"> 
            <h1> Pamantasan ng Lungsod ng Pasig </h1>
        </div>
        
        <div class="tab-container">
            <div class="menu-items">
                <a href="student-profile.php"> <i class="fa-regular fa-circle-user"></i> <span class="label"> Profile </span> </a>
                <a href="student-home.php" class="active"> <i class="fa-solid fa-home"></i> <span class="label"> Home </span> </a>
                <a href="student-attendance.php"> <i class="fa-solid fa-qrcode"></i> <span class="label"> Scan QR </span> </a>
                <a href="5_About.php"> <i class="fa-solid fa-circle-info"></i> <span class="label"> About </span> </a>
                <a href="../php/1logout.php" onclick="return confirm('Are you sure you want to logout?');"> 
                    <i class="fa-solid fa-right-from-bracket"></i> <span class="label"> Logout </span> 
                </a>
            </div>
        </div>

        <div class="image-background">
            <div class="image-background-dim"></div>
            <div class="image-content" id="banner">
                <h1> PLP EVENTS </h1>
                <div class="image-description">
                    <p>Welcome to Pamantasan ng Lungsod ng Pasig Events</p>
                    <p>View and participate in events relevant to your course</p>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="first-page">
                <div class="event-details">
                    <div class="event-attendance-top">
                        <p>Event List</p>
                        
                        <div class="search-container">
                            <form class="search-form" action="" method="GET">
                                <input type="text" id="search" name="search" placeholder="Search events..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                                <button type="submit"><i class="fa fa-search"></i></button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="event-list">
                        <?php
                        $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

                        if (!empty($search)) {
                            $query = "SELECT *, 
                                    CASE 
                                        WHEN event_image IS NULL OR event_image = '' OR event_image = 'null' 
                                        THEN '../images-icon/plm_courtyard.png'
                                        ELSE event_image 
                                    END as event_image 
                                    FROM event_table 
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
                            $query = "SELECT *, 
                                    CASE 
                                        WHEN event_image IS NULL OR event_image = '' OR event_image = 'null' 
                                        THEN '../images-icon/plm_courtyard.png'
                                        ELSE event_image 
                                    END as event_image 
                                    FROM event_table 
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
                                $dateTimeStart = (new DateTime($row['event_start']))->format('H:i');
                                
                                echo "<div class='event-box-details'>";
                                echo "  <div class='floating-card'>";
                                echo "      <div class='event-date'>";
                                echo "          <img src='" . ($row['event_image'] ? '../' . htmlspecialchars($row['event_image']) : '../images-icon/plm_courtyard.png') . "' alt='" . htmlspecialchars($row['event_title']) . "' class='eventbg' />";
                                echo "          <div class='date-overlay'>";
                                echo "              <p class='day'>" . $dateOnly . "</p>";
                                echo "              <p class='time'>" . $dateTimeStart . "</p>";
                                echo "          </div>";
                                echo "      </div>";
                                
                                echo "      <div class='event-description'>";
                                echo "          <h3>" . htmlspecialchars($row['event_title']) . "</h3>";
                                echo "          <p>" . htmlspecialchars($row['event_description']) . "</p>";
                                echo "      </div>";
                                
                                echo "      <div class='status'>";
                                echo "          <p><i class='fas fa-info-circle'></i> Status: <b>" . htmlspecialchars($row['event_status']) . "</b></p>";
                                echo "          <p><i class='fas fa-qrcode'></i> Event Code: <b>" . htmlspecialchars($row['event_code']) . "</b></p>";
                                echo "      </div>";
                                
                                echo "      <div class='even-more-details'>";
                                echo "          <div class='event-box-row'>";
                                echo "              <p><i class='fas fa-map-marker-alt'></i> Location: <b>" . htmlspecialchars($row['event_location']) . "</b></p>";
                                echo "              <p><i class='fas fa-building'></i> Organization: <b>" . htmlspecialchars($row['organization']) . "</b></p>";
                                echo "          </div>";
                                echo "      </div>";
                                
                                echo "      <div class='event-actions'>";
                                echo "          <button onclick='window.location.href=\"student-attendance.php?event=" . $row['number'] . "\"' class='action-btn'>";
                                echo "              <i class='fas fa-qrcode'></i> Scan QR Code";
                                echo "          </button>";
                                echo "      </div>";
                                echo "  </div>";
                                echo "</div>";
                            }
                        } else {
                            echo "<div class='no-events'>";
                            echo "  <p>No events found matching your search.</p>";
                            echo "</div>";
                        }

                        // Close prepared statement
                        if (isset($stmt)) {
                            $stmt->close();
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effects to event boxes
            const eventBoxes = document.querySelectorAll('.event-box-details');
            eventBoxes.forEach(box => {
                box.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                    this.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.2)';
                });
                
                box.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
                });
            });

            // Add click effect to action buttons
            const actionButtons = document.querySelectorAll('.action-btn');
            actionButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const href = this.getAttribute('onclick').match(/'([^']+)'/)[1];
                    window.location.href = href;
                });
            });
        });
        </script>
    </body>
</html>