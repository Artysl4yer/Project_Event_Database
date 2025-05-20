<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PLP: Event Attendance</title>
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
        <div class="event-details">
            <div class="event-top">
                <p>Event List</p>
                <div class="search-container">
                    <form class="example" action="action_page.php">
                        <label for="search"></label>
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
        
        <?php
        // Check if we're editing an event
        $isEditing = isset($_GET['edit']);
        $eventData = [];
        
        if ($isEditing) {
            include '../php/conn.php';
            $eventId = $_GET['edit'];
            $sql = "SELECT * FROM event_table WHERE number = $eventId";
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                $eventData = $result->fetch_assoc();
                
                // Split datetime into date and time components
                $startDateTime = new DateTime($eventData['date_start']);
                $endDateTime = new DateTime($eventData['date_end']);
                
                $eventData['date_start_date'] = $startDateTime->format('Y-m-d');
                $eventData['date_start_time'] = $startDateTime->format('H:i');
                $eventData['date_end_date'] = $endDateTime->format('Y-m-d');
                $eventData['date_end_time'] = $endDateTime->format('H:i');
            }
            $conn->close();
        }
        ?>
        
        <div class="event-table-section">
            <h2>Events</h2>
            <div class="add-button">
                <button class="btn-import" onclick="openModal()">
                    <span><i class="fa-solid fa-plus"></i> Add Event</span>
                </button>
            </div>
            <table class="event-display-table">
                <tr>
                    <th>Number</th>
                    <th>Title</th>
                    <th>Event Code</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Location</th>
                    <th>Description</th>
                    <th>Organization</th>   
                    <th>Status</th>
                    <th>Actions</th>
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
                        <button class="dropdown-toggle">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu">
                            <button onclick="window.location.href='6_NewEvent.php?edit=<?= $row['number'] ?>'">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <form method="POST" action="../php/delete_event.php" onsubmit="return confirm('Are you sure you want to delete this event?');">
                                <input type="hidden" name="delete_id" value="<?= $row['number'] ?>">
                                <button type="submit" name="delete">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                            </form>
                            <button onclick="generateQRCode(<?= $row['number'] ?>, '<?= htmlspecialchars($row['event_code']) ?>')">
                                <i class="fas fa-qrcode"></i> Generate QR
                            </button>
                        </div>
                    </td>
                </tr>
                <?php
                    endwhile;
                else:
                ?>
                <tr><td colspan="10">No events found.</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
    
    <!-- Event Modal -->
    <div id="eventModal" class="modal <?= $isEditing ? 'active' : '' ?>">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <div class="header">
                <h3><?= $isEditing ? 'Edit Event' : 'Create New Event' ?></h3>
                <p>Fill out the information below to <?= $isEditing ? 'update' : 'create' ?> the event</p>
            </div> 
            <form id="eventForm" action="../php/<?= $isEditing ? 'update_event.php' : 'event-sub.php' ?>" method="POST">
                <?php if ($isEditing): ?>
                <input type="hidden" name="event_id" value="<?= $eventData['number'] ?>">
                <?php endif; ?>
                
                <div class="user-details">
                    <div class="input-box">
                        <label for="event-title">Event Title:</label>
                        <input type="text" name="event-title" value="<?= $isEditing ? htmlspecialchars($eventData['event_title']) : '' ?>" required> 
                    </div>
                    <div class="input-box">
                        <label for="event-location">Location:</label>
                        <input type="text" name="event-location" value="<?= $isEditing ? htmlspecialchars($eventData['event_location']) : '' ?>" required> 
                    </div>
                    <div class="date-box">
                        <label for="event-date-start">Start Time</label>
                        <input type="date" name="event-date-start" value="<?= $isEditing ? $eventData['date_start_date'] : '' ?>" required> 
                        <input type="time" name="event-time-start" value="<?= $isEditing ? $eventData['date_start_time'] : '' ?>" required> 
                    </div>
                    <div class="date-box">
                        <label for="event-date-end">End Time</label>
                        <input type="date" name="event-date-end" value="<?= $isEditing ? $eventData['date_end_date'] : '' ?>" required> 
                        <input type="time" name="event-time-end" value="<?= $isEditing ? $eventData['date_end_time'] : '' ?>" required> 
                    </div>
                    <div class="input-box">
                        <label for="event-orgs">Organization:</label>
                        <input type="text" name="event-orgs" value="<?= $isEditing ? htmlspecialchars($eventData['organization']) : '' ?>" required> 
                    </div>
                    <div class="input-box">
                        <label for="event-status">Status:</label>
                        <select name="event-status" required>
                            <option value="Ongoing" <?= $isEditing && $eventData['event_status'] == 'Ongoing' ? 'selected' : '' ?>>Ongoing</option>
                            <option value="Finished" <?= $isEditing && $eventData['event_status'] == 'Finished' ? 'selected' : '' ?>>Finished</option>
                            <option value="Archived" <?= $isEditing && $eventData['event_status'] == 'Archived' ? 'selected' : '' ?>>Archived</option>
                        </select>
                    </div>
                    <div class="input-box">
                        <label for="event-description">Description:</label>
                        <textarea id="description" name="event-description"><?= $isEditing ? htmlspecialchars($eventData['event_description']) : '' ?></textarea>
                    </div>
                    <?php if (!$isEditing): ?>
                    <div class="input-box">
                        <label for="event-code">Event Code:</label>
                        <input type="text" name="code" id="codeField" readonly>
                        <small style="color: #666; font-size: 0.8em;">This code will be auto-generated for QR code registration</small>
                    </div>
                    <?php endif; ?>
                    <div class="input-box-options">
                        <div class="option-title-box">
                            <label for="option-box">Options:</label>
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
                    <button class="btn-submit" type="submit"><?= $isEditing ? 'Update' : 'Submit' ?></button>
                    <button class="btn-close" type="button" onclick="closeModal()">Close</button>
                </div>
            </form>
        </div>
    </div>
    
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        let qrcode = null;
        
        // Function to open modal
        function openModal() {
            document.getElementById('eventModal').classList.add('active');
        }
            
        // Function to close modal
        function closeModal() {
            document.getElementById('eventModal').classList.remove('active');
            <?php if ($isEditing): ?>
            window.location.href = '6_NewEvent.php';
            <?php endif; ?>
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('eventModal');
            const qrModal = document.getElementById('qrModal');
            
            if (event.target == modal) {
                closeModal();
            }
            if (event.target == qrModal) {
                qrModal.classList.remove('show');
            }
        }
        
        // Generate code for new events
        function generateCode(length = 12) {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            const timestamp = Date.now().toString().slice(-4);
            let code = '';
            for (let i = 0; i < length - 4; i++) {
                code += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return code + timestamp;
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (!$isEditing): ?>
            document.getElementById('codeField').value = generateCode(12);
            <?php endif; ?>
        });
        
        // QR Code Generation
        function generateQRCode(eventNumber, eventCode) {
            const modal = document.getElementById('qrModal');
            const container = document.getElementById('qrcode-container');
            const qrcodeDiv = document.getElementById('qrcode');
            qrcodeDiv.innerHTML = '';
            const registrationUrl = `${window.location.origin}/Project_Event_Database/pages/register_participant.php?event=${eventNumber}&code=${eventCode}`;
            
            qrcode = new QRCode(qrcodeDiv, {
                text: registrationUrl,
                width: 256,
                height: 256,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });

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
    
        document.querySelector('.close-qr').onclick = function() {
            document.getElementById('qrModal').classList.remove('show');
        }
    </script>
</body>
</html>