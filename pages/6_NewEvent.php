<?php
session_start();
$role = $_SESSION['role'] ?? null;
$page = basename($_SERVER['PHP_SELF']);
$coordinator_allowed = [
    '4_Event.php',
    '5_About.php',
    '6_NewEvent.php',
    '8_archive.php',
    '11_Attendance.php'
];
if (!$role) {
    header("Location: 1_Login.php");
    exit();
}
if ($role === 'admin') {
    // allow
} elseif ($role === 'coordinator') {
    if (!in_array($page, $coordinator_allowed)) {
        header("Location: 4_Event.php");
        exit();
    }
} else {
    header("Location: 1_Login.php");
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../styles/search.css">
    <style>
        /* Existing styles ... */

        /* Custom Organization Input Styles */
        .form-group input[name="custom_organization"] {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s ease;
            margin-top: 10px;
        }

        .form-group input[name="custom_organization"]:focus {
            border-color: #17692d;
            outline: none;
            box-shadow: 0 0 0 2px rgba(23, 105, 45, 0.1);
        }

        .form-group input[name="custom_organization"]::placeholder {
            color: #999;
        }

        /* Organization Select Styles */
        .form-group select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            background-color: white;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }

        .form-group select:focus {
            border-color: #17692d;
            outline: none;
            box-shadow: 0 0 0 2px rgba(23, 105, 45, 0.1);
        }

        .form-group select option {
            padding: 8px;
        }
    </style>
