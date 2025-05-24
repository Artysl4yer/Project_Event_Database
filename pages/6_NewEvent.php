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
    <link rel="stylesheet" href="../styles/event_modal.css">
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
                <button class="filter-btn btn-import" onclick="openModal()">
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
                          LEFT JOIN participants_table p ON e.number = p.number";

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
                    $query .= " WHERE " . implode(" AND ", $where_conditions);
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
            <form id="eventForm" action="../php/<?= $isEditing ? 'update_event.php' : 'event-sub.php' ?>" method="POST" enctype="multipart/form-data">
                <?php if ($isEditing): ?>
                <input type="hidden" name="event_id" value="<?= $eventData['number'] ?>">
                <?php endif; ?>
                
                <div class="form-container">
                    <!-- Image Upload Section -->
                    <div class="image-upload-section">
                        <div class="image-preview-container">
                            <div id="imagePreview" class="image-preview">
                                <img id="previewImg" src="<?= $isEditing ? '../' . htmlspecialchars($eventData['file']) : '../images-icon/plm_courtyard.png' ?>" alt="Event Preview">
                            </div>
                            <div class="upload-controls">
                                <label for="fileInput" class="upload-btn">
                                    <i class="fas fa-camera"></i>
                                    <span>Choose Image</span>
                                </label>
                                <input type="file" id="fileInput" name="file" accept="image/*" onchange="handleFileSelect(this)" style="display: none;">
                                <span id="fileName" class="file-name">No file chosen</span>
                            </div>
                        </div>
                    </div>

                    <!-- Form Fields Section -->
                    <div class="form-fields">
                        <div class="form-row">
                            <div class="input-box full-width">
                                <label for="event-title">Event Title</label>
                                <input type="text" name="event-title" id="event-title" value="<?= $isEditing ? htmlspecialchars($eventData['event_title']) : '' ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="input-box full-width">
                                <label for="event-location">Location</label>
                                <input type="text" name="event-location" id="event-location" value="<?= $isEditing ? htmlspecialchars($eventData['event_location']) : '' ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="input-box">
                                <label for="event-date-start">Start Date & Time</label>
                                <div class="datetime-inputs">
                                    <input type="date" name="event-date-start" id="event-date-start" value="<?= $isEditing ? $eventData['date_start_date'] : '' ?>" required>
                                    <input type="time" name="event-time-start" id="event-time-start" value="<?= $isEditing ? $eventData['date_start_time'] : '' ?>" required>
                                </div>
                            </div>
                            <div class="input-box">
                                <label for="event-date-end">End Date & Time</label>
                                <div class="datetime-inputs">
                                    <input type="date" name="event-date-end" id="event-date-end" value="<?= $isEditing ? $eventData['date_end_date'] : '' ?>" required>
                                    <input type="time" name="event-time-end" id="event-time-end" value="<?= $isEditing ? $eventData['date_end_time'] : '' ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="input-box">
                                <label for="event-orgs">Organization</label>
                                <select name="event-orgs" id="event-orgs" required>
                                    <option value="">Select Organization</option>
                                    <option value="College of Computer Studies" <?= $isEditing && $eventData['organization'] == 'College of Computer Studies' ? 'selected' : '' ?>>College of Computer Studies</option>
                                    <option value="College of Engineering" <?= $isEditing && $eventData['organization'] == 'College of Engineering' ? 'selected' : '' ?>>College of Engineering</option>
                                    <option value="College of Business Accounting" <?= $isEditing && $eventData['organization'] == 'College of Business Accounting' ? 'selected' : '' ?>>College of Business Accounting</option>
                                    <option value="College of Nursing" <?= $isEditing && $eventData['organization'] == 'College of Nursing' ? 'selected' : '' ?>>College of Nursing</option>
                                    <option value="All Courses" <?= $isEditing && $eventData['organization'] == 'All Courses' ? 'selected' : '' ?>>All Courses</option>
                                </select>
                            </div>
                            <div class="input-box">
                                <label for="event-status">Status</label>
                                <select name="event-status" id="event-status" required>
                                    <option value="Ongoing" <?= $isEditing && $eventData['event_status'] == 'Ongoing' ? 'selected' : '' ?>>Ongoing</option>
                                    <option value="Finished" <?= $isEditing && $eventData['event_status'] == 'Finished' ? 'selected' : '' ?>>Finished</option>
                                    <option value="Archived" <?= $isEditing && $eventData['event_status'] == 'Archived' ? 'selected' : '' ?>>Archived</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="input-box full-width">
                                <label for="event-description">Description</label>
                                <textarea id="event-description" name="event-description" rows="4"><?= $isEditing ? htmlspecialchars($eventData['event_description']) : '' ?></textarea>
                            </div>
                        </div>

                        <?php if (!$isEditing): ?>
                        <div class="form-row">
                            <div class="input-box full-width">
                                <label for="event-code">Event Code</label>
                                <input type="text" name="code" id="codeField" readonly>
                                <small class="help-text">This code will be auto-generated for QR code registration</small>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-submit"><?= $isEditing ? 'Update Event' : 'Create Event' ?></button>
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
    <script src="../Javascript/utils/code-generator.js"></script>
    <script src="../Javascript/qrcode.js"></script>
    <script src="../Javascript/filter.js"></script>
    <script src="../Javascript/dropdown.js"></script>
    <script src="../Javascript/event_modal.js"></script>
    <script src="../Javascript/table-sortjs"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize event table
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
                    // Reset to 'all' when showing the filter
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