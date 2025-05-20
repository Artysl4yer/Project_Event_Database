<?php
// Handle form submissions
include '../php/conn.php';

// Add/Edit Participant
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_participant') {
        $number = $_POST['participant-number'] ?? null;
        $id = $_POST['participant-id'];
        $name = $_POST['participant-name'];
        $course = $_POST['participant-course'];
        $section = $_POST['participant-section'];
        $gender = $_POST['participant-gender'];
        $age = $_POST['participant-age'];
        $year = $_POST['participant-year'];
        $dept = $_POST['participant-dept'];

        if (empty($number)) {
            // Insert new
            $stmt = $conn->prepare("INSERT INTO participants_table (ID, Name, Course, Section, Gender, Age, Year, Dept) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssiss", $id, $name, $course, $section, $gender, $age, $year, $dept);
        } else {
            // Update existing
            $stmt = $conn->prepare("UPDATE participants_table SET ID=?, Name=?, Course=?, Section=?, Gender=?, Age=?, Year=?, Dept=? WHERE number=?");
            $stmt->bind_param("sssssissi", $id, $name, $course, $section, $gender, $age, $year, $dept, $number);
        }

        if ($stmt->execute()) {
            header("Location: 7_StudentTable.php?success=1");
            exit();
        } else {
            header("Location: 7_StudentTable.php?error=1");
            exit();
        }
    }
    // Delete Participant
    elseif ($_POST['action'] === 'delete_participant') {
        $id = $_POST['delete_id'];
        $stmt = $conn->prepare("DELETE FROM participants_table WHERE number = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            header("Location: 7_StudentTable.php?deleted=1");
            exit();
        } else {
            header("Location: 7_StudentTable.php?error=1");
            exit();
        }
    }
}

// Get participant data for editing
if (isset($_GET['action']) && $_GET['action'] === 'get_participant') {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM participants_table WHERE number = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $participant = $result->fetch_assoc();
    
    header('Content-Type: application/json');
    echo json_encode($participant);
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PLP: Participants</title>
    <link rel="stylesheet" href="../styles/style1.css">
    <link rel="stylesheet" href="../styles/style2.css">
    <link rel="stylesheet" href="../styles/style3.css">
    <link rel="stylesheet" href="../styles/style5.css">
    <link rel="stylesheet" href="../styles/style6.css">
    <link rel="stylesheet" href="../styles/student-table.css">
    <script src="https://kit.fontawesome.com/d78dc5f742.js" crossorigin="anonymous"></script>
    <style>
        /* Additional dropdown styles */
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white;
            font-size: 15px;
        }
        select:focus {
            outline: none;
            border-color: #104911;
        }
    </style>
