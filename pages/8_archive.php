<?php
session_start();
require_once '../php/config.php';
require_once '../php/conn.php';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: 1_Login.php");
    exit();
}

// Get archived events
$sql = "SELECT a.*, u.name as archived_by_name, e.number 
        FROM archive_table a 
        LEFT JOIN users u ON a.archived_by = u.id 
        LEFT JOIN event_table e ON a.event_code = e.event_code 
        ORDER BY a.archived_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>PLP: Archived Events</title>
        <link rel="stylesheet" href="../styles/style2.css">
        <link rel="stylesheet" href="../styles/style3.css">
        <link rel="stylesheet" href="../styles/style1.css">
        <link rel="stylesheet" href="../styles/style5.css">
        <link rel="stylesheet" href="../styles/style6.css">
        <link rel="stylesheet" href="../styles/style8.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <!-- Add SweetAlert2 CSS and JS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
        <div class="title-container">
            <img src="../images-icon/plplogo.png"> <h1> Pamantasan ng Lungsod ng Pasig </h1>
        </div>
        <div class="tab-container">
            <div class="menu-items">
                <a href="4_Event.php"> <i class="fa-solid fa-home"></i> <span class="label"> Home </span> </a>
                <a href="6_NewEvent.php"> <i class="fa-solid fa-calendar"></i> <span class="label"> Events </span> </a>
                <a href="registered_participants.php"> <i class="fa-solid fa-users"></i> <span class="label"> Registered Participants </span> </a>
                <a href="10_Admin.php"> <i class="fa-regular fa-circle-user"></i> <span class="label"> Admins </span> </a>
                <a href="7_StudentTable.php"> <i class="fa-solid fa-address-card"></i> <span class="label"> Participants </span> </a>
                <a href="5_About.php"> <i class="fa-solid fa-circle-info"></i> <span class="label"> About </span> </a>
                <a href="8_archive.php" class="active"> <i class="fa-solid fa-bars"></i> <span class="label"> Logs </span> </a>
                <a href="1_Login.php"> <i class="fa-solid fa-circle-info"></i> <span class="label"> Login </span> </a>
            </div>
            <div class="logout">
                <a href=""> <i class="fa-solid fa-gear"></i> <span class="label"> Logout </span> </a>
            </div>
        </div>
        <div class="event-main">
            <div class="event-details">
                <div class="event-top">
                    <p>Archived Events</p>
                </div>
            </div>

            <div class="event-table-section">
                <table class="event-display-table">
                    <thead>
                        <tr>
                            <th>Event Title</th>
                            <th>Event Code</th>
                            <th>Location</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Organization</th>
                            <th>Archived By</th>
                            <th>Archived At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['event_title']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['event_code']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['event_location']) . "</td>";
                                echo "<td>" . $row['date_start'] . "</td>";
                                echo "<td>" . $row['date_end'] . "</td>";
                                echo "<td>" . htmlspecialchars($row['organization']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['archived_by_name']) . "</td>";
                                echo "<td>" . $row['archived_at'] . "</td>";
                                echo "<td class='dropdown-wrapper'>";
                                echo "<button class='dropdown-toggle' onclick='toggleDropdown(" . $row['number'] . ")'>";
                                echo "<i class='fas fa-ellipsis-v'></i>";
                                echo "</button>";
                                echo "<div class='dropdown-menu' id='dropdown" . $row['number'] . "'>";
                                echo "<button onclick='viewEventDetails(" . $row['number'] . ")'>";
                                echo "<i class='fas fa-eye'></i> View Details";
                                echo "</button>";
                                echo "<button onclick='unarchiveEvent(" . $row['number'] . ")'>";
                                echo "<i class='fas fa-archive'></i> Unarchive";
                                echo "</button>";
                                echo "<button onclick='deleteArchive(" . $row['number'] . ")'>";
                                echo "<i class='fas fa-trash'></i> Delete";
                                echo "</button>";
                                echo "</div>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='9'>No archived events found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- View Event Modal -->
        <div id="viewEventModal" class="modal">
            <div class="modal-content">
                <span class="close-modal" onclick="closeViewModal()">&times;</span>
                <div class="header">
                    <h3>Event Details</h3>
                </div>
                <div id="eventDetails" class="event-details-content">
                    <!-- Event details will be loaded here -->
                </div>
            </div>
        </div>

        <script>
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

            function viewEventDetails(eventId) {
                fetch(`../php/get_event_details.php?id=${eventId}`)
                    .then(response => response.json())
                    .then(data => {
                        const detailsHtml = `
                            <div class="detail-item">
                                <label>Title:</label>
                                <p>${data.event_title}</p>
                            </div>
                            <div class="detail-item">
                                <label>Location:</label>
                                <p>${data.event_location}</p>
                            </div>
                            <div class="detail-item">
                                <label>Start Date:</label>
                                <p>${data.date_start}</p>
                            </div>
                            <div class="detail-item">
                                <label>End Date:</label>
                                <p>${data.date_end}</p>
                            </div>
                            <div class="detail-item">
                                <label>Organization:</label>
                                <p>${data.organization}</p>
                            </div>
                            <div class="detail-item">
                                <label>Description:</label>
                                <p>${data.event_description}</p>
                            </div>
                        `;
                        document.getElementById('eventDetails').innerHTML = detailsHtml;
                        document.getElementById('viewEventModal').style.display = 'block';
                    })
                    .catch(error => console.error('Error:', error));
            }

            function unarchiveEvent(eventId) {
                Swal.fire({
                    title: 'Unarchive Event',
                    text: 'Are you sure you want to unarchive this event?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, unarchive it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('../php/unarchive_event.php', {
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
                                    text: data.message || 'Failed to unarchive event'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'An error occurred while unarchiving the event'
                            });
                        });
                    }
                });
            }

            function deleteArchive(eventId) {
                Swal.fire({
                    title: 'Delete Event',
                    text: 'Are you sure you want to permanently delete this archived event?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('../php/delete_archive.php', {
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
                                    text: data.message || 'Failed to delete event'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'An error occurred while deleting the event'
                            });
                        });
                    }
                });
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
        </script>
        

            <div id="importModal" class="modal">
                <div class="modal-content">
                    <div class="header">
                        <h3> Create New Event </h3>
                        <p> Fill out the information below to get started </p>
                    </div> 
                    <form id="eventForm" action="../php/event-sub.php" method="POST">
                        <div class="user-details">
                            <div class="input-box">
                                <label for="event-title"> Event Title: </label>
                                <input type="text" name="event-title" required> 
                            </div>
                            <div class="input-box">
                                <label for="event-location"> Location: </label>
                                <input type="text" name="event-location" required> 
                            </div>
                            <div class="date-box">
                                <label for="event-date-start"> Start Time </label>
                                <input type="date" name="event-date-start" placeholder="00/00/0000" required> 
                                <input type="time" name="event-time-start" placeholder="00:00" required> 
                            </div>
                            <div class="date-box">
                                <label for="event-date-end"> End Time </label>
                                <input type="date" id="event-date-end" name="event-date-end" placeholder="00/00/0000" required> 
                                <input type="time" id= "event-time-end" name="event-time-end" placeholder="00:00" required> 
                                </div>
                            <div class="input-box">
                                <label for="event-orgs"> Organization: </label>
                                <input type="text" name="event-orgs" required> 
                            </div>
                            <div class="input-box">
                                <label for="event-description"> Decription: </label>
                                <textarea id="description" name="event-description"></textarea>
                            </div>
                            <input type="hidden" name="code" id="codeField">
                            <div class="input-box-options">
                                <div class="option-title-box">
                                    <label for="option-box">
                                        Options:
                                    </label>
                                </div>
                                <label for="option"> 
                                    <input type="checkbox" name="option" value="StudentID"> StudentID
                                </label>
                                <label for="option"> 
                                    <input type="checkbox" name="option" value="Gender"> Gender 
                                </label>
                                <label for="option"> 
                                    <input type="checkbox" name="option" value="Section"> Section
                                </label>
                                <label for="option"> 
                                    <input type="checkbox" name="option" value="Year"> Year
                                </label>
                                <label for="option"> 
                                    <input type="checkbox" name="option" value="Age"> Age
                                </label>
                                <label for="option"> 
                                    <input type="checkbox" name="option" value="Parents"> Parents
                                </label>
                                <label for="option"> 
                                    <input type="checkbox" name="option" value="ContactNo"> Contact No
                                </label>
                                <label for="option"> 
                                    <input type="checkbox" name="option" value="Email"> Email
                                </label>
                            </div>
                            </div>
                            <div class="controls">
                                <button class="btn-submit" type="submit">Submit</button>
                                <button class="btn-close" type="button" id="btn-click" onclick="closeModal()"> Close </button>
                                
                            </div>
                    </form>
                    </div>
                </div>
        <script src="../Javascript/popup.js"></script>
        <script src="../Javacscript/RandomCodeGenerator.js"></script>
        <style>
            .dropdown-wrapper {
                position: relative;
                display: inline-block;
            }
            .dropdown-toggle {
                background: none;
                border: none;
                cursor: pointer;
                font-size: 18px;
            }
            .dropdown-menu {
                display: none;
                position: absolute;
                right: 0;
                background: #fff;
                min-width: 140px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.15);
                z-index: 100;
                border-radius: 6px;
                padding: 8px 0;
            }
            .dropdown-wrapper.open .dropdown-menu {
                display: block;
            }
            .dropdown-menu button, .dropdown-menu form {
                width: 100%;
                background: none;
                border: none;
                text-align: left;
                padding: 10px 20px;
                cursor: pointer;
                font-size: 15px;
                color: #333;
            }
            .dropdown-menu button:hover {
                background: #f0f0f0;
            }
        </style>
        <script>
            document.querySelectorAll('.dropdown-toggle').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    document.querySelectorAll('.dropdown-wrapper').forEach(w => w.classList.remove('open'));
                    this.parentElement.classList.toggle('open');
                });
            });
            window.addEventListener('click', function() {
                document.querySelectorAll('.dropdown-wrapper').forEach(w => w.classList.remove('open'));
            });
        </script>
    </body>
</html>
