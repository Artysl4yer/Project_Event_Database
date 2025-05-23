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
    <link rel="stylesheet" href="../styles/filter.css">
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
            <a href="10_Admin.php" class="active"> <i class="fa-regular fa-circle-user"></i> <span class="label"> Admins </span> </a>
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
            
                <div class="col-md-12" id="importFrm">
                    <form action="../php/importData.php" method="post" enctype="multipart/form-data">
                        <label class="upload-btn">
                            Upload File
                            <input type="file" id="fileInput" name="file" hidden>
                        </label>
                        <span id="fileName">No file chosen</span>
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
            <div class="filter-buttons">
                <button class="filter-btn active" data-filter="all">All Events</button>
                <button class="filter-btn" data-filter="title">Sort by Title</button>
                <button class="filter-btn" data-filter="attendees">Sort by Attendees</button>
                <button class="filter-btn" data-filter="status">Filter by Status</button>
                <select id="statusFilter" class="status-select" style="display: none;">
                    <option value="all">All Status</option>
                    <option value="Ongoing">Ongoing</option>
                    <option value="Finished">Finished</option>
                    <option value="Archived">Archived</option>
                </select>
            </div>
            <div class="add-button">
                <button class="btn-import" onclick="openModal()">
                    <span><i class="fa-solid fa-plus"></i> Add Event</span>
                </button>
            </div>
            <table class="event-display-table" id="eventTable">
                <thead>
                    <tr>
                        <th data-sort="number">Number</th>
                        <th data-sort="title">Title</th>
                        <th data-sort="code">Event Code</th>
                        <th data-sort="start">Start</th>
                        <th data-sort="end">End</th>
                        <th data-sort="location">Location</th>
                        <th data-sort="description">Description</th>
                        <th data-sort="organization">Organization</th>   
                        <th data-sort="status">Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
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
                            <button onclick="window.location.href='11_Attendance.php?event=<?= $row['number'] ?>'">
                                <i class="fas fa-clipboard-check"></i> Take Attendance
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
                </tbody>
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
    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="../Javascript/qrcode.js"></script>
    <script src="../Javascript/filter.js"></script>
    <script src="../Javascript/dropdown.js"></script>
    <script src="../Javascript/event-edit.js"></script>
    <script>
        // Make isEditing available to the JavaScript
        window.isEditing = <?= json_encode($isEditing) ?>;
    </script>
</body>
</html>