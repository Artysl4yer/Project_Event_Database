<?php
session_start();
if (!isset($_SESSION['email']) && !isset($_SESSION['client_id'])) {
    header('Location: 1_Login.php');
    exit();
}
include '../php/conn.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/1_Login.php");
    exit();
}

if (!isset($_SESSION['client_id'])) {
    header("Location: 1_Login.php");
    exit();
}
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
    <link rel="stylesheet" href="../styles/style10.css">
    <link rel="stylesheet" href="../styles/style12.css">
    <script src="https://kit.fontawesome.com/d78dc5f742.js" crossorigin="anonymous"></script>
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
                <p>Admin Management</p>
                <div class="search-container">
                    <form class="example" action="10_Admin.php" method="GET">
                        <label for="search"></label>
                        <input type="text" id="search" name="search" placeholder="Search...">
                        <button type="submit"><i class="fa fa-search"></i></button>
                    </form>
                </div>
                <button class="btn-import" id="addAdminBtn" type="button">
                    <span><i class="fa-solid fa-plus"></i> Add Admin</span>
                </button>
            </div>
        </div>
        
        <div class="event-table-section">
            <h2>Registered Admins</h2>
            <table class="event-display-table" id="adminTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
                if (!empty($search)) {
                    $sql = "SELECT * FROM users WHERE role = 'admin' AND (name LIKE '%$search%' OR email LIKE '%$search%' OR username LIKE '%$search%') ORDER BY id DESC";
                } else {
                    $sql = "SELECT * FROM users WHERE role = 'admin' ORDER BY id DESC";
                }
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td class="dropdown-wrapper">
                        <button class="dropdown-toggle" type="button">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu">
                            <button class="dropdown-edit-btn" data-admin-id="<?= $row['id'] ?>">
                                <i class="fas fa-edit"></i> Edit Admin
                            </button>
                            <button class="delete-btn" data-admin-id="<?= $row['id'] ?>">
                                <i class="fas fa-trash-alt"></i> Delete
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="5">No admins found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Admin Modal -->
    <div class="modal" id="addAdminModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Admin</h2>
                <span class="close-modal" onclick="closeAddAdminModal()">&times;</span>
            </div>
            <form id="addAdminForm" action="../php/register.php" method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="admin-name">Full Name</label>
                        <input type="text" id="admin-name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="admin-email">Email</label>
                        <input type="email" id="admin-email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="admin-username">Username</label>
                        <input type="text" id="admin-username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="admin-password">Password</label>
                        <input type="password" id="admin-password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="admin-confirm-password">Confirm Password</label>
                        <input type="password" id="admin-confirm-password" name="confirm_password" required>
                    </div>
                    <input type="hidden" name="role" value="admin">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Register Admin</button>
                    <button type="button" class="btn-cancel" onclick="closeAddAdminModal()">Cancel</button>
                </div>
                <div id="adminMessage" class="message"></div>
            </form>
        </div>
    </div>

    <script src="../Javascript/event-dropdown.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Modal logic for Add/Edit Admin
        function openAddAdminModal() {
            const form = document.getElementById('addAdminForm');
            form.reset();
            form.action = '../php/register.php';
            form.querySelector('input[name="role"]').value = 'admin';
            form.querySelector('.btn-submit').textContent = 'Register Admin';
            document.getElementById('addAdminModal').style.display = 'block';
            document.getElementById('addAdminModal').querySelector('.modal-header h2').textContent = 'Add New Admin';
            document.getElementById('adminMessage').textContent = '';
            // Remove hidden admin_id if present
            let idField = form.querySelector('input[name="admin_id"]');
            if (idField) idField.remove();
        }
        function openEditAdminModal(admin) {
            const form = document.getElementById('addAdminForm');
            form.reset();
            form.action = '../php/update_admin.php';
            form.querySelector('input[name="role"]').value = 'admin';
            form.querySelector('.btn-submit').textContent = 'Update Admin';
            document.getElementById('addAdminModal').style.display = 'block';
            document.getElementById('addAdminModal').querySelector('.modal-header h2').textContent = 'Edit Admin';
            document.getElementById('adminMessage').textContent = '';
            // Fill fields
            form.querySelector('#admin-name').value = admin.name;
            form.querySelector('#admin-email').value = admin.email;
            if (form.querySelector('#admin-username')) {
                form.querySelector('#admin-username').value = admin.username || '';
            }
            // Add hidden id field
            let idField = form.querySelector('input[name="admin_id"]');
            if (!idField) {
                idField = document.createElement('input');
                idField.type = 'hidden';
                idField.name = 'admin_id';
                form.appendChild(idField);
            }
            idField.value = admin.id;
            // Password fields blank
            form.querySelector('#admin-password').value = '';
            form.querySelector('#admin-confirm-password').value = '';
        }
        function closeAddAdminModal() {
            const form = document.getElementById('addAdminForm');
            form.reset();
            document.getElementById('addAdminModal').style.display = 'none';
            document.getElementById('adminMessage').textContent = '';
        }
        document.getElementById('addAdminBtn').addEventListener('click', openAddAdminModal);
        // Edit button logic
        document.querySelectorAll('.dropdown-edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const tr = this.closest('tr');
                const admin = {
                    id: tr.querySelector('td:nth-child(1)').textContent.trim(),
                    name: tr.querySelector('td:nth-child(2)').textContent.trim(),
                    email: tr.querySelector('td:nth-child(3)').textContent.trim(),
                    username: tr.querySelector('td:nth-child(4)') ? tr.querySelector('td:nth-child(4)').textContent.trim() : ''
                };
                openEditAdminModal(admin);
            });
        });
        // Delete button logic
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const adminId = this.dataset.adminId;
                if (confirm('Are you sure you want to delete this admin?')) {
                    fetch('../php/delete_admin.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'admin_id=' + adminId
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Admin deleted successfully');
                            window.location.reload();
                        } else {
                            alert('Error deleting admin: ' + (data.message || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while deleting the admin');
                    });
                }
            });
        });
        // AJAX form submission for add/edit admin
        document.getElementById('addAdminForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const formData = new FormData(form);
            const action = form.action;
            const adminMessage = document.getElementById('adminMessage');
            adminMessage.textContent = '';
            fetch(action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    adminMessage.textContent = data.message;
                    adminMessage.className = 'message success';
                    setTimeout(() => {
                        closeAddAdminModal();
                        window.location.reload();
                    }, 1200);
                } else {
                    adminMessage.textContent = data.message;
                    adminMessage.className = 'message error';
                }
            })
            .catch(error => {
                adminMessage.textContent = 'An error occurred.';
                adminMessage.className = 'message error';
            });
        });
        // Cancel button logic
        document.querySelectorAll('.btn-cancel, .close-modal').forEach(btn => {
            btn.addEventListener('click', function() {
                closeAddAdminModal();
            });
        });
    });
    </script>
</body>
</html>