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
            <img src="../images-icon/plplogo.png"> <h1> Pamantasan ng Lungsod ng Pasig </h1>
            <div class="tab-container">
                <div class="menu-items">
                    <a href="4_Event.php" class="active"> <i class="fa-solid fa-home"></i> <span class="label"> Home </span> </a>
                    <a href="6_NewEvent.php" class="active"> <i class="fa-solid fa-calendar"></i> <span class="label"> Events </span> </a>
                    <a href="" class="active"> <i class="fa-regular fa-circle-user"></i> <span class="label"> Admins </span> </a>
                    <a href="" class="active"> <i class="fa-solid fa-address-card"></i> <span class="label"> Register </span> </a>
                    <a href="#About" class="active"> <i class="fa-solid fa-circle-info"></i> <span class="label"> About </span> </a>
                    <a href="" class="active"> <i class="fa-solid fa-bars"></i> <span class="label"> Logs </span> </a>
                </div>
                <div class="logout">
                    <a href=""> <i class="fa-solid fa-gear"></i> <span class="label"> Logout </span> </a>
                </div>
            </div>
        </div>
        <div class="image-background-dim">
            <div class="image-background">
                <h1> PLP EVENT ATTENDACNCE SYSTEM </h1>
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


                            $result = $conn->query("SELECT * FROM event_table ORDER BY number DESC"); 
                                
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
                                echo "              <p> </p>";
                                echo "              <p> </p>";
                                echo "          </div>";
                                echo "      </div>";
                                echo "  </div>";
                                echo "</div>";
                                }
                            ?>
                        <div class="add-button">
                            <button class="btn-import" id="openModal" onclick="openModal()"> <i class="fa-solid fa-plus"></i> </button>
                        </div>
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
            <div id="importModal" class="modal">
                <div class="modal-content">
                    <div class="header">
                        <h3> Create New Event </h3>
                        <p> Fill out the information below to get started </p>
                    </div> 
                    <form id="evetForm" action="../php/event-sub.php" method="POST">
                        <div class="user-details">
                            <div class="input-box">
                                <label for="event-title"> Event Title: </label>
                                <input type="text" name="event-title" required> 
                            </div>
                            <div class="input-box">
                                <label for="event-location"> Location: </label>
                                <input type="text" name="event-location" required> 
                            </div>
                            <div class="date-box">
                                <label for="event-date-start"> Start Time </label>
                                <input type="date" name="event-date-start" placeholder="00/00/0000" required> 
                                <input type="time" name="event-time-start" placeholder="00:00" required> 
                            </div>
                            <div class="date-box">
                                <label for="event-date-end"> End Time </label>
                                <input type="date" id="event-date-end" name="event-date-end" placeholder="00/00/0000" required> 
                                <input type="time" id= "event-time-end" name="event-time-end" placeholder="00:00" required> 
                                </div>
                            <div class="input-box">
                                <label for="event-orgs"> Organization: </label>
                                <input type="text" name="event-orgs" required> 
                            </div>
                            <div class="input-box">
                                <label for="event-description"> Decription: </label>
                                <textarea id="description" name="event-description"></textarea>
                            </div>
                            <input type="hidden" name="code" id="codeField">
                            <div class="input-box-options">
                                <div class="option-title-box">
                                    <label for="option-box">
                                        Options:
                                    </label>
                                </div>
                                <label for="option"> 
                                    <input type="checkbox" name="option" value="StudentID"> StudentID
                                </label>
                                <label for="option"> 
                                    <input type="checkbox" name="option" value="Gender"> Gender 
                                </label>
                                <label for="option"> 
                                    <input type="checkbox" name="option" value="Section"> Section
                                </label>
                                <label for="option"> 
                                    <input type="checkbox" name="option" value="Section"> Year
                                </label>
                                <label for="option"> 
                                    <input type="checkbox" name="option" value="Age"> Age
                                </label>
                                <label for="option"> 
                                    <input type="checkbox" name="option" value="Parents"> Parents
                                </label>
                                <label for="option"> 
                                    <input type="checkbox" name="option" value="ContactNo"> Contact No
                                </label>
                                <label for="option"> 
                                    <input type="checkbox" name="option" value="Email"> Email
                                </label>
                            </div>
                        </div>
                        <div class="controls">
                            <button class="btn-submit" type="submit">Submit</button>
                            <button class="btn-close" type="button" id="btn-click" onclick="closeModal()"> Close </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>    

       
        <script src="../Javascript/popup.js"></script>
        <script src="../Javascript/RandomCodeGenerator.js"></script>
    
    </body>
</html>