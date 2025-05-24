<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/event_errors.log');

// Log the start of the script
error_log("Starting 4_Event.php script");

// Start session at the very beginning
if (session_status() === PHP_SESSION_NONE) {
    error_log("Starting new session");
    session_start();
} else {
    error_log("Session already started");
}

// Log session data
error_log("Session data: " . print_r($_SESSION, true));

// Check if user is logged in and is a coordinator
if (!isset($_SESSION['email'], $_SESSION['student_id'], $_SESSION['role'])) {
    error_log("Session check failed - Missing required session variables");
    error_log("Available session variables: " . print_r($_SESSION, true));
    header("Location: 1_Login.php");
    exit();
}

if ($_SESSION['role'] !== 'coordinator') {
    error_log("Invalid role access attempt - User role: " . $_SESSION['role']);
    header("Location: 1_Login.php");
    exit();
}

error_log("Coordinator page accessed by: " . $_SESSION['email']);

// Fix the path to config.php
$config_path = __DIR__ . '/../config.php';
error_log("Looking for config file at: " . $config_path);

if (!file_exists($config_path)) {
    error_log("Configuration file not found at: " . $config_path);
    die("Configuration file not found at: " . $config_path);
}

error_log("Including config file");
require_once $config_path;

// Test database connection
if (!isset($conn) || $conn === false) {
    error_log("Database connection failed: " . (isset($conn) ? mysqli_connect_error() : "Connection not established"));
    die("Database connection failed. Please try again later.");
}

error_log("Database connection successful");

// Test if we can query the event table
try {
    $test_query = "SELECT COUNT(*) as count FROM event_table";
    $result = $conn->query($test_query);
    if (!$result) {
        error_log("Error accessing event table: " . $conn->error);
        die("Error accessing event table: " . $conn->error);
    }
    error_log("Event table query successful");
} catch (Exception $e) {
    error_log("Exception while querying event table: " . $e->getMessage());
    die("Error accessing event table: " . $e->getMessage());
}

// Define SITE_URL if not already defined
if (!defined('SITE_URL')) {
    define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/Project_Event_Database');
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="../styles/style1.css">
        <link rel="stylesheet" href="../styles/style2.css">
        <link rel="stylesheet" href="../styles/style4.css">
        <link rel="stylesheet" href="../styles/style11.css">
        <title>PLP: Events</title>
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
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="10_Admin.php"> <i class="fa-regular fa-circle-user"></i> <span class="label"> Admins </span> </a>
                <?php endif; ?>
                <a href="7_StudentTable.php"> <i class="fa-solid fa-address-card"></i> <span class="label"> Participants </span> </a>
                <a href="5_About.php"> <i class="fa-solid fa-circle-info"></i> <span class="label"> About </span> </a>
                <a href="8_archive.php"> <i class="fa-solid fa-bars"></i> <span class="label"> Logs </span> </a>
            </div>
            <div class="logout">
                <a href="../php/1logout.php" onclick="return confirm('Are you sure you want to logout?');"> <i class="fa-solid fa-right-from-bracket"></i> <span class="label"> Logout </span> </a>
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
                <div class="event-details">
                    <div class="event-attendance-top">
                        <p> Event List </p>
                        
                        <div class="search-container">
                            <form class="example" action="4_Event.php" method="GET">
                                <label for="search"></label>
                                <input type="text" id="search" name="search" placeholder="Search..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                                <button type="submit"><i class="fa fa-search"></i></button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="event-list">
                        <?php
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                try {
                                    $dateOnly = (new DateTime($row['date_start']))->format('Y-m-d');
                                    $dateTimeStart = (new DateTime($row['event_start']))->format('H:i');
                                    $eventImage = htmlspecialchars($row['event_image']);
                                    
                                    echo "<div class='event-box-details'>";
                                    echo "  <div class='floating-card'>";
                                    echo "      <div class='event-date'>";
                                    echo "          <img src='" . ($row['file'] ? '../' . htmlspecialchars($row['file']) : '../images-icon/plm_courtyard.png') . "' alt='" . htmlspecialchars($row['event_title']) . "' class='eventbg' />";
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
                                    echo "          <button onclick='viewParticipants(" . $row['number'] . ", \"" . htmlspecialchars($row['event_title']) . "\")' class='action-btn'>";
                                    echo "              <i class='fas fa-users'></i> View Participants";
                                    echo "          </button>";
                                    echo "      </div>";
                                    echo "  </div>";
                                    echo "</div>";
                                } catch (Exception $e) {
                                    error_log("Error processing event row: " . $e->getMessage());
                                    continue;
                                }
                            }
                        } else {
                            echo "<div class='no-events'>";
                            echo "  <p>No events found matching your search.</p>";
                            echo "</div>";
                        }

                        // Close prepared statement if it exists
                        if (isset($stmt)) {
                            $stmt->close();
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- This is the popup box for the import of Event System that includes the Event title, Event location, Date, time and Organization-->
       
        <script src="../Javascript/popup.js"></script>
        <script src="../Javascript/dynamic.js"></script>
        <script src="../Javascript/event_modal.js"></script>
        <script src="../Javascript/table-search.js"></script>
        <script src="../Javascript/filter.js"></script>
        
    
    </div>

        <script>
        function viewParticipants(eventId, eventTitle) {
            window.location.href = `11_Attendance.php?event=${eventId}`;
        }
        </script>
    </body>
</html>
