<?php
session_start();

$role = $_SESSION['role'] ?? null;
$page = basename($_SERVER['PHP_SELF']);
$coordinator_allowed = [
    '4_Event.php',
    '5_About.php',
    '6_NewEvent.php',
    '8_archive.php',
    '11_Attendance.php'
];
if (!$role) {
    header("Location: 1_Login.php");
    exit();
}
if ($role === 'admin') {
    // allow
} elseif ($role === 'coordinator') {
    if (!in_array($page, $coordinator_allowed)) {
        header("Location: 4_Event.php");
        exit();
    }
} else {
    header("Location: 1_Login.php");
    exit();
}

include '../php/conn.php';

$sql = "SELECT * FROM archive_table ORDER BY number DESC";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>PLP: Archived Events</title>
        <link rel="stylesheet" href="../styles/style2.css">
        <link rel="stylesheet" href="../styles/style3.css">
        <link rel="stylesheet" href="../styles/style1.css">
        <link rel="stylesheet" href="../styles/style5.css">
        <link rel="stylesheet" href="../styles/style6.css">
        <link rel="stylesheet" href="../styles/style8.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <!-- Add SweetAlert2 CSS and JS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <style>
            /* Add these styles for better UI */
            .event-detail-item {
                margin-bottom: 15px;
                padding: 10px;
                border-bottom: 1px solid #eee;
            }

            .event-detail-item:last-child {
                border-bottom: none;
            }

            .event-detail-item strong {
                display: inline-block;
                width: 120px;
                color: #333;
            }

            .modal-content {
                max-width: 600px;
                width: 90%;
                max-height: 80vh;
                overflow-y: auto;
            }

            .header {
                position: sticky;
                top: 0;
                background: white;
                padding: 15px;
                border-bottom: 1px solid #eee;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .close-modal {
                font-size: 24px;
                cursor: pointer;
                color: #666;
            }

            .close-modal:hover {
                color: #333;
            }

            .event-details-content {
                padding: 20px;
            }

            /* Status badge styles */
            .status-badge {
                display: inline-block;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 12px;
                font-weight: 500;
                text-transform: uppercase;
            }

            .status-active {
                background-color: #e3f2fd;
                color: #1976d2;
            }

            .status-archived {
                background-color: #fff3e0;
                color: #f57c00;
            }

            .status-completed {
                background-color: #e8f5e9;
                color: #388e3c;
            }

            /* Improved table styles */
            .event-display-table td[data-label="Status"] {
                text-align: center;
            }

            .event-display-table td[data-label="Description"] {
                max-width: 200px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            /* Search bar styles */
            .search-container {
                margin-bottom: 20px;
            }

            .search-container input[type="text"] {
                width: 300px;
                padding: 8px 12px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 14px;
            }

            .search-container input[type="text"]:focus {
                outline: none;
                border-color: #17692d;
                box-shadow: 0 0 0 2px rgba(23, 105, 45, 0.1);
            }

            /* Dropdown menu styles */
            .dropdown-wrapper {
                position: relative;
            }

            .dropdown-toggle {
                background: none;
                border: none;
                padding: 8px;
                cursor: pointer;
                color: #666;
                transition: color 0.2s;
            }

            .dropdown-toggle:hover {
                color: #333;
            }

            .dropdown-menu {
                display: none;
                position: absolute;
                right: 0;
                top: 100%;
                background: white;
                min-width: 160px;
                box-shadow: 0 3px 6px rgba(0,0,0,0.16);
                border-radius: 4px;
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
                text-align: left;
                background: none;
                border: none;
                cursor: pointer;
                font-size: 14px;
                color: #333;
                transition: background-color 0.2s;
            }

            .dropdown-menu button i {
                margin-right: 8px;
                width: 16px;
            }

            .dropdown-menu button:hover {
                background-color: #f5f5f5;
            }

            .dropdown-menu .view-btn:hover {
                color: #17692d;
            }

            .dropdown-menu .unarchive-btn:hover {
                color: #1976d2;
            }

            .dropdown-menu .delete-btn:hover {
                color: #d32f2f;
            }

            /* Fix table cell positioning */
            .event-display-table td.dropdown-wrapper {
                position: relative;
                padding: 8px;
                text-align: center;
            }

            /* Ensure dropdown is above other elements */
            .dropdown-menu {
                z-index: 1000;
            }
        </style>
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
                    <h2>Archived Events</h2>
                    <div class="search-container">
                        <form class="search-form" action="" method="GET">
                            <input type="text" id="search" name="search" placeholder="Search archives..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                            <button type="submit"><i class="fa fa-search"></i></button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="event-table-section">
                <div class="table-header">
                    <div class="filter-container">
                        <div class="filter-buttons">
                            <button class="filter-btn active" data-filter="all">All Events</button>
                            <button class="filter-btn" data-filter="title">Sort by Title</button>
                            <button class="filter-btn" data-filter="date">Sort by Date</button>
                            <button class="filter-btn" data-filter="status">Filter by Status</button>
                        </div>
                    </div>
                </div>
                <table class="event-display-table" id="archiveTable">
                    <thead>
                        <tr>
                            <th data-sort="number">NUMBER</th>
                            <th data-sort="title">TITLE</th>
                            <th data-sort="code">EVENT CODE</th>
                            <th data-sort="start">START</th>
                            <th data-sort="end">END</th>
                            <th data-sort="location">LOCATION</th>
                            <th data-sort="description">DESCRIPTION</th>
                            <th data-sort="organization">ORGANIZATION</th>
                            <th data-sort="status">STATUS</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td data-label='Number'>" . htmlspecialchars($row['number']) . "</td>";
                                echo "<td data-label='Title'>" . htmlspecialchars($row['event_title']) . "</td>";
                                echo "<td data-label='Event Code'>" . htmlspecialchars($row['event_code']) . "</td>";
                                echo "<td data-label='Start'>" . date('M d, Y h:i A', strtotime($row['event_start'])) . "</td>";
                                echo "<td data-label='End'>" . date('M d, Y h:i A', strtotime($row['event_end'])) . "</td>";
                                echo "<td data-label='Location'>" . htmlspecialchars($row['event_location']) . "</td>";
                                echo "<td data-label='Description' title='" . htmlspecialchars($row['event_description']) . "'>" . htmlspecialchars($row['event_description']) . "</td>";
                                echo "<td data-label='Organization'>" . htmlspecialchars($row['organization']) . "</td>";
                                echo "<td data-label='Status'>" . htmlspecialchars($row['event_status']) . "</td>";
                                echo "<td class='dropdown-wrapper'>";
                                echo "<button class='dropdown-toggle'>";
                                echo "<i class='fas fa-ellipsis-v'></i>";
                                echo "</button>";
                                echo "<div class='dropdown-menu'>";
                                echo "<button class='view-btn' onclick='viewEventDetails(" . $row['number'] . ")'>";
                                echo "<i class='fas fa-eye'></i> View Details";
                                echo "</button>";
                                echo "<button class='unarchive-btn' onclick='unarchiveEvent(" . $row['number'] . ")'>";
                                echo "<i class='fas fa-box-open'></i> Unarchive";
                                echo "</button>";
                                echo "<button class='delete-btn' onclick='deleteArchive(" . $row['number'] . ")'>";
                                echo "<i class='fas fa-trash'></i> Delete";
                                echo "</button>";
                                echo "</div>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='10'>No archived events found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- View Event Modal -->
        <div id="viewEventModal" class="modal">
            <div class="modal-content">
                <div class="header">
                    <h3>Event Details</h3>
                    <span class="close-modal" onclick="closeViewModal()">&times;</span>
                </div>
                <div id="eventDetails" class="event-details-content">
                    <!-- Event details will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Scripts -->
        <script src="../Javascript/archive.js"></script>
        <script>
            // Add search functionality
            document.getElementById('searchInput').addEventListener('input', function(e) {
                const searchText = e.target.value.toLowerCase();
                const rows = document.querySelectorAll('#archiveTable tbody tr');
                
                rows.forEach(row => {
                    const title = row.querySelector('td[data-label="Title"]').textContent.toLowerCase();
                    const code = row.querySelector('td[data-label="Event Code"]').textContent.toLowerCase();
                    const location = row.querySelector('td[data-label="Location"]').textContent.toLowerCase();
                    
                    if (title.includes(searchText) || code.includes(searchText) || location.includes(searchText)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });

            // Initialize status badges
            document.addEventListener('DOMContentLoaded', function() {
                const statusCells = document.querySelectorAll('td[data-label="Status"]');
                statusCells.forEach(cell => {
                    const status = cell.textContent.toLowerCase();
                    cell.innerHTML = `<span class="status-badge status-${status}">${status}</span>`;
                });
            });
        </script>

        <!-- Add search.css and search.js -->
        <link rel="stylesheet" href="../styles/search.css">
        <script src="../Javascript/search.js"></script>
    </body>
</html>