</head>
<body>
    <div class="title-container">
        <img src="../images-icon/plplogo.png"> <h1> Pamantasan ng Lungsod ng Pasig </h1>
        <div class="tab-container">
            <div class="menu-items">
                <a href="4_Event.php" class="active"> <i class="fa-solid fa-home"></i> <span class="label"> Home </span> </a>
                <a href="6_NewEvent.php" class="active"> <i class="fa-solid fa-calendar"></i> <span class="label">Events </span> </a>
                <a href="" class="active"> <i class="fa-regular fa-circle-user"></i> <span class="label"> Admins </span> </a>
                <a href="7_StudentTable.php" class="active"> <i class="fa-solid fa-address-card"></i> <span class="label"> Participants </span> </a>
                <a href="5_About.php" class="active"> <i class="fa-solid fa-circle-info"></i> <span class="label"> About </span> </a>
                <a href="8_archive.php" class="active"> <i class="fa-solid fa-bars"></i> <span class="label"> Logs </span> </a>
                <a href="1_Login.php" class="active"> <i class="fa-solid fa-circle-info"></i> <span class="label"> Login </span> </a>
            </div>
            <div class="logout">
                <a href=""> <i class="fa-solid fa-gear"></i> <span class="label"> Logout </span> </a>
            </div>
        </div>
    </div>
    <div class="event-main">
        <div class="event-details">
            <div class="event-top">
                <p> Event List </p>
                <div class="search-container">
                    <form class="example" action="action_page.php">
                        <label for="search"> </label>
                        <input type="text" id="search" name="fname" placeholder="Search...">
                        <button type="submit"><i class="fa fa-search"></i></button>
                    </form>
                </div>
                <div class="col-md-12" id="importFrm" style="display:block">
                    <form action="../php/importData.php" method="post" enctype="multipart/form-data">
                        <input type="file" name="file" />
                        <input type="submit" class="btn btn-primary" name="importSubmit" value="IMPORT">
                    </form>
                </div>
            </div>  
        </div>
        <div class="event-table-section">
            <h2>Participants</h2>
            <div class="add-button">
                <button class="btn-import" onclick="openParticipantModal()"><span>ADD <i class="fa-solid fa-plus"></i></span></button>
            </div>
            
            <!-- Add/Edit Participant Modal -->
            <div id="participantModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <div class="header">
                        <h3 id="modalTitle">Add New Participant</h3>
                    </div>
                    <form id="participantForm" method="POST">
                        <input type="hidden" name="action" value="save_participant">
                        <input type="hidden" name="participant-number" id="participant-number">
                        <div class="user-details">
                            <div class="input-box">
                                <label>ID Number</label>
                                <input type="text" name="participant-id" id="participant-id" required>
                            </div>
                            <div class="input-box">
                                <label>Full Name</label>
                                <input type="text" name="participant-name" id="participant-name" required>
                            </div>
                            <div class="input-box">
                                <label>Course</label>
                                <select name="participant-course" id="participant-course" required>
                                    <option value="">Select Course</option>
                                    <option value="BSIT">BS Information Technology</option>
                                    <option value="BSCS">BS Computer Science</option>
                                    <option value="BSIS">BS Information Systems</option>
                                    <option value="BSCE">BS Computer Engineering</option>
                                    <option value="BSA">BS Accountancy</option>
                                    <option value="BSBA">BS Business Administration</option>
                                </select>
                            </div>
                            <div class="input-box">
                                <label>Section</label>
                                <input type="text" name="participant-section" id="participant-section" required>
                            </div>
                            <div class="input-box">
                                <label>Gender</label>
                                <select name="participant-gender" id="participant-gender" required>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="input-box">
                                <label>Age</label>
                                <input type="number" name="participant-age" id="participant-age" required>
                            </div>
                            <div class="input-box">
                                <label>Year</label>
                                <select name="participant-year" id="participant-year" required>
                                    <option value="">Select Year</option>
                                    <option value="1">1st Year</option>
                                    <option value="2">2nd Year</option>
                                    <option value="3">3rd Year</option>
                                    <option value="4">4th Year</option>
                                </select>
                            </div>
                            <div class="input-box">
                                <label>Department</label>
                                <select name="participant-dept" id="participant-dept" required>
                                    <option value="">Select Department</option>
                                    <option value="CCS">College of Computer Studies</option>
                                    <option value="CBA">College of Business Administration</option>
                                    <option value="CAS">College of Arts and Sciences</option>
                                    <option value="COE">College of Engineering</option>
                                </select>
                            </div>
                        </div>
                        <div class="controls">
                            <button type="submit" class="btn-submit">Save</button>
                            <button type="button" class="btn-close" onclick="closeModal()">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>

            <table class="event-display-table">
                <tr>
                    <th>Number</th>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Course</th>
                    <th>Section</th>
                    <th>Gender</th>
                    <th>Age</th>   
                    <th>Year</th>
                    <th>Dept</th>
                    <th>Actions</th>
                </tr>
                <?php
                $sql = "SELECT * FROM participants_table ORDER BY number DESC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?= $row['number'] ?></td>
                    <td><?= $row['ID'] ?></td>
                    <td><?= htmlspecialchars($row['Name']) ?></td>
                    <td><?= htmlspecialchars($row['Course']) ?></td>
                    <td><?= htmlspecialchars($row['Section']) ?></td>
                    <td><?= htmlspecialchars($row['Gender']) ?></td>
                    <td><?= $row['Age'] ?></td>
                    <td><?= htmlspecialchars($row['Year']) ?></td>
                    <td><?= htmlspecialchars($row['Dept']) ?></td>
                    <td class="dropdown-wrapper">
                        <button class="dropdown-toggle">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu">
                            <button class="dropdown-item" onclick="openParticipantModal(<?= $row['number'] ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <form method="POST" action="7_StudentTable.php" onsubmit="return confirm('Are you sure you want to delete this participant?');">
                                <input type="hidden" name="action" value="delete_participant">
                                <input type="hidden" name="delete_id" value="<?= $row['number'] ?>">
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                            </form>
                            <button class="dropdown-item" onclick="generateQRCode(<?= $row['number'] ?>, '<?= $row['ID'] ?>')">
                                <i class="fas fa-qrcode"></i> Generate QR
                            </button>
                        </div>
                    </td>
                </tr>
                <?php
                    endwhile;
                else:
                ?>
                <tr><td colspan="10">No participants found.</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
    <!-- QR Code Modal -->
    <div id="qrModal" class="modal">
        <div class="modal-content">
            <div class="header">
                <h3>Participant QR Code</h3>
                <span class="close-qr">&times;</span>
            </div>
            <div id="qrcode-container" class="text-center">
                <div id="qrcode"></div>
                <p>Scan this QR code to view participant details</p>
                <button onclick="downloadQRCode()" class="btn-download">Download QR Code</button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        // Modal Functions
        function openParticipantModal(participantId = null) {
            const modal = document.getElementById('participantModal');
            const form = document.getElementById('participantForm');
            const title = document.getElementById('modalTitle');
            
            if (participantId) {
                title.textContent = 'Edit Participant';
                document.getElementById('participant-number').value = participantId;
                
                fetch(`7_StudentTable.php?action=get_participant&id=${participantId}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('participant-id').value = data.ID || '';
                        document.getElementById('participant-name').value = data.Name || '';
                        document.getElementById('participant-course').value = data.Course || '';
                        document.getElementById('participant-section').value = data.Section || '';
                        document.getElementById('participant-gender').value = data.Gender || '';
                        document.getElementById('participant-age').value = data.Age || '';
                        document.getElementById('participant-year').value = data.Year || '';
                        document.getElementById('participant-dept').value = data.Dept || '';
                    });
            } else {
                title.textContent = 'Add New Participant';
                form.reset();
                document.getElementById('participant-number').value = '';
            }
            
            modal.style.display = 'block';
        }

        function closeModal() {
            document.getElementById('participantModal').style.display = 'none';
        }

        // QR Code Functions
        let qrcode = null;
        
        function generateQRCode(participantNumber, participantId) {
            const modal = document.getElementById('qrModal');
            const qrcodeDiv = document.getElementById('qrcode');
            
            qrcodeDiv.innerHTML = '';
            
            const participantData = {
                type: 'participant',
                id: participantId,
                number: participantNumber,
                system: 'PLP Event System',
                timestamp: new Date().toISOString()
            };
            
            qrcode = new QRCode(qrcodeDiv, {
                text: JSON.stringify(participantData),
                width: 256,
                height: 256,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
            
            modal.classList.add('show');
        }
        
        function downloadQRCode() {
            if (!qrcode) return;
            
            const canvas = document.querySelector("#qrcode canvas");
            const image = canvas.toDataURL("image/png");
            const link = document.createElement('a');
            link.href = image;
            link.download = `participant-qr-${new Date().getTime()}.png`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle dropdown on click
            document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const menu = this.nextElementSibling;
                    
                    // Close all other dropdowns
                    document.querySelectorAll('.dropdown-menu').forEach(m => {
                        if (m !== menu) m.classList.remove('show');
                    });
                    
                    // Toggle current dropdown
                    menu.classList.toggle('show');
                });
            });
            
            // Close dropdown when clicking elsewhere
            document.addEventListener('click', function() {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.classList.remove('show');
                });
            });

            // Close modals when clicking their close buttons
            document.querySelector('.close-qr')?.addEventListener('click', function() {
                document.getElementById('qrModal').classList.remove('show');
            });
        });

        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target == document.getElementById('participantModal')) {
                closeModal();
            }
            if (event.target == document.getElementById('qrModal')) {
                document.getElementById('qrModal').classList.remove('show');
            }
        });
    </script>
</body>
</html>