<?php
session_start();
include '../php/conn.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/1_Login.php");
    exit();
}

// Get event ID from URL
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

if ($event_id === 0) {
    die("Invalid event ID");
}

// Get event details
$stmt = $conn->prepare("SELECT event_name, event_date, event_time, event_location FROM event_table WHERE event_id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    die("Event not found");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Attendance - <?php echo htmlspecialchars($event['event_name']); ?></title>
    <link rel="stylesheet" href="../styles/style13.css">
    <script src="https://unpkg.com/html5-qrcode"></script>
</head>
<body>
    <div class="attendance-form-container">
        <div class="attendance-form-header">
            <h1>Event Attendance</h1>
            <h2><?php echo htmlspecialchars($event['event_name']); ?></h2>
            <p>Date: <?php echo htmlspecialchars($event['event_date']); ?></p>
            <p>Time: <?php echo htmlspecialchars($event['event_time']); ?></p>
            <p>Location: <?php echo htmlspecialchars($event['event_location']); ?></p>
        </div>

        <div class="attendance-form-options">
            <div class="attendance-option-card" id="scan-option">
                <h3>Scan QR Code</h3>
                <p>Scan participant's QR code to mark attendance</p>
            </div>
            <div class="attendance-option-card" id="manual-option">
                <h3>Manual Entry</h3>
                <p>Enter participant details manually</p>
            </div>
        </div>

        <div class="attendance-scanner-container" style="display: none;">
            <div id="reader" class="attendance-scanner"></div>
            <div class="attendance-preview-box" id="preview-box" style="display: none;">
                <h3>Participant Details</h3>
                <div class="attendance-preview-item" id="preview-name"></div>
                <div class="attendance-preview-item" id="preview-id"></div>
                <div class="attendance-preview-item" id="preview-email"></div>
                <button class="attendance-btn" id="confirm-attendance">Confirm Attendance</button>
            </div>
        </div>

        <div class="attendance-manual-input" style="display: none;">
            <form id="manual-form" class="attendance-form">
                <div class="attendance-form-group">
                    <label for="participant_id">Participant ID:</label>
                    <input type="text" id="participant_id" name="participant_id" class="attendance-form-control" required>
                </div>
                <button type="submit" class="attendance-btn">Submit</button>
            </form>
        </div>

        <div class="attendance-message" id="message" style="display: none;"></div>
        <button class="attendance-back-button" onclick="window.location.href='4_Event.php'">Back to Events</button>
    </div>

    <script>
        let currentParticipant = null;
        const eventId = <?php echo $event_id; ?>;

        function processAttendance(data, isManual = false) {
            console.log('Sending attendance data:', data);
            $.ajax({
                url: '../php/process_attendance.php',
                method: 'POST',
                data: {
                    event_id: <?php echo $event_id; ?>,
                    qr_data: data,
                    is_manual: isManual
                },
                dataType: 'json',
                contentType: 'application/x-www-form-urlencoded',
                success: function(response) {
                    console.log('Server response:', response);
                    if (typeof response === 'string') {
                        try {
                            response = JSON.parse(response);
                        } catch (e) {
                            console.error('Error parsing response:', e);
                            showMessage('Invalid server response', false);
                            return;
                        }
                    }
                    showMessage(response.message, response.success);
                    
                    if (response.success) {
                        $('#preview').hide();
                        $('#manual-form')[0].reset();
                        currentScanData = null;
                    }
                    
                    setTimeout(() => {
                        if (html5QrcodeScanner) {
                            html5QrcodeScanner.resume();
                        }
                    }, 2000);
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                    showMessage('Error processing attendance: ' + error, false);
                }
            });
        }

        // QR Code Scanner
        function onScanSuccess(decodedText, decodedResult) {
            try {
                const data = JSON.parse(decodedText);
                if (data.participant_id) {
                    checkParticipant(data.participant_id);
                }
            } catch (e) {
                showMessage('Invalid QR code format', 'error');
            }
        }

        function onScanFailure(error) {
            console.warn(`QR scan error: ${error}`);
        }

        let html5QrcodeScanner = new Html5QrcodeScanner(
            "reader",
            { fps: 10, qrbox: { width: 250, height: 250 } },
            false
        );

        // Option Selection
        document.getElementById('scan-option').addEventListener('click', function() {
            document.querySelector('.attendance-form-options').style.display = 'none';
            document.querySelector('.attendance-scanner-container').style.display = 'block';
            document.querySelector('.attendance-manual-input').style.display = 'none';
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        });

        document.getElementById('manual-option').addEventListener('click', function() {
            document.querySelector('.attendance-form-options').style.display = 'none';
            document.querySelector('.attendance-scanner-container').style.display = 'none';
            document.querySelector('.attendance-manual-input').style.display = 'block';
        });

        // Check Participant
        function checkParticipant(participantId) {
            fetch(`../php/check_participant.php?participant_id=${participantId}&event_id=${eventId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        currentParticipant = data.participant;
                        showPreview(data.participant);
                    } else {
                        showMessage(data.message, 'error');
                    }
                })
                .catch(error => {
                    showMessage('Error checking participant', 'error');
                });
        }

        // Show Preview
        function showPreview(participant) {
            document.getElementById('preview-name').textContent = `Name: ${participant.name}`;
            document.getElementById('preview-id').textContent = `ID: ${participant.participant_id}`;
            document.getElementById('preview-email').textContent = `Email: ${participant.email}`;
            document.getElementById('preview-box').style.display = 'block';
        }

        // Confirm Attendance
        document.getElementById('confirm-attendance').addEventListener('click', function() {
            if (!currentParticipant) return;

            fetch('../php/mark_attendance.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    participant_id: currentParticipant.participant_id,
                    event_id: eventId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('Attendance marked successfully!', 'success');
                    document.getElementById('preview-box').style.display = 'none';
                    currentParticipant = null;
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                showMessage('Error marking attendance', 'error');
            });
        });

        // Manual Form Submission
        document.getElementById('manual-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const participantId = document.getElementById('participant_id').value;
            checkParticipant(participantId);
        });

        // Show Message
        function showMessage(message, type) {
            const messageDiv = document.getElementById('message');
            messageDiv.textContent = message;
            messageDiv.className = `attendance-message ${type}`;
            messageDiv.style.display = 'block';
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 3000);
        }
    </script>
</body>
</html> 