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
        <link rel="stylesheet" href="../styles/style8.css">
        <link rel="stylesheet" href="../styles/style10.css">
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
                <a href="7_StudentTable.php" class="active"> <i class="fa-solid fa-address-card"></i> <span class="label"> Participants </span> </a>
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
                            <th>Status</th>
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
                            <td><?= htmlspecialchars($row['event_status']) ?></td>
                                
                            <td class="dropdown-wrapper">
                            <button class="dropdown-toggle" data-dropdown-number="<?= $row['number'] ?>">⋮</button>
                            <div id="dropdown<?= $row['number'] ?>" class="dropdown-menu">
                                <button onclick="editEvent(<?= $row['number'] ?>)">Edit</button>
                                <button onclick="deleteEvent(<?= $row['number'] ?>)">Delete</button>
                                <button onclick="generateQRCode(<?= $row['number'] ?>, '<?= htmlspecialchars($row['event_code']) ?>')">Generate QR Code</button>
                            </div>
                            </td>

                            <!-- QR Code Modal -->
                            <div id="qrModal" class="modal">
                                <div class="modal-content">
                                    <div class="header">
                                        <h3>Event QR Code</h3>
                                        <span class="close-qr">&times;</span>
                                    </div>
                                    <div id="qrcode-container" class="text-center">
                                        <div id="qrcode"></div>
                                        <p>Scan this QR code to register for the event</p>
                                        <button onclick="downloadQRCode()" class="btn-download">Download QR Code</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Add QR Code generation script -->
                            <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
                            <script>
                            let qrcode = null;
                            
                            function generateQRCode(eventNumber, eventCode) {
                                const modal = document.getElementById('qrModal');
                                const container = document.getElementById('qrcode-container');
                                const qrcodeDiv = document.getElementById('qrcode');
                                
                                // Clear previous QR code
                                qrcodeDiv.innerHTML = '';
                                
                                // Create registration URL with correct path
                                const registrationUrl = `${window.location.origin}/Project_Event_Database/php/register_participant.php?event=${eventNumber}&code=${eventCode}`;
                                
                                // Generate new QR code
                                qrcode = new QRCode(qrcodeDiv, {
                                    text: registrationUrl,
                                    width: 256,
                                    height: 256,
                                    colorDark: "#000000",
                                    colorLight: "#ffffff",
                                    correctLevel: QRCode.CorrectLevel.H
                                });
                                
                                // Show modal
                                modal.classList.add('show');
                            }
                            
                            function downloadQRCode() {
                                if (!qrcode) return;
                                
                                const canvas = document.querySelector("#qrcode canvas");
                                const image = canvas.toDataURL("image/png");
                                const link = document.createElement('a');
                                link.href = image;
                                link.download = 'event-qr-code.png';
                                document.body.appendChild(link);
                                link.click();
                                document.body.removeChild(link);
                            }
                            
                            // Close QR modal when clicking the close button
                            document.querySelector('.close-qr').onclick = function() {
                                document.getElementById('qrModal').classList.remove('show');
                            }
                            
                            // Close QR modal when clicking outside
                            window.onclick = function(event) {
                                const modal = document.getElementById('qrModal');
                                if (event.target == modal) {
                                    modal.classList.remove('show');
                                }
                            }
                            </script>
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
                                            <label for="event-status"> Status: </label>
                                            <select name="event-status" required>
                                            <option value="Ongoing">Ongoing</option>
                                            <option value="Finished">Finished</option>
                                            <option value="Archived">Archived</option>
                                        </select>
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
                 <div id="editModal" class="modal">

        <script src="../Javascript/popup.js"></script>
        <script src="../Javascript/dropdown.js"></script> 
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
                    const newCode = generateCode(12);  // Generates a 12-character code
                    codeField.value = newCode;
                    console.log('Generated code:', newCode); // Debug log (optional)
                } else {
                    console.log('Code field not found!');  // Error log if field doesn't exist
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                console.log('JavaScript is running');
                populateCodeField();
            });
        </script>
    </body>
</html>
