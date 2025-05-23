<?php
session_start();
include '../php/conn.php';

// Check email, student_id, and role
if (!isset($_SESSION['email'], $_SESSION['student_id'], $_SESSION['role'])) {
    header("Location: 1_Login.php");
    exit();
}

// Allowed roles
$allowed_roles = ['coordinator', 'admin'];

if (!in_array($_SESSION['role'], $allowed_roles)) {
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
            <a href="4_Event.php"> <i class="fa-solid fa-home"></i> <span class="label"> Home </span> </a>
            <a href="6_NewEvent.php"> <i class="fa-solid fa-calendar"></i> <span class="label"> Events </span> </a>
            <a href="10_Admin.php" class="active"> <i class="fa-regular fa-circle-user"></i> <span class="label"> Admins </span> </a>
            <a href="7_StudentTable.php"> <i class="fa-solid fa-address-card"></i> <span class="label"> Participants </span> </a>
            <a href="5_About.php"> <i class="fa-solid fa-circle-info"></i> <span class="label"> About </span> </a>
            <a href="8_archive.php"> <i class="fa-solid fa-bars"></i> <span class="label"> Logs </span> </a>
            <a href="1_Login.php"> <i class="fa-solid fa-circle-info"></i> <span class="label"> Login </span> </a>
        </div>
        <div class="logout">
            <a href="../php/1logout.php" onclick="return confirm('Are you sure you want to logout?');"> <i class="fa-solid fa-right-from-bracket"></i> <span class="label"> Logout </span> </a>
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
            </div>
        </div>
        
        <div class="event-table-section">
            <h2>Registered Admins</h2>
            <div class="add-button">
                <button class="btn-import" onclick="openAdminModal()"><span>ADD ADMIN <i class="fa-solid fa-plus"></i></span></button>
            </div>
            
            <!-- Add Admin Modal -->
            <div id="adminModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeAdminModal()">&times;</span>
                    <div class="header">
                        <h3>Add New Admin</h3>
                    </div>
                    <form id="adminForm" method="POST" action="../php/register.php">
                        <div class="user-details">
                            <div class="input-box">
                                <label>Full Name</label>
                                <input type="text" name="name" id="admin-name" required>
                            </div>
                            <div class="input-box">
                                <label>Organization</label>
                                <select name="organization" id="admin-org" required>
                                    <option value="">Select Organization</option>
                                    <option value="CCS">College of Computer Studies</option>
                                    <option value="CBA">College of Business and Accountancy</option>
                                    <option value="CON">College of Nursing</option>
                                    <option value="COE">College of Education</option>
                                    <option value="CIHM">College of International Hospitality Management</option>
                                    <option value="COA">College of Arts</option>
                                </select>
                            </div>
                            <div class="input-box">
                                <label>Username</label>
                                <input type="text" name="username" id="admin-username" required>
                            </div>
                            <div class="input-box">
                                <label>Password</label>
                                <input type="password" name="password" id="admin-password" required>
                            </div>
                            <div class="input-box">
                                <label>Confirm Password</label>
                                <input type="password" name="confirm_password" id="admin-confirm-password" required>
                            </div>
                        </div>
                        <div class="controls">
                            <button type="submit" class="btn-submit">Register Admin</button>
                            <button type="button" class="btn-close" onclick="closeAdminModal()">Cancel</button>
                        </div>
                        <div id="adminMessage" class="message"></div>
                    </form>
                </div>
            </div>

            <table class="event-display-table">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Organization</th>
                    <th>Username</th>
                    <th>Actions</th>
                </tr>
                <?php
                $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
                
                if (!empty($search)) {
                    $sql = "SELECT * FROM clients 
                            WHERE name LIKE '%$search%' 
                            OR organization LIKE '%$search%' 
                            OR username LIKE '%$search%'
                            ORDER BY id DESC";
                } else {
                    $sql = "SELECT * FROM clients ORDER BY id DESC";
                }
                
                $result = $conn->query($sql);

                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['organization']) ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td class="dropdown-wrapper">
                        <button class="dropdown-toggle">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu">
                           <form method="POST" action="../php/delete_admin.php" onsubmit="return confirm('Are you sure you want to delete this admin?');">
                                <input type="hidden" name="admin_id" value="<?= $row['id'] ?>">
                                <button type="submit" class="dropdown-item">
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
                <tr><td colspan="5">No admins found.</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

   <script src="../Javascript/adminmodal.js"></script>

</body>
</html>