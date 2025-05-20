<?php
include '../php/conn.php';

// Get event details
$event_id = isset($_GET['event']) ? $_GET['event'] : '';
$event_code = isset($_GET['code']) ? $_GET['code'] : '';

if (empty($event_id) || empty($event_code)) {
    die('Invalid QR code');
}

// Get event information
$stmt = $conn->prepare("SELECT * FROM event_table WHERE number = ? AND event_code = ?");
$stmt->bind_param("is", $event_id, $event_code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Event not found');
}

$event = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLP: Event Registration</title>
    <link rel="stylesheet" href="../styles/style2.css">
    <link rel="stylesheet" href="../styles/style3.css">
    <link rel="stylesheet" href="../styles/style1.css">
    <link rel="stylesheet" href="../styles/style5.css">
    <link rel="stylesheet" href="../styles/style6.css">
    <link rel="stylesheet" href="../styles/style8.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .modal {
            display: block !important;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            position: relative;
        }
        @media (max-width: 768px) {
            .modal-content {
                margin: 10% auto;
                width: 95%;
            }
        }
    </style>
</head>
<body>
    <div class="title-container">
        <img src="../images-icon/plplogo.png"> <h1> Pamantasan ng Lungsod ng Pasig </h1>
    </div>

    <div class="modal">
        <div class="modal-content">
            <div class="header">
                <h3><?= htmlspecialchars($event['event_title']) ?> - Registration</h3>
                <p>Fill out the information below to register for the event</p>
            </div>
            <form id="participantForm" action="../php/process_registration.php" method="POST">
                <input type="hidden" name="event_id" value="<?= $event_id ?>">
                <input type="hidden" name="event_code" value="<?= $event_code ?>">
                
                <div class="user-details">
                    <div class="input-box">
                        <label for="student_id">Student ID:</label>
                        <input type="text" id="student_id" name="ID" required>
                    </div>

                    <div class="input-box">
                        <label for="name">Full Name:</label>
                        <input type="text" id="name" name="Name" required>
                    </div>

                    <div class="input-box">
                        <label for="course">Course:</label>
                        <input type="text" id="course" name="Course" required>
                    </div>

                    <div class="input-box">
                        <label for="section">Section:</label>
                        <input type="text" id="section" name="Section" required>
                    </div>

                    <div class="input-box">
                        <label for="gender">Gender:</label>
                        <select id="gender" name="Gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>

                    <div class="input-box">
                        <label for="age">Age:</label>
                        <input type="number" id="age" name="Age" required min="1" max="100">
                    </div>

                    <div class="input-box">
                        <label for="year">Year Level:</label>
                        <select id="year" name="Year" required>
                            <option value="">Select Year Level</option>
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                        </select>
                    </div>

                    <div class="input-box">
                        <label for="dept">Department:</label>
                        <input type="text" id="dept" name="Dept" required>
                    </div>
                </div>

                <div class="controls">
                    <button type="submit" class="btn-submit">Register</button>
                    <a href="javascript:history.back()" class="btn-close">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#participantForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                type: 'POST',
                url: '../php/process_registration.php',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Registration successful!');
                        window.location.href = '../pages/7_StudentTable.php';
                    } else {
                        alert(response.message || 'Registration failed. Please try again.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                }
            });
        });
    });
    </script>
</body>
</html> 