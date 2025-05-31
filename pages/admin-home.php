<?php
session_start();
include '../php/conn.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/1_Login.php");
    exit();
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Set header for JSON response
    header('Content-Type: application/json');
    
    $response = ['success' => false, 'message' => 'Unknown error'];

    try {
        if (!isset($_POST['action'])) {
            throw new Exception('No action specified');
        }

        $conn->begin_transaction();

        switch ($_POST['action']) {
            case 'add_user':
            case 'edit_user':
                $required_fields = ['name', 'email', 'role'];
                foreach ($required_fields as $field) {
                    if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                        throw new Exception("Missing required field: $field");
                    }
                }

                $name = trim($_POST['name']);
                $email = trim($_POST['email']);
                $role = trim($_POST['role']);
                $password = isset($_POST['password']) && !empty($_POST['password']) ? 
                           password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
                
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Invalid email format');
                }

                if (!in_array($role, ['admin', 'coordinator'])) {
                    throw new Exception('Invalid role selected');
                }

                if ($_POST['action'] === 'add_user') {
                    // Check if email exists
                    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
                    $check_stmt->bind_param("s", $email);
                    $check_stmt->execute();
                    $check_stmt->bind_result($count);
                    $check_stmt->fetch();
                    $check_stmt->close();

                    if ($count > 0) {
                        throw new Exception('Email already exists');
                    }

                    if (empty($password)) {
                        throw new Exception('Password is required for new users');
                    }

                    $stmt = $conn->prepare("INSERT INTO users (name, email, role, password) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $name, $email, $role, $password);
                } else {
                    if (!isset($_POST['user_id'])) {
                        throw new Exception('User ID is required for editing');
                    }
                    
                    $user_id = $_POST['user_id'];
                    
                    // Check if email exists for other users
                    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
                    $check_stmt->bind_param("si", $email, $user_id);
                    $check_stmt->execute();
                    $check_stmt->bind_result($count);
                    $check_stmt->fetch();
                    $check_stmt->close();

                    if ($count > 0) {
                        throw new Exception('Email already exists');
                    }

                    if ($password) {
                        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, role=?, password=? WHERE id=?");
                        $stmt->bind_param("ssssi", $name, $email, $role, $password, $user_id);
                    } else {
                        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, role=? WHERE id=?");
                        $stmt->bind_param("sssi", $name, $email, $role, $user_id);
                    }
                }
                
                if (!$stmt->execute()) {
                    throw new Exception($stmt->error);
                }
                
                $response = [
                    'success' => true, 
                    'message' => $_POST['action'] === 'add_user' ? 'User added successfully' : 'User updated successfully'
                ];
                break;

            case 'delete_user':
                if (!isset($_POST['user_id'])) {
                    throw new Exception('User ID is required for deletion');
                }

                $user_id = intval($_POST['user_id']);
                
                // Prevent deleting the last admin
                $check_stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin' AND id != ?");
                $check_stmt->bind_param("i", $user_id);
                $check_stmt->execute();
                $check_stmt->bind_result($admin_count);
                $check_stmt->fetch();
                $check_stmt->close();

                if ($admin_count === 0) {
                    throw new Exception('Cannot delete the last admin user');
                }

                $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                
                if (!$stmt->execute()) {
                    throw new Exception($stmt->error);
                }

                if ($stmt->affected_rows === 0) {
                    throw new Exception('User not found');
                }
                
                $response = ['success' => true, 'message' => 'User deleted successfully'];
                break;

            default:
                throw new Exception('Invalid action');
        }

        $conn->commit();
        
    } catch (Exception $e) {
        $conn->rollback();
        $response = ['success' => false, 'message' => $e->getMessage()];
    }

    // Return JSON response
    echo json_encode($response);
    exit;
}

