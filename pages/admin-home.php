<?php
session_start();
include '../php/conn.php';


include '../php/conn.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'add_user' || $_POST['action'] === 'edit_user') {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $role = $_POST['role'];
            $password = isset($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
            
            if ($_POST['action'] === 'add_user') {
                $stmt = $conn->prepare("INSERT INTO users (name, email, role, password) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $name, $email, $role, $password);
            } else {
                $user_id = $_POST['user_id'];
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
            header("Location: admin-home.php?success=1");
            exit();
        } elseif ($_POST['action'] === 'delete_user') {
            $user_id = $_POST['user_id'];
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            
            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }
            header("Location: admin-home.php?deleted=1");
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: admin-home.php?error=1");
        exit();
    }
}

// Check email, student_id, and role
if (!isset($_SESSION['email'], $_SESSION['student_id'], $_SESSION['role'])) {
    header("Location: ../pages/Login_v1.php");
    exit();
}

// Allowed roles
$allowed_roles = ['student', 'coordinator', 'admin'];

if (!in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: ../pages/Login_v1.php");
    exit();
}

// Get any error messages
$error_msg = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['error']); // Clear the error message after getting it
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
    </head>
    <body>
        <div class="title-container">
            <img src="../images-icon/plplogo.png"> <h1> Pamantasan ng Lungsod ng Pasig </h1>
    </div>
            <div class="tab-container">
                <div class="menu-items">
                    <a href="admin-home.php" class="active"> <i class="fa-solid fa-users-gear"></i> <span class="label">User Manage</span> </a>
                    <a href="5_About.php"> <i class="fa-solid fa-circle-info"></i> <span class="label"> About </span> </a>
                </div>
                <div class="logout">
            <a href="../php/1logout.php"> <i class="fa-solid fa-gear"></i> <span class="label"> Logout </span> </a>

        </div>
    </div>

    <div class="event-main">
        <?php if (!empty($error_msg)): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($error_msg) ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            User saved successfully!
        </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">
            User deleted successfully!
        </div>
        <?php endif; ?>

        <div class="event-details">
            <div class="event-top">
                <p>User Management</p>
                <div class="search-container">
                    <form class="example" action="admin-home.php" method="GET">
                        <label for="search"></label>
                        <input type="text" id="search" name="search" placeholder="Search users...">
                        <button type="submit"><i class="fa fa-search"></i></button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="event-table-section">
            <h2>System Users</h2>
            <div class="add-button">
                <button class="btn-import" onclick="openUserModal()">
                    <span><i class="fa-solid fa-plus"></i> Add New User</span>
                </button>
            </div>

            <table class="event-display-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
                
                if (!empty($search)) {
                    $sql = "SELECT * FROM users WHERE role IN ('admin', 'coordinator') AND 
                           (name LIKE '%$search%' OR email LIKE '%$search%' OR role LIKE '%$search%')
                           ORDER BY id DESC";
                } else {
                    $sql = "SELECT * FROM users WHERE role IN ('admin', 'coordinator') ORDER BY id DESC";
                }
                
                $result = $conn->query($sql);

                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['role']) ?></td>
                    <td class="dropdown-wrapper">
                        <button class="dropdown-toggle">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu">
                            <button onclick="openUserModal(<?= htmlspecialchars(json_encode($row)) ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                <button type="submit">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php
                    endwhile;
                else:
                ?>
                <tr><td colspan="5">No users found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add/Edit User Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <div class="header">
                <h3 id="modalTitle">Add New User</h3>
                <p>Fill out the information below</p>
            </div>
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
                <div class="controls">
                    <button type="submit" class="btn-submit">Save</button>
                    <button type="button" class="btn-close" onclick="closeUserModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openUserModal(userData = null) {
        const modal = document.getElementById('userModal');
        const form = document.getElementById('userForm');
        const modalTitle = document.getElementById('modalTitle');
        const passwordHint = document.querySelector('.password-hint');

        if (userData) {
            modalTitle.textContent = 'Edit User';
            form.action.value = 'edit_user';
            form.user_id.value = userData.id;
            form.name.value = userData.name;
            form.email.value = userData.email;
            form.role.value = userData.role;
            form.password.required = false;
            passwordHint.style.display = 'block';
        } else {
            modalTitle.textContent = 'Add New User';
            form.reset();
            form.action.value = 'add_user';
            form.user_id.value = '';
            form.password.required = true;
            passwordHint.style.display = 'none';
        }

        modal.style.display = 'block';
    }

    function closeUserModal() {
        document.getElementById('userModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('userModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }

    // Handle dropdown menus
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

    <style>
    .password-hint {
        color: #666;
        font-size: 0.8em;
        margin-top: 5px;
        display: none;
    }
    
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
    
    .dropdown-menu button {
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
    
    .alert {
        padding: 15px;
        margin: 15px;
        border-radius: 4px;
    }
    
    .alert-error {
        background-color: #fee;
        color: #c00;
        border: 1px solid #fcc;
    }
    
    .alert-success {
        background-color: #efe;
        color: #0c0;
        border: 1px solid #cfc;
    }
    </style>
    </body>
</html>