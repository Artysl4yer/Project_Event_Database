<?php
session_start();

if (!isset($_SESSION['email']) || !isset($_SESSION['student_id'])) {
    header("Location: ../pages/Login_v1.php");
    exit();
}

include '../php/conn.php';

$student_id = $_SESSION['student_id'];

// Get student details from the database
$stmt = $conn->prepare("SELECT * FROM users WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Student not found.";
    exit();
}

$user = $result->fetch_assoc();

// Handle form submission to update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $field = $_POST['field'] ?? '';
    $value = $_POST['value'] ?? '';

    // Basic validation for allowed fields to update
    $allowed_fields = ['student_id', 'name', 'email', 'course', 'year', 'password'];
    if (in_array($field, $allowed_fields)) {
        if ($field === 'password') {
            $value = password_hash($value, PASSWORD_DEFAULT);
        }
        $stmt = $conn->prepare("UPDATE users SET $field = ? WHERE student_id = ?");
        $stmt->bind_param("ss", $value, $student_id);
        $stmt->execute();

        // Refresh $user data after update
        $stmt = $conn->prepare("SELECT * FROM users WHERE student_id = ?");
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        echo "<script>alert('Profile updated successfully.');</script>";
    } else {
        echo "<script>alert('Invalid field.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Profile</title>
    <link rel="stylesheet" href="../styles/style1.css">
    <link rel="stylesheet" href="../styles/student-profile.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="title-container">
        <img src="../images-icon/plplogo.png" alt="Logo">
        <h1>Pamantasan ng Lungsod ng Pasig</h1>
    </div>

    <div class="tab-container">
        <div class="menu-items">
            <a href="../pages/student-profile.php" class="active"><i class="fa-regular fa-circle-user"></i><span class="label"> Profile </span></a>
            <a href="../pages/student-home.php" class="active"><i class="fa-solid fa-home"></i><span class="label"> Home </span></a>
            <a href="../pages/5_About.php" class="active"><i class="fa-solid fa-circle-info"></i><span class="label"> About </span></a>
            <a href="#" class="active"><i class="fa-solid fa-calendar"></i><span class="label"> QR Code </span></a>
        </div>
        <div class="logout">
            <a href="../php/1logout.php"><i class="fa-solid fa-gear"></i><span class="label"> Logout </span></a>
        </div>
    </div>

    <div class="profile-card">
        <div class="cover-photo"></div>

        <div class="profile-info">
            <div class="avatar-section">
                <input type="hidden" name="student_id" value="<?= htmlspecialchars($user['student_id']) ?>">
            </div>

            <div class="user-details">
                <h2><?= htmlspecialchars($user['name']) ?></h2>
                <p class="subtitle"><?= substr($user['student_id'], 0, 2) . '-' . substr($user['student_id'], 2) ?></p>
            </div>
        </div>

        <div class="details-table">
            <div class="detail-row">
                <span>Student ID</span>
                <span class="value"><?= substr($user['student_id'], 0, 2) . '-' . substr($user['student_id'], 2) ?> 
                    <a href="javascript:void(0)" onclick="openModal('student_id', '<?= htmlspecialchars(addslashes($user['student_id'])) ?>')">Edit</a>
                </span>
            </div>
            <div class="detail-row">
                <span>Name</span>
                <span class="value"><?= htmlspecialchars($user['name']) ?> 
                    <a href="javascript:void(0)" onclick="openModal('name', '<?= htmlspecialchars(addslashes($user['name'])) ?>')">Edit</a>
                </span>
            </div>
            <div class="detail-row">
                <span>Email</span>
                <span class="value"><?= htmlspecialchars($user['email']) ?> 
                    <a href="javascript:void(0)" onclick="openModal('email', '<?= htmlspecialchars(addslashes($user['email'])) ?>')">Edit</a>
                </span>
            </div>
            <div class="detail-row">
                <span>Course</span>
                <span class="value"><?= htmlspecialchars($user['course'] ?? 'N/A') ?> 
                <a href="javascript:void(0)" onclick="course()">Edit</a>
                </span>
            </div>

            <div class="detail-row">
                <span>Year</span>
                <span class="value"><?= htmlspecialchars($user['year'] ?? 'N/A') ?> 
                    <a href="javascript:void(0)" onclick="openModal('year', '<?= htmlspecialchars(addslashes($user['year'] ?? '')) ?>')">Edit</a>
                </span>
            </div>
            <div class="detail-row">
                <span>Password</span>
                <span class="value">******** 
                    <a href="javascript:void(0)" onclick="openModal('password', '')">Edit</a>
                </span>
            </div>
        </div>
    </div>

    <!-- Modal HTML -->
    <div id="edit-profile" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; 
            background: rgba(0,0,0,0.5); justify-content:center; align-items:center;">
        <div style="background:#fff; padding:20px; border-radius:5px; width:300px; position:relative;">
            <h4>Edit <span id="modalFieldName"></span></h4>
            <form id="editForm" method="POST" action="">
            <input type="hidden" name="field" id="fieldInput">
            <input type="text" name="value" id="valueInput" style="width:100%; margin-bottom:10px;" required>
            <button type="submit" class="btn btn-primary btn-sm">Save</button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal()">Cancel</button>
            </form>
        </div>
        </div>

    <script>
    function openModal(field, currentValue) {
        document.getElementById('modalFieldName').innerText = field.charAt(0).toUpperCase() + field.slice(1);
        document.getElementById('fieldInput').value = field;
        document.getElementById('valueInput').value = currentValue;
        // If editing password, clear the value field and change input type
        if(field === 'password'){
        document.getElementById('valueInput').type = 'password';
        document.getElementById('valueInput').value = '';
        } else {
        document.getElementById('valueInput').type = 'text';
        }
        document.getElementById('edit-profile').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('edit-profile').style.display = 'none';
    }
    </script>

<script src="../Javascript/login1.js"></script>

</body>
</html>
