 <!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>PLP: Event Attendace</title>
        <link rel="stylesheet" href="../styles/style2.css">
        <link rel="stylesheet" href="../styles/style3.css">
        <link rel="stylesheet" href="../styles/style1.css">
        <link rel="stylesheet" href="../styles/style5.css">
        <link rel="stylesheet" href="../styles/style6.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        
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
        <div class="event-main">
            <!-- The Event List. The compilation of events, sort to newest to latest -->
                <div class="event-details">
                    <div class="event-top">
                        <p> Event List </p>
                        <div class="search-container">
                            <form class="example" action="action_page.php">
                                <label for="search"> </label>
                                <input type="text" id="search" name="fname" placeholder="Search...">
                                <button type="submit"><i class="fa fa-search"></i></button>
                            </form>
                        </div>
                       
                       
                        <div class="col-md-12" id="importFrm" style="display:block">
                            <form action="../php/importData.php" method="post" enctype="multipart/form-data">
                                <input type="file" name="file" />
                                <input type="submit" class="btn btn-primary" name="importSubmit" value="IMPORT">
                            </form>
                        </div>
                        
                    </div>  
                </div>
                <div class="event-table-section">
                    <h2>Events</h2>
                    <div class="add-button">
                        <button class="btn-import" id="openModal" onclick="openModal()">  <span> <i class="fa-solid fa-plus"></i>  Add Event </span></button>
                    </div>
                    <table class="event-display-table">
                        <tr>
                            <th>Number</th>
                            <th>Title</th>
                            <th>event_code</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Location</th>
                            <th>Description</th>
                            <th>Organization</th>   
                            <th>Edit</th>
                            <th>Delete</th>
                        </tr>
                        <?php

                        include '../php/conn.php';
                        $sql = "SELECT * FROM event_table ORDER BY number DESC";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0):
                            while ($row = $result->fetch_assoc()):
                                
                        ?>
                        <tr>
                            <td><?= $row['number'] ?></td>
                            <td><?= htmlspecialchars($row['event_title']) ?></td>
                            <td><?= htmlspecialchars($row['event_code'])?></td>
                            <td><?= $row['date_start'] ?></td>
                            <td><?= $row['date_end'] ?></td>
                            <td><?= htmlspecialchars($row['event_location']) ?></td>
                            <td><?= htmlspecialchars($row['event_description']) ?></td>
                            <td><?= htmlspecialchars($row['organization']) ?></td>
                            <td><a class="edit-btn" href="#" onclick="openModal(<?= $row['number'] ?>)">Edit</a></td>
                            <td>
                                <form method="POST" action="../php/delete_event.php" onsubmit="return confirm('Are you sure you want to delete this event?');">
                                    <input type="hidden" name="delete_id" value="<?= $row['number'] ?>">
                                    <button type="submit" name="delete" class="delete-btn">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php
                            endwhile;
                            else:
                        ?>
                        <tr><td colspan="8">No events found.</td></tr>
                        <?php endif; ?>
                    </table>
                </div>
        </div>
        

                <div id="importModal" class="modal">
                    <div class="modal-content">
                        <div class="header">
                            <h3> Create New Event </h3>
                            <p> Fill out the information below to get started </p>
                        </div> 
                        <form id="eventForm" action="../php/event-sub.php" method="POST">
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
                                        <input type="checkbox" name="option" value="Year"> Year
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
        <script src="../Javascript/popup.js"></script>
        
        <script>
            function generateCode(length = 12) {
                const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                let code = '';
                for (let i = 0; i < length; i++) {
                    code += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                return code;
            }

            function populateCodeField() {
                const codeField = document.getElementById('codeField');
                if (codeField) {
                    const newCode = generateCode(12);
                    codeField.value = newCode;
                    console.log('Generated code:', newCode); // Debug log
                } else {
                    console.log('Code field not found!');
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                console.log('JavaScript is running');
                populateCodeField();
            });
        </script>
    </body>
</html>
