<?php
session_start();

// Check email, student_id, and role
if (!isset($_SESSION['email'], $_SESSION['student_id'], $_SESSION['role'])) {
    header("Location: ../pages/1_Login.php");
    exit();
}

// Allowed roles
$allowed_roles = ['student', 'coordinator', 'admin'];

if (!in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: ../pages/1_Login.php");
    exit();
}

include '../php/conn.php';

$student_id = $_SESSION['student_id'];
$upload_msg = '';

// Handle image upload
if (isset($_FILES['profile_image'])) {
    $target_dir = "../uploads/profile_pictures/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
    $new_filename = $student_id . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["profile_image"]["tmp_name"]);
    if($check !== false) {
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array($file_extension, $allowed_types)) {
            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                // Update database with new image path
                $image_path = "uploads/profile_pictures/" . $new_filename;
                $stmt = $conn->prepare("UPDATE users SET image = ? WHERE student_id = ?");
                $stmt->bind_param("ss", $image_path, $student_id);
                $stmt->execute();
                $upload_msg = "Profile picture updated successfully.";
            } else {
                $upload_msg = "Sorry, there was an error uploading your file.";
            }
        } else {
            $upload_msg = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        }
    } else {
        $upload_msg = "File is not an image.";
    }
}

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
        </div>
        <div class="logout">
            <a href="../php/1logout.php" onclick="return confirm('Are you sure you want to logout?');"> <i class="fa-solid fa-right-from-bracket"></i> <span class="label"> Logout </span> </a>
        </div>
    </div>

    <div class="profile-card">
        <div class="cover-photo"></div>
        <div class="profile-info">
            <div class="avatar-section">
                <?php if (!empty($user['image']) && file_exists("../" . $user['image'])): ?>
                    <img src="../<?= htmlspecialchars($user['image']) ?>" alt="Profile Picture">
                <?php else: ?>
                    <i class="fas fa-user"></i>
                <?php endif; ?>
                <label class="avatar-upload">
                    <input type="file" name="profile_image" accept="image/*" onchange="uploadProfileImage(this)">
                    <i class="fas fa-camera"></i> Change Photo
                </label>
            </div>
            <div class="user-details">
                <h2><?= htmlspecialchars($user['name']) ?></h2>
                <p class="subtitle"><?= substr($user['student_id'], 0, 2) . '-' . substr($user['student_id'], 2) ?></p>
            </div>
        </div>

        <div class="details-table">
            <div class="detail-row">
                <span>Student ID</span>
                <span class="value">
                    <?= substr($user['student_id'], 0, 2) . '-' . substr($user['student_id'], 2) ?>
                    <a href="javascript:void(0)" onclick="openModal('student_id', '<?= htmlspecialchars(addslashes($user['student_id'])) ?>')">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </span>
            </div>
            <div class="detail-row">
                <span>Name</span>
                <span class="value">
                    <?= htmlspecialchars($user['name']) ?>
                    <a href="javascript:void(0)" onclick="openModal('name', '<?= htmlspecialchars(addslashes($user['name'])) ?>')">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </span>
            </div>
            <div class="detail-row">
                <span>Email</span>
                <span class="value">
                    <?= htmlspecialchars($user['email']) ?>
                    <a href="javascript:void(0)" onclick="openModal('email', '<?= htmlspecialchars(addslashes($user['email'])) ?>')">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </span>
            </div>
            <div class="detail-row">
                <span>Course</span>
                <span class="value">
                    <?= htmlspecialchars($user['course'] ?? 'N/A') ?>
                    <a href="javascript:void(0)" onclick="openModal('course', '<?= htmlspecialchars(addslashes($user['course'] ?? '')) ?>')">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </span>
            </div>
            <div class="detail-row">
                <span>Year</span>
                <span class="value">
                    <?= htmlspecialchars($user['year'] ?? 'N/A') ?>
                    <a href="javascript:void(0)" onclick="openModal('year', '<?= htmlspecialchars(addslashes($user['year'] ?? '')) ?>')">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </span>
            </div>
            <div class="detail-row">
                <span>Password</span>
                <span class="value">
                    ********
                    <a href="javascript:void(0)" onclick="openModal('password', '')">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </span>
            </div>
        </div>
    </div>

    <?php if (!empty($upload_msg)): ?>
    <div class="alert <?= strpos($upload_msg, 'successfully') !== false ? 'alert-success' : 'alert-error' ?>">
        <?= htmlspecialchars($upload_msg) ?>
    </div>
    <?php endif; ?>

    <!-- Edit Modal -->
    <div id="edit-profile" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h4>Edit <span id="modalFieldName"></span></h4>
            <form id="editForm" method="POST" action="">
                <input type="hidden" name="field" id="fieldInput">
                <div id="valueInputContainer" class="form-group">
                    <input type="text" name="value" id="valueInput" required>
                </div>
                <div id="password-confirm-container" style="display: none;" class="form-group">
                    <input type="password" name="confirm_password" id="confirmPassword" placeholder="Confirm Password">
                    <span id="password-match" class="validation-message"></span>
                </div>
                <div class="button-group">
                    <button type="submit" class="btn btn-primary" id="saveButton">Save Changes</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <style>
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }

    .modal-content {
        background-color: #fff;
        padding: 25px;
        border-radius: 8px;
        width: 90%;
        max-width: 500px;
        position: relative;
    }

    .close {
        position: absolute;
        right: 15px;
        top: 10px;
        font-size: 24px;
        cursor: pointer;
        color: #666;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group input, .form-group select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-top: 5px;
    }

    .button-group {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 20px;
    }

    .btn {
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
        border: none;
        font-weight: 500;
    }

    .btn-primary {
        background-color: #007bff;
        color: white;
    }

    .btn-secondary {
        background-color: #6c757d;
        color: white;
    }

    .validation-message {
        font-size: 12px;
        margin-top: 5px;
        display: block;
    }

    .validation-message.error {
        color: #dc3545;
    }

    .validation-message.success {
        color: #28a745;
    }
    </style>

    <script>
    function openModal(field, currentValue) {
        document.getElementById('modalFieldName').innerText = field.replace('_', ' ').charAt(0).toUpperCase() + field.slice(1);
        document.getElementById('fieldInput').value = field;
        const valueInputContainer = document.getElementById('valueInputContainer');
        const passwordConfirmContainer = document.getElementById('password-confirm-container');
        
        // Reset form
        document.getElementById('editForm').reset();
        passwordConfirmContainer.style.display = 'none';
        
        // Configure input based on field type
        if(field === 'course') {
            const selectHtml = `
                <select name="value" id="valueInput" required class="form-select">
                    <option value="">Select a Course</option>
                    <option value="College of Computer Studies" ${currentValue === 'College of Computer Studies' ? 'selected' : ''}>College of Computer Studies</option>
                    <option value="College of Engineering" ${currentValue === 'College of Engineering' ? 'selected' : ''}>College of Engineering</option>
                    <option value="College of Business Accounting" ${currentValue === 'College of Business Accounting' ? 'selected' : ''}>College of Business Accounting</option>
                    <option value="College of Nursing" ${currentValue === 'College of Nursing' ? 'selected' : ''}>College of Nursing</option>
                </select>
            `;
            valueInputContainer.innerHTML = selectHtml;
        } else if(field === 'year') {
            const selectHtml = `
                <select name="value" id="valueInput" required class="form-select">
                    <option value="">Select Year Level</option>
                    <option value="1" ${currentValue === '1' ? 'selected' : ''}>1st Year</option>
                    <option value="2" ${currentValue === '2' ? 'selected' : ''}>2nd Year</option>
                    <option value="3" ${currentValue === '3' ? 'selected' : ''}>3rd Year</option>
                    <option value="4" ${currentValue === '4' ? 'selected' : ''}>4th Year</option>
                </select>
            `;
            valueInputContainer.innerHTML = selectHtml;
        } else if(field === 'password') {
            valueInputContainer.innerHTML = '<input type="password" name="value" id="valueInput" placeholder="New Password" required>';
            passwordConfirmContainer.style.display = 'block';
            
            // Add password validation
            const passwordInput = document.getElementById('valueInput');
            const confirmInput = document.getElementById('confirmPassword');
            const saveButton = document.getElementById('saveButton');
            
            function validatePasswords() {
                const passwordMatch = document.getElementById('password-match');
                if(confirmInput.value && passwordInput.value !== confirmInput.value) {
                    passwordMatch.textContent = 'Passwords do not match';
                    passwordMatch.className = 'validation-message error';
                    saveButton.disabled = true;
                } else if(confirmInput.value) {
                    passwordMatch.textContent = 'Passwords match';
                    passwordMatch.className = 'validation-message success';
                    saveButton.disabled = false;
                } else {
                    passwordMatch.textContent = '';
                    saveButton.disabled = false;
                }
            }
            
            passwordInput.addEventListener('input', validatePasswords);
            confirmInput.addEventListener('input', validatePasswords);
        } else if(field === 'email') {
            valueInputContainer.innerHTML = '<input type="email" name="value" id="valueInput" required>';
            document.getElementById('valueInput').value = currentValue;
        } else {
            valueInputContainer.innerHTML = '<input type="text" name="value" id="valueInput" required>';
            document.getElementById('valueInput').value = currentValue;
        }
        
        document.getElementById('edit-profile').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('edit-profile').style.display = 'none';
        // Reset validation messages
        const validationMessages = document.getElementsByClassName('validation-message');
        Array.from(validationMessages).forEach(msg => msg.textContent = '');
    }

    function uploadProfileImage(input) {
        if (input.files && input.files[0]) {
            const formData = new FormData();
            formData.append('profile_image', input.files[0]);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            }).then(response => {
                window.location.reload();
            }).catch(error => {
                console.error('Error:', error);
                alert('Error uploading image. Please try again.');
            });
        }
    }

    // Form validation before submit
    document.getElementById('editForm').addEventListener('submit', function(e) {
        const field = document.getElementById('fieldInput').value;
        const value = document.getElementById('valueInput').value;
        
        if(field === 'email') {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if(!emailRegex.test(value)) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return;
            }
        } else if(field === 'student_id') {
            const studentIdRegex = /^\d{8}$/;
            if(!studentIdRegex.test(value)) {
                e.preventDefault();
                alert('Student ID must be 8 digits');
                return;
            }
        }
    });
    </script>
</body>
</html>
