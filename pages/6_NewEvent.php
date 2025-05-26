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
    <!-- Add SweetAlert2 CSS and JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Add jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="title-container">   
        <img src="../images-icon/plplogo.png"> <h1> Pamantasan ng Lungsod ng Pasig </h1>
    </div>
    <div class="tab-container">
        <div class="menu-items">
            <a href="4_Event.php" class="active"> <i class="fa-solid fa-home"></i> <span class="label"> Home </span> </a>
            <a href="6_NewEvent.php" class="active"> <i class="fa-solid fa-calendar"></i> <span class="label"> Events </span> </a>
            <a href="registered_participants.php" class="active"> <i class="fa-solid fa-users"></i> <span class="label"> Registered Participants </span> </a>
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
                <button class="filter-btn active" data-filter="all" data-sort="number">All Events</button>
                <button class="filter-btn" data-filter="title" data-sort="event_title">Sort by Title</button>
                <button class="filter-btn" data-filter="attendees" data-sort="attendee_count">Sort by Attendees</button>
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
                        <th data-sort="event_title">Title</th>
                        <th data-sort="event_code">Event Code</th>
                        <th data-sort="date_start">Start</th>
                        <th data-sort="date_end">End</th>
                        <th data-sort="event_location">Location</th>
                        <th data-sort="event_description">Description</th>
                        <th data-sort="organization">Organization</th>   
                        <th data-sort="event_status">Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                include '../php/conn.php';
                // Get sort parameters from URL
                $sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'number';
                $sort_direction = isset($_GET['direction']) ? strtoupper($_GET['direction']) : 'DESC';

                // Validate sort column to prevent SQL injection
                $allowed_columns = [
                    'number' => 'e.number',
                    'event_title' => 'e.event_title',
                    'event_code' => 'e.event_code',
                    'date_start' => 'e.date_start',
                    'date_end' => 'e.date_end',
                    'event_location' => 'e.event_location',
                    'event_description' => 'e.event_description',
                    'organization' => 'e.organization',
                    'event_status' => 'e.event_status',
                    'attendee_count' => 'attendee_count'
                ];

                // Get filter parameters
                $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
                $status = isset($_GET['status']) ? $_GET['status'] : 'all';

                // Build the base query
                $query = "SELECT e.*, COUNT(p.number) as attendee_count 
                          FROM event_table e 
                          LEFT JOIN participants_table p ON e.number = p.number
                          WHERE e.event_status != 'archived' 
                          AND e.event_code NOT IN (SELECT a.event_code FROM archive_table a)
                          AND e.event_status IS NOT NULL";

                // Add filters
                $where_conditions = [];
                $params = [];
                $param_types = "";

                if ($filter === 'status' && $status !== 'all') {
                    $where_conditions[] = "e.event_status = ?";
                    $params[] = $status;
                    $param_types .= "s";
                }

                // Add WHERE clause if we have conditions
                if (!empty($where_conditions)) {
                    $query .= " AND " . implode(" AND ", $where_conditions);
                }

                // Add GROUP BY for attendee count
                $query .= " GROUP BY e.number";

                // Handle sorting
                if (isset($allowed_columns[$sort_column])) {
                    $query .= " ORDER BY " . $allowed_columns[$sort_column] . " " . $sort_direction;
                } else {
                    $query .= " ORDER BY e.number DESC"; // Default sorting
                }

                // Prepare and execute the query
                $stmt = $conn->prepare($query);
                if (!empty($params)) {
                    $stmt->bind_param($param_types, ...$params);
                }
                $stmt->execute();
                $result = $stmt->get_result();

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
                        <button class="dropdown-toggle" onclick="toggleDropdown(<?= $row['number'] ?>)">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu" id="dropdown<?= $row['number'] ?>">
                            <button onclick="loadEventData(<?= $row['number'] ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button onclick="archiveEvent(<?= $row['number'] ?>)">
                                <i class="fas fa-archive"></i> Archive
                            </button>
                            <button onclick="generateQRCode('<?= htmlspecialchars($row['event_code']) ?>')" data-event-id="<?= $row['number'] ?>">
                                <i class="fas fa-qrcode"></i> Generate QR
                            </button>
                            <button onclick="window.location.href='11_Attendance.php?event_id=<?= $row['number'] ?>&event_title=<?= urlencode($row['event_title']) ?>'">
                                <i class="fas fa-clipboard-check"></i> Take Attendance
                            </button>
                            <?php if ($row['event_status'] === 'Open'): ?>
                            <button onclick="closeRegistration(<?= $row['number'] ?>)">
                                <i class="fas fa-lock"></i> Close Registration
                            </button>
                            <?php endif; ?>
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
    <div class="modal" id="eventModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Create New Event</h2>
                <span class="close-modal">&times;</span>
            </div>
            <form id="eventForm" action="../php/create_event.php" method="POST">
                <div class="form-group">
                    <label for="eventTitle">Event Title</label>
                    <input type="text" id="eventTitle" name="event_title" required>
                </div>
                <div class="form-group">
                    <label for="eventDescription">Description</label>
                    <textarea id="eventDescription" name="event_description" required></textarea>
                </div>
                <div class="form-group">
                    <label for="eventVenue">Venue</label>
                    <input type="text" id="eventVenue" name="event_venue" required>
                </div>
                <div class="input-box">
                    <label for="organization">Organization</label>
                    <select name="organization" id="organization" required>
                        <option value="">Select Organization</option>
                        <option value="Open for All">Open for All</option>
                        <option value="College of Computer Studies (BSIT, BSCS)">College of Computer Studies (BSIT, BSCS)</option>
                        <option value="College of Education (BSEDUC)">College of Education (BSEDUC)</option>
                        <option value="College of Nursing (BSN)">College of Nursing (BSN)</option>
                        <option value="College of International Hospitality Management (BSHM)">College of International Hospitality Management (BSHM)</option>
                        <option value="College of Business Administration (BSBA)">College of Business Administration (BSBA)</option>
                        <option value="College of Arts and Sciences">College of Arts and Sciences</option>
                        <option value="College of Engineering (BSENG)">College of Engineering (BSENG)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="eventDate">Date</label>
                    <input type="date" id="eventDate" name="event_date" required>
                </div>
                <div class="form-group">
                    <label for="eventTime">Time</label>
                    <input type="time" id="eventTime" name="event_time" required>
                </div>
                <div class="form-group">
                    <label for="eventDuration">Duration (hours)</label>
                    <input type="number" id="eventDuration" name="event_duration" min="1" max="24" required>
                </div>
                <div class="form-group">
                    <label for="registrationDeadline">Registration Deadline</label>
                    <input type="datetime-local" id="registrationDeadline" name="registration_deadline" required>
                </div>
                <div class="form-group">
                    <label for="eventCode">Event Code</label>
                    <input type="text" id="codeField" name="event_code" readonly>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Create Event</button>
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
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
    <script src="../Javascript/qrcode.js"></script>
    <script src="../Javascript/filter.js"></script>
    <script src="../Javascript/dropdown.js"></script>
    <script src="../Javascript/event-modal.js"></script>
    <script src="../Javascript/table-sort.js"></script>

    <script>
    // Global functions for event management
    function loadEventData(eventId) {
        fetch(`../php/get_event.php?id=${eventId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Populate form with event data
                    document.getElementById('eventTitle').value = data.event.event_title;
                    document.getElementById('eventDescription').value = data.event.event_description;
                    document.getElementById('eventVenue').value = data.event.event_location;
                    document.getElementById('organization').value = data.event.organization;
                    document.getElementById('eventDate').value = data.event.date_start;
                    document.getElementById('eventTime').value = data.event.event_start.split(' ')[1];
                    document.getElementById('eventDuration').value = data.event.event_duration;
                    document.getElementById('registrationDeadline').value = data.event.registration_deadline;
                    
                    // Add hidden input for event ID
                    let eventIdInput = document.getElementById('event_id');
                    if (!eventIdInput) {
                        eventIdInput = document.createElement('input');
                        eventIdInput.type = 'hidden';
                        eventIdInput.id = 'event_id';
                        eventIdInput.name = 'event_id';
                        document.getElementById('eventForm').appendChild(eventIdInput);
                    }
                    eventIdInput.value = eventId;
                    
                    // Change form action and submit button text
                    document.getElementById('eventForm').action = '../php/update_event.php';
                    document.querySelector('.btn-submit').textContent = 'Update Event';
                    
                    // Open the modal
                    openModal();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Error loading event data'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error loading event data'
                });
            });
    }

    function archiveEvent(eventId) {
        Swal.fire({
            title: 'Archive Event',
            text: 'Are you sure you want to archive this event?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, archive it!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('../php/archive_event.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `event_id=${eventId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to archive event'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while archiving the event'
                    });
                });
            }
        });
    }

    function toggleDropdown(number) {
        const dropdown = document.getElementById('dropdown' + number);
        if (!dropdown) return;

        // Close all other dropdowns
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            if (menu.id !== 'dropdown' + number) {
                menu.style.display = 'none';
            }
        });

        // Toggle current dropdown
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.dropdown-wrapper')) {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.style.display = 'none';
            });
        }
    });

    // Prevent dropdown from closing when clicking inside
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
        menu.addEventListener('click', function(event) {
            event.stopPropagation();
        });
    });

    // Form submission handler
    document.getElementById('eventForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const isUpdate = this.action.includes('update_event.php');
        
        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    closeModal();
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'An error occurred'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while processing your request'
            });
        });
    });

    // Initialize event table
    document.addEventListener('DOMContentLoaded', function() {
        const eventTable = new TableManager('eventTable', {
            filterColumn: 8,  // Status column
            nameColumn: 1,    // Title column
            courseColumn: 7   // Organization column
        });

        // Show/hide status filter
        const statusFilterBtn = document.querySelector('[data-filter="status"]');
        const statusFilter = document.getElementById('statusFilter');
        
        if (statusFilterBtn && statusFilter) {
            // Set initial state based on URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const currentFilter = urlParams.get('filter');
            const currentStatus = urlParams.get('status');
            
            if (currentFilter === 'status') {
                statusFilterBtn.classList.add('active');
                statusFilter.style.display = 'inline-block';
                if (currentStatus) {
                    statusFilter.value = currentStatus;
                }
            }

            statusFilterBtn.addEventListener('click', function() {
                const isVisible = statusFilter.style.display === 'inline-block';
                statusFilter.style.display = isVisible ? 'none' : 'inline-block';
                
                if (!isVisible) {
                    statusFilter.value = 'all';
                    const params = new URLSearchParams(window.location.search);
                    params.set('filter', 'status');
                    params.set('status', 'all');
                    window.location.href = `${window.location.pathname}?${params.toString()}`;
                }
            });

            statusFilter.addEventListener('change', function() {
                const params = new URLSearchParams(window.location.search);
                params.set('filter', 'status');
                params.set('status', this.value);
                window.location.href = `${window.location.pathname}?${params.toString()}`;
            });
        }
    });
    </script>
</body>
</html>