</head>
<body>
    <div class="title-container">
        <img src="../images-icon/plplogo.png"> <h1> Pamantasan ng Lungsod ng Pasig </h1>
    </div>
    <div class="tab-container">
        <div class="menu-items">
            <a href="admin-home.php" class="active"> <i class="fa-solid fa-users-gear"></i> <span class="label">User Manage</span> </a>
        <a href="4_Event.php"> <i class="fa-solid fa-home"></i> <span class="label"> Home </span> </a>
            <a href="6_NewEvent.php"> <i class="fa-solid fa-calendar"></i> <span class="label"> Events </span> </a>
            <a href="7_StudentTable.php"> <i class="fa-solid fa-address-card"></i> <span class="label"> Students </span> </a>
            <a href="guest-table.php"> <i class="fa-solid fa-users"></i> <span class="label"> Guests </span> </a>
            <a href="5_About.php"> <i class="fa-solid fa-circle-info"></i> <span class="label"> About </span> </a>
            <a href="8_archive.php"> <i class="fa-solid fa-bars"></i> <span class="label"> Logs </span> </a>
        </div>
        <div class="logout">
            <a href="../php/logout.php"> <i class="fa-solid fa-sign-out"></i> <span class="label"> Logout </span> </a>
        </div>
    </div>
    <div class="event-main">
        <div class="event-details">
            <div class="event-top">
                <p>Event List</p>
                <div class="search-container">
                    <form class="search-form" action="" method="GET">
                        <input type="text" id="search" name="search" placeholder="Search events..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
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
            <div class="filter-container">
                <div class="filter-buttons">
                    <button class="filter-btn active" data-filter="all">All Events</button>
                    <button class="filter-btn" data-filter="title">Sort by Title</button>
                    <button class="filter-btn" data-filter="attendees">Sort by Attendees</button>
                    <button class="filter-btn" data-filter="status">Filter by Status</button>
                    <select id="statusFilter" class="status-select" style="display: none;">
                        <option value="all">All Status</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="ongoing">Ongoing</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="archived">Archived</option>
                    </select>
                    <button class="btn-import" id="addEventBtn" type="button">
                        <span><i class="fa-solid fa-plus"></i> Add Event</span>
                    </button>
                </div>
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
                require_once '../php/update_event_status.php';
                updateEventStatuses($conn);
                $sql = "SELECT * FROM event_table ORDER BY number DESC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?= $row['number'] ?></td>
                    <td><?= htmlspecialchars($row['event_title']) ?></td>
                    <td><?= htmlspecialchars($row['event_code'])?></td>
                    <td><?php 
                        $start_date = new DateTime($row['event_start']);
                        echo $start_date->format('d-m-Y H:i');
                    ?></td>
                    <td><?php 
                        $end_date = new DateTime($row['event_end']);
                        echo $end_date->format('d-m-Y H:i');
                    ?></td>
                    <td><?= htmlspecialchars($row['event_location']) ?></td>
                    <td><?= htmlspecialchars($row['event_description']) ?></td>
                    <td><?= htmlspecialchars($row['organization']) ?></td>
                    <td><?= htmlspecialchars($row['event_status']) ?></td>
                    <td class="dropdown-wrapper">
                        <button class="dropdown-toggle" type="button">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu">
                            <button class="dropdown-edit-btn" data-event-id="<?= $row['number'] ?>">
                                <i class="fas fa-edit"></i> Edit Event
                            </button>
                            <button class="dropdown-attendance-btn" data-event-id="<?= $row['number'] ?>">
                                <i class="fas fa-clipboard-check"></i> Take Attendance
                            </button>
                            <button class="dropdown-qr-btn" data-event-id="<?= $row['number'] ?>" data-event-code="<?= htmlspecialchars($row['event_code']) ?>">
                                <i class="fas fa-qrcode"></i> Generate QR
                            </button>
                            <button class="dropdown-view-participants-btn" data-event-id="<?= $row['number'] ?>">
                                <i class="fas fa-users"></i> View Participants
                            </button>
                            <?php if ($row['event_status'] === 'archived'): ?>
                            <button class="dropdown-unarchive-btn" data-event-id="<?= $row['number'] ?>">
                                <i class="fas fa-box-open"></i> Unarchive
                            </button>
                            <?php endif; ?>
                            <button class="delete-btn" data-event-id="<?= $row['number'] ?>">
                                <i class="fas fa-trash-alt"></i> Delete
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
    <!-- Add Event Modal -->
    <div class="modal" id="addEventModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Create New Event</h2>
                <span class="close-modal" onclick="closeAddModal()">&times;</span>
            </div>
            <form id="addEventForm" action="../php/create_event.php" method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <!-- Left Column -->
                    <div class="form-group">
                        <label for="newEventTitle">Event Title</label>
                        <input type="text" id="newEventTitle" name="event_title" required>
                    </div>
                    <div class="form-group">
                        <label for="newEventVenue">Venue</label>
                        <input type="text" id="newEventVenue" name="event_venue" required>
                    </div>
                    <div class="form-group">
                        <label for="newEventDate">Date</label>
                        <input type="date" id="newEventDate" name="event_date" required>
                    </div>
                    <div class="form-group">
                        <label for="newEventTime">Time</label>
                        <input type="time" id="newEventTime" name="event_time" required>
                    </div>
                    <!-- Right Column -->
                    <div class="form-group">
                        <label for="newOrganization">Organization</label>
                        <select name="organization" id="newOrganization" onchange="handleOrgChange(this, 'new')" required>
                            <option value="">Select Organization</option>
                            <option value="Open for All">Open for All</option>
                            <option value="College of Computer Studies (BSIT, BSCS)">College of Computer Studies (BSIT, BSCS)</option>
                            <option value="College of Education (BSEDUC)">College of Education (BSEDUC)</option>
                            <option value="College of Nursing (BSN)">College of Nursing (BSN)</option>
                            <option value="College of International Hospitality Management (BSHM)">College of International Hospitality Management (BSHM)</option>
                            <option value="College of Business Administration (BSBA)">College of Business Administration (BSBA)</option>
                            <option value="College of Arts and Sciences">College of Arts and Sciences</option>
                            <option value="College of Engineering (BSENG)">College of Engineering (BSENG)</option>
                            <option value="others">Others</option>
                        </select>
                        <input type="text" id="newCustomOrg" name="custom_organization" placeholder="Enter organization name" style="display: none; margin-top: 10px;">
                    </div>
                    <div class="form-group">
                        <label for="newEventDuration">Duration (hours)</label>
                        <input type="number" id="newEventDuration" name="event_duration" min="1" max="24" required>
                    </div>
                    <div class="form-group">
                        <label for="newRegistrationDeadline">Registration Deadline</label>
                        <input type="datetime-local" id="newRegistrationDeadline" name="registration_deadline" required>
                    </div>
                    <div class="form-group">
                        <label for="newCodeField">Event Code</label>
                        <input type="text" id="newCodeField" name="event_code" readonly>
                    </div>
                    <!-- Full Width Items -->
                    <div class="form-group full-width">
                        <label for="newEventDescription">Description</label>
                        <textarea id="newEventDescription" name="event_description" required></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label for="new-event-image">Event Image</label>
                        <div class="image-upload-container">
                            <div class="image-preview">
                                <img id="newPreviewImg" src="../images-icon/plm_courtyard.png" alt="Event Image Preview">
                            </div>
                            <div class="upload-controls">
                                <input type="file" name="event-image" id="newEventImage" accept="image/*" onchange="handleNewImageSelect(this)">
                                <label for="newEventImage" class="upload-btn">Choose Image</label>
                                <span id="newImageName">No file chosen</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-submit" id="createEventBtn">Create Event</button>
                    <button type="button" class="btn-cancel" onclick="closeAddModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Event Modal -->
    <div class="modal" id="editEventModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Event</h2>
                <span class="close-modal" onclick="closeEditModal()">&times;</span>
            </div>
            <form id="editEventForm" action="../php/update_event.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="editEventId" name="event_id">
                <div class="form-grid">
                    <!-- Left Column -->
                    <div class="form-group">
                        <label for="editEventTitle">Event Title</label>
                        <input type="text" id="editEventTitle" name="event_title" required>
                    </div>
                    <div class="form-group">
                        <label for="editEventVenue">Venue</label>
                        <input type="text" id="editEventVenue" name="event_venue" required>
                    </div>
                    <div class="form-group">
                        <label for="editEventDate">Date</label>
                        <input type="date" id="editEventDate" name="event_date" required>
                    </div>
                    <div class="form-group">
                        <label for="editEventTime">Time</label>
                        <input type="time" id="editEventTime" name="event_time" required>
                    </div>
                    <!-- Right Column -->
                    <div class="form-group">
                        <label for="editOrganization">Organization</label>
                        <select name="organization" id="editOrganization" onchange="handleOrgChange(this, 'edit')" required>
                            <option value="">Select Organization</option>
                            <option value="Open for All">Open for All</option>
                            <option value="College of Computer Studies (BSIT, BSCS)">College of Computer Studies (BSIT, BSCS)</option>
                            <option value="College of Education (BSEDUC)">College of Education (BSEDUC)</option>
                            <option value="College of Nursing (BSN)">College of Nursing (BSN)</option>
                            <option value="College of International Hospitality Management (BSHM)">College of International Hospitality Management (BSHM)</option>
                            <option value="College of Business Administration (BSBA)">College of Business Administration (BSBA)</option>
                            <option value="College of Arts and Sciences">College of Arts and Sciences</option>
                            <option value="College of Engineering (BSENG)">College of Engineering (BSENG)</option>
                            <option value="others">Others</option>
                        </select>
                        <input type="text" id="editCustomOrg" name="custom_organization" placeholder="Enter organization name" style="display: none; margin-top: 10px;">
                    </div>
                    <div class="form-group">
                        <label for="editEventDuration">Duration (hours)</label>
                        <input type="number" id="editEventDuration" name="event_duration" min="1" max="24" required>
                    </div>
                    <div class="form-group">
                        <label for="editRegistrationDeadline">Registration Deadline</label>
                        <input type="datetime-local" id="editRegistrationDeadline" name="registration_deadline" required>
                    </div>
                    <div class="form-group">
                        <label for="editCodeField">Event Code</label>
                        <input type="text" id="editCodeField" name="event_code" readonly>
                    </div>
                    <!-- Full Width Items -->
                    <div class="form-group full-width">
                        <label for="editEventDescription">Description</label>
                        <textarea id="editEventDescription" name="event_description" required></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label for="edit-event-image">Event Image</label>
                        <div class="image-upload-container">
                            <div class="image-preview">
                                <img id="editPreviewImg" src="../images-icon/plm_courtyard.png" alt="Event Image Preview">
                            </div>
                            <div class="upload-controls">
                                <input type="file" name="event-image" id="editEventImage" accept="image/*" onchange="handleEditImageSelect(this)">
                                <label for="editEventImage" class="upload-btn">Choose Image</label>
                                <span id="editImageName">No file chosen</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Update Event</button>
                    <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
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
    <script src="../Javascript/event-dropdown.js"></script>
    <script src="../Javascript/event-edit.js"></script>
    <script src="../Javascript/search.js"></script>
    <script>
        // Make isEditing available to the JavaScript
        window.isEditing = <?= json_encode($isEditing) ?>;
        
        // Handle Add Event button separately
        document.addEventListener('DOMContentLoaded', function() {
            // Add Event button handler
            const addEventBtn = document.getElementById('addEventBtn');
            if (addEventBtn) {
                addEventBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    openAddModal();
                });
            }

            // Form submission handler
            const addEventForm = document.getElementById('addEventForm');
            if (addEventForm) {
                addEventForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    console.log('Form submission triggered');
                    
                    // Validate form
                    if (!validateEventDateTime(this)) {
                        console.log('Validation failed');
                        return false;
                    }
                    
                    // Create FormData object
                    const formData = new FormData(this);
                    
                    // Log form data
                    for (let [key, value] of formData.entries()) {
                        console.log(`${key}: ${value}`);
                    }
                    
                    // Submit form using fetch
                    fetch('../php/create_event.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Event created successfully',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: data.message || 'Failed to create event',
                                customClass: {
                                    container: 'swal-on-top-custom-modal'
                                }
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred while creating the event',
                            customClass: {
                                container: 'swal-on-top-custom-modal'
                            }
                        });
                    });
                });
            }

            // Handle edit button clicks
            document.querySelectorAll('.dropdown-edit-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const eventId = this.getAttribute('data-event-id');
                    if (eventId) {
                        openEditModal(eventId);
                    }
                });
            });

            // Initialize dropdowns
            let activeDropdown = null;

            // Handle dropdown toggle clicks
            document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const dropdownMenu = this.nextElementSibling;
                    
                    // If there's an active dropdown and it's not this one, hide it
                    if (activeDropdown && activeDropdown !== dropdownMenu) {
                        activeDropdown.style.display = 'none';
                    }
                    
                    // Toggle current dropdown
                    if (dropdownMenu) {
                        const isVisible = dropdownMenu.style.display === 'block';
                        dropdownMenu.style.display = isVisible ? 'none' : 'block';
                        activeDropdown = isVisible ? null : dropdownMenu;
                    }
                });
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown-wrapper') && activeDropdown) {
                    activeDropdown.style.display = 'none';
                    activeDropdown = null;
                }
            });

            // Handle scroll events
            window.addEventListener('scroll', function() {
                if (activeDropdown) {
                    activeDropdown.style.display = 'none';
                    activeDropdown = null;
                }
            });
        });
    </script>
</body>
</html>