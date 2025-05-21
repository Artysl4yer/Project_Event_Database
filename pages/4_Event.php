<?php
// Start session at the very beginning
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fix the path to config.php
$config_path = __DIR__ . '../php/conn.php';
if (!file_exists($config_path)) {
    die("Configuration file not found at: " . $config_path);
}
include $config_path;

// Test database connection
if ($conn === false) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Test if we can query the event table
$test_query = "SELECT COUNT(*) as count FROM event_table";
$result = $conn->query($test_query);
if (!$result) {
    die("Error accessing event table: " . $conn->error);
}

// Debug information
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="../styles/style1.css">
        <link rel="stylesheet" href="../styles/style2.css">
        <link rel="stylesheet" href="../styles/style3.css">
        <link rel="stylesheet" href="../styles/style11.css">
        <title> PLP: Events </title>
        <script src="https://kit.fontawesome.com/d78dc5f742.js" crossorigin="anonymous"></script>
        <script>
            // Add base URL to JavaScript
            const BASE_URL = '<?php echo SITE_URL; ?>';
        </script>
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
                <a href="5_About.php" class="active"> <i class="fa-solid fa-circle-info"></i> <span class="label"> About </span> </a>
                <a href="8_archive.php" class="active"> <i class="fa-solid fa-bars"></i> <span class="label"> Logs </span> </a>
                <a href="1_Login.php" class="active"> <i class="fa-solid fa-circle-info"></i> <span class="label"> Login </span> </a>
            </div>
            <div class="logout">
                <a href=""> <i class="fa-solid fa-gear"></i> <span class="label"> Logout </span> </a>
            </div>
        </div>
        <div class="image-background">
            <div class="image-background-dim"></div>
            <div class="image-content" id="banner">
                <h1> PLP EVENTS </h1>
                <div class="image-description">
                <p> Welcome to Pamantasan ng Lungsod ng Pasig Updates </p>
                <p> Get Up to date with the latest upcoming Events </p>
            </div>
            </div>
            
        </div>
        
        <div class="main-content">
    
            <div class="first-page">
            <!-- The Event List. The compilation of events, sort to newest to latest -->
            <div class="event-details">
                <div class="event-attendance-top">
                    <p> Event List </p>
                    
                    <div class="search-container">
                        <form class="example" actiion="action_page.php">
                            <label for="search"> </label>
                            <input type="text" id="search" name="fname" placeholder="Search...">
                            <button type="submit"><i class="fa fa-search"></i></button>
                        </form>
                    </div>
                </div>
                
                <div class="event-list">
                    <?php
                    include '../php/conn.php';

                    $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

                    if (!empty($search)) {
                        $query = "SELECT * FROM event_table 
                                    WHERE event_title LIKE '%$search%' 
                                    OR event_description LIKE '%$search%' 
                                    OR event_location LIKE '%$search%' 
                                    OR organization LIKE '%$search%' 
                                    ORDER BY number DESC";
                    } else {
                        $query = "SELECT * FROM event_table ORDER BY number DESC";
                    }

                    $result = $conn->query($query);

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $dateOnly = (new DateTime($row['date_start']))->format('Y-m-d');
                            $dateTimeStart = (new DateTime($row['event_start']))->format('Y-m-d H:i');
                            
                            echo "<div class='event-box-details'>";
                            echo "  <div class='floating-card'>";
                            echo "      <div class='event-date'>";
                            echo "          <img src='../images-icon/plm_courtyard.png' alt='Event Background' class='eventbg' />";
                            echo "          <p class='day'>" .$dateOnly. "</p>";
                            echo "          <p class='time'>" .$dateTimeStart. "</p>";
                            echo "      </div>";
                            echo "      <div class='event-description'>";
                            echo "          <h3>" .htmlspecialchars($row['event_title']). "</h3>";
                            echo "          <p>" .htmlspecialchars($row['event_description'])."</p>";
                            echo "      </div>";
                            echo "      <div class='status'>";
                            echo "          <p> Status: <b> " . htmlspecialchars($row['event_status']) . " </b></p>";
                            echo "      </div>";
                            echo "      <div class='event-actions'>";
                            echo "          <button onclick='viewParticipants(".$row['number'].", \"".htmlspecialchars($row['event_title'])."\")' class='action-btn'>";
                            echo "              <i class='fas fa-users'></i> View Participants";
                            echo "          </button>";
                            echo "      </div>";
                            echo "  </div>";
                            echo "  <div class='even-more-details'>";
                            echo "      <div class='event-box-row'>";
                            echo "          <p> Location: <b> " .htmlspecialchars($row['event_location']). "</b></p>";
                            echo "          <p> Organization: <b> " .htmlspecialchars($row['organization']). "</b></p>";
                            echo "      </div>";
                            echo "  </div>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p style='grid-column: 1 / -1; margin: 20px; color: red;'>No events found matching your search.</p>";
                    }
                    ?>
                </div>
            </div>
            </div>




                                <!-- temporary -->
    <!--        <div class="second-page">
                Popup for the registration list of attendies 
                <div class="registration-table-box" id="importRegistration">
                    <div class="registration-modal-content">
                        <div class="registraion-header">
                        </div>
                        <form>
                            <label>First Name:
                                <input type="text" name="firstName" maxlength="30" required>
                            </label>

                            <label>Last Name:
                                <input type="text" name="lastName" maxlength="30" required>
                            </label>

                            <label>Section:
                                <input type="text" name="section" maxlength="10">
                            </label>

                            <label>Year Level:
                                <input type="text" name="grade" maxlength="10">
                            </label>

                            <label>Course:
                                <input type="text" name="course" maxlength="50">
                            </label>

                            <div class="gender">
                                <label>Gender:</label>
                                <input type="radio" name="gender" value="Male" id="male"> 
                                <label for="male" style="display: inline;">Male</label>
                                <input type="radio" name="gender" value="Female" id="female"> 
                                <label for="female" style="display: inline;">Female</label>
                            </div>

                            <label>Email:
                                <input type="email" name="email" required>
                            </label>

                            <label>Phone Number:
                                <input type="tel" name="phone" maxlength="10" pattern="[0-9]{10}" required>
                            </label>

                            <label>Address:
                                <textarea name="address" maxlength="100" rows="3"></textarea>
                            </label>

                            <h3>Qualifications</h3>
                            <table id="qualificationsTable">
                                <thead>
                                    <tr>
                                        <th>Sl. No</th>
                                        <th>Examination</th>
                                        <th>Board</th>
                                        <th>Percentage</th>
                                        <th>Year of Passing</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td><input type="text" name="exam1" maxlength="15"></td>
                                        <td><input type="text" name="board1" maxlength="15"></td>
                                        <td><input type="text" name="percentage1" maxlength="5"></td>
                                        <td><input type="text" name="year1" maxlength="4"></td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td><input type="text" name="exam2" maxlength="15"></td>
                                        <td><input type="text" name="board2" maxlength="15"></td>
                                        <td><input type="text" name="percentage2" maxlength="5"></td>
                                        <td><input type="text" name="year2" maxlength="4"></td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td><input type="text" name="exam3" maxlength="15"></td>
                                        <td><input type="text" name="board3" maxlength="15"></td>
                                        <td><input type="text" name="percentage3" maxlength="5"></td>
                                        <td><input type="text" name="year3" maxlength="4"></td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="table-actions">
                                <button type="button" class="add-row-btn" id="addQualificationBtn">+ Add Qualification</button>
                            </div>

                            <button type="submit" class="submit-btn">Submit Registration</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>                -->

            <!-- This is the popup box for the import of Event System that includes the Event title, Event location, Date, time and Organization-->
       
        <script src="../Javascript/popup.js"></script>
        <script src="../Javascript/dynamic.js"></script>
        
    
    </div>

    <!-- Participants Modal eto ung modal na bago sa home page for adding participants -->
    <div id="participantsModal" class="modal">
        <div class="modal-content">
            <div class="header">
                <h3>Event Participants</h3>
                <span class="close-participants">&times;</span>
            </div>
            <div class="participants-container">
                <h4 id="eventTitle"></h4>
                <div class="participants-table-container">
                    <table class="participants-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Course</th>
                                <th>Section</th>
                                <th>Gender</th>
                                <th>Age</th>
                                <th>Year</th>
                                <th>Department</th>
                                <th>Registration Date</th>
                            </tr>
                        </thead>
                        <tbody id="participantsList">
                        </tbody>
                    </table>
                </div>
                <div class="participants-summary">
                    <p>Total Participants: <span id="totalParticipants">0</span></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM Content Loaded'); 
            const closeButtons = document.querySelectorAll('.close-admin, .close-participants');
            closeButtons.forEach(button => {
                button.onclick = function() {
                    console.log('Close button clicked'); 
                    const modal = this.closest('.modal, .admin-modal');
                    if (modal) {
                        modal.classList.remove('show');
                        if (modal.id === 'adminModal') {
                            document.getElementById('adminPassword').disabled = false;
                            document.getElementById('adminError').textContent = '';
                        }
                    }
                };
            });

            // Admin password form submission
            const adminPasswordForm = document.getElementById('adminPasswordForm');
            if (adminPasswordForm) {
                console.log('Admin password form found');
                adminPasswordForm.onsubmit = function(e) {
                    e.preventDefault();
                    console.log('Admin password form submitted');
                    
                    const password = document.getElementById('adminPassword').value;
                    console.log('Password entered:', password);
                    
                    const participantForm = document.getElementById('participantForm');
                    const adminError = document.getElementById('adminError');

                    
                    adminError.textContent = '';

                    if (!password) {
                        adminError.textContent = 'Please enter the admin password';
                        return;
                    }

                    if (password === 'admin') {
                        console.log('Password correct, showing participant form');
                        participantForm.style.display = 'block';
                        adminError.textContent = '';
                        document.getElementById('adminPassword').disabled = true;
                        participantForm.scrollIntoView({ behavior: 'smooth' });
                    } else {
                        console.log('Invalid password entered');
                        adminError.textContent = 'Invalid admin password';
                        participantForm.style.display = 'none';
                    }
                };
            }

            // Participant form submission
            const participantForm = document.getElementById('participantForm');
            if (participantForm) {
                console.log('Participant form found');
                participantForm.onsubmit = function(e) {
                    e.preventDefault();
                    console.log('Participant form submitted');

                    // Get form data
                    const formData = {
                        name: document.getElementById('participantName').value.trim(),
                        course: document.getElementById('participantCourse').value,
                        section: document.getElementById('participantSection').value.trim(),
                        gender: document.getElementById('participantGender').value,
                        age: parseInt(document.getElementById('participantAge').value),
                        year: document.getElementById('participantYear').value,
                        dept: document.getElementById('participantDept').value.trim(),
                        event_id: parseInt(document.getElementById('currentEventId').value)
                    };

                    // Validate form data
                    console.log('Form data:', formData);
                    if (!formData.event_id) {
                        console.error('No event ID found!');
                        document.getElementById('adminError').textContent = 'Error: No event selected';
                        return;
                    }

                    // Disable submit button and show loading state
                    const submitBtn = participantForm.querySelector('.submit-btn');
                    const originalBtnText = submitBtn.textContent;
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Adding Participant...';

                    // Clear any previous errors
                    document.getElementById('adminError').textContent = '';

                    // Send request to add participant
                    const endpoint = `${window.location.origin}/Project_Event_Database/php/add_participant.php`;
                    console.log('Sending request to:', endpoint);

                    fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    })
                    .then(async response => {
                        console.log('Response status:', response.status);
                        const responseText = await response.text();
                        console.log('Raw response:', responseText);
                        
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}, response: ${responseText}`);
                        }
                        
                        try {
                            return JSON.parse(responseText);
                        } catch (e) {
                            console.error('JSON parse error:', e);
                            throw new Error('Invalid JSON response from server');
                        }
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        if (data.error) {
                            throw new Error(data.error);
                        }
                        if (data.success) {
                            alert('Participant added successfully!');
                            // Reset form and close modal
                            participantForm.reset();
                            document.getElementById('adminModal').classList.remove('show');
                            // Refresh participants list
                            viewParticipants(formData.event_id, document.getElementById('eventTitle').textContent);
                        } else {
                            throw new Error(data.message || 'Unknown error occurred');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('adminError').textContent = error.message || 'Error adding participant. Please try again.';
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalBtnText;
                    });
                };
            }
        });

        // Update viewParticipants function to use BASE_URL
        function viewParticipants(eventId, eventTitle) {
            console.log('Viewing participants for event:', eventId, eventTitle);
            
            const modal = document.getElementById('participantsModal');
            const titleElement = document.getElementById('eventTitle');
            const participantsList = document.getElementById('participantsList');
            
            if (!eventId) {
                console.error('No event ID provided to viewParticipants');
                return;
            }
            
            titleElement.textContent = eventTitle;
            participantsList.innerHTML = '<tr><td colspan="9" class="loading">Loading participants...</td></tr>';
            modal.classList.add('show');

            // Add admin button
            const header = modal.querySelector('.header');
            const existingBtn = header.querySelector('.admin-add-btn');
            if (existingBtn) {
                existingBtn.remove();
            }
            const adminBtn = document.createElement('button');
            adminBtn.className = 'action-btn admin-add-btn';
            adminBtn.innerHTML = '<i class="fas fa-user-plus"></i> Admin Add';
            adminBtn.onclick = function() {
                showAdminModal(eventId);
            };
            header.appendChild(adminBtn);

            // Construct the URL properly
            const endpoint = `${window.location.origin}/Project_Event_Database/php/get_participants.php?event_id=${eventId}`;
            console.log('Fetching participants from:', endpoint);

            fetch(endpoint, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(async response => {
                console.log('Response status:', response.status);
                const responseText = await response.text();
                console.log('Raw response:', responseText);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}, response: ${responseText}`);
                }
                
                try {
                    return JSON.parse(responseText);
                } catch (e) {
                    console.error('JSON parse error:', e);
                    throw new Error('Invalid JSON response from server');
                }
            })
            .then(data => {
                console.log('Participants data:', data);
                participantsList.innerHTML = '';
                
                if (data.error) {
                    throw new Error(data.error);
                }
                
                if (!Array.isArray(data) || data.length === 0) {
                    participantsList.innerHTML = '<tr><td colspan="9" class="no-data">No participants registered yet</td></tr>';
                } else {
                    data.forEach(participant => {
                        const row = document.createElement('tr');
                        const registrationDate = participant.registration_date ? 
                            new Date(participant.registration_date).toLocaleString() : 
                            'N/A';
                        row.innerHTML = `
                            <td>${participant.ID || ''}</td>
                            <td>${participant.Name || ''}</td>
                            <td>${participant.Course || ''}</td>
                            <td>${participant.Section || ''}</td>
                            <td>${participant.Gender || ''}</td>
                            <td>${participant.Age || ''}</td>
                            <td>${participant.Year || ''}</td>
                            <td>${participant.Dept || ''}</td>
                            <td>${registrationDate}</td>
                        `;
                        participantsList.appendChild(row);
                    });
                }
                document.getElementById('totalParticipants').textContent = Array.isArray(data) ? data.length : 0;
            })
            .catch(error => {
                console.error('Error loading participants:', error);
                participantsList.innerHTML = `<tr><td colspan="9" class="error">Error loading participants: ${error.message}</td></tr>`;
            });
        }

        function showAdminModal(eventId) {
            console.log('Opening admin modal for event:', eventId);
            const modal = document.getElementById('adminModal');
            if (!modal) {
                console.error('Admin modal not found!');
                return;
            }
            document.getElementById('currentEventId').value = eventId;
            document.getElementById('participantForm').style.display = 'none';
            document.getElementById('adminPassword').value = '';
            document.getElementById('adminError').textContent = '';
            modal.classList.add('show');
        }

       
        function closeAdminModal() {
            const modal = document.getElementById('adminModal');
            const adminPasswordForm = document.getElementById('adminPasswordForm');
            const participantForm = document.getElementById('participantForm');

            adminPasswordForm.reset();
            participantForm.reset();
            
            document.getElementById('adminPassword').disabled = false;
            participantForm.style.display = 'none';
            document.getElementById('adminError').textContent = '';
            
            modal.classList.remove('show');
        }

        
        window.onclick = function(event) {
            if (event.target.classList.contains('modal') || event.target.classList.contains('admin-modal')) {
                if (event.target.id === 'adminModal') {
                    closeAdminModal();
                } else {
                    event.target.classList.remove('show');
                }
            }
        };
    </script>

    <!-- Admin Add Participant Modal -->
    <div id="adminModal" class="admin-modal">
        <div class="admin-modal-content">
            <div class="header">
                <h3>Admin Add Participant</h3>
                <span class="close-admin">&times;</span>
            </div>
            <!-- Separate admin password form -->
            <form id="adminPasswordForm" class="admin-form">
                <div class="form-group">
                    <label for="adminPassword">Admin Password</label>
                    <input type="password" id="adminPassword" name="adminPassword" placeholder="Enter admin password" required>
                    <button type="submit" class="submit-btn">Enter Password</button>
                </div>
                <div id="adminError" class="error-message"></div>
            </form>
            <!-- Separate participant form -->
            <form id="participantForm" style="display: none;">
                <div class="form-group">
                    <label for="participantName">Full Name</label>
                    <input type="text" id="participantName" name="participantName" placeholder="Enter full name" required>
                </div>
                <div class="form-group">
                    <label for="participantCourse">Course</label>
                    <select id="participantCourse" name="participantCourse" required>
                        <option value="">Select course</option>
                        <option value="BS Information Technology">BS Information Technology</option>
                        <option value="BS Computer Science">BS Computer Science</option>
                        <option value="BS Information Systems">BS Information Systems</option>
                        <option value="BS Computer Engineering">BS Computer Engineering</option>
                        <option value="BS Accountancy">BS Accountancy</option>
                        <option value="BS Business Administration">BS Business Administration</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="participantSection">Section</label>
                    <input type="text" id="participantSection" name="participantSection" placeholder="Enter section" required>
                </div>
                <div class="form-group">
                    <label for="participantGender">Gender</label>
                    <select id="participantGender" name="participantGender" required>
                        <option value="">Select gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="participantAge">Age</label>
                    <input type="number" id="participantAge" name="participantAge" min="15" max="100" placeholder="Enter age" required>
                </div>
                <div class="form-group">
                    <label for="participantYear">Year Level</label>
                    <select id="participantYear" name="participantYear" required>
                        <option value="">Select year level</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="participantDept">Department</label>
                    <input type="text" id="participantDept" name="participantDept" placeholder="Enter department" required>
                </div>
                <input type="hidden" id="currentEventId" name="currentEventId">
                <div class="form-buttons">
                    <button type="submit" class="submit-btn">Add Participant</button>
                    <button type="button" class="exit-btn" onclick="closeAdminModal()">Exit</button>
                </div>
            </form>
        </div>
    </div>

    <?php
    // Debug output at the top of the page
    if (isset($_GET['debug'])) {
        echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px;'>";
        echo "<h3>Debug Information:</h3>";
        echo "Database connection: " . ($conn ? "OK" : "Failed") . "<br>";
        echo "Event table count: " . $result->fetch_assoc()['count'] . "<br>";
        echo "Session status: " . session_status() . "<br>";
        echo "Config path: " . $config_path . "<br>";
        echo "</div>";
    }
    ?>
    </body>
</html>
