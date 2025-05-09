<?php
// No event code needed, just start session
session_start();
?>


<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="../styles/style1.css">
        <link rel="stylesheet" href="../styles/style2.css">
        <link rel="stylesheet" href="../styles/style3.css">
        <link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css">
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
                <a href="6_NewEvent.php" class="active"> <i class="fa-solid fa-calendar"></i> <span class="label"> Events </span> </a>
                <a href="" class="active"> <i class="fa-regular fa-circle-user"></i> <span class="label"> Admins </span> </a>
                <a href="" class="active"> <i class="fa-solid fa-address-card"></i> <span class="label"> Participants </span> </a>
                <a href="5_About.php" class="active"> <i class="fa-solid fa-circle-info"></i> <span class="label"> About </span> </a>
                <a href="8_archive.php" class="active"> <i class="fa-solid fa-bars"></i> <span class="label"> Logs </span> </a>
                <a href="1_Login.php" class="active"> <i class="fa-solid fa-circle-info"></i> <span class="label"> Login </span> </a>
            </div>
            <div class="logout">
                <a href=""> <i class="fa-solid fa-gear"></i> <span class="label"> Logout </span> </a>
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
                       
                    </div>
                </div>
            </div>




                                <!-- temporary -->
    <!--        <div class="second-page">
                 Popup for the registration list of attendies 
                <div class="registration-table-box" id="importRegistration">
                    <div class="registration-modal-content">
                        <div class="registraion-header">
                        </div>
                        <form>
                            <label>First Name:
                                <input type="text" name="firstName" maxlength="30" required>
                            </label>

                            <label>Last Name:
                                <input type="text" name="lastName" maxlength="30" required>
                            </label>

                            <label>Section:
                                <input type="text" name="section" maxlength="10">
                            </label>

                            <label>Year Level:
                                <input type="text" name="grade" maxlength="10">
                            </label>

                            <label>Course:
                                <input type="text" name="course" maxlength="50">
                            </label>

                            <div class="gender">
                                <label>Gender:</label>
                                <input type="radio" name="gender" value="Male" id="male"> 
                                <label for="male" style="display: inline;">Male</label>
                                <input type="radio" name="gender" value="Female" id="female"> 
                                <label for="female" style="display: inline;">Female</label>
                            </div>

                            <label>Email:
                                <input type="email" name="email" required>
                            </label>

                            <label>Phone Number:
                                <input type="tel" name="phone" maxlength="10" pattern="[0-9]{10}" required>
                            </label>

                            <label>Address:
                                <textarea name="address" maxlength="100" rows="3"></textarea>
                            </label>

                            <h3>Qualifications</h3>
                            <table id="qualificationsTable">
                                <thead>
                                    <tr>
                                        <th>Sl. No</th>
                                        <th>Examination</th>
                                        <th>Board</th>
                                        <th>Percentage</th>
                                        <th>Year of Passing</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td><input type="text" name="exam1" maxlength="15"></td>
                                        <td><input type="text" name="board1" maxlength="15"></td>
                                        <td><input type="text" name="percentage1" maxlength="5"></td>
                                        <td><input type="text" name="year1" maxlength="4"></td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td><input type="text" name="exam2" maxlength="15"></td>
                                        <td><input type="text" name="board2" maxlength="15"></td>
                                        <td><input type="text" name="percentage2" maxlength="5"></td>
                                        <td><input type="text" name="year2" maxlength="4"></td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td><input type="text" name="exam3" maxlength="15"></td>
                                        <td><input type="text" name="board3" maxlength="15"></td>
                                        <td><input type="text" name="percentage3" maxlength="5"></td>
                                        <td><input type="text" name="year3" maxlength="4"></td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="table-actions">
                                <button type="button" class="add-row-btn" id="addQualificationBtn">+ Add Qualification</button>
                            </div>

                            <button type="submit" class="submit-btn">Submit Registration</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>                -->

            <!-- This is the popup box for the import of Event System that includes the Event title, Event location, Date, time and Organization-->
       
        <script src="../Javascript/popup.js"></script>
        <script src="../Javascript/dynamic.js"></script>
    
    </body>
</html>
