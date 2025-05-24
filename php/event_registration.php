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
</head>
<body>
    <!-- Registration Modal -->
    <div id="importModal" class="modal">
        <div class="modal-content">
            <div class="header">
                <h3>Event Registration</h3>
                <p>Please fill out the registration form below</p>
            </div>
            <form id="participantForm" method="POST">
                <input type="hidden" name="event_id" id="event_id">
                <input type="hidden" name="event_code" id="event_code">
                
                <div class="user-details">
                    <div class="input-box">
                        <label for="student_id">Student ID:</label>
                        <input type="text" id="student_id" name="ID" required>
                    </div>

                    <div class="input-box">
                        <label for="firstname">First Name:</label>
                        <input type="text" id="firstname" name="FirstName" required>
                    </div>

                    <div class="input-box">
                        <label for="lastname">Last Name:</label>
                        <input type="text" id="lastname" name="LastName" required>
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
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="Email" required>
                    </div>

                    <div class="input-box">
                        <label for="contact">Contact Number:</label>
                        <input type="tel" id="contact" name="ContactNo" required>
                    </div>

                    <div class="input-box">
                        <label for="parents">Parent/Guardian Name:</label>
                        <input type="text" id="parents" name="Parents" required>
                    </div>
                </div>

                <div class="controls">
                    <button type="submit" class="btn-submit">Register</button>
                    <button type="button" class="btn-close" onclick="closeModal()">Close</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Show modal automatically when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Get event ID and code from URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const eventId = urlParams.get('event');
            const eventCode = urlParams.get('code');

            if (eventId && eventCode) {
                // Set hidden form values
                document.getElementById('event_id').value = eventId;
                document.getElementById('event_code').value = eventCode;

                // Show the modal
                document.getElementById('importModal').classList.add('show');
            }
        });

        // Handle form submission
        document.getElementById('participantForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
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
                    alert(data.message || 'Registration failed. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });

        function closeModal() {
            document.getElementById('importModal').classList.remove('show');
            // Redirect to home page or show a message
            window.location.href = '../index.php';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('importModal');
            if (event.target === modal) {
                closeModal();
            }
        };
    </script>
</body>
</html> 