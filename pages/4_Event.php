<?php
// Enable all error reporting at the very top
error_reporting(-1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Log errors to a file
ini_set('log_errors', 1);
ini_set('error_log', '../php/error.log');

// Start output buffering to catch any errors
ob_start();

try {
    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    error_log("Session started");

    // Include database connection
    if (!file_exists('../php/conn.php')) {
        throw new Exception("Database connection file not found");
    }
    require_once '../php/conn.php';
    error_log("Database connection file included");

    // Test database connection
    if (!isset($conn)) {
        throw new Exception("Database connection variable not set");
    }
    if (!$conn) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }
    error_log("Database connection successful");

    // Test if we can query the database
    $test_query = "SELECT 1";
    if (!$conn->query($test_query)) {
        throw new Exception("Database query test failed: " . $conn->error);
    }
    error_log("Database query test successful");

    // Get search parameter
    $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
    error_log("Search parameter: " . $search);

    // Prepare the query based on search
    if (!empty($search)) {
        $query = "SELECT *, 
                CASE 
                    WHEN file IS NULL OR file = '' OR file = 'null' 
                    THEN '../images-icon/plm_courtyard.png'
                    ELSE file 
                END as event_image 
                FROM event_table 
                WHERE event_title LIKE ? 
                OR event_description LIKE ? 
                OR event_location LIKE ? 
                OR organization LIKE ? 
                ORDER BY number DESC";
        $search_param = "%$search%";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("ssss", $search_param, $search_param, $search_param, $search_param);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        $result = $stmt->get_result();
        error_log("Search query executed successfully");
    } else {
        $query = "SELECT *, 
                CASE 
                    WHEN file IS NULL OR file = '' OR file = 'null' 
                    THEN '../images-icon/plm_courtyard.png'
                    ELSE file 
                END as event_image 
                FROM event_table 
                ORDER BY number DESC";
        $result = $conn->query($query);
        if (!$result) {
            throw new Exception("Query failed: " . $conn->error);
        }
        error_log("Default query executed successfully");
    }

    // Check if we got any results
    if ($result) {
        error_log("Number of results: " . $result->num_rows);
    } else {
        error_log("No results returned");
    }

} catch (Exception $e) {
    error_log("Error in 4_Event.php: " . $e->getMessage());
    die("An error occurred: " . $e->getMessage());
}

// Rest of your existing code...
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>PLP: Events</title>
        <link rel="stylesheet" href="../styles/style1.css">
        <link rel="stylesheet" href="../styles/style2.css">
        <link rel="stylesheet" href="../styles/style3.css">
        <link rel="stylesheet" href="../styles/style11.css">
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
        
    
    </div>

        <script>
        function viewParticipants(eventId, eventTitle) {
            window.location.href = `11_Attendance.php?event=${eventId}`;
        }
        </script>
    </body>
</html>
