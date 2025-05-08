<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="../styles/style1.css">
        <link rel="stylesheet" href="../styles/style2.css">
        <link rel="stylesheet" href="../styles/style3.css">
        <link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css">
        <script src="https://kit.fontawesome.com/d78dc5f742.js" crossorigin="anonymous"></script>
    </head>
    <body>
        <div class="title-container">
            <img src="../images-icon/plplogo.png"> 
            <h1>Pamantasan ng Lungsod ng Pasig</h1>
            <div class="tab-container">
                <div class="menu-items">
                    <a href="4_Event.php" class="active"> <i class="fa-solid fa-home"></i> <span class="label">Home</span> </a>
                    <a href="6_NewEvent.php" class="active"> <i class="fa-solid fa-calendar"></i> <span class="label">Event Table</span> </a>
                    <a href="" class="active"> <i class="fa-regular fa-circle-user"></i> <span class="label">Admins</span> </a>
                    <a href="" class="active"> <i class="fa-solid fa-address-card"></i> <span class="label">Register</span> </a>
                    <a href="#About" class="active"> <i class="fa-solid fa-circle-info"></i> <span class="label">About</span> </a>
                    <a href="" class="active"> <i class="fa-solid fa-bars"></i> <span class="label">Logs</span> </a>
                </div>
                <div class="logout">
                    <a href=""> <i class="fa-solid fa-gear"></i> <span class="label">Logout</span> </a>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="first-page">
                <div class="event-details">
                    <div class="event-attendance-top">
                        <p>Event List</p>

                        <div class="search-container">
                            <form class="example" method="GET" action="4_Event.php">
                                <label for="search"></label>
                                <input type="text" id="search" name="search" placeholder="Search..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
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

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $dateOnly = (new DateTime($row['date_start']))->format('Y-m-d');
                                    $dateTimeStart = (new DateTime($row['event_start']))->format('Y-m-d H:i');

                                    echo "<div class='event-box-details'>";
                                    echo "  <div class='floating-card'>";
                                    echo "      <div class='event-date'>";
                                    echo "          <p class='day'>" .$dateOnly. "</p>";
                                    echo "          <p class='time'>" .$dateTimeStart. "</p>";
                                    echo "      </div>";
                                    echo "      <div class='event-description'>";
                                    echo "          <h3>" .htmlspecialchars($row['event_title']). "</h3>";
                                    echo "          <p>" .htmlspecialchars($row['event_description'])."</p>";
                                    echo "      </div>";
                                    echo "      <div class='date'>";
                                    echo "          <p>" . $dateOnly. "</p>"; 
                                    echo "      </div>";
                                    echo "  </div>";
                                    echo "  <div class='even-more-details'>";
                                    echo "      <div class='event-box-row' id='box1'>";
                                    echo "          <div class='event-box-column'>";
                                    echo "              <p> Location: <b> " .htmlspecialchars($row['event_location']). "</b></p>";
                                    echo "              <p> Organization: <b> " .htmlspecialchars($row['organization']). "</b></p>";
                                    echo "          </div>";
                                    echo "      </div>";
                                    echo "  </div>";
                                    echo "</div>";
                                }
                            } else {
                                echo "<p style='margin: 20px; color: red;'>No events found matching your search.</p>";
                            }
                        ?>
                        <div class="add-button">
                            <button class="btn-import" id="openModal" onclick="openModal()"> <i class="fa-solid fa-plus"></i> </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal code and second-page code (unchanged) -->
            <!-- Your popup, registration, and form modals go here (as you already have in your original code) -->
        </div>

        <script src="../Javascript/popup.js"></script>
        <script src="../Javascript/RandomCodeGenerator.js"></script>
    </body>
</html>