// Display session messages if they exist
if (isset($_SESSION['message'])) {
    echo "<script>
        window.onload = function() {
            Swal.fire({
                icon: '" . ($_SESSION['message_type'] ?? 'success') . "',
                title: '" . ($_SESSION['message_type'] === 'error' ? 'Error' : 'Success') . "',
                text: '" . addslashes($_SESSION['message']) . "'
            });
        };
    </script>";
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

$sql = "SELECT * FROM users WHERE role IN ('admin', 'coordinator') ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
    <head>
    <meta charset="UTF-8">
    <title>PLP: Admin Management</title>
        <link rel="stylesheet" href="../styles/style1.css">
    <link rel="stylesheet" href="../styles/style2.css">
    <link rel="stylesheet" href="../styles/style3.css">
    <link rel="stylesheet" href="../styles/style5.css">
    <link rel="stylesheet" href="../styles/style6.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../styles/search.css">
    <script src="../Javascript/search.js"></script>
    </head>
    <body>
        <div class="title-container">
            <img src="../images-icon/plplogo.png"> <h1> Pamantasan ng Lungsod ng Pasig </h1>
    </div>
            <div class="tab-container">
                <div class="menu-items">
                    <a href="admin-home.php" class="active"> <i class="fa-solid fa-users-gear"></i> <span class="label">User Manage</span> </a>
                    <a href="4_Event.php" class="active"> <i class="fa-solid fa-home"></i> <span class="label"> Home </span> </a>
                    <a href="6_NewEvent.php"> <i class="fa-solid fa-calendar"></i> <span class="label"> Events </span> </a>
                    <a href="7_StudentTable.php"> <i class="fa-solid fa-address-card"></i> <span class="label"> Students </span> </a>
                    <a href="guest-table.php"> <i class="fa-solid fa-users"></i> <span class="label"> Guests </span> </a>
                    <a href="5_About.php"> <i class="fa-solid fa-circle-info"></i> <span class="label"> About </span> </a>
                    <a href="8_archive.php"> <i class="fa-solid fa-bars"></i> <span class="label"> Logs </span> </a>
                </div>
                <div class="logout">
                    <a href="../php/logout.php" onclick="return confirm('Are you sure you want to logout?');"> <i class="fa-solid fa-right-from-bracket"></i> <span class="label"> Logout </span> </a>
                </div>
            </div>

    <div class="event-main">
        <div class="event-details">
            <div class="event-top">
                <h2>System Users</h2>
                <div class="search-container">
                    <form class="search-form" action="" method="GET">
                        <input type="text" id="search" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                        <button type="submit"><i class="fa fa-search"></i></button>
                    </form>
                </div>
                <div class="col-md-12" id="importFrm">
                    <div class="upload-container">
                        <button class="btn-import" onclick="openUserModal()">
                            <i class="fa-solid fa-plus"></i> Add New User
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="event-table-section">
            <div class="table-header">
                <div class="filter-container">
                    <div class="filter-buttons">
                        <button class="filter-btn active" data-filter="all">All Users</button>
                        <button class="filter-btn" data-filter="admin">Admin</button>
                        <button class="filter-btn" data-filter="coordinator">Coordinator</button>
                    </div>
                </div>
            </div>

            <table class="event-display-table" id="userTable">
                <thead>
                    <tr>
                        <th data-sort="id">No.</th>
                        <th data-sort="name">Full Name</th>
                        <th data-sort="email">Email</th>
                        <th data-sort="role">Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr data-role="<?= strtolower($row['role']) ?>">
                            <td><?= str_pad($row['id'], 3, '0', STR_PAD_LEFT) ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['role']) ?></td>
                            <td class="dropdown-wrapper">
                                <button class="dropdown-toggle">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <button type="button" class="edit-btn" onclick="editUser(<?= htmlspecialchars(json_encode($row)) ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button type="button" class="delete-btn" onclick="deleteUser(<?= $row['id'] ?>, '<?= htmlspecialchars($row['name']) ?>')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="no-data">No users found</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add/Edit User Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeUserModal()">&times;</span>
            <h2 id="modalTitle">Add New User</h2>
            <form id="userForm" method="POST">
                <input type="hidden" name="action" value="add_user">
                <input type="hidden" name="user_id" id="user_id">
                <div class="user-details">
                    <div class="input-box">
                        <label for="name">Full Name</label>
                        <input type="text" name="name" id="name" required>
                    </div>
                    <div class="input-box">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" required>
                    </div>
                    <div class="input-box">
                        <label for="role">Role</label>
                        <select name="role" id="role" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="coordinator">Coordinator</option>
                        </select>
                    </div>
                    <div class="input-box">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password">
                        <small class="password-hint">Leave blank to keep existing password when editing</small>
                    </div>
                </div>
                <div class="button">
                    <input type="submit" value="Save">
                </div>
            </form>
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        initializeDropdowns();
        initializeFormHandling();
    });

    function editUser(userData) {
        if (!userData) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No user data provided'
            });
            return;
        }
        openUserModal(userData);
    }

    function initializeFormHandling() {
        const userForm = document.getElementById('userForm');
        if (userForm) {
            userForm.addEventListener('submit', async function(event) {
                event.preventDefault();
                
                try {
                    const formData = new FormData(this);
                    
                    const response = await fetch('admin-home.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        throw new Error(data.message || 'Operation failed');
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'An unexpected error occurred'
                    });
                }
            });
        }
    }

    function openUserModal(userData = null) {
        const modal = document.getElementById('userModal');
        const form = document.getElementById('userForm');
        const modalTitle = document.getElementById('modalTitle');
        const passwordHint = document.querySelector('.password-hint');

        // Reset form and clear previous data
        form.reset();
        
        if (userData) {
            modalTitle.textContent = 'Edit User';
            form.elements['action'].value = 'edit_user';
            form.elements['user_id'].value = userData.id;
            form.elements['name'].value = userData.name;
            form.elements['email'].value = userData.email;
            form.elements['role'].value = userData.role;
            form.elements['password'].required = false;
            passwordHint.style.display = 'block';
        } else {
            modalTitle.textContent = 'Add New User';
            form.elements['action'].value = 'add_user';
            form.elements['user_id'].value = '';
            form.elements['password'].required = true;
            passwordHint.style.display = 'none';
        }

        modal.style.display = 'block';
    }

    async function deleteUser(userId, userName) {
        try {
            const result = await Swal.fire({
                title: 'Are you sure?',
                text: `Do you want to delete ${userName}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            });

            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'delete_user');
                formData.append('user_id', userId);

                const response = await fetch('admin-home.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const data = await response.json();

                if (data.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: data.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                    window.location.reload();
                } else {
                    throw new Error(data.message || 'Failed to delete user');
                }
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'An unexpected error occurred'
            });
        }
    }

    function closeUserModal() {
        const modal = document.getElementById('userModal');
        modal.style.display = 'none';
        document.getElementById('userForm').reset();
    }

    // Dropdown functionality
    function initializeDropdowns() {
        const dropdowns = document.querySelectorAll('.dropdown-wrapper');
        let activeDropdown = null;

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (activeDropdown && !activeDropdown.contains(e.target)) {
                const menu = activeDropdown.querySelector('.dropdown-menu');
                if (menu) {
                    menu.classList.remove('show');
                }
                activeDropdown = null;
            }
        });

        // Toggle dropdown
        dropdowns.forEach(dropdown => {
            const toggleBtn = dropdown.querySelector('.dropdown-toggle');
            const menu = dropdown.querySelector('.dropdown-menu');
            
            if (toggleBtn && menu) {
                toggleBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    
                    // Close other dropdowns
                    if (activeDropdown && activeDropdown !== dropdown) {
                        const activeMenu = activeDropdown.querySelector('.dropdown-menu');
                        if (activeMenu) {
                            activeMenu.classList.remove('show');
                        }
                    }
                    
                    menu.classList.toggle('show');
                    activeDropdown = menu.classList.contains('show') ? dropdown : null;
                });
            }
        });
    }

    // Filter functionality
    document.querySelectorAll('.filter-btn').forEach(button => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.filter-btn').forEach(btn => 
                btn.classList.remove('active'));
            button.classList.add('active');
            
            const filter = button.getAttribute('data-filter');
            const rows = document.querySelectorAll('#userTable tbody tr');
            
            rows.forEach(row => {
                row.style.display = 
                    filter === 'all' || row.getAttribute('data-role') === filter 
                    ? '' 
                    : 'none';
            });
        });
    });
    </script>

    <style>
    .event-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .event-table-section {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        padding: 20px;
        margin-top: 20px;
    }

    .filter-container {
        margin-bottom: 20px;
        width: 100%;
    }

    .filter-buttons {
        display: flex;
        gap: 10px;
    }

    .filter-btn {
        padding: 8px 16px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background: #fff;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 14px;
    }

    .filter-btn.active {
        background: #4CAF50;
        color: white;
        border-color: #4CAF50;
    }

    .event-display-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background: white;
        border-radius: 8px;
        overflow: hidden;
    }

    .event-display-table th {
        background: #f8f9fa;
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: #333;
        border-bottom: 2px solid #dee2e6;
        white-space: nowrap;
    }

    .event-display-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #dee2e6;
        color: #555;
        vertical-align: middle;
    }

    .event-display-table tr:last-child td {
        border-bottom: none;
    }

    /* Column specific widths */
    .event-display-table th:nth-child(1),
    .event-display-table td:nth-child(1) {
        width: 80px;
    }

    .event-display-table th:nth-child(2),
    .event-display-table td:nth-child(2) {
        width: 25%;
    }

    .event-display-table th:nth-child(3),
    .event-display-table td:nth-child(3) {
        width: 35%;
    }

    .event-display-table th:nth-child(4),
    .event-display-table td:nth-child(4) {
        width: 15%;
    }

    .event-display-table th:nth-child(5),
    .event-display-table td:nth-child(5) {
        width: 100px;
        text-align: center;
    }

    .dropdown-wrapper {
        position: relative;
        display: inline-block;
        width: 40px;
    }

    .dropdown-toggle {
        background: none;
        border: none;
        padding: 8px;
        cursor: pointer;
        color: #666;
        transition: color 0.3s ease;
    }

    .dropdown-toggle:hover {
        color: #333;
    }

    .dropdown-menu {
        position: absolute;
        right: 0;
        top: 100%;
        background: white;
        min-width: 160px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-radius: 4px;
        padding: 8px 0;
        z-index: 1000;
        display: none;
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
        color: #333;
        font-size: 14px;
        text-align: left;
        transition: background-color 0.2s;
    }

    .dropdown-menu button i {
        margin-right: 8px;
        width: 16px;
    }

    .dropdown-menu button:hover {
        background-color: #f5f5f5;
    }

    .dropdown-menu button.edit-btn:hover {
        color: #4CAF50;
    }

    .dropdown-menu button.delete-btn:hover {
        color: #dc3545;
    }

    .btn-import {
        background: #4CAF50;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: background-color 0.2s;
        white-space: nowrap;
    }

    .btn-import:hover {
        background: #45a049;
    }

    .no-data {
        text-align: center;
        color: #666;
        padding: 20px;
    }

    .search-container {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .search-container input {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        min-width: 250px;
    }

    .search-container button {
        background: #4CAF50;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 38px;
    }

    @media (max-width: 768px) {
        .event-top {
            flex-direction: column;
            align-items: stretch;
        }
        
        .search-container input {
            min-width: unset;
            width: 100%;
        }
        
        .event-display-table {
            display: block;
            overflow-x: auto;
        }
    }
    </style>
    </body>
</html>