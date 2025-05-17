
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
                                <input type="text" name="participant-course" id="participant-course" required>
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
                                <input type="text" name="participant-year" id="participant-year" required>
                            </div>
                            <div class="input-box">
                                <label>Department</label>
                                <input type="text" name="participant-dept" id="participant-dept" required>
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
                    <td>
                        <button class="edit-btn" onclick="openParticipantModal(<?= $row['number'] ?>)">Edit</button>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="delete_participant">
                            <input type="hidden" name="delete_id" value="<?= $row['number'] ?>">
                            <button type="submit" class="delete-btn" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
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

    <script src="../Javascript/student-table.js"></script>
</body>
</html>