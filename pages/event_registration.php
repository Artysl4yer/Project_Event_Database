<?php
session_start();
include '../php/conn.php';

// Get event details from URL parameters
$event_id = isset($_GET['event']) ? $_GET['event'] : null;
$event_code = isset($_GET['code']) ? $_GET['code'] : null;

if (!$event_id || !$event_code) {
    header("Location: 4_Event.php");
    exit();
}

// Get event details
$sql = "SELECT * FROM event_table WHERE number = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: 4_Event.php");
    exit();
}

$event = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Registration - PLM Events</title>
    <link rel="stylesheet" href="../styles/style1.css">
    <link rel="stylesheet" href="../styles/style2.css">
    <link rel="stylesheet" href="../styles/style4.css">
    <script src="https://kit.fontawesome.com/d78dc5f742.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        .registration-type {
            margin-bottom: 20px;
            text-align: center;
        }
        .registration-type label {
            margin: 0 10px;
            cursor: pointer;
        }
        .registration-type input[type="radio"] {
            margin-right: 5px;
        }
        .form-section {
            display: none;
        }
        .form-section.active {
            display: block;
        }
        .input-box {
            margin-bottom: 15px;
        }
        .input-box label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .input-box input, .input-box select {
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
        .error-message {
            color: #dc3545;
            margin-top: 10px;
            text-align: center;
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

            <div class="registration-type">
                <label>
                    <input type="radio" name="registration_type" value="student" checked> Student
                </label>
                <label>
                    <input type="radio" name="registration_type" value="guest"> Guest
                </label>
            </div>

            <form id="registrationForm" method="POST">
                <input type="hidden" name="event_id" value="<?= $event_id ?>">
                <input type="hidden" name="event_code" value="<?= $event_code ?>">

                <!-- Student Registration Form -->
                <div id="studentForm" class="form-section active">
                    <div class="input-box">
                        <label for="student_id">Student ID:</label>
                        <input type="text" id="student_id" name="student_id" required>
                    </div>

                    <div class="input-box">
                        <label for="student_name">Full Name:</label>
                        <input type="text" id="student_name" name="student_name" required>
                    </div>

                    <div class="input-box">
                        <label for="student_course">Course:</label>
                        <input type="text" id="student_course" name="student_course" required>
                    </div>

                    <div class="input-box">
                        <label for="student_section">Section:</label>
                        <input type="text" id="student_section" name="student_section" required>
                    </div>

                    <div class="input-box">
                        <label for="student_gender">Gender:</label>
                        <select id="student_gender" name="student_gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                </div>

                <!-- Guest Registration Form -->
                <div id="guestForm" class="form-section">
                    <div class="input-box">
                        <label for="guest_name">Full Name:</label>
                        <input type="text" id="guest_name" name="guest_name">
                    </div>

                    <div class="input-box">
                        <label for="guest_email">Email:</label>
                        <input type="email" id="guest_email" name="guest_email">
                    </div>

                    <div class="input-box">
                        <label for="guest_contact">Contact Number:</label>
                        <input type="tel" id="guest_contact" name="guest_contact">
                    </div>

                    <div class="input-box">
                        <label for="guest_org">Organization:</label>
                        <input type="text" id="guest_org" name="guest_org">
                    </div>

                    <div class="input-box">
                        <label for="guest_position">Position:</label>
                        <input type="text" id="guest_position" name="guest_position">
                    </div>
                </div>

                <button type="submit" class="submit-btn">Register</button>
                <div id="error-message" class="error-message"></div>
            </form>
        </div>
    </div>

    <script>
        // Handle registration type switching
        document.querySelectorAll('input[name="registration_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const studentForm = document.getElementById('studentForm');
                const guestForm = document.getElementById('guestForm');
                
                if (this.value === 'student') {
                    studentForm.classList.add('active');
                    guestForm.classList.remove('active');
                } else {
                    studentForm.classList.remove('active');
                    guestForm.classList.add('active');
                }
            });
        });

        // Handle form submission
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const registrationType = document.querySelector('input[name="registration_type"]:checked').value;
            const formData = new FormData(this);
            formData.append('registration_type', registrationType);
            
            fetch('../php/process_registration.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Registration successful!');
                    window.location.href = 'registration_success.php';
                } else {
                    document.getElementById('error-message').textContent = data.message || 'Registration failed. Please try again.';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('error-message').textContent = 'An error occurred. Please try again.';
            });
        });
    </script>
</body>
</html> 