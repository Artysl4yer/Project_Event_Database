<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: 1_Login.php");
    exit;
}

// Get event ID from URL
$event_id = isset($_GET['event_id']) ? $_GET['event_id'] : null;
if (!$event_id) {
    header("Location: events.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        #reader {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
        }
        .attendance-form {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        #manual-input {
            margin-top: 20px;
        }
        .success-message {
            color: green;
            margin-top: 10px;
        }
        .error-message {
            color: red;
            margin-top: 10px;
        }
        #preview {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            display: none;
        }
        .preview-item {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2 class="text-center mb-4">Event Attendance</h2>
        
        <div class="attendance-form">
            <div id="reader"></div>
            
            <div id="preview" class="mt-3">
                <h4>Scanned Information</h4>
                <div class="preview-item"><strong>Name:</strong> <span id="preview-name"></span></div>
                <div class="preview-item"><strong>Student ID:</strong> <span id="preview-id"></span></div>
                <div class="preview-item"><strong>Course:</strong> <span id="preview-course"></span></div>
                <button id="confirm-scan" class="btn btn-success mt-3">Confirm Registration</button>
                <button id="cancel-scan" class="btn btn-secondary mt-3 ms-2">Cancel</button>
            </div>
            
            <div id="manual-input" class="mt-4">
                <h4>Manual Entry</h4>
                <form id="manual-form" class="mt-3">
                    <div class="mb-3">
                        <label for="student_id" class="form-label">Student ID</label>
                        <input type="text" class="form-control" id="student_id" 
                               pattern="[0-9]{2}-[0-9]{5}" 
                               placeholder="Format: 23-00239" required>
                        <div class="form-text">Enter student ID in format: YY-XXXXX</div>
                    </div>
                    <button type="submit" class="btn btn-primary">Register Attendance</button>
                </form>
            </div>
            
            <div id="message" class="mt-3"></div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let currentScanData = null;
        let html5QrcodeScanner = null;

        function onScanSuccess(decodedText, decodedResult) {
            // Stop scanning after successful scan
            html5QrcodeScanner.pause();
            
            // Parse the QR code data
            const lines = decodedText.split('\n').map(line => line.trim());
            if (lines.length !== 3) {
                showMessage('Invalid QR code format', false);
                setTimeout(() => html5QrcodeScanner.resume(), 2000);
                return;
            }

            // Store the data and show preview
            currentScanData = {
                full_name: lines[0],
                student_id: lines[1],
                course: lines[2]
            };

            // Show preview
            $('#preview-name').text(currentScanData.full_name);
            $('#preview-id').text(currentScanData.student_id);
            $('#preview-course').text(currentScanData.course);
            $('#preview').show();
        }

        function onScanFailure(error) {
            console.warn(`QR Code scan error: ${error}`);
        }

        function processAttendance(data) {
            $.ajax({
                url: '../php/process_attendance.php',
                method: 'POST',
                data: {
                    event_id: <?php echo $event_id; ?>,
                    qr_data: data
                },
                success: function(response) {
                    const result = JSON.parse(response);
                    showMessage(result.message, result.success);
                    
                    if (result.success) {
                        $('#preview').hide();
                        currentScanData = null;
                    }
                    
                    // Resume scanning after 2 seconds
                    setTimeout(() => {
                        if (html5QrcodeScanner) {
                            html5QrcodeScanner.resume();
                        }
                    }, 2000);
                },
                error: function() {
                    showMessage('Error processing attendance', false);
                }
            });
        }

        function showMessage(message, isSuccess) {
            $('#message')
                .removeClass('success-message error-message')
                .addClass(isSuccess ? 'success-message' : 'error-message')
                .text(message);
        }

        // Initialize QR scanner
        html5QrcodeScanner = new Html5QrcodeScanner(
            "reader",
            { fps: 10, qrbox: {width: 250, height: 250} },
            false
        );
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);

        // Handle confirm button
        $('#confirm-scan').on('click', function() {
            if (currentScanData) {
                const qrData = `${currentScanData.full_name}\n${currentScanData.student_id}\n${currentScanData.course}`;
                processAttendance(qrData);
            }
        });

        // Handle cancel button
        $('#cancel-scan').on('click', function() {
            $('#preview').hide();
            currentScanData = null;
            if (html5QrcodeScanner) {
                html5QrcodeScanner.resume();
            }
        });

        // Handle manual form submission
        $('#manual-form').on('submit', function(e) {
            e.preventDefault();
            const studentId = $('#student_id').val();
            // For manual entry, we'll need to fetch student info first
            // This is a placeholder - you might want to add a form to input full name and course
            showMessage('Please use QR code scanning for automatic registration', false);
        });
    </script>
</body>
</html> 