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
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="../styles/style1.css">
        <link rel="stylesheet" href="../styles/style2.css">
        <link rel="stylesheet" href="../styles/style3.css">
        <title> PLP: Events </title>
        <script src="https://kit.fontawesome.com/d78dc5f742.js" crossorigin="anonymous"></script>
        <style>
        .event-list-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 32px;
            justify-content: start;
            margin: 40px auto;
            max-width: 1400px;
            padding: 0 20px;
        }
        .event-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            display: flex;
            flex-direction: column;
            min-height: 480px;
            width: 100%;
            margin: 0;
            overflow: hidden;
            transition: box-shadow 0.2s;
        }
        .event-card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        }
        .card-header {
            position: relative;
            min-height: 120px;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            background: #f7f7f7;
        }
        .card-header img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 12px 12px 0 0;
            background: 
        }
        .card-header > div {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 4px 8px;
            border-radius: 6px;
        }
        .card-body {
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            padding: 16px;
        }
        .card-footer {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 16px;
            background: #f7f7f7;
            text-align: center;
        }
        .event-card h3, .event-card p {
            margin: 0 0 8px 0;
            word-break: break-word;
        }
        .view-btn {
            margin-top: 12px;
            width: 100%;
            background: #218838;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 10px 0;
            font-size: 1em;
            cursor: pointer;
        }
        .view-btn:hover {
            background: #17692d;
        }
        @media (max-width: 1200px) {
            .event-list-container { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 800px) {
            .event-list-container { grid-template-columns: 1fr; }
        }
        .event-list-scroll-wrapper {
            overflow-x: auto;
            white-space: nowrap;
            padding-bottom: 12px;
        }
        .event-list-container {
            display: flex;
            flex-direction: row;
            gap: 32px;
            min-width: 600px;
            align-items: flex-start;
        }
        .event-card {
            flex-shrink: 0;
            width: 320px;
            min-width: 320px;
            max-width: 320px;
            min-height: 480px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            display: flex;
            flex-direction: column;
            margin: 0;
            overflow: hidden;
            transition: box-shadow 0.2s;
        }
        .attendance-btn {
            margin-top: 10px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 16px 0;
            font-size: 1.1em;
            width: 100%;
            cursor: pointer;
            transition: background 0.2s;
        }
        .attendance-btn:hover {
            background: #0056b3;
        }
        </style>
        <link rel="stylesheet" href="../styles/search.css">
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
            <a href="guest-table.php"> <i class="fa-solid fa-users"></i> <span class="label"> Guests </span> </a>
            <a href="5_About.php"> <i class="fa-solid fa-circle-info"></i> <span class="label"> About </span> </a>
            <a href="8_archive.php"> <i class="fa-solid fa-bars"></i> <span class="label"> Logs </span> </a>
            </div>
            <div class="logout">
                <a href="../php/logout.php"> <i class="fa-solid fa-sign-out"></i> <span class="label"> Logout </span> </a>
            </div>
        </div>
        <div class="image-background">
            <div class="image-background-dim"></div>
            <div class="image-content" id="banner">
                <h1> PLP EVENTS </h1>
                <div class="image-description">
                <p> Welcome to Pamantasan ng Lungsod ng Pasig Updates </p>
                <p> Get Up to date with the latest upcoming Events </p>
            </div>
            </div>
        </div>
        
        <div class="main-content">
    
            <div class="first-page">
            <!-- The Event List. The compilation of events, sort to newest to latest -->
            <div class="event-details">
                <div class="event-attendance-top">
                    <p> Event List </p>
                    
                    <div class="search-container">
                        <form class="search-form" action="" method="GET">
                            <input type="text" id="search" name="search" placeholder="Search events..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                            <button type="submit"><i class="fa fa-search"></i></button>
                        </form>
                    </div>
                </div>
                
                <!-- Event List Section -->
                <div style="margin-bottom: 48px;">
                    <h2 style="text-align:center; margin-bottom: 24px; color: #17692d; letter-spacing: 1px;">All Events</h2>
                    <div class="event-list-scroll-wrapper">
                        <div class="event-list-container">
                            <?php
                            include '../php/conn.php';

                            $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

                            if (!empty($search)) {
                                $query = "SELECT * FROM event_table 
                                            WHERE event_title LIKE '%$search%' 
                                            OR event_description LIKE '%$search%' 
                                            OR event_location LIKE '%$search%' 
                                            OR organization LIKE '%$search%' 
                                            ORDER BY number DESC";
                            } else {
                                $query = "SELECT * FROM event_table ORDER BY number DESC";
                            }

                            $result = $conn->query($query);

                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $dateValue = $row['date_start'] ?? null;
                                    if ($dateValue) {
                                        $startDateTime = new DateTime($dateValue);
                                        $dateOnly = $startDateTime->format('Y-m-d');
                                        $dateTimeStart = $startDateTime->format('Y-m-d H:i');
                                    } else {
                                        $dateOnly = null;
                                        $dateTimeStart = null;
                                    }
                                    $eventNumber = $row['number'];
                                    echo "<div class='event-card'>";
                                    echo "  <div class='card-header'>";
                                    echo "      <img src='" . ($row['event_image'] ? '../uploads/events/' . htmlspecialchars($row['event_image']) : '../images-icon/plm_courtyard.png') . "' alt='Event Background' />";
                                    echo "      <div class='event-date'>";
                                    echo "          <p class='day'>" . $dateOnly . "</p>";
                                    echo "          <p class='time'>" . $dateTimeStart . "</p>";
                                    echo "      </div>";
                                    echo "  </div>";
                                    echo "  <div class='card-body'>";
                                    echo "      <h3>" . htmlspecialchars($row['event_title']) . "</h3>";
                                    echo "      <p>" . htmlspecialchars($row['event_description']) . "</p>";
                                    echo "  </div>";
                                    echo "  <div class='card-footer'>";
                                    echo "      <p> Status: <b> " . htmlspecialchars($row['event_status']) . " </b></p>";
                                    echo "      <button onclick='event.stopPropagation(); window.location.href=\"view_attendance.php?event=" . $eventNumber . "\"' class='view-btn'>";
                                    echo "          <i class='fas fa-users'></i> View Participants";
                                    echo "      </button>";
                                    echo "      <button onclick='event.stopPropagation(); window.location.href=\"11_Attendance.php?event=" . $eventNumber . "\"' class='attendance-btn'>";
                                    echo "          <i class='fas fa-clipboard-check'></i> Go to Attendance";
                                    echo "      </button>";
                                    echo "  </div>";
                                    echo "</div>";
                                }
                            } else {
                                echo "<p style='grid-column: 1 / -1; margin: 20px; color: red;'>No events found matching your search.</p>";
                            }

                            // Query for most males in one event
                            $sql_male = "
                                SELECT e.event_title, e.number, COUNT(s.Gender) as male_count
                                FROM event_attendance ea
                                JOIN event_table e ON ea.event_id = e.number
                                JOIN student_table s ON ea.student_id = s.ID
                                WHERE s.Gender = 'Male'
                                GROUP BY e.number
                                ORDER BY male_count DESC
                                LIMIT 1
                            ";
                            $result_male = $conn->query($sql_male);
                            $top_male = $result_male ? $result_male->fetch_assoc() : null;

                            // Query for most females in one event
                            $sql_female = "
                                SELECT e.event_title, e.number, COUNT(s.Gender) as female_count
                                FROM event_attendance ea
                                JOIN event_table e ON ea.event_id = e.number
                                JOIN student_table s ON ea.student_id = s.ID
                                WHERE s.Gender = 'Female'
                                GROUP BY e.number
                                ORDER BY female_count DESC
                                LIMIT 1
                            ";
                            $result_female = $conn->query($sql_female);
                            $top_female = $result_female ? $result_female->fetch_assoc() : null;

                            // Query for top 5 events with most males
                            $sql_top_males = "
                                SELECT e.event_title, COUNT(s.Gender) as male_count
                                FROM event_attendance ea
                                JOIN event_table e ON ea.event_id = e.number
                                JOIN student_table s ON ea.student_id = s.ID
                                WHERE s.Gender = 'Male'
                                GROUP BY e.number
                                ORDER BY male_count DESC
                                LIMIT 5
                            ";
                            $result_top_males = $conn->query($sql_top_males);
                            $top_male_events = [];
                            if ($result_top_males) {
                                while ($row = $result_top_males->fetch_assoc()) {
                                    $top_male_events[] = $row;
                                }
                            }
                            // Query for top 5 events with most females
                            $sql_top_females = "
                                SELECT e.event_title, COUNT(s.Gender) as female_count
                                FROM event_attendance ea
                                JOIN event_table e ON ea.event_id = e.number
                                JOIN student_table s ON ea.student_id = s.ID
                                WHERE s.Gender = 'Female'
                                GROUP BY e.number
                                ORDER BY female_count DESC
                                LIMIT 5
                            ";
                            $result_top_females = $conn->query($sql_top_females);
                            $top_female_events = [];
                            if ($result_top_females) {
                                while ($row = $result_top_females->fetch_assoc()) {
                                    $top_female_events[] = $row;
                                }
                            }

                            // Query for department with the most events
                            $sql_dept = "
                                SELECT 	organization, COUNT(*) as event_count
                                FROM event_table
                                WHERE 	organization IS NOT NULL AND organization != ''
                                GROUP BY 	organization
                                ORDER BY event_count DESC
                                LIMIT 1
                            ";
                            $result_dept = $conn->query($sql_dept);
                            $top_dept = $result_dept ? $result_dept->fetch_assoc() : null;
                            // Query for top 5 departments by event count
                            $sql_top_depts = "
                                SELECT 	organization, COUNT(*) as event_count
                                FROM event_table
                                WHERE 	organization IS NOT NULL AND organization != ''
                                GROUP BY 	organization
                                ORDER BY event_count DESC
                                LIMIT 5
                            ";
                            $result_top_depts = $conn->query($sql_top_depts);
                            $top_dept_list = [];
                            if ($result_top_depts) {
                                while ($row = $result_top_depts->fetch_assoc()) {
                                    $top_dept_list[] = $row;
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <!-- Divider -->
                <div style="width:100%;height:2px;background:linear-gradient(90deg,#218838 0,#fff 100%);margin:48px 0 32px 0;"></div>
                <!-- Modern Statistics Chart for Event Attendance -->
                <div style="margin: 0 auto 0 auto; max-width: 900px; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 32px;">
                    <h2 style="text-align:center; margin-bottom: 32px; color: #218838;">Event Attendance Statistics</h2>
                    <div id="eventStats" style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-start; gap: 24px; margin-bottom: 32px;">
                        <div style="flex:1; min-width:220px; background:#f7f7f7; border-radius:8px; padding:18px 24px; box-shadow:0 1px 4px rgba(0,0,0,0.04);">
                            <h3 style='margin:0 0 8px 0; color:#17692d; font-size:1.1em;'>Total Attendance</h3>
                            <div id="totalAttendance" style="font-size:2em; font-weight:bold; color:#218838;">...</div>
                        </div>
                        <div style="flex:1; min-width:220px; background:#f7f7f7; border-radius:8px; padding:18px 24px; box-shadow:0 1px 4px rgba(0,0,0,0.04);">
                            <h3 style='margin:0 0 8px 0; color:#17692d; font-size:1.1em;'>Average Attendance per Event</h3>
                            <div id="avgAttendance" style="font-size:2em; font-weight:bold; color:#218838;">...</div>
                        </div>
                        <div style="flex:2; min-width:320px; background:#f7f7f7; border-radius:8px; padding:18px 24px; box-shadow:0 1px 4px rgba(0,0,0,0.04);">
                            <h3 style='margin:0 0 8px 0; color:#17692d; font-size:1.1em;'>Top 5 Events</h3>
                            <ol id="topEvents" style="margin:0; padding-left:20px; color:#218838; font-size:1.1em;"></ol>
                        </div>
                    </div>
                    <canvas id="eventAttendanceChart" height="120"></canvas>
                    <div style="display: flex; gap: 32px; justify-content: center; margin: 32px 0; flex-wrap: wrap;">
                        <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 24px; min-width: 320px;">
                            <h3 style="color: #218838;">Event with Most Males</h3>
                            <?php if ($top_male): ?>
                                <p><b><?= htmlspecialchars($top_male['event_title']) ?></b></p>
                                <p>Males: <b><?= $top_male['male_count'] ?></b></p>
                            <?php else: ?>
                                <p>No data available.</p>
                            <?php endif; ?>
                            <canvas id="maleBarChart" height="180"></canvas>
                        </div>
                        <div style="background: #fff; border-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 24px; min-width: 320px;">
                            <h3 style="color: #218838;">Event with Most Females</h3>
                            <?php if ($top_female): ?>
                                <p><b><?= htmlspecialchars($top_female['event_title']) ?></b></p>
                                <p>Females: <b><?= $top_female['female_count'] ?></b></p>
                            <?php else: ?>
                                <p>No data available.</p>
                            <?php endif; ?>
                            <canvas id="femaleBarChart" height="180"></canvas>
                        </div>
                        <div style="background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 24px; min-width: 320px;">
                            <h3 style="color: #218838;">Department with Most Events</h3>
                            <?php if ($top_dept): ?>
                                <p><b><?= htmlspecialchars($top_dept['organization']) ?></b></p>
                                <p>Events: <b><?= $top_dept['event_count'] ?></b></p>
                            <?php else: ?>
                                <p>No data available.</p>
                            <?php endif; ?>
                            <canvas id="deptBarChart" height="180"></canvas>
                        </div>
                    </div>
                </div>
                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    fetch('../php/event_attendance_stats.php')
                        .then(response => response.json())
                        .then(data => {
                            // Calculate statistics
                            const totalAttendance = data.reduce((sum, e) => sum + e.attendance_count, 0);
                            const avgAttendance = data.length ? (totalAttendance / data.length) : 0;
                            const topEvents = data.slice(0, 5);
                            // Update stats
                            document.getElementById('totalAttendance').textContent = totalAttendance;
                            document.getElementById('avgAttendance').textContent = avgAttendance.toFixed(2);
                            const topList = document.getElementById('topEvents');
                            topList.innerHTML = '';
                            topEvents.forEach(e => {
                                const li = document.createElement('li');
                                li.textContent = `${e.event_title} (${e.attendance_count})`;
                                topList.appendChild(li);
                            });
                            // Chart.js with animation
                            const ctx = document.getElementById('eventAttendanceChart').getContext('2d');
                            const chart = new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: data.map(e => e.event_title),
                                    datasets: [{
                                        label: 'Number of Attendees',
                                        data: data.map(e => e.attendance_count),
                                        backgroundColor: 'rgba(33, 136, 56, 0.7)',
                                        borderColor: 'rgba(33, 136, 56, 1)',
                                        borderWidth: 2,
                                        borderRadius: 8,
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    animation: {
                                        duration: 1200,
                                        easing: 'easeOutBounce',
                                    },
                                    plugins: {
                                        legend: { display: false },
                                        title: { display: false },
                                        tooltip: {
                                            callbacks: {
                                                label: function(context) {
                                                    return 'Attendees: ' + context.parsed.y;
                                                }
                                            }
                                        }
                                    },
                                    scales: {
                                        x: {
                                            title: { display: true, text: 'Event' },
                                            ticks: { autoSkip: false, maxRotation: 45, minRotation: 0 }
                                        },
                                        y: {
                                            beginAtZero: true,
                                            title: { display: true, text: 'Number of Attendees' },
                                            grace: '10%',
                                            ticks: {
                                                stepSize: 1
                                            }
                                        }
                                    }
                                }
                            });
                            // Bar chart for top 5 events with most males
                            const maleLabels = <?php echo json_encode(array_column($top_male_events, 'event_title')); ?>;
                            const maleCounts = <?php echo json_encode(array_map('intval', array_column($top_male_events, 'male_count'))); ?>;
                            const ctxMale = document.getElementById('maleBarChart').getContext('2d');
                            new Chart(ctxMale, {
                                type: 'bar',
                                data: {
                                    labels: maleLabels,
                                    datasets: [{
                                        label: 'Number of Males',
                                        data: maleCounts,
                                        backgroundColor: 'rgba(33, 136, 56, 0.7)',
                                        borderColor: 'rgba(33, 136, 56, 1)',
                                        borderWidth: 2,
                                        borderRadius: 8,
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    plugins: { legend: { display: false } },
                                    animation: {
                                        duration: 1400,
                                        easing: 'easeOutBounce',
                                    },
                                    scales: {
                                        x: { title: { display: true, text: 'Event' }, ticks: { autoSkip: false } },
                                        y: { beginAtZero: true, title: { display: true, text: 'Males' }, grace: '10%', ticks: { stepSize: 1 } }
                                    }
                                }
                            });
                            // Bar chart for top 5 events with most females
                            const femaleLabels = <?php echo json_encode(array_column($top_female_events, 'event_title')); ?>;
                            const femaleCounts = <?php echo json_encode(array_map('intval', array_column($top_female_events, 'female_count'))); ?>;
                            const ctxFemale = document.getElementById('femaleBarChart').getContext('2d');
                            new Chart(ctxFemale, {
                                type: 'bar',
                                data: {
                                    labels: femaleLabels,
                                    datasets: [{
                                        label: 'Number of Females',
                                        data: femaleCounts,
                                        backgroundColor: 'rgba(255, 99, 132, 0.7)',
                                        borderColor: 'rgba(255, 99, 132, 1)',
                                        borderWidth: 2,
                                        borderRadius: 8,
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    plugins: { legend: { display: false } },
                                    animation: {
                                        duration: 1400,
                                        easing: 'easeOutBounce',
                                    },
                                    scales: {
                                        x: { title: { display: true, text: 'Event' }, ticks: { autoSkip: false } },
                                        y: { beginAtZero: true, title: { display: true, text: 'Females' }, grace: '10%', ticks: { stepSize: 1 } }
                                    }
                                }
                            });
                            // Bar chart for top 5 departments by event count
                            const deptLabels = <?php echo json_encode(array_column($top_dept_list, 'organization')); ?>;
                            const deptCounts = <?php echo json_encode(array_map('intval', array_column($top_dept_list, 'event_count'))); ?>;
                            const ctxDept = document.getElementById('deptBarChart').getContext('2d');
                            new Chart(ctxDept, {
                                type: 'bar',
                                data: {
                                    labels: deptLabels,
                                    datasets: [{
                                        label: 'Number of Events',
                                        data: deptCounts,
                                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                                        borderColor: 'rgba(54, 162, 235, 1)',
                                        borderWidth: 2,
                                        borderRadius: 8,
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    plugins: { legend: { display: false } },
                                    animation: {
                                        duration: 1400,
                                        easing: 'easeOutBounce',
                                    },
                                    scales: {
                                        x: { title: { display: true, text: 'Department/Organization' }, ticks: { autoSkip: false } },
                                        y: { beginAtZero: true, title: { display: true, text: 'Events' }, grace: '10%', ticks: { stepSize: 1 } }
                                    }
                                }
                            });
                        })
                        .catch(() => {
                            document.getElementById('eventAttendanceChart').parentElement.innerHTML += '<div style="color:red;text-align:center;margin-top:20px;">Unable to load statistics.</div>';
                        });
                });
                </script>
            </div>
        </div>
    </div>               
   
    
    <script src="../Javascript/dynamic.js"></script>    
    <script src="../Javascript/search.js"></script>
   
    </body>
</html>
