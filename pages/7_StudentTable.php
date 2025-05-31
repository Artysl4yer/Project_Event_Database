<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/1_Login.php");
    exit();
}

include '../php/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    
    $response = ['success' => false, 'message' => 'Unknown error'];

    try {
        if (!isset($_POST['action'])) {
            throw new Exception('No action specified');
        }

        switch ($_POST['action']) {
            case 'get_participant':
                if (!isset($_POST['id'])) {
                    throw new Exception('No participant ID provided');
                }
                $id = intval($_POST['id']);
    $stmt = $conn->prepare("SELECT * FROM student_table WHERE number = ?");
    $stmt->bind_param("i", $id);
                if (!$stmt->execute()) {
                    throw new Exception('Database error: ' . $stmt->error);
                }
    $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    echo json_encode(['success' => true, 'data' => $row]);
                } else {
                    throw new Exception('Student not found');
                }
                exit;

            case 'save_participant':
                $required_fields = ['participant-id', 'participant-firstname', 'participant-lastname', 
                                  'participant-course', 'section-letter', 'participant-gender', 
                                  'participant-age', 'participant-year', 'participant-dept'];
                
                foreach ($required_fields as $field) {
                    if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                        throw new Exception("Missing required field: $field");
                    }
                }

                $conn->begin_transaction();

                try {
                    $number = $_POST['participant-number'] ?? null;
                    $id = trim($_POST['participant-id']);
                    $first_name = ucwords(strtolower(trim($_POST['participant-firstname'])));
                    $last_name = ucwords(strtolower(trim($_POST['participant-lastname'])));
                    $course = strtoupper(trim($_POST['participant-course']));
                    $year = trim($_POST['participant-year']);
                    $section_letter = trim($_POST['section-letter']);
                    $section = $course . $year . $section_letter;
                    $gender = ucfirst(strtolower(trim($_POST['participant-gender'])));
                    $age = intval($_POST['participant-age']);
                    $dept = trim($_POST['participant-dept']);

                    if (!preg_match('/^\d{2}-\d{5}$/', $id)) {
                        throw new Exception('Invalid Student ID format. Please use XX-XXXXX format.');
                    }

                    if ($age < 15 || $age > 99) {
                        throw new Exception('Age must be between 15 and 99.');
                    }

                    if (empty($number)) {
                        // Adding new student
                        $check_stmt = $conn->prepare("SELECT COUNT(*) FROM student_table WHERE ID = ?");
                        $check_stmt->bind_param("s", $id);
                        $check_stmt->execute();
                        $check_stmt->bind_result($count);
                        $check_stmt->fetch();
                        $check_stmt->close();

                        if ($count > 0) {
                            throw new Exception('Student ID already exists.');
                        }

                        $stmt = $conn->prepare("INSERT INTO student_table (ID, first_name, last_name, Course, Section, Gender, Age, Year, Dept) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("ssssssiss", $id, $first_name, $last_name, $course, $section, $gender, $age, $year, $dept);
                    } else {
                        // Updating existing student
                        $check_stmt = $conn->prepare("SELECT COUNT(*) FROM student_table WHERE ID = ? AND number != ?");
                        $check_stmt->bind_param("si", $id, $number);
                        $check_stmt->execute();
                        $check_stmt->bind_result($count);
                        $check_stmt->fetch();
                        $check_stmt->close();

                        if ($count > 0) {
                            throw new Exception('Student ID already exists.');
                        }

                        $stmt = $conn->prepare("UPDATE student_table SET ID=?, first_name=?, last_name=?, Course=?, Section=?, Gender=?, Age=?, Year=?, Dept=? WHERE number=?");
                        $stmt->bind_param("ssssssissi", $id, $first_name, $last_name, $course, $section, $gender, $age, $year, $dept, $number);
                    }

                    if (!$stmt->execute()) {
                        throw new Exception('Database error: ' . $stmt->error);
                    }

                    $conn->commit();
                    $response = [
                        'success' => true,
                        'message' => empty($number) ? 'Student added successfully.' : 'Student updated successfully.'
                    ];
                } catch (Exception $e) {
                    $conn->rollback();
                    throw $e;
                }
                break;

            case 'delete_participant':
                if (!isset($_POST['delete_id'])) {
                    throw new Exception('Student ID is required for deletion.');
                }

                $conn->begin_transaction();

                try {
                    $delete_id = intval($_POST['delete_id']);
                    $stmt = $conn->prepare("DELETE FROM student_table WHERE number = ?");
                    $stmt->bind_param("i", $delete_id);

                    if (!$stmt->execute()) {
                        throw new Exception('Database error: ' . $stmt->error);
                    }

                    if ($stmt->affected_rows === 0) {
                        throw new Exception('Student not found.');
                    }

                    $conn->commit();
                    $response = ['success' => true, 'message' => 'Student deleted successfully.'];
                } catch (Exception $e) {
                    $conn->rollback();
                    throw $e;
                }
                break;

            default:
                throw new Exception('Invalid action specified');
        }
    } catch (Exception $e) {
        $response = ['success' => false, 'message' => $e->getMessage()];
    }

    echo json_encode($response);
    exit;
}

