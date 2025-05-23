<?php
session_start();

// Check email, student_id, and role
if (!isset($_SESSION['email'], $_SESSION['student_id'], $_SESSION['role'])) {
    header("Location: ../pages/Login_v1.php");
    exit();
}

$allowed_roles = ['coordinator'];

if (!in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: ../pages/Login_v1.php");
    exit();
}

// Handle form submissions
include '../php/conn.php';

// Add/Edit Participant
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_participant') {
        $number = $_POST['participant-number'] ?? null;
        $id = $_POST['participant-id'] ?? null;
        $first_name = $_POST['participant-firstname'];
        $last_name = $_POST['participant-lastname'];
        $course = $_POST['participant-course'];
        $section = $_POST['participant-section'];
        $gender = $_POST['participant-gender'];
        $age = $_POST['participant-age'] ?? null;
        $year = $_POST['participant-year'];
        $dept = $_POST['participant-dept'];

        if (empty($number)) {
            // Insert new
            $stmt = $conn->prepare("INSERT INTO participants_table (ID, first_name, last_name, Course, Section, Gender, Age, Year, Dept) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssiss", $id, $first_name, $last_name, $course, $section, $gender, $age, $year, $dept);
        } else {
            // Update existing
            $stmt = $conn->prepare("UPDATE participants_table SET ID=?, first_name=?, last_name=?, Course=?, Section=?, Gender=?, Age=?, Year=?, Dept=? WHERE number=?");
            $stmt->bind_param("ssssssissi", $id, $first_name, $last_name, $course, $section, $gender, $age, $year, $dept, $number);
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
    <link rel="stylesheet" href="../styles/style2.css">
    <link rel="stylesheet" href="../styles/style3.css">
    <link rel="stylesheet" href="../styles/style1.css">
    <link rel="stylesheet" href="../styles/style5.css">
    <link rel="stylesheet" href="../styles/style6.css">
    <link rel="stylesheet" href="../styles/style8.css">
    <link rel="stylesheet" href="../styles/style10.css">
    <link rel="stylesheet" href="../styles/filter.css">
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
    <div class="event-main">
        <div class="event-details">
            <div class="event-top">
                <p>Participants List</p>
                <div class="search-container">
                    <form class="example" action="action_page.php">
                        <label for="search"></label>
                        <input type="text" id="search" name="fname" placeholder="Search...">
                        <button type="submit"><i class="fa fa-search"></i></button>
                    </form>
                </div>
                <div class="col-md-12" id="importFrm">
                    <form action="../php/importData.php" method="post" enctype="multipart/form-data">
                        <label class="upload-btn">
                            Upload File
                            <input type="file" id="fileInput" name="file" hidden>
                        </label>
                        <span id="fileName">No file chosen</span>
                    </form>
                </div>
            </div>  
        </div>
        <div class="event-table-section">
            <h2>Participants</h2>
            <div class="filter-buttons">
                <button class="filter-btn active" data-filter="all">All Participants</button>
                <button class="filter-btn" data-filter="name">Sort by Name</button>
                <button class="filter-btn" data-filter="course">Sort by Course</button>
                <button class="filter-btn" data-filter="department">Filter by Department</button>
                <select id="departmentFilter" class="status-select" style="display: none;">
                    <option value="all">All Departments</option>
                    <option value="CCS">College of Computer Studies</option>
                    <option value="CBA">College of Business Administration</option>
                    <option value="CAS">College of Arts and Sciences</option>
                    <option value="COE">College of Engineering</option>
                </select>
            </div>
            <div class="add-button">
                <button class="btn-import" onclick="openParticipantModal()">
                    <span><i class="fa-solid fa-plus"></i> Add Participant</span>
                </button>
            </div>
            <table class="event-display-table" id="participantTable">
                <thead>
                    <tr>
                        <th data-sort="number">Number</th>
                        <th data-sort="id">ID</th>
                        <th data-sort="name">Name</th>
                        <th data-sort="course">Course</th>
                        <th data-sort="section">Section</th>
                        <th data-sort="gender">Gender</th>
                        <th data-sort="age">Age</th>
                        <th data-sort="year">Year</th>
                        <th data-sort="department">Department</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $sql = "SELECT * FROM participants_table ORDER BY number DESC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?= $row['number'] ?></td>
                    <td><?= htmlspecialchars($row['ID']) ?></td>
                    <td><?= htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) ?></td>
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
                            <button onclick="openParticipantModal(<?= $row['number'] ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <form method="POST" action="7_StudentTable.php" onsubmit="return confirm('Are you sure you want to delete this participant?');">
                                <input type="hidden" name="action" value="delete_participant">
                                <input type="hidden" name="delete_id" value="<?= $row['number'] ?>">
                                <button type="submit">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                            </form>
                            <button onclick="generateQRCode(<?= $row['number'] ?>, '<?= htmlspecialchars($row['ID']) ?>')">
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
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add/Edit Participant Modal -->
    <div id="participantModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <div class="header">
                <h3 id="modalTitle">Add New Participant</h3>
                <p>Fill out the information below to add a new participant</p>
            </div>
            <form id="participantForm" method="POST">
                <input type="hidden" name="action" value="save_participant">
                <input type="hidden" name="participant-number" id="participant-number">
                <div class="user-details">
                    <div class="input-box">
                        <label for="participant-id">ID Number</label>
                        <input type="text" name="participant-id" id="participant-id" maxlength="8" required>
                    </div>
                    <div class="input-box">
                        <label for="participant-firstname">First Name</label>
                        <input type="text" name="participant-firstname" id="participant-firstname" required>
                    </div>
                    <div class="input-box">
                        <label for="participant-lastname">Last Name</label>
                        <input type="text" name="participant-lastname" id="participant-lastname" required>
                    </div>
                    <div class="input-box">
                        <label for="participant-course">Course</label>
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
                        <label for="participant-section">Section</label>
                        <input type="text" name="participant-section" id="participant-section" required>
                    </div>
                    <div class="input-box">
                        <label for="participant-gender">Gender</label>
                        <select name="participant-gender" id="participant-gender" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="input-box">
                        <label for="participant-age">Age</label>
                        <input type="number" name="participant-age" id="participant-age" min="15" max="99" required>
                    </div>
                    <div class="input-box">
                        <label for="participant-year">Year</label>
                        <select name="participant-year" id="participant-year" required>
                            <option value="">Select Year</option>
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                        </select>
                    </div>
                    <div class="input-box">
                        <label for="participant-dept">Department</label>
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
                    <button type="button" class="btn-close">Cancel</button>
                </div>
            </form>
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
                <p>Scan this QR code to verify participant identity</p>
                <button onclick="downloadQRCode()" class="btn-download">Download QR Code</button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="../Javascript/filter.js"></script>
    <script src="../Javascript/uploadscv.js"></script>
    <script src="../Javascript/participant-modal.js"></script>
    <script src="../Javascript/dropdown.js"></script>
    <script src="../Javascript/table-sort.js"></script>
    <script src="../Javascript/qr-modal.js"></script>
</body>
</html>