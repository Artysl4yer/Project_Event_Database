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
            Pamantasan ng Lungsod ng Pasig
            <div class="tab-container">
                <div class="menu-items">
                    <a href="" class="active"> <i class="fa-solid fa-calendar"></i> <span class="label">Events </span> </a>
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

        <!-- The Event List. The compilation of events, sort to newest to latest -->
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

                        $result = $conn->query("SELECT * FROM event_table ORDER BY number DESC"); 

                        while ($row = $result->fetch_assoc()) {
                            $dateOnly = (new DateTime($row['date_start']))->format('Y-m-d');
                            $dateTimeStart = (new DateTime($row['event_start']))->format('Y-m-d H:i');

                            echo "<div class='event-box-details'>";
                            echo "  <div class='floating-card'>";
                            echo "      <div class='event-date'>";
                            echo "          <p class='day'>" . $dateOnly . "</p>";
                            echo "          <p class='time'>" . $dateTimeStart . "</p>";

                            echo "      </div>";
                            echo "      <div class='event-description'>";
                            echo "          <h3>" .htmlspecialchars($row['event_title']). "</h3>";
                            echo "          <p>" .htmlspecialchars($row['event_description'])."</p>";
                            echo "      </div>";
                            echo "      <div class='date'>";
                            echo "          <p>" . $dateOnly . "</p>";
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
                            echo "          <div class='event-controls' id='box2'>";
                            echo "              <button class='edit'> Edit </button>";
                            echo "              <button class='delete'> Delete </button>";
                            echo "          </div>";
                            echo "      </div>";
                            echo "  </div>";
                            echo "</div>";
                        }
                    ?>
                <div class="add-button">
                    <button class="btn-import" id="openModal"> <i class="fa-solid fa-plus"></i> </button>
                </div>
            </div>
        </div>


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
                            <input type="date" id="event-date-start" name="event-date-start" placeholder="DD/MM/YYYY" required>
                            <input type="time" id="event-time-start" name="event-time-start" required>
                        </div>
                        <div class="date-box">
                            <label for="event-date-end"> End Time </label>
                            <input type="date" id="event-date-end" name="event-date-end" placeholder="DD/MM/YYYY" required>
                            <input type="time" id="event-time-end" name="event-time-end" required>
                        </div>
                        <div class="input-box">
                            <label for="event-orgs"> Organization: </label>
                            <input type="text" name="event-orgs" required> 
                        </div>
                        <div class="input-box">
                            <label for="event-description"> Description: </label>
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
                    <input type="hidden" name="event_start" id="event_start_hidden">
                    <input type="hidden" name="event_end" id="event_end_hidden">

                    <div class="controls">
                        <button class="btn-submit" type="submit">Submit</button>
                        <button class="btn-close" type="button"> Close </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Popup for the registration list of attendies -->
        <div class="registration-table-box">
            <div class="registration-modal-content">
                <div class="header">
                </div>
                <form>
                </form>
            </div>
        </div>
        <script src="../Javascript/popup.js"></script>
        <script src="../Javascript/RandomCodeGenerator.js"></script>
    </body>
</html>