// Display session messages if they exist
if (isset($_SESSION['message'])) {
    $icon = isset($_SESSION['message_type']) && $_SESSION['message_type'] === 'error' ? 'error' : 'success';
    $title = $icon === 'error' ? 'Error' : 'Success';
    echo "<script>
        window.onload = function() {
            Swal.fire({
                icon: '$icon',
                title: '$title',
                text: '" . addslashes($_SESSION['message']) . "'
            });
        };
    </script>";
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

$sql = "SELECT * FROM student_table ORDER BY number DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PLP: Students</title>
    <link rel="stylesheet" href="../styles/style2.css">
    <link rel="stylesheet" href="../styles/style3.css">
    <link rel="stylesheet" href="../styles/style1.css">
    <link rel="stylesheet" href="../styles/style5.css">
    <link rel="stylesheet" href="../styles/style6.css">
    <link rel="stylesheet" href="../styles/style8.css">
    <link rel="stylesheet" href="../styles/student-filter.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="title-container">
        <img src="../images-icon/plplogo.png"> <h1> Pamantasan ng Lungsod ng Pasig </h1>
    </div>
    <div class="tab-container">
        <div class="menu-items">
            <a href="admin-home.php" class="active"> <i class="fa-solid fa-users-gear"></i> <span class="label">User Manage</span> </a>
            <a href="4_Event.php"> <i class="fa-solid fa-home"></i> <span class="label"> Home </span> </a>
            <a href="6_NewEvent.php"> <i class="fa-solid fa-calendar"></i> <span class="label"> Events </span> </a>
            <a href="7_StudentTable.php"> <i class="fa-solid fa-address-card"></i> <span class="label"> Students </span> </a>
            <a href="guest-table.php"> <i class="fa-solid fa-users"></i> <span class="label"> Guests </span> </a>
            <a href="5_About.php"> <i class="fa-solid fa-circle-info"></i> <span class="label"> About </span> </a>
            <a href="8_archive.php"> <i class="fa-solid fa-bars"></i> <span class="label"> Logs </span> </a>
        </div>
        <div class="logout">
            <a href="../php/logout.php"> <i class="fa-solid fa-sign-out"></i> <span class="label"> Logout </span> </a>
        </div>
    </div>
    <div class="event-main">
        <div class="event-details">
            <div class="event-top">
                <h2>Students List</h2>
                <div class="search-container">
                    <form class="search-form" action="" method="GET">
                        <input type="text" id="search" name="search" placeholder="Search students..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                        <button type="submit"><i class="fa fa-search"></i></button>
                    </form>
                </div>
                <div class="col-md-12" id="importFrm">
                    <form enctype="multipart/form-data">
                        <div class="upload-container">
                        <label class="upload-btn">
                                <i class="fas fa-file-upload"></i> Upload CSV
                                <input type="file" id="fileInput" name="file" accept=".csv" hidden>
                        </label>
                        <span id="fileName">No file chosen</span>
                            <a href="#" id="downloadTemplate" onclick="downloadCSVTemplate(event)">
                                <i class="fas fa-download"></i> Download Template
                            </a>
                        </div>
                    </form>
                </div>
            </div>  
        </div>
        <div class="event-table-section">
            <div class="table-header">
                <div class="filter-container">
                    <div class="filter-buttons">
                        <button class="filter-btn active" data-filter="all">All Students</button>
                        <button class="filter-btn" data-filter="name">Sort by Name</button>
                        <div class="course-filter-container">
                            <button class="filter-btn" id="courseFilterBtn">
                                <i class="fas fa-filter"></i> Filter by Course
                            </button>
                            <select id="courseFilter" class="course-select">
                                <option value="all">All Courses</option>
                                <option value="BSIT">BSIT</option>
                                <option value="BSCS">BSCS</option>
                                <option value="BSIS">BSIS</option>
                                <option value="BSCE">BSCE</option>
                                <option value="BSBA">BSBA</option>
                                <option value="BSA">BSA</option>
                                <option value="BSEDUC">BSEDUC</option>
                                <option value="BSN">BSN</option>
                                <option value="BSHM">BSHM</option>
                                <option value="BSENG">BSENG</option>
                                <option value="BSAS">BSAS</option>
                            </select>
                        </div>
                        <button class="btn-import" onclick="openAddStudentModal()">
                            <i class="fa-solid fa-plus"></i> Add Student
                        </button>
                    </div>
                </div>
            </div>
            <table class="event-display-table" id="studentTable">
                <thead>
                    <tr>
                        <th data-sort="number">No.</th>
                        <th data-sort="id">Student ID</th>
                        <th data-sort="name">Full Name</th>
                        <th data-sort="course">Course</th>
                        <th data-sort="section">Section</th>
                        <th data-sort="year">Year</th>
                        <th data-sort="department">Department</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                        $section = $row['Section'] ?? '';
                        $year = $row['Year'] ?? '';
                        $formatted_section = $section ? $section : ($row['Course'] . $year . ($row['Section'] ?? ''));
                ?>
                <tr>
                    <td><?= str_pad($row['number'], 3, '0', STR_PAD_LEFT) ?></td>
                    <td><?= htmlspecialchars($row['ID'] ?? '') ?></td>
                    <td><?= htmlspecialchars(ucwords(strtolower(($row['first_name'] ?? '') . " " . ($row['last_name'] ?? '')))) ?></td>
                    <td><?= htmlspecialchars($row['Course'] ?? '') ?></td>
                    <td><?= htmlspecialchars($formatted_section) ?></td>
                            <td><?= $year ? $year . ' Year' : '' ?></td>
                    <td><?= htmlspecialchars($row['Dept'] ?? '') ?></td>
                    <td class="dropdown-wrapper">
                        <button class="dropdown-toggle">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu">
                                    <button type="button" class="edit-btn" 
                                        data-student='<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'
                                        onclick="editStudent(this.dataset.student)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                                    <button type="button" class="delete-btn" onclick="deleteStudent(<?= $row['number'] ?>, '<?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name'], ENT_QUOTES) ?>')">
                                        <i class="fas fa-trash"></i> Delete
                                </button>
                                    <button type="button" class="qr-btn" onclick="generateQRCode(<?= $row['number'] ?>, '<?= htmlspecialchars($row['ID'], ENT_QUOTES) ?>')">
                                <i class="fas fa-qrcode"></i> Generate QR
                            </button>
                        </div>
                    </td>
                </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                <tr><td colspan="8" class="no-data">No students found</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add/Edit Student Modal -->
    <div id="participantModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeParticipantModal()">&times;</span>
            <h2 id="modalTitle">Add New Student</h2>
            <form id="participantForm" method="POST" action="7_StudentTable.php">
                <input type="hidden" name="action" value="save_participant">
                <input type="hidden" name="participant-number" id="participant-number">
                <div class="user-details">
                    <div class="input-box">
                        <label for="participant-id">ID Number</label>
                        <input type="text" name="participant-id" id="participant-id" 
                               pattern="[0-9]{2}-[0-9]{5}" 
                               placeholder="e.g., 23-00992"
                               title="Please enter ID in format: 23-00992"
                               maxlength="8" required>
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
                            <option value="BSIT">BSIT</option>
                            <option value="BSCS">BSCS</option>
                            <option value="BSIS">BSIS</option>
                            <option value="BSCE">BSCE</option>
                            <option value="BSBA">BSBA</option>
                            <option value="BSA">BSA</option>
                            <option value="BSEDUC">BSEDUC</option>
                            <option value="BSN">BSN</option>
                            <option value="BSHM">BSHM</option>
                            <option value="BSENG">BSENG</option>
                            <option value="BSAS">BSAS</option>
                        </select>
                    </div>
                    <div class="input-box">
                        <label for="participant-section">Section</label>
                        <select id="participant-section" name="section-letter" required>
                            <option value="">Select Section</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                            <option value="E">E</option>
                            <option value="F">F</option>
                            <option value="G">G</option>
                            <option value="H">H</option>
                        </select>
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
                            <option value="COED">College of Education</option>
                            <option value="CON">College of Nursing</option>
                            <option value="CIHM">College of International Hospitality Management</option>
                        </select>
                    </div>
                </div>
                <div class="button">
                    <input type="submit" value="Save">
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

    <!-- Success Modal -->
    <div id="successModal" class="modal">
        <div class="modal-content">
            <div class="success-checkmark">
                <div class="check-icon">
                    <span class="icon-line line-tip"></span>
                    <span class="icon-line line-long"></span>
                    <div class="icon-circle"></div>
                    <div class="icon-fix"></div>
                </div>
            </div>
            <h2 id="successTitle">Success!</h2>
            <p id="successMessage"></p>
            <div class="button">
                <button type="button" onclick="closeSuccessModal()" class="btn-ok">OK</button>
            </div>
        </div>
    </div>

    <style>
    .success-checkmark {
        width: 80px;
        height: 80px;
        margin: 0 auto;
        margin-bottom: 20px;
    }
    .success-checkmark .check-icon {
        width: 80px;
        height: 80px;
        position: relative;
        border-radius: 50%;
        box-sizing: content-box;
        border: 4px solid #4CAF50;
    }
    .success-checkmark .check-icon::before {
        top: 3px;
        left: -2px;
        width: 30px;
        transform-origin: 100% 50%;
        border-radius: 100px 0 0 100px;
    }
    .success-checkmark .check-icon::after {
        top: 0;
        left: 30px;
        width: 60px;
        transform-origin: 0 50%;
        border-radius: 0 100px 100px 0;
        animation: rotate-circle 4.25s ease-in;
    }
    .success-checkmark .check-icon::before, .success-checkmark .check-icon::after {
        content: '';
        height: 100px;
        position: absolute;
        background: #FFFFFF;
        transform: rotate(-45deg);
    }
    .success-checkmark .check-icon .icon-line {
        height: 5px;
        background-color: #4CAF50;
        display: block;
        border-radius: 2px;
        position: absolute;
        z-index: 10;
    }
    .success-checkmark .check-icon .icon-line.line-tip {
        top: 46px;
        left: 14px;
        width: 25px;
        transform: rotate(45deg);
        animation: icon-line-tip 0.75s;
    }
    .success-checkmark .check-icon .icon-line.line-long {
        top: 38px;
        right: 8px;
        width: 47px;
        transform: rotate(-45deg);
        animation: icon-line-long 0.75s;
    }
    .success-checkmark .check-icon .icon-circle {
        top: -4px;
        left: -4px;
        z-index: 10;
        width: 80px;
        height: 80px;
        border-radius: 50%;
        position: absolute;
        box-sizing: content-box;
        border: 4px solid rgba(76, 175, 80, .5);
    }
    .success-checkmark .check-icon .icon-fix {
        top: 8px;
        width: 5px;
        left: 26px;
        z-index: 1;
        height: 85px;
        position: absolute;
        transform: rotate(-45deg);
        background-color: #FFFFFF;
    }
    @keyframes rotate-circle {
        0% { transform: rotate(-45deg); }
        5% { transform: rotate(-45deg); }
        12% { transform: rotate(-405deg); }
        100% { transform: rotate(-405deg); }
    }
    @keyframes icon-line-tip {
        0% { width: 0; left: 1px; top: 19px; }
        54% { width: 0; left: 1px; top: 19px; }
        70% { width: 50px; left: -8px; top: 37px; }
        84% { width: 17px; left: 21px; top: 48px; }
        100% { width: 25px; left: 14px; top: 46px; }
    }
    @keyframes icon-line-long {
        0% { width: 0; right: 46px; top: 54px; }
        65% { width: 0; right: 46px; top: 54px; }
        84% { width: 55px; right: 0px; top: 35px; }
        100% { width: 47px; right: 8px; top: 38px; }
    }
    .btn-ok {
        background-color: #4CAF50;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        margin-top: 20px;
    }
    .btn-ok:hover {
        background-color: #45a049;
    }
    #successModal .modal-content {
        text-align: center;
        padding: 40px;
    }
    #successMessage {
        font-size: 18px;
        color: #666;
        margin: 20px 0;
    }
    .sorted-asc::after {
        content: ' ▲';
        color: #4CAF50;
    }

    .sorted-desc::after {
        content: ' ▼';
        color: #4CAF50;
    }

    th[data-sort] {
        cursor: pointer;
    }

    th[data-sort]:hover {
        background-color: #f0f0f0;
    }

    .filter-buttons {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 15px;
    }

    .filter-btn {
        padding: 0 16px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background: #fff;
        cursor: pointer;
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .filter-btn.active {
        background: #4CAF50;
        color: white;
        border-color: #4CAF50;
    }

    .filter-btn:hover {
        background: #f0f0f0;
    }

    .filter-btn.active:hover {
        background: #45a049;
    }

    .btn-import {
        background: #4CAF50;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: background-color 0.3s ease;
    }

    .btn-import:hover {
        background: #45a049;
    }

    .search-container {
        margin-bottom: 15px;
    }

    .search-container input {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        width: 250px;
        margin-right: 8px;
    }

    .search-container button {
        padding: 8px 12px;
        background: #4CAF50;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .search-container button:hover {
        background: #45a049;
    }

    .course-filter-container {
        display: inline-block;
        margin-left: 10px;
        vertical-align: middle;
    }

    .course-select {
        height: 35px;
        padding: 0 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background-color: white;
        font-size: 14px;
        min-width: 180px;
        cursor: pointer;
        outline: none;
        transition: all 0.3s ease;
    }

    .course-select:hover {
        border-color: #4CAF50;
    }

    .course-select:focus {
        border-color: #4CAF50;
        box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
    }

    .filter-btn, .course-select {
        height: 35px;
        line-height: 35px;
        vertical-align: middle;
    }

    .filter-container {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .dropdown-wrapper {
        position: relative;
    }

    .dropdown-toggle {
        background: none;
        border: none;
        color: #666;
        cursor: pointer;
        padding: 5px 10px;
        transition: color 0.3s;
    }

    .dropdown-toggle:hover {
        color: #4CAF50;
    }

    .dropdown-menu {
        display: none;
        position: absolute;
        right: 0;
        background: white;
        min-width: 160px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-radius: 4px;
        border: 1px solid #ddd;
        z-index: 1000;
        padding: 8px 0;
    }

    .dropdown-menu.show {
        display: block;
    }

    .dropdown-menu button {
        display: flex;
        align-items: center;
        width: 100%;
        padding: 8px 16px;
        border: none;
        background: none;
        cursor: pointer;
        font-size: 14px;
        color: #333;
        text-align: left;
        transition: background-color 0.2s;
    }

    .dropdown-menu button:hover {
        background-color: #f5f5f5;
    }

    .dropdown-menu button i {
        margin-right: 8px;
        width: 16px;
    }

    .dropdown-menu .edit-btn:hover {
        color: #2196F3;
    }

    .dropdown-menu .delete-btn:hover {
        color: #f44336;
    }

    .dropdown-menu .qr-btn:hover {
        color: #4CAF50;
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        z-index: 1000;
    }

    .modal-content {
        position: relative;
        background-color: #fff;
        margin: 5% auto;
        padding: 20px;
        width: 80%;
        max-width: 600px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .close {
        position: absolute;
        right: 20px;
        top: 10px;
        font-size: 28px;
        font-weight: bold;
        color: #666;
        cursor: pointer;
    }

    .close:hover {
        color: #000;
    }

    .user-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .input-box {
        margin-bottom: 15px;
    }

    .input-box label {
        display: block;
        margin-bottom: 5px;
        color: #333;
        font-weight: 500;
    }

    .input-box input,
    .input-box select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }

    .input-box input:focus,
    .input-box select:focus {
        border-color: #4CAF50;
        outline: none;
        box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
    }

    .button {
        text-align: center;
        margin-top: 20px;
    }

    .button input[type="submit"] {
        background-color: #4CAF50;
        color: white;
        padding: 10px 30px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        transition: background-color 0.3s;
    }

    .button input[type="submit"]:hover {
        background-color: #45a049;
    }
    </style>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="../Javascript/search.js"></script>
    <script src="../Javascript/student-table.js"></script>
    <script src="../Javascript/student-filter.js"></script>
    <script src="../Javascript/student-modal.js"></script>
    <script src="../Javascript/student-qr.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Close modal when clicking outside
            window.addEventListener('click', function(event) {
                const participantModal = document.getElementById('participantModal');
                if (event.target === participantModal) {
                    closeParticipantModal();
                }
            });

            // Form submission handler
        const participantForm = document.getElementById('participantForm');
        if (participantForm) {
                participantForm.addEventListener('submit', function(event) {
                    event.preventDefault();
                    
                    const formData = new FormData(this);

                    fetch('7_StudentTable.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: data.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                closeParticipantModal();
                                window.location.reload();
                            });
                        } else {
                            throw new Error(data.message);
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error.message || 'An unexpected error occurred'
                        });
                    });
                });
            }

            // Initialize filter buttons
            const filterButtons = document.querySelectorAll('.filter-btn');
            if (filterButtons) {
                filterButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        filterButtons.forEach(btn => btn.classList.remove('active'));
                        this.classList.add('active');
                    });
                });
            }
        });
    </script>
</body>
</html>