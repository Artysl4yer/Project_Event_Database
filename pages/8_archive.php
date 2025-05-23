<?php
session_start();

// Check email, student_id, and role
if (!isset($_SESSION['email'], $_SESSION['student_id'], $_SESSION['role'])) {
    header("Location: ../pages/Login_v1.php");
    exit();
}

// Allowed roles
$allowed_roles = ['coordinator'];

if (!in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: ../pages/Login_v1.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>PLP: Event Attendace</title>
        <link rel="stylesheet" href="../styles/style2.css">
        <link rel="stylesheet" href="../styles/style3.css">
        <link rel="stylesheet" href="../styles/style1.css">
        <link rel="stylesheet" href="../styles/style5.css">
        <link rel="stylesheet" href="../styles/style6.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        
    </head>
    <body>
        <div class="title-container">
            <img src="../images-icon/plplogo.png"> <h1> Pamantasan ng Lungsod ng Pasig </h1>
        </div>
        <div class="tab-container">
            <div class="menu-items">
                <a href="4_Event.php" class="active"> <i class="fa-solid fa-home"></i> <span class="label"> Home </span> </a>
                <a href="6_NewEvent.php" class="active"> <i class="fa-solid fa-calendar"></i> <span class="label"> Events </span> </a>
                <a href="7_StudentTable.php" class="active"> <i class="fa-solid fa-address-card"></i> <span class="label"> Participants </span> </a>
                <a href="5_About.php" class="active"> <i class="fa-solid fa-circle-info"></i> <span class="label"> About </span> </a>
                <a href="8_archive.php" class="active"> <i class="fa-solid fa-bars"></i> <span class="label"> Logs </span> </a>
            </div>
            <div class="logout">
                <a href="../php/1logout.php"> <i class="fa-solid fa-right-from-bracket"></i> <span class="label"> Logout </span> </a>
            </div>
        </div>
        <div class="event-main">
            <!-- The Event List. The compilation of events, sort to newest to latest -->
                <div class="event-details">
                    <div class="event-top">
                        <p> Logs </p>
                        <div class="search-container">
                            <form class="example" action="action_page.php">
                                <label for="search"> </label>
                                <input type="text" id="search" name="fname" placeholder="Search...">
                                <button type="submit"><i class="fa fa-search"></i></button>
                            </form>
                        </div>
                    </div>  
                </div>
                <div class="event-table-section">
                    <h2>Archived Events</h2>
                    <table class="event-display-table">
                        <tr>
                            <th>Number</th>
                            <th>Title</th>
                            <th>event_code</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Location</th>
                            <th>Description</th>
                            <th>Organization</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                        <?php
                        include '../php/conn.php';
                        $sql = "SELECT * FROM archive_table ORDER BY number DESC";
                        $result = $conn->query($sql);

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
                                    <button onclick="viewEventDetails(<?= $row['number'] ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <form method="POST" action="../php/delete_archive.php" onsubmit="return confirm('Are you sure you want to permanently delete this event?');">
                                        <input type="hidden" name="delete_id" value="<?= $row['number'] ?>">
                                        <button type="submit" name="delete">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    </form>
                                    <form method="POST" action="../php/unarchive_event.php" onsubmit="return confirm('Are you sure you want to unarchive this event?');">
                                        <input type="hidden" name="unarchive_id" value="<?= $row['number'] ?>">
                                        <button type="submit" name="unarchive">
                                            <i class="fas fa-archive"></i> Unarchive
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php
                            endwhile;
                            else:
                        ?>
                        <tr><td colspan="10">No events found.</td></tr>
                        <?php endif; ?>
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
                function viewEventDetails(eventId) {
                    // Fetch event details using AJAX
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

                function closeViewModal() {
                    document.getElementById('viewEventModal').style.display = 'none';
                }

                // Close modal when clicking outside
                window.onclick = function(event) {
                    const modal = document.getElementById('viewEventModal');
                    if (event.target == modal) {
                        modal.style.display = 'none';
                    }
                }
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
