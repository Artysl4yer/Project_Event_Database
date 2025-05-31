<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PLP: Registered Participants</title>
    <link rel="stylesheet" href="../styles/style2.css">
    <link rel="stylesheet" href="../styles/style3.css">
    <link rel="stylesheet" href="../styles/style1.css">
    <link rel="stylesheet" href="../styles/style5.css">
    <link rel="stylesheet" href="../styles/style6.css">
    <link rel="stylesheet" href="../styles/style8.css">
    <link rel="stylesheet" href="../styles/filter.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .event-main {
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .event-details {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .event-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .event-top p {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin: 0;
        }

        .search-box {
            padding: 12px 20px;
            width: 300px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .search-box:focus {
            border-color: #4CAF50;
            outline: none;
        }

        .event-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            padding: 10px;
        }
        
        .event-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .event-title {
            font-size: 1.4em;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
            line-height: 1.3;
        }
        
        .event-details {
            color: #666;
            margin-bottom: 20px;
            flex-grow: 1;
        }

        .event-details div {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .event-details i {
            color: #4CAF50;
            width: 20px;
        }
        
        .participant-count {
            background: #f8f9fa;
            padding: 8px 15px;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 20px;
            color: #4CAF50;
            font-weight: 500;
        }
        
        .view-participants {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        
        .view-participants:hover {
            background: #45a049;
        }
        
        .participants-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        
        .modal-content {
            background: white;
            width: 90%;
            max-width: 1000px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 15px;
            max-height: 85vh;
            overflow-y: auto;
            position: relative;
        }
        
        .participants-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 15px;
        }
        
        .participants-table th,
        .participants-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .participants-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .participants-table tr:hover {
            background: #f8f9fa;
        }
        
        .close-modal {
            position: absolute;
            right: 20px;
            top: 20px;
            cursor: pointer;
            font-size: 28px;
            color: #666;
            transition: color 0.3s;
        }

        .close-modal:hover {
            color: #333;
        }

        #modalEventTitle {
            margin: 0 0 20px 0;
            color: #333;
            font-size: 24px;
            padding-right: 40px;
        }

        .no-events {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 18px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .refresh-button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            margin-left: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.3s;
        }

        .refresh-button:hover {
            background: #45a049;
        }

        .refresh-button i {
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
            <a href="4_Event.php"> <i class="fa-solid fa-home"></i> <span class="label"> Home </span> </a>
            <a href="6_NewEvent.php"> <i class="fa-solid fa-calendar"></i> <span class="label"> Events </span> </a>
            <a href="registered_participants.php" class="active"> <i class="fa-solid fa-users"></i> <span class="label"> Registered Participants </span> </a>
            <a href="10_Admin.php"> <i class="fa-regular fa-circle-user"></i> <span class="label"> Admins </span> </a>
            <a href="7_StudentTable.php"> <i class="fa-solid fa-address-card"></i> <span class="label"> Participants </span> </a>
            <a href="5_About.php"> <i class="fa-solid fa-circle-info"></i> <span class="label"> About </span> </a>
            <a href="8_archive.php"> <i class="fa-solid fa-bars"></i> <span class="label"> Logs </span> </a>
            <a href="1_Login.php"> <i class="fa-solid fa-circle-info"></i> <span class="label"> Login </span> </a>
        </div>
        <div class="logout">
            <a href="../php/logout.php"> <i class="fa-solid fa-gear"></i> <span class="label"> Logout </span> </a>
        </div>
    </div>

    <div class="event-main">
        <div class="event-details">
            <div class="event-top">
                <p>Registered Participants</p>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <input type="text" class="search-box" id="searchEvents" placeholder="Search events...">
                    <button class="refresh-button" onclick="refreshEvents()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
        </div>

        <div class="event-cards" id="eventCards">
            <?php
            include '../php/conn.php';
            
            $query = "SELECT e.*, COUNT(ea.student_id) as participant_count 
                     FROM event_table e 
                     LEFT JOIN event_attendance ea ON e.number = ea.event_id 
                     GROUP BY e.number 
                     ORDER BY e.date_start DESC, e.event_start DESC";
            
            $result = $conn->query($query);
            
            if ($result->num_rows > 0) {
                while ($event = $result->fetch_assoc()) {
                    ?>
                    <div class="event-card" data-event-id="<?= $event['number'] ?>">
                        <div class="event-title"><?= htmlspecialchars($event['event_title']) ?></div>
                        <div class="event-details">
                            <div><i class="fas fa-calendar"></i> <?= date('F j, Y', strtotime($event['date_start'])) ?></div>
                            <div><i class="fas fa-clock"></i> <?= date('g:i A', strtotime($event['event_start'])) ?></div>
                            <div><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['event_location']) ?></div>
                        </div>
                        <div class="participant-count">
                            <i class="fas fa-users"></i> <?= $event['participant_count'] ?> Participants
                        </div>
                        <button class="view-participants" onclick="viewParticipants(<?= $event['number'] ?>)">
                            View Participants
                        </button>
                    </div>
                    <?php
                }
            } else {
                echo '<div class="no-events">No events found.</div>';
            }
            $conn->close();
            ?>
        </div>
    </div>

    <!-- Participants Modal -->
    <div id="participantsModal" class="participants-modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h2 id="modalEventTitle"></h2>
            <div id="participantsContent"></div>
        </div>
    </div>

    <script>
    function viewParticipants(eventId) {
        fetch('../php/get_participants.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `event_id=${eventId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = document.getElementById('participantsModal');
                const content = document.getElementById('participantsContent');
                
                if (data.participants.length === 0) {
                    content.innerHTML = '<p>No participants registered for this event.</p>';
                } else {
                    let html = '<table class="participants-table">';
                    html += '<thead><tr><th>Student ID</th><th>Name</th><th>Course</th><th>Registration Date</th></tr></thead>';
                    html += '<tbody>';
                    
                    data.participants.forEach(participant => {
                        html += `<tr>
                            <td>${participant.student_id}</td>
                            <td>${participant.full_name}</td>
                            <td>${participant.course}</td>
                            <td>${new Date(participant.registration_date).toLocaleString()}</td>
                        </tr>`;
                    });
                    
                    html += '</tbody></table>';
                    content.innerHTML = html;
                }
                
                modal.style.display = 'block';
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to load participants'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load participants'
            });
        });
    }

    function closeModal() {
        document.getElementById('participantsModal').style.display = 'none';
    }

    function refreshEvents() {
        location.reload();
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('participantsModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }

    // Search functionality
    document.getElementById('searchEvents').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const cards = document.querySelectorAll('.event-card');
        
        cards.forEach(card => {
            const title = card.querySelector('.event-title').textContent.toLowerCase();
            const location = card.querySelector('.event-details').textContent.toLowerCase();
            
            if (title.includes(searchTerm) || location.includes(searchTerm)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
    </script>
</body>
</html> 