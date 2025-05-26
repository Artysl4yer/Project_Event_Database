<?php
session_start();
include '../php/conn.php';

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['email'], $_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'coordinator'])) {
    header("Location: ../pages/1_Login.php");
    exit();
}

// Get event details
$event_id = isset($_GET['event_id']) ? $_GET['event_id'] : null;
$event_title = isset($_GET['event_title']) ? $_GET['event_title'] : null;

if ($event_id) {
    $sql = "SELECT * FROM event_table WHERE number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $event = $result->fetch_assoc();
        
        // Format dates properly
        $start_date = !empty($event['date_start']) ? date('F j, Y g:i A', strtotime($event['date_start'])) : 'Not set';
        $end_date = !empty($event['date_end']) ? date('F j, Y g:i A', strtotime($event['date_end'])) : 'Not set';
        
        // Check if event is ongoing
        $current_time = new DateTime('now', new DateTimeZone('Asia/Manila'));
        $event_start = new DateTime($event['date_start'], new DateTimeZone('Asia/Manila'));
        $event_end = new DateTime($event['date_end'], new DateTimeZone('Asia/Manila'));
        
        $is_ongoing = $current_time >= $event_start && $current_time <= $event_end;
        $is_finished = $current_time > $event_end;
        
        // Update event status if needed
        if ($is_ongoing && $event['event_status'] !== 'ongoing') {
            $update_sql = "UPDATE event_table SET event_status = 'ongoing' WHERE number = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $event_id);
            $update_stmt->execute();
            $event['event_status'] = 'ongoing';
        } elseif ($is_finished && $event['event_status'] !== 'completed') {
            $update_sql = "UPDATE event_table SET event_status = 'completed' WHERE number = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $event_id);
            $update_stmt->execute();
            $event['event_status'] = 'completed';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PLP: Take Attendance</title>
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
        }
        #reader {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
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
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .submit-btn {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        .submit-btn:hover {
            background: #45a049;
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
        
        /* Registration Modal Styles */
        .registration-container {
            padding: 20px;
        }
        
        .registration-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .tab-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background: #f0f0f0;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .tab-btn.active {
            background: #4CAF50;
            color: white;
        }
        
        .registration-form {
            margin-top: 20px;
        }
        
        .registration-form .form-group {
            margin-bottom: 15px;
        }
        
        .registration-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        
        .registration-form input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .registration-form input:focus {
            border-color: #4CAF50;
            outline: none;
            box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
        }
        
        .swal2-popup {
            font-size: 1.1em !important;
        }
        
        .swal2-input {
            margin: 10px auto !important;
        }
        
        .swal2-confirm {
            background-color: #4CAF50 !important;
        }
        
        .swal2-cancel {
            background-color: #f44336 !important;
        }
    </style>
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
            <a href="8_archive.php" class="active"> <i class="fa-solid fa-bars"></i> <span class="label"> Logs </span> </a>
        </div>
        <div class="logout">
            <a href="../php/1logout.php" onclick="return confirm('Are you sure you want to logout?');"> <i class="fa-solid fa-right-from-bracket"></i> <span class="label"> Logout </span> </a>
        </div>
    </div>
    <div class="main-container">
        <?php
            $event_number = $event['number'];
            $is_registration_open = $event['registration_status'] === 'open';
            $can_control_registration = isset($_SESSION['client_id']) && 
                ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'coordinator');
        ?>
        <div class="attendance-top">
            <div class="event-title">
                <h2>Event: <?php echo htmlspecialchars($event['event_title']); ?></h2>
            </div>
            
            <div class="event-status">
                <div>
                    <span class="status-badge status-<?php echo $event['event_status']; ?>">
                        <?php echo ucfirst($event['event_status']); ?>
                    </span>
                    <span class="registration-status registration-<?php echo $event['registration_status']; ?>">
                        Registration <?php echo ucfirst($event['registration_status']); ?>
                    </span>
                </div>
                
                <div class="event-time">
                    <p>Start: <?php echo date('F j, Y g:i A', strtotime($event['date_start'])); ?></p>
                    <p>End: <?php echo date('F j, Y g:i A', strtotime($event['date_end'])); ?></p>
                    <?php if ($event['registration_deadline']): ?>
                        <p>Registration Deadline: <?php echo date('F j, Y g:i A', strtotime($event['registration_deadline'])); ?></p>
                    <?php endif; ?>
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
            
            <div class="attendance-container <?php echo !$is_registration_open ? 'disabled-section' : ''; ?>">
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
            
            // Check if this is a registration code
            if (decodedText.startsWith('REG:')) {
                // Split the registration code into parts
                const parts = decodedText.split(':');
                if (parts.length === 3) {
                    const [prefix, eventId, eventCode] = parts;
                    console.log('Registration code detected:', { eventId, eventCode });
                    
                    // Show registration modal instead of redirecting
                    showRegistrationModal(eventCode, eventId);
                    return;
                }
            }
            
            // Split into lines and clean each line
            const lines = decodedText.split('\n')
                .map(line => line.trim())
                .filter(line => line.length > 0);
            
            console.log('Cleaned lines:', lines);
            
            // If it's just an event code
            if (lines.length === 1) {
                const eventCode = lines[0];
                // Handle event code
                processAttendance(eventCode);
                return;
            }
            
            // Handle student information format
            if (lines.length < 3) {
                showMessage('Invalid QR code format. Expected at least 3 lines: Name, ID, Course', false);
                setTimeout(() => {
                    if (html5QrcodeScanner) {
                        html5QrcodeScanner.resume();
                    }
                }, 2000);
                return;
            }
            
            const [fullName, studentId, course, eventCode] = lines;
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
                course: cleanCourse,
                event_code: eventCode || null
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
            fetch(`../php/check_student.php?student_id=${studentId}`)
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
                // Get event ID from PHP variable
                const eventId = <?php echo json_encode($event_id); ?>;
                
                if (!eventId) {
                    throw new Error('Event ID not found');
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

        function toggleRegistration(status) {
            $.ajax({
                url: '../php/update_registration_status.php',
                method: 'POST',
                data: {
                    event_id: <?php echo $event_id; ?>,
                    status: status
                },
                dataType: 'json',
                success: function(response) {
                    showMessage(response.message, response.success);
                    if (response.success) {
                        // Reload the page to update the status
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function(xhr, status, error) {
                    showMessage('Error updating registration status: ' + error, false);
                }
            });
        }

        // Add this new function for the registration modal
        function showRegistrationModal(eventCode, eventId) {
            Swal.fire({
                title: 'Event Registration',
                html: `
                    <div class="registration-container">
                        <div class="registration-tabs">
                            <button class="tab-btn active" onclick="switchTab('student')">Student</button>
                            <button class="tab-btn" onclick="switchTab('guest')">Guest</button>
                        </div>
                        
                        <div id="student-form" class="registration-form">
                            <div class="form-group">
                                <label for="student-name">Full Name</label>
                                <input type="text" id="student-name" class="swal2-input" placeholder="Enter full name">
                            </div>
                            <div class="form-group">
                                <label for="student-id">Student ID</label>
                                <input type="text" id="student-id" class="swal2-input" placeholder="YY-XXXXX">
                            </div>
                            <div class="form-group">
                                <label for="student-course">Course</label>
                                <input type="text" id="student-course" class="swal2-input" placeholder="Enter course">
                            </div>
                        </div>
                        
                        <div id="guest-form" class="registration-form" style="display: none;">
                            <div class="form-group">
                                <label for="guest-name">Full Name</label>
                                <input type="text" id="guest-name" class="swal2-input" placeholder="Enter full name">
                            </div>
                            <div class="form-group">
                                <label for="guest-email">Email</label>
                                <input type="email" id="guest-email" class="swal2-input" placeholder="Enter email">
                            </div>
                            <div class="form-group">
                                <label for="guest-contact">Contact Number</label>
                                <input type="tel" id="guest-contact" class="swal2-input" placeholder="Enter contact number">
                            </div>
                            <div class="form-group">
                                <label for="guest-org">Organization</label>
                                <input type="text" id="guest-org" class="swal2-input" placeholder="Enter organization">
                            </div>
                            <div class="form-group">
                                <label for="guest-position">Position</label>
                                <input type="text" id="guest-position" class="swal2-input" placeholder="Enter position">
                            </div>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Register',
                cancelButtonText: 'Cancel',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    const activeTab = document.querySelector('.tab-btn.active').textContent.toLowerCase();
                    if (activeTab === 'student') {
                        return registerStudent(eventId);
                    } else {
                        return registerGuest(eventId);
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Registration Successful!',
                        text: 'Thank you for registering.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        if (html5QrcodeScanner) {
                            html5QrcodeScanner.resume();
                        }
                    });
                }
            });
        }

        function switchTab(tab) {
            const studentForm = document.getElementById('student-form');
            const guestForm = document.getElementById('guest-form');
            const tabs = document.querySelectorAll('.tab-btn');
            
            tabs.forEach(t => t.classList.remove('active'));
            event.target.classList.add('active');
            
            if (tab === 'student') {
                studentForm.style.display = 'block';
                guestForm.style.display = 'none';
            } else {
                studentForm.style.display = 'none';
                guestForm.style.display = 'block';
            }
        }

        async function registerStudent(eventId) {
            const name = document.getElementById('student-name').value;
            const studentId = document.getElementById('student-id').value;
            const course = document.getElementById('student-course').value;
            
            if (!name || !studentId || !course) {
                Swal.showValidationMessage('Please fill in all fields');
                return false;
            }
            
            try {
                const response = await fetch('../php/register_participant.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        event_id: eventId,
                        full_name: name,
                        student_id: studentId,
                        course: course
                    })
                });
                
                const data = await response.json();
                if (!data.success) {
                    throw new Error(data.message);
                }
                
                return true;
            } catch (error) {
                Swal.showValidationMessage(error.message);
                return false;
            }
        }

        async function registerGuest(eventId) {
            const name = document.getElementById('guest-name').value;
            const email = document.getElementById('guest-email').value;
            const contact = document.getElementById('guest-contact').value;
            const org = document.getElementById('guest-org').value;
            const position = document.getElementById('guest-position').value;
            
            if (!name || !email || !contact) {
                Swal.showValidationMessage('Please fill in all required fields');
                return false;
            }
            
            try {
                const response = await fetch('../php/register_guest.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        event_id: eventId,
                        full_name: name,
                        email: email,
                        contact_number: contact,
                        organization: org,
                        position: position
                    })
                });
                
                const data = await response.json();
                if (!data.success) {
                    throw new Error(data.message);
                }
                
                return true;
            } catch (error) {
                Swal.showValidationMessage(error.message);
                return false;
            }
        }
    </script>
</body>
</html>