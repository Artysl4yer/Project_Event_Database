<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="../styles/style1.css">
        <link rel="stylesheet" href="../styles/style2.css">
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
        <div class="event-details">
            <div class="event-attendance-top">
                <p> Event List </p>
                <button class="btn-import" id="openModal"> Import Event</button>
                <div class="search-container">
                    <form class="example" actiion="action_page.php">
                        <label for="search"> </label>
                        <input type="text" id="search" name="fname" placeholder="Search...">
                        <button type="submit"><i class="fa fa-search"></i></button>
                    </form>
                </div>
            </div>
          
            <div class="event-list">
                <table>
                    <tr class="event-list-sets">
                        <th> Events: </th>
                    </tr>
                    <tr>
                    <?php
                        include '../php/conn.php';

                        $result = $conn->query("SELECT * FROM event_table"); 

                        while($row = $result->fetch_assoc()) {
                            echo "<td>" . htmlspecialchars($row['event_title']) . "</td";
                            echo "<td>" . htmlspecialchars($row['event_description']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['event_location']) . "</td>";
                        }
                        ?>
                    </tr>
                </table>
            </div>
        </div>

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
                            <label for="event-date"> Location: </label>
                            <input type="text" name="event-location" required> 
                        </div>
                        <div class="date-box">
                            <label for="event-date-start"> Start Time </label>
                            <input type="text" name="event-date-start" placeholder="00/00/0000" required> 
                            <input type="text" name="event-time-start" placeholder="00:00" required> 
                            <select name="timezone" id="timezone">
                                    <option value="AM">AM</option>
                                    <option value="PM">PM</option>
                            </select>
                        </div>
                        <div class="date-box">
                            <label for="event-date-end"> End Time </label>
                            <input type="text" name="event-date-end" placeholder="00/00/0000" required> 
                            <input type="text" name="event-time-end" placeholder="00:00" required> 
                            <select name="timezone" id="timezone">
                                    <option value="AM">AM</option>
                                    <option value="PM">PM</option>
                            </select>
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
                        <button class="btn-close" type="button"> Close </button>
                    </div>
                </form>
            </div>
        </div>
        <script src="../Javascript/popup.js"></script>
    </body>
</html>