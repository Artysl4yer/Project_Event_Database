<?php
session_start();
include '../php/conn.php';

if (!isset($_SESSION['email']) && !isset($_SESSION['client_id'])) {
    header('Location: 1_Login.php');
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/1_Login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PLP: Guests</title>
    <link rel="stylesheet" href="../styles/style2.css">
    <link rel="stylesheet" href="../styles/style3.css">
    <link rel="stylesheet" href="../styles/style1.css">
    <link rel="stylesheet" href="../styles/style5.css">
    <link rel="stylesheet" href="../styles/style6.css">
    <link rel="stylesheet" href="../styles/style8.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/search.css">
    <style>
        .event-main {
            margin: 10% auto;
        }

        .event-table-section {
            display: flex;
            flex-direction: column;
            margin: auto;
            max-width: 85%;
            padding: 20px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
        }

        .event-table-section h2 {
            margin-bottom: 15px;
            font-size: 2em;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .event-display-table {
            max-width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .event-display-table th,
        .event-display-table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
            max-width: 100%;
        }

        .event-display-table th {
            background-color: #f8f8f8;
            color: #333;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.75em;
            letter-spacing: 0.5px;
            max-width: 100%;
        }

        .event-display-table tr:hover {
            background-color: #f5f5f5;
        }

        .event-display-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .event-display-table tr:nth-child(even):hover {
            background-color: #f5f5f5;
        }

        /* Filter container styles */
        .filter-container {
            margin-bottom: 20px;
            width: 100%;
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin: 0;
            padding: 0;
        }

        .filter-btn {
            padding: 8px 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #fff;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .filter-btn:hover {
            background: #f0f0f0;
        }

        .filter-btn.active {
            background: #17692d;
            color: white;
            border-color: #17692d;
        }

        .status-select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-left: 10px;
        }

        /* Search container styles */
        .search-container {
            margin-bottom: 20px;
            width: 100%;
        }

        .search-container form {
            display: flex;
            gap: 10px;
            max-width: 400px;
        }

        .search-container input {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .search-container button {
            padding: 8px 16px;
            background: #17692d;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .search-container button:hover {
            background: #145c26;
        }

        /* Responsive styles */
        @media (max-width: 1200px) {
            .event-table-section {
                max-width: 95%;
            }
        }

        @media (max-width: 768px) {
            .event-table-section {
                padding: 10px;
            }

            .event-display-table th,
            .event-display-table td {
                padding: 15px;
            }

            .filter-buttons {
                flex-direction: column;
            }

            .filter-btn,
            .status-select {
                width: 100%;
                margin: 5px 0;
            }

            .search-container form {
                flex-direction: column;
                max-width: 100%;
            }

            .search-container button {
                width: 100%;
            }
        }

        /* Add these styles to the existing style section */
        .event-display-table th[data-sort] {
            cursor: pointer;
            position: relative;
        }

        .event-display-table th[data-sort]::after {
            content: '↕';
            position: absolute;
            right: 8px;
            color: #999;
        }

        .event-display-table th[data-sort].sorted-asc::after {
            content: '↑';
            color: #17692d;
        }

        .event-display-table th[data-sort].sorted-desc::after {
            content: '↓';
            color: #17692d;
        }

        .no-data {
            text-align: center;
            padding: 20px !important;
            color: #666;
            font-style: italic;
        }

        /* Style the dropdown buttons */
        .dropdown-menu .edit-btn {
            color: #3284ed;
        }

        .dropdown-menu .edit-btn:hover {
            background-color: rgba(50, 132, 237, 0.1);
        }

        .dropdown-menu .delete-btn {
            color: #e74c3c;
        }

        .dropdown-menu .delete-btn:hover {
            background-color: rgba(231, 76, 60, 0.1);
        }

        /* Fix table column widths */
        .event-display-table th:nth-child(1), /* Number */
        .event-display-table td:nth-child(1) {
            width: 8%;
        }

        .event-display-table th:nth-child(2), /* Name */
        .event-display-table td:nth-child(2) {
            width: 20%;
        }

        .event-display-table th:nth-child(3), /* Email */
        .event-display-table td:nth-child(3) {
            width: 25%;
        }

        .event-display-table th:nth-child(4), /* Event ID */
        .event-display-table td:nth-child(4) {
            width: 15%;
        }

        .event-display-table th:nth-child(5), /* Gender */
        .event-display-table td:nth-child(5) {
            width: 10%;
        }

        .event-display-table th:nth-child(6), /* Age */
        .event-display-table td:nth-child(6) {
            width: 8%;
        }

        .event-display-table th:nth-child(7), /* Actions */
        .event-display-table td:nth-child(7) {
            width: 7%;
            text-align: center;
        }

        /* Style for select input */
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white;
        }

        /* Style for number input */
        input[type="number"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        /* Add these styles to the existing style section */
        .dropdown-wrapper {
            position: relative;
        }

        .dropdown-toggle {
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
        }

        .dropdown-toggle:hover {
            background-color: #f5f5f5;
            border-radius: 4px;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            background-color: #fff;
            min-width: 120px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            border-radius: 4px;
            z-index: 1000;
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-menu button {
            display: block;
            width: 100%;
            padding: 8px 16px;
            text-align: left;
            border: none;
            background: none;
            cursor: pointer;
            white-space: nowrap;
        }

        .dropdown-menu button:hover {
            background-color: #f5f5f5;
        }

        .dropdown-menu i {
            margin-right: 8px;
            width: 16px;
        }

        /* Update the actions column style */
        .event-display-table td:last-child {
            padding: 8px !important;
            text-align: center;
        }

        /* Ensure the dropdown toggle is visible */
        .dropdown-toggle i {
            color: #666;
            font-size: 16px;
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
                <h2>Guests List</h2>
                <div class="search-container">
                    <form class="search-form" action="" method="GET">
                        <input type="text" id="search" name="search" placeholder="Search guests..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                        <button type="submit"><i class="fa fa-search"></i></button>
                    </form>
                </div>
            </div>  
        </div>
        <div class="event-table-section">
            <div class="table-header">
                <div class="filter-container">
                    <div class="filter-buttons">
                        <button class="filter-btn active" data-filter="all">All Guests</button>
                        <button class="filter-btn" data-filter="name">Sort by Name</button>
                        <button class="filter-btn" data-filter="organization">Sort by Organization</button>
                        <button class="btn-import" onclick="openGuestModal()">
                            <i class="fa-solid fa-plus"></i> Add Guest
                        </button>
                    </div>
                </div>
            </div>
            <table class="event-display-table" id="guestTable">
                <thead>
                    <tr>
                        <th data-sort="number">NUMBER</th>
                        <th data-sort="name">NAME</th>
                        <th data-sort="email">EMAIL</th>
                        <th data-sort="event_id">EVENT ID</th>
                        <th data-sort="gender">GENDER</th>
                        <th data-sort="age">AGE</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
                $allowedSort = [
                    'name' => "first_name",
                    'email' => "email",
                    'organization' => "organization",
                    'contact' => "contact",
                    'number' => "number"
                ];
                $sort = $_GET['sort'] ?? 'number';
                $direction = (isset($_GET['direction']) && strtolower($_GET['direction']) === 'asc') ? 'ASC' : 'DESC';
                $orderBy = $allowedSort[$sort] ?? "number";

                if (!empty($search)) {
                    $sql = "SELECT * FROM participants_table WHERE 
                        first_name LIKE '%$search%' OR 
                        last_name LIKE '%$search%' OR 
                        email LIKE '%$search%' OR 
                        event_id LIKE '%$search%'
                        ORDER BY $orderBy $direction";
                } else {
                    $sql = "SELECT * FROM participants_table ORDER BY $orderBy $direction";
                }
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                        // Format the name and other fields properly
                        $fullName = ucwords(strtolower(trim($row['first_name'] . ' ' . $row['last_name'])));
                        $email = strtolower(trim($row['email']));
                        $eventId = $row['event_id'];
                        $gender = $row['Gender'];
                        $age = $row['Age'];
                ?>
                <tr>
                    <td><?= str_pad($row['number'], 3, '0', STR_PAD_LEFT) ?></td>
                    <td><?= htmlspecialchars($fullName) ?></td>
                    <td><?= htmlspecialchars($email) ?></td>
                    <td><?= htmlspecialchars($eventId) ?></td>
                    <td><?= htmlspecialchars($gender) ?></td>
                    <td><?= htmlspecialchars($age) ?></td>
                    <td class="dropdown-wrapper">
                        <button class="dropdown-toggle">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu">
                            <button class="edit-btn" onclick="openGuestModal(<?= $row['number'] ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="delete-btn" onclick="deleteGuest(<?= $row['number'] ?>)">
                                <i class="fas fa-trash-alt"></i> Delete
                            </button>
                        </div>
                    </td>
                </tr>
                <?php
                    endwhile;
                else:
                ?>
                <tr><td colspan="7" class="no-data">No participants found</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add/Edit Guest Modal -->
    <div id="guestModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeGuestModal()">&times;</span>
            <h2 id="modalTitle">Add New Guest</h2>
            <form id="guestForm" method="POST" action="guest-table.php">
                <input type="hidden" name="action" value="save_guest">
                <input type="hidden" name="guest-number" id="guest-number">
                <div class="user-details">
                    <div class="input-box">
                        <label for="guest-firstname">First Name</label>
                        <input type="text" name="guest-firstname" id="guest-firstname" required>
                    </div>
                    <div class="input-box">
                        <label for="guest-lastname">Last Name</label>
                        <input type="text" name="guest-lastname" id="guest-lastname" required>
                    </div>
                    <div class="input-box">
                        <label for="guest-email">Email</label>
                        <input type="email" name="guest-email" id="guest-email" required>
                    </div>
                    <div class="input-box">
                        <label for="guest-event-id">Event ID</label>
                        <input type="text" name="guest-event-id" id="guest-event-id" required>
                    </div>
                    <div class="input-box">
                        <label for="guest-gender">Gender</label>
                        <select name="guest-gender" id="guest-gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="input-box">
                        <label for="guest-age">Age</label>
                        <input type="number" name="guest-age" id="guest-age" min="1" max="120" required>
                    </div>
                </div>
                <div class="button">
                    <input type="submit" value="Save">
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../Javascript/event-dropdown.js"></script>
    <script src="../Javascript/filter.js"></script>
    <script src="../Javascript/table-search.js"></script>
    <script src="../Javascript/guest-dropdown.js"></script>
    <script src="../Javascript/guest-modal.js"></script>
    <script src="../Javascript/search.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize table sorting and filtering
            const table = document.getElementById('guestTable');
            if (!table) return;

            // Initialize filter buttons
            const filterButtons = document.querySelectorAll('.filter-btn');
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    const filter = this.dataset.filter;
                    applyFilter(filter);
                });
            });

            // Initialize sorting
            table.querySelectorAll('th[data-sort]').forEach(header => {
                header.addEventListener('click', function() {
                    const column = this.dataset.sort;
                    const currentOrder = this.dataset.order || 'asc';
                    const newOrder = currentOrder === 'asc' ? 'desc' : 'asc';
                    
                    // Update order indicators
                    table.querySelectorAll('th[data-sort]').forEach(h => {
                        if (h !== this) {
                            h.dataset.order = '';
                            h.classList.remove('sorted-asc', 'sorted-desc');
                        }
                    });
                    this.dataset.order = newOrder;
                    this.classList.remove('sorted-asc', 'sorted-desc');
                    this.classList.add(`sorted-${newOrder}`);
                    
                    sortTable(column, newOrder);
                });
            });

            // Handle form submission
            const guestForm = document.getElementById('guestForm');
            if (guestForm) {
                guestForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Validate form before submission
                    if (!validateGuestForm()) {
                        return;
                    }

                    const formData = new FormData(this);
                    
                    fetch('../php/save_guest.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            closeGuestModal();
                            window.location.reload();
                        } else {
                            alert(data.message || 'Error saving participant');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while saving the participant. Please try again.');
                    });
                });
            }
        });

        function applyFilter(filter) {
            const rows = document.querySelectorAll('#guestTable tbody tr');
            
            rows.forEach(row => {
                switch(filter) {
                    case 'all':
                        row.style.display = '';
                        break;
                    case 'name':
                        sortTable('name', 'asc');
                        break;
                    case 'organization':
                        sortTable('organization', 'asc');
                        break;
                    default:
                        row.style.display = '';
                }
            });
        }

        function sortTable(column, order) {
            const table = document.getElementById('guestTable');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            rows.sort((a, b) => {
                let aVal = a.querySelector(`td:nth-child(${getColumnIndex(column)})`).textContent.trim();
                let bVal = b.querySelector(`td:nth-child(${getColumnIndex(column)})`).textContent.trim();

                if (column === 'number') {
                    aVal = parseInt(aVal);
                    bVal = parseInt(bVal);
                }

                if (order === 'asc') {
                    return aVal > bVal ? 1 : -1;
                } else {
                    return aVal < bVal ? 1 : -1;
                }
            });

            rows.forEach(row => tbody.appendChild(row));
        }

        function getColumnIndex(column) {
            const indices = {
                'number': 1,
                'name': 2,
                'email': 3,
                'organization': 4,
                'contact': 5
            };
            return indices[column] || 1;
        }

        function openGuestModal(guestNumber = null) {
            const modal = document.getElementById('guestModal');
            const form = document.getElementById('guestForm');
            const title = document.getElementById('modalTitle');
            
            if (!modal || !form || !title) {
                console.error('Required modal elements not found');
                return;
            }
            
            if (guestNumber) {
                title.textContent = 'Edit Participant';
                const numberInput = document.getElementById('guest-number');
                if (numberInput) {
                    numberInput.value = guestNumber;
                }
                
                // Fetch participant data
                fetch(`../php/get_guest.php?id=${guestNumber}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const guest = data.guest;
                            const fields = {
                                'guest-firstname': guest.first_name,
                                'guest-lastname': guest.last_name,
                                'guest-email': guest.email,
                                'guest-event-id': guest.event_id,
                                'guest-gender': guest.gender,
                                'guest-age': guest.age
                            };

                            // Safely set form field values
                            Object.keys(fields).forEach(fieldId => {
                                const field = document.getElementById(fieldId);
                                if (field) {
                                    field.value = fields[fieldId];
                                } else {
                                    console.warn(`Field ${fieldId} not found in form`);
                                }
                            });
                        } else {
                            alert(data.message || 'Error fetching participant data');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error fetching participant data. Please try again.');
                    });
            } else {
                title.textContent = 'Add New Participant';
                form.reset();
                const numberInput = document.getElementById('guest-number');
                if (numberInput) {
                    numberInput.value = '';
                }
            }
            
            modal.style.display = 'block';
        }

        function closeGuestModal() {
            const modal = document.getElementById('guestModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        // Function to validate form
        function validateGuestForm() {
            const fields = [
                { id: 'guest-firstname', label: 'First Name' },
                { id: 'guest-lastname', label: 'Last Name' },
                { id: 'guest-email', label: 'Email' },
                { id: 'guest-event-id', label: 'Event ID' },
                { id: 'guest-gender', label: 'Gender' },
                { id: 'guest-age', label: 'Age' }
            ];

            for (const field of fields) {
                const element = document.getElementById(field.id);
                if (!element) {
                    console.error(`${field.label} field not found`);
                    return false;
                }

                const value = element.value.trim();
                if (!value) {
                    alert(`Please enter ${field.label}`);
                    element.focus();
                    return false;
                }

                // Email validation
                if (field.id === 'guest-email') {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(value)) {
                        alert('Please enter a valid email address');
                        element.focus();
                        return false;
                    }
                }

                // Age validation
                if (field.id === 'guest-age') {
                    const age = parseInt(value);
                    if (isNaN(age) || age < 1 || age > 120) {
                        alert('Please enter a valid age between 1 and 120');
                        element.focus();
                        return false;
                    }
                }
            }

            return true;
        }

        // Add this function to handle automatic dash insertion
        function formatStudentId(input) {
            // Remove any non-digit characters
            let value = input.value.replace(/\D/g, '');
            
            // Add dash after second digit if there are more than 2 digits
            if (value.length > 2) {
                value = value.substring(0, 2) + '-' + value.substring(2);
            }
            
            // Limit to 6 digits total (2 digits + dash + 4 digits)
            if (value.length > 7) {
                value = value.substring(0, 7);
            }
            
            // Update input value
            input.value = value;
        }
    </script>
</body>
</html> 