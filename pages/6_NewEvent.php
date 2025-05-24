<?php
session_start();

// Check email, student_id, and role
if (!isset($_SESSION['email'], $_SESSION['student_id'], $_SESSION['role'])) {
    header("Location: ../pages/1_Login.php");
    exit();
}

$allowed_roles = ['coordinator'];

if (!in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: ../pages/1_Login.php");
    exit();
}
?>


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
            <a href="6_NewEvent.php"> <i class="fa-solid fa-calendar"></i> <span class="label"> Events </span> </a>
            <a href="7_StudentTable.php"> <i class="fa-solid fa-address-card"></i> <span class="label"> Participants </span> </a>
            <a href="8_archive.php"> <i class="fa-solid fa-bars"></i> <span class="label"> Logs </span> </a>
            <a href="5_About.php"> <i class="fa-solid fa-circle-info"></i> <span class="label"> About </span> </a>
        </div>
        <div class="logout">
            <a href="../php/1logout.php" onclick="return confirm('Are you sure you want to logout?');"> <i class="fa-solid fa-right-from-bracket"></i> <span class="label"> Logout </span> </a>
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
                <button class="filter-btn btn-import" onclick="openModal()">
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
                        <select name="event-orgs" class="form-select" required>
                            <option value="">Select Organization</option>
                            <option value="College of Computer Studies" <?= $isEditing && $eventData['organization'] == 'College of Computer Studies' ? 'selected' : '' ?>>College of Computer Studies</option>
                            <option value="College of Engineering" <?= $isEditing && $eventData['organization'] == 'College of Engineering' ? 'selected' : '' ?>>College of Engineering</option>
                            <option value="College of Business Accounting" <?= $isEditing && $eventData['organization'] == 'College of Business Accounting' ? 'selected' : '' ?>>College of Business Accounting</option>
                            <option value="College of Nursing" <?= $isEditing && $eventData['organization'] == 'College of Nursing' ? 'selected' : '' ?>>College of Nursing</option>
                            <option value="All Courses" <?= $isEditing && $eventData['organization'] == 'All Courses' ? 'selected' : '' ?>>All Courses</option>
                        </select>
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
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Generate random event code
        function generateEventCode() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            let code = '';
            for (let i = 0; i < 6; i++) {
                code += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return code;
        }

        // Set initial event code
        const codeField = document.getElementById('codeField');
        if (codeField) {
            codeField.value = generateEventCode();
        }

        // Handle form submission
        const form = document.getElementById('eventForm');
        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                try {
                    const formData = new FormData(this);
                    const response = await fetch(this.action, {
                        method: 'POST',
                        body: formData
                    });

                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        // Handle JSON response
                        const result = await response.json();
                        if (result.error) {
                            alert(result.message || 'Error saving event');
                        } else {
                            // Redirect on success without showing error
                            window.location.href = '6_NewEvent.php?success=true';
                            return;
                        }
                    } else {
                        // Handle regular form response
                        if (response.ok) {
                            window.location.href = '6_NewEvent.php?success=true';
                            return;
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error saving event. Please try again.');
                }
            });
        }

        // Modal functions
        window.openModal = function() {
            document.getElementById('eventModal').style.display = 'block';
            // Reset form and generate new code when opening modal
            if (!window.isEditing) {
                form.reset();
                if (codeField) {
                    codeField.value = generateEventCode();
                }
            }
        };

        window.closeModal = function() {
            document.getElementById('eventModal').style.display = 'none';
        };

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('eventModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        };

        // Show success message if present in URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('success') === 'true') {
            alert('Event saved successfully!');
            // Remove the success parameter from URL
            window.history.replaceState({}, document.title, '6_NewEvent.php');
        }
    });
    </script>
</body>
</html>