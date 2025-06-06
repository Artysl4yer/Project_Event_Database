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
date_default_timezone_set('Asia/Manila'); // Set to your local timezone
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="../styles/style1.css">
        <link rel="stylesheet" href="../styles/style6.css">
        <link rel="stylesheet" href="../styles/attendance.css">
        <script src="https://kit.fontawesome.com/d78dc5f742.js" crossorigin="anonymous"></script>
        <script src="https://unpkg.com/html5-qrcode"></script>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <style>
            .attendance-container {
                display: flex;
                gap: 20px;
                margin: 20px 0;
            }
            .scanner-section {
                flex: 1;
                padding: 20px;
                border: 2px solid #e0e0e0;
                border-radius: 10px;
            }
            .manual-section {
                flex: 1;
                padding: 20px;
                border: 2px solid #e0e0e0;
                border-radius: 10px;
                background: #fff;
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            .manual-section h3 {
                margin-bottom: 18px;
                font-size: 1.2em;
                font-weight: 600;
            }
            .manual-section form {
                width: 100%;
                max-width: 400px;
                margin: 0 auto;
                display: flex;
                flex-direction: column;
                gap: 16px;
                align-items: center;
            }
            .form-group {
                width: 100%;
                display: flex;
                flex-direction: column;
                gap: 6px;
                margin-bottom: 0;
            }
            .form-group label {
                font-weight: 500;
                margin-bottom: 2px;
            }
            .form-group input,
            .form-group select {
                width: 100%;
                box-sizing: border-box;
                padding: 10px 12px;
                border: 1.5px solid #bdbdbd;
                border-radius: 5px;
                font-size: 1em;
                background: #f9f9f9;
                transition: border 0.2s;
            }
            .form-group input:focus,
            .form-group select:focus {
                border: 1.5px solid #4CAF50;
                outline: none;
                background: #fff;
            }
            .submit-btn, .generate-id-btn {
                width: 80%;
                min-width: 160px;
                max-width: 250px;
                align-self: center;
                background: #4CAF50;
                color: white;
                padding: 12px 0;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 1.1em;
                font-weight: 600;
                margin-top: 8px;
                transition: background 0.2s;
            }
            .submit-btn:hover, .generate-id-btn:hover {
                background: #388e3c;
            }
            .preview-box {
                display: none;
                margin-top: 20px;
                padding: 20px;
                border: 2px solid #e0e0e0;
                border-radius: 10px;
                background: #f7f9fc;
            }
            .preview-item {
                margin: 10px 0;
                padding: 10px;
                background: white;
                border-radius: 8px;
            }
            .message {
                margin-top: 20px;
                padding: 15px;
                border-radius: 8px;
                text-align: center;
                display: none;
            }
            .success-message {
                background: #d4edda;
                color: #155724;
                border: 1px solid #c3e6cb;
            }
            .error-message {
                background: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }
            .event-status {
                margin: 20px 0;
                padding: 15px;
                border-radius: 8px;
                background: #f8f9fa;
                border: 1px solid #dee2e6;
            }
            
            .status-badge {
                display: inline-block;
                padding: 5px 10px;
                border-radius: 15px;
                font-size: 0.9em;
                font-weight: bold;
                margin-right: 10px;
            }
            
            .status-scheduled { background: #cce5ff; color: #004085; }
            .status-ongoing { background: #d4edda; color: #155724; }
            .status-completed { background: #f8d7da; color: #721c24; }
            .status-cancelled { background: #fff3cd; color: #856404; }
            .status-archived { background: #e2e3e5; color: #383d41; }
            
            .registration-status {
                display: inline-block;
                padding: 5px 10px;
                border-radius: 15px;
                font-size: 0.9em;
                font-weight: bold;
            }
            
            .registration-open { background: #d4edda; color: #155724; }
            .registration-closed { background: #f8d7da; color: #721c24; }
            
            .control-buttons {
                margin-top: 10px;
            }
            
            .control-btn {
                padding: 8px 15px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-weight: bold;
                margin-right: 10px;
            }
            
            .open-registration { background: #28a745; color: white; }
            .close-registration { background: #dc3545; color: white; }
            
            .event-time {
                margin-top: 10px;
                font-size: 0.9em;
                color: #6c757d;
            }
            
            .disabled-section {
                opacity: 0.6;
                pointer-events: none;
            }
            @media (max-width: 900px) {
                .attendance-container {
                    flex-direction: column;
                }
                .manual-section form {
                    max-width: 100%;
                }
            }
            @media (max-width: 700px) {
                .manual-section form {
                    gap: 12px;
                }
                .manual-section {
                    padding: 10px;
                }
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
                <a href="../php/logout.php"> <i class="fa-solid fa-gear"></i> <span class="label"> Logout </span> </a>
            </div>
        </div>
        <div class="main-container">
            <?php
                include '../php/conn.php';
                require_once '../php/update_event_status.php';
                updateEventStatuses($conn);
                $event_id = isset($_GET['event']) ? $_GET['event'] : null;
                $event_title = isset($_GET['event_title']) ? urldecode($_GET['event_title']) : null;

                if ($event_id !== null) {
                    $event_query = "SELECT *, 
                        TIMESTAMPDIFF(MINUTE, NOW(), event_start) as minutes_until_start,
                        TIMESTAMPDIFF(MINUTE, NOW(), event_end) as minutes_until_end
                        FROM event_table WHERE number = ?";
                    $stmt = $conn->prepare($event_query);
                    $stmt->bind_param("i", $event_id);
                } else if ($event_title !== null) {
                    $event_query = "SELECT *, 
                        TIMESTAMPDIFF(MINUTE, NOW(), event_start) as minutes_until_start,
                        TIMESTAMPDIFF(MINUTE, NOW(), event_end) as minutes_until_end
                        FROM event_table WHERE event_title = ?";
                    $stmt = $conn->prepare($event_query);
                    $stmt->bind_param("s", $event_title);
                } else {
                    echo "<div class='error-message'>No event selected or event not found.</div>";
                    exit;
                }

                $stmt->execute();
                $event_result = $stmt->get_result();
                $event_row = $event_result->fetch_assoc();

                if (!$event_row) {
                    echo "<div class='error-message'>No event selected or event not found.</div>";
                    exit;
                }

                $event_number = $event_row['number'];
                $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
                $registration_deadline = new DateTime($event_row['registration_deadline'], new DateTimeZone('Asia/Manila'));
                $is_registration_open = strtolower(trim($event_row['registration_status'])) === 'open'
                    && $now <= $registration_deadline;
                $can_control_registration = isset($_SESSION['client_id']) && 
                    ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'coordinator');

                $eventStart = new DateTime($event_row['event_start'], new DateTimeZone('Asia/Manila'));
                $eventEnd = new DateTime($event_row['event_end'], new DateTimeZone('Asia/Manila'));
                $canMarkAttendance = $is_registration_open
                    && in_array($event_row['event_status'], ['scheduled', 'ongoing'])
                    && $now >= $eventStart && $now <= $eventEnd;
            ?>
            <div class="attendance-top">
                <div class="event-title">
                    <h2>Event: <?php echo htmlspecialchars($event_row['event_title']); ?></h2>
                </div>
                
                <div class="event-status">
                    <div>
                        <span class="status-badge status-<?php echo $event_row['event_status']; ?>">
                            <?php echo ucfirst($event_row['event_status']); ?>
                        </span>
                        <span class="registration-status registration-<?php echo isset($event_row['registration_status']) && $event_row['registration_status'] ? $event_row['registration_status'] : 'unknown'; ?>">
                            Registration <?php echo isset($event_row['registration_status']) && $event_row['registration_status'] ? ucfirst($event_row['registration_status']) : 'Unknown'; ?>
                        </span>
                    </div>
                    
                    <div class="event-time">
                        <p>Start: 
                        <?php
                        if (!empty($event_row['event_start'])) {
                            $start_time = new DateTime($event_row['event_start'], new DateTimeZone('Asia/Manila'));
                            echo $start_time->format('F j, Y g:i A');
                        } else {
                            echo 'N/A';
                        }
                        ?>
                        </p>
                        <p>Registration Deadline: 
                        <?php
                        if (!empty($event_row['registration_deadline'])) {
                            $deadline = new DateTime($event_row['registration_deadline'], new DateTimeZone('Asia/Manila'));
                            echo $deadline->format('F j, Y g:i A');
                        } else {
                            echo 'N/A';
                        }
                        ?>
                        </p>
                    </div>
                    
                    <?php if ($can_control_registration): ?>
                    <div class="control-buttons">
                        <?php if ($is_registration_open): ?>
                            <button class="control-btn close-registration" onclick="toggleRegistration('closed')">
                                Close Registration
                            </button>
                        <?php else: ?>
                            <button class="control-btn open-registration" onclick="toggleRegistration('open')">
                                Open Registration
                            </button>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="attendance-container <?php echo !$canMarkAttendance ? 'disabled-section' : ''; ?>">
                    <?php if (!$canMarkAttendance): ?>
                        <div class="error-message">
                            <?php if (!$is_registration_open): ?>
                                Registration is closed for this event.
                            <?php elseif (!in_array($event_row['event_status'], ['scheduled', 'ongoing'])): ?>
                                Attendance is not allowed. Event status: <?php echo htmlspecialchars($event_row['event_status']); ?>
                            <?php elseif ($now < $eventStart): ?>
                                Attendance is not allowed before the event starts.
                            <?php elseif ($now > $eventEnd): ?>
                                Attendance is not allowed after the event ends.
                            <?php else: ?>
                                Attendance is not allowed at this time.
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                    <div class="scanner-section">
                        <h3><i class="fas fa-qrcode"></i> Scan QR Code</h3>
                        <div id="reader"></div>
                        <div class="preview-box" id="preview-box">
                            <h3>Student Details</h3>
                            <div class="preview-item"><strong>Name:</strong> <span id="preview-name"></span></div>
                            <div class="preview-item"><strong>Student ID:</strong> <span id="preview-id"></span></div>
                            <div class="preview-item"><strong>Course:</strong> <span id="preview-course"></span></div>
                            <button class="submit-btn" id="confirm-attendance">Confirm Attendance</button>
                            <button class="submit-btn" style="background: #95a5a6; margin-top: 10px;" id="cancel-scan">Cancel</button>
                        </div>
                    </div>

                    <div class="manual-section">
                        <h3><i class="fas fa-keyboard"></i> Manual Entry</h3>
                        <form class="attendance-form" id="manual-form">
                            <div class="form-group">
                                <label for="studentId">Student ID</label>
                                <input type="text" id="studentId" name="studentId" 
                                       placeholder="Enter Student ID (e.g., 23-00239)" 
                                       pattern="[0-9]{2}-[0-9]{5}" required>
                            </div>
                            <button type="submit" class="submit-btn">Mark Attendance</button>
                        </form>
                    </div>

                    <?php if ($event_row['organization'] === 'Open for All'): ?>
                    <div class="manual-section">
                        <h3><i class="fas fa-user-plus"></i> Register Guest</h3>
                        <form id="guestForm">
                            <div class="form-group">
                                <label for="guestID">ID</label>
                                <input type="text" id="guestID" name="ID" readonly required>
                                <button type="button" class="generate-id-btn" onclick="generateGuestID()">Generate ID</button>
                            </div>
                            <div class="form-group">
                                <label for="guestFirstName">First Name</label>
                                <input type="text" id="guestFirstName" name="first_name" required>
                            </div>
                            <div class="form-group">
                                <label for="guestLastName">Last Name</label>
                                <input type="text" id="guestLastName" name="last_name" required>
                            </div>
                            <div class="form-group">
                                <label for="guestEmail">Email</label>
                                <input type="email" id="guestEmail" name="email">
                            </div>
                            <div class="form-group">
                                <label for="guestGender">Gender</label>
                                <select id="guestGender" name="Gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="guestAge">Age</label>
                                <input type="number" id="guestAge" name="Age" min="1" max="120" required>
                            </div>
                            <button type="submit" class="submit-btn">Register & Mark Attendance</button>
                        </form>
                    </div>
                    <script>
                    function generateGuestID() {
                        // Example: GUEST-XXXXX
                        const id = 'GUEST-' + Math.floor(10000 + Math.random() * 90000);
                        document.getElementById('guestID').value = id;
                    }
                    </script>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>

                <div class="message" id="message"></div>
            </div>
        </div>

        <script>
            let html5QrcodeScanner = null;
            let currentScanData = null;
            const eventNumber = <?php echo $event_number; ?>;

            // QR Code Scanner Functions
            function onScanSuccess(decodedText, decodedResult) {
                console.log('QR Code scanned:', decodedText);
                
                // Stop scanner temporarily
                if (html5QrcodeScanner) {
                    html5QrcodeScanner.pause();
                }
                
                // Clean and normalize the text
                decodedText = decodedText.replace(/\[/g, '(').replace(/\]/g, ')');
                decodedText = decodedText.replace(/\.$/, '');
                decodedText = decodedText.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
                decodedText = decodedText.trim();
                
                // Split into lines and clean each line
                const lines = decodedText.split('\n')
                    .map(line => line.trim())
                    .filter(line => line.length > 0);
                
                console.log('Cleaned lines:', lines);
                
                if (lines.length !== 3) {
                    showMessage('Invalid QR code format. Expected 3 lines: Name, ID, Course', false);
                    setTimeout(() => {
                        if (html5QrcodeScanner) {
                            html5QrcodeScanner.resume();
                        }
                    }, 2000);
                    return;
                }
                
                const [fullName, studentId, course] = lines;
                // Remove any brackets or parentheses from student ID
                const cleanStudentId = studentId.replace(/[\[\]()]/g, '');
                // Clean course (remove any stray parentheses)
                const cleanCourse = course.replace(/[\[\]()]/g, '');
                
                // Validate student ID format
                if (!/^\d{2}-\d{5}$/.test(cleanStudentId)) {
                    showMessage('Invalid student ID format. Expected format: YY-XXXXX', false);
                    setTimeout(() => {
                        if (html5QrcodeScanner) {
                            html5QrcodeScanner.resume();
                        }
                    }, 2000);
                    return;
                }
                
                // Store the data
                currentScanData = {
                    full_name: fullName,
                    student_id: cleanStudentId,
                    course: cleanCourse
                };
                
                console.log('Processed data:', currentScanData);
                
                // Show preview
                showPreview(currentScanData);
            }

            function onScanFailure(error) {
                // Only log the error if it's not the "Cannot pause" error
                if (!error.includes('Cannot pause')) {
                    console.warn(`QR scan error: ${error}`);
                }
            }

            // Check Student Function
            function checkStudent(studentId) {
                fetch(`../php/check_students.php?student_id=${studentId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            currentScanData = data.student;
                            showPreview(data.student);
                        } else {
                            showMessage(data.message, false);
                        }
                    })
                    .catch(error => {
                        showMessage('Error checking student', false);
                    });
            }

            // Show Preview Function
            function showPreview(student) {
                document.getElementById('preview-name').textContent = student.full_name;
                document.getElementById('preview-id').textContent = student.student_id;
                document.getElementById('preview-course').textContent = student.course;
                document.getElementById('preview-box').style.display = 'block';
                if (html5QrcodeScanner) {
                    html5QrcodeScanner.pause();
                }
            }

            // Process Attendance Function
            async function processAttendance(qrData) {
                try {
                    // Get event ID from URL
                    const urlParams = new URLSearchParams(window.location.search);
                    const eventId = urlParams.get('event');
                    
                    if (!eventId) {
                        throw new Error('Event ID not found in URL');
                    }

                    // Disable the scan button to prevent multiple submissions
                    const scanButton = document.querySelector('button[onclick="startScan()"]');
                    if (scanButton) {
                        scanButton.disabled = true;
                    }

                    let processedData;
                    
                    // Handle both object and string inputs
                    if (typeof qrData === 'object') {
                        processedData = qrData;
                    } else {
                        // Clean and process QR data if it's a string
                        const lines = qrData.split('\n').map(line => line.trim());
                        console.log('Cleaned lines:', lines);
                        
                        // Remove parentheses from student ID
                        const studentId = lines[1].replace(/[()]/g, '');
                        
                        processedData = {
                            full_name: lines[0],
                            student_id: studentId,
                            course: lines[2]
                        };
                    }
                    
                    console.log('Processed data:', processedData);

                    // Format data for sending
                    const qrDataString = `${processedData.full_name}\n${processedData.student_id}\n${processedData.course}`;

                    // Send attendance data
                    const response = await $.ajax({
                        url: '../php/process_attendance.php',
                        method: 'POST',
                        data: {
                            event_id: eventId,
                            qr_data: qrDataString
                        }
                    });

                    // Parse response if it's a string
                    const result = typeof response === 'string' ? JSON.parse(response) : response;

                    if (result.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: result.message || 'Attendance registered successfully',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // Reset scanner and preview
                            document.getElementById('preview-box').style.display = 'none';
                            currentScanData = null;
                            if (html5QrcodeScanner) {
                                html5QrcodeScanner.resume();
                            }
                        });
                    } else {
                        throw new Error(result.message || 'Failed to register attendance');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'An error occurred while processing attendance'
                    }).then(() => {
                        // Reset scanner and preview on error
                        document.getElementById('preview-box').style.display = 'none';
                        currentScanData = null;
                        if (html5QrcodeScanner) {
                            html5QrcodeScanner.resume();
                        }
                    });
                } finally {
                    // Re-enable the scan button
                    const scanButton = document.querySelector('button[onclick="startScan()"]');
                    if (scanButton) {
                        scanButton.disabled = false;
                    }
                }
            }

            // Show Message Function
            function showMessage(message, isSuccess) {
                const messageDiv = document.getElementById('message');
                messageDiv.textContent = message;
                messageDiv.className = `message ${isSuccess ? 'success-message' : 'error-message'}`;
                messageDiv.style.display = 'block';
                setTimeout(() => {
                    messageDiv.style.display = 'none';
                }, 3000);
            }

            // Initialize QR Scanner
            html5QrcodeScanner = new Html5QrcodeScanner(
                "reader",
                { fps: 10, qrbox: { width: 250, height: 250 } },
                false
            );
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);

            // Event Listeners
            document.getElementById('confirm-attendance').addEventListener('click', function() {
                if (currentScanData) {
                    processAttendance(currentScanData);
                }
            });

            document.getElementById('cancel-scan').addEventListener('click', function() {
                document.getElementById('preview-box').style.display = 'none';
                currentScanData = null;
                if (html5QrcodeScanner) {
                    html5QrcodeScanner.resume();
                }
            });

            document.getElementById('manual-form').addEventListener('submit', function(e) {
                e.preventDefault();
                const studentId = document.getElementById('studentId').value;
                checkStudent(studentId);
            });

            function toggleRegistration(newStatus) {
                // Use $event_row['number'] as it's reliably the event's primary key
                const currentEventId = <?php echo json_encode($event_row['number']); ?>;

                $.ajax({
                    url: '../php/update_registration_status.php',
                    method: 'POST',
                    data: {
                        event_id: currentEventId,
                        status: newStatus
                    },
                    dataType: 'json',
                    success: function(response) {
                        showMessage(response.message, response.success);
                        if (response.success) {
                            const attendanceContainer = document.querySelector('.attendance-container');
                            const registrationStatusSpan = document.querySelector('.registration-status');
                            const controlButtonContainer = document.querySelector('.control-buttons');

                            if (newStatus === 'closed') {
                                if (attendanceContainer) {
                                    attendanceContainer.classList.add('disabled-section');
                                }
                                if (registrationStatusSpan) {
                                    registrationStatusSpan.textContent = 'Registration Closed';
                                    registrationStatusSpan.className = 'registration-status registration-closed';
                                }
                                if (controlButtonContainer) {
                                    controlButtonContainer.innerHTML = `
                                        <button class="control-btn open-registration" onclick="toggleRegistration('open')">
                                            Open Registration
                                        </button>
                                    `;
                                }
                            } else { // newStatus === 'open'
                                // For 'open', optimistically update text and button.
                                // The 'disabled-section' removal is best handled by PHP on reload,
                                // as $canMarkAttendance depends on multiple server-side conditions (event time, etc.).
                                if (registrationStatusSpan) {
                                    registrationStatusSpan.textContent = 'Registration Open';
                                    registrationStatusSpan.className = 'registration-status registration-open';
                                }
                                if (controlButtonContainer) {
                                    controlButtonContainer.innerHTML = `
                                        <button class="control-btn close-registration" onclick="toggleRegistration('closed')">
                                            Close Registration
                                        </button>
                                    `;
                                }
                            }
                            // Reload the page to fully update based on PHP logic and ensure consistency
                            setTimeout(() => location.reload(), 1500);
                        }
                    },
                    error: function(xhr, status, error) {
                        showMessage('Error updating registration status: ' + error, false);
                    }
                });
            }

            // Guest Registration Modal
            function showGuestSuccessModal(guest) {
                Swal.fire({
                    icon: 'success',
                    title: 'Guest Registered!',
                    html: `<b>Name:</b> ${guest.first_name} ${guest.last_name}<br>` +
                          `<b>ID:</b> ${guest.ID}<br>` +
                          `<b>Email:</b> ${guest.email || 'N/A'}<br>` +
                          `<b>Gender:</b> ${guest.Gender}<br>` +
                          `<b>Age:</b> ${guest.Age}`,
                    timer: 3000,
                    showConfirmButton: false
                });
            }

            // Modify guest form submit handler to show modal
            const guestForm = document.getElementById('guestForm');
            if (guestForm) {
                guestForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    formData.append('event_id', <?= json_encode($event_row['number']) ?>);
                    fetch('../php/register_guest.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            // Show modal with guest details
                            showGuestSuccessModal({
                                first_name: document.getElementById('guestFirstName').value,
                                last_name: document.getElementById('guestLastName').value,
                                ID: document.getElementById('guestID').value,
                                email: document.getElementById('guestEmail').value,
                                Gender: document.getElementById('guestGender').value,
                                Age: document.getElementById('guestAge').value
                            });
                            this.reset();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'Guest registration failed.'
                            });
                        }
                    });
                });
            }
        </script>
    </body>
</html>