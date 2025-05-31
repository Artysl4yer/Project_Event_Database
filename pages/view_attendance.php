<?php
session_start();
include '../php/conn.php';

if (!isset($_SESSION['email']) && !isset($_SESSION['client_id'])) {
    header('Location: 1_Login.php');
    exit();
}

$event_id = isset($_GET['event']) ? intval($_GET['event']) : 0;
$event = null;

if ($event_id) {
    // Fetch event details
    $stmt = $conn->prepare("SELECT * FROM event_table WHERE number = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>View Attendance</title>
    <link rel="stylesheet" href="../styles/style1.css">
    <link rel="stylesheet" href="../styles/style2.css">
    <link rel="stylesheet" href="../styles/style3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body>
    <div class="title-container">
        <img src="../images-icon/plplogo.png"> <h1> Pamantasan ng Lungsod ng Pasig </h1>
    </div>
    <div class="tab-container">
        <div class="menu-items">
            <a href="4_Event.php" class="active"> <i class="fa-solid fa-home"></i> <span class="label"> Home </span> </a>
            <a href="6_NewEvent.php" class="active"> <i class="fa-solid fa-calendar"></i> <span class="label"> Events </span> </a>
            <a href="10_Admin.php" class="active"> <i class="fa-regular fa-circle-user"></i> <span class="label"> Admins </span> </a>
            <a href="7_StudentTable.php" class="active"> <i class="fa-solid fa-address-card"></i> <span class="label"> Students </span> </a>
            <a href="guest-table.php" class="active"> <i class="fa-solid fa-users"></i> <span class="label"> Guests </span> </a>
            <a href="5_About.php" class="active"> <i class="fa-solid fa-circle-info"></i> <span class="label"> About </span> </a>
            <a href="8_archive.php" class="active"> <i class="fa-solid fa-bars"></i> <span class="label"> Logs </span> </a>
        </div>
        <div class="logout">
            <a href=""> <i class="fa-solid fa-gear"></i> <span class="label"> Logout </span> </a>
        </div>
    </div>
    <div class="main-container">
        <?php if ($event): ?>
        <div class="attendance-top" style="max-width:900px;margin:40px auto 0 auto;">
            <div class="event-info-box" style="background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.07);padding:32px 32px 24px 32px;margin-bottom:32px;">
                <div class="event-title">
                    <h2 style="margin-bottom:10px;"><i class="fa-solid fa-calendar-check" style="color:#218838;margin-right:8px;"></i>Event: <?= htmlspecialchars($event['event_title'] ?? '') ?></h2>
                </div>
                <div class="event-status" style="margin-bottom:10px;">
                    <span class="status-badge status-<?= $event['event_status'] ?>">
                        <i class="fa-solid fa-circle" style="font-size:0.8em;margin-right:4px;"></i>
                        <?= ucfirst($event['event_status']) ?>
                    </span>
                    <span class="registration-status registration-<?= isset($event['registration_status']) && $event['registration_status'] ? $event['registration_status'] : 'unknown' ?>">
                        <i class="fa-solid fa-user-check" style="font-size:0.8em;margin-right:4px;"></i>
                        Registration <?= isset($event['registration_status']) && $event['registration_status'] ? ucfirst($event['registration_status']) : 'Unknown' ?>
                    </span>
                </div>
                <div class="event-time" style="margin-bottom:10px;">
                    <p><b><i class="fa-solid fa-clock" style="margin-right:6px;"></i>Start:</b> <?php
                        if (!empty($event['event_start'])) {
                            $start_time = new DateTime($event['event_start']);
                            echo $start_time->format('F j, Y g:i A');
                        } else {
                            echo 'N/A';
                        }
                    ?></p>
                    <p><b><i class="fa-solid fa-clock" style="margin-right:6px;"></i>End:</b> <?php
                        if (!empty($event['event_end'])) {
                            $end_time = new DateTime($event['event_end']);
                            echo $end_time->format('F j, Y g:i A');
                        } else {
                            echo 'N/A';
                        }
                    ?></p>
                    <?php if (!empty($event['registration_deadline'])): ?>
                        <p><b><i class="fa-solid fa-calendar-day" style="margin-right:6px;"></i>Registration Deadline:</b> <?php
                            $deadline = new DateTime($event['registration_deadline']);
                            echo $deadline->format('F j, Y g:i A');
                        ?></p>
                    <?php endif; ?>
                </div>
                <div class="event-description" style="margin-bottom:0;">
                    <b><i class="fa-solid fa-align-left" style="margin-right:6px;"></i>Description:</b> <?= htmlspecialchars($event['event_description'] ?? '') ?>
                </div>
            </div>
            <div class="attendees-box" style="background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.07);padding:32px 32px 24px 32px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;gap:12px;">
                    <h3 style="margin-top:0;"><i class="fa-solid fa-users" style="color:#218838;margin-right:8px;"></i>Attendees</h3>
                    <div style="display:flex;gap:10px;align-items:center;">
                        <button id="downloadCsvBtn" style="background:#218838;color:#fff;border:none;border-radius:6px;padding:8px 18px;font-size:1em;cursor:pointer;">
                            <i class="fa-solid fa-download"></i> Download CSV
                        </button>
                        <form id="importCsvForm" action="../php/import_attendance_csv.php" method="POST" enctype="multipart/form-data" style="display:inline;">
                            <label for="importCsvInput" style="background:#17692d;color:#fff;border:none;border-radius:6px;padding:8px 18px;font-size:1em;cursor:pointer;display:inline-block;">
                                <i class="fa-solid fa-upload"></i> Import CSV
                                <input type="file" id="importCsvInput" name="attendance_csv" accept=".csv" style="display:none;" onchange="this.form.submit()">
                            </label>
                        </form>
                    </div>
                </div>
                <div class="event-table-section">
                    <h2>Attendees</h2>
                    <table class="event-display-table" id="participants-table">
                        <thead>
                            <tr>
                                <th>Attendance ID</th>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Course</th>
                                <th>Section</th>
                                <th>Gender</th>
                                <th>Age</th>
                                <th>Year</th>
                                <th>Department</th>
                                <th>Registered By</th>
                                <th>Attendance Time</th>
                            </tr>
                        </thead>
                        <tbody id="participants-tbody">
                            <tr><td colspan="11">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Report Section -->
        <div class="report-section" style="max-width:900px;margin:32px auto 0 auto;background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.07);padding:28px 32px 24px 32px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
                <h2 style="margin:0;color:#17692d;font-size:1.3em;">Event Attendance Report</h2>
                <button id="downloadPdfBtn" style="background:#218838;color:#fff;border:none;border-radius:6px;padding:8px 18px;font-size:1em;cursor:pointer;">
                    <i class="fa-solid fa-file-pdf"></i> Download PDF
                </button>
            </div>
            <div id="reportContent">
                <p><b>Event Title:</b> <span id="reportEventTitle"></span></p>
                <p><b>Description:</b> <span id="reportEventDesc"></span></p>
                <p><b>Event Code:</b> <span id="reportEventCode"></span></p>
                <p><b>Organization:</b> <span id="reportEventOrg"></span></p>
                <p><b>Status:</b> <span id="reportEventStatus"></span></p>
                <p><b>Registration Status:</b> <span id="reportRegStatus"></span></p>
                <p><b>Registration Deadline:</b> <span id="reportRegDeadline"></span></p>
                <p><b>Date:</b> <span id="reportEventDate"></span></p>
                <p><b>Time:</b> <span id="reportEventTime"></span></p>
                <p><b>Total Students:</b> <span id="reportTotal"></span></p>
                <p><b>Male:</b> <span id="reportMale"></span> &nbsp; <b>Female:</b> <span id="reportFemale"></span></p>
                <p><b>Courses:</b> <span id="reportCourses"></span></p>
                <p><b>Sections:</b> <span id="reportSections"></span></p>
                <p><b>Years:</b> <span id="reportYears"></span></p>
                <p><b>Departments:</b> <span id="reportDepts"></span></p>
                <p><b>Age (Avg/Min/Max):</b> <span id="reportAges"></span></p>
            </div>
        </div>
        <?php else: ?>
            <div class='error-message'>Event not found.</div>
        <?php endif; ?>
    </div>
</body>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const eventId = <?= json_encode($event_id) ?>;
    const tbody = document.getElementById('participants-tbody');
    if (!eventId) {
        tbody.innerHTML = '<tr><td colspan="11">No event selected.</td></tr>';
        return;
    }
    // Download CSV button
    document.getElementById('downloadCsvBtn').addEventListener('click', function() {
        window.location.href = '../php/download_attendance_csv.php?event_id=' + encodeURIComponent(eventId);
    });
    fetch('../php/get_participants.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'event_id=' + encodeURIComponent(eventId)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.participants.length > 0) {
            tbody.innerHTML = '';
            data.participants.forEach(p => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${p.attendance_id ? p.attendance_id : ''}</td>
                    <td>${p.student_id ? p.student_id : ''}</td>
                    <td>${(p.first_name ? p.first_name : '') + ' ' + (p.last_name ? p.last_name : '')}</td>
                    <td>${p.Course ? p.Course : ''}</td>
                    <td>${p.Section ? p.Section : ''}</td>
                    <td>${p.Gender ? p.Gender : ''}</td>
                    <td>${p.Age ? p.Age : ''}</td>
                    <td>${p.Year ? p.Year : ''}</td>
                    <td>${p.Dept ? p.Dept : ''}</td>
                    <td>${p.registered_by ? p.registered_by : ''}</td>
                    <td>${p.attendance_time ? p.attendance_time : ''}</td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="11">No attendees found.</td></tr>';
        }
    })
    .catch(() => {
        tbody.innerHTML = '<tr><td colspan="11">Error loading participants.</td></tr>';
    });
});

// Fill report section after loading event and participants
document.addEventListener('DOMContentLoaded', function() {
    const event = <?= json_encode($event) ?>;
    if (event) {
        document.getElementById('reportEventTitle').textContent = event.event_title || '';
        document.getElementById('reportEventDesc').textContent = event.event_description || '';
        document.getElementById('reportEventCode').textContent = event.event_code || '';
        document.getElementById('reportEventOrg').textContent = event.organization || '';
        document.getElementById('reportEventStatus').textContent = event.event_status || '';
        document.getElementById('reportRegStatus').textContent = event.registration_status || '';
        document.getElementById('reportRegDeadline').textContent = event.registration_deadline ? (new Date(event.registration_deadline)).toLocaleString() : '';
        let date = '', time = '';
        if (event.event_start) {
            const dt = new Date(event.event_start);
            date = dt.toLocaleDateString();
            time = dt.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }
        document.getElementById('reportEventDate').textContent = date;
        document.getElementById('reportEventTime').textContent = time;
    }
});

// Count stats after loading participants
function updateReportStats(participants) {
    let total = 0, male = 0, female = 0;
    const courses = {}, sections = {}, years = {}, depts = {}, ages = [];
    participants.forEach(p => {
        total++;
        if ((p.Gender || '').toLowerCase() === 'male') male++;
        if ((p.Gender || '').toLowerCase() === 'female') female++;
        if (p.Course) courses[p.Course] = (courses[p.Course] || 0) + 1;
        if (p.Section) sections[p.Section] = (sections[p.Section] || 0) + 1;
        if (p.Year) years[p.Year] = (years[p.Year] || 0) + 1;
        if (p.Dept) depts[p.Dept] = (depts[p.Dept] || 0) + 1;
        if (p.Age && !isNaN(Number(p.Age))) ages.push(Number(p.Age));
    });
    document.getElementById('reportTotal').textContent = total;
    document.getElementById('reportMale').textContent = male;
    document.getElementById('reportFemale').textContent = female;
    document.getElementById('reportCourses').textContent = Object.entries(courses).map(([k,v])=>`${k} (${v})`).join(', ');
    document.getElementById('reportSections').textContent = Object.entries(sections).map(([k,v])=>`${k} (${v})`).join(', ');
    document.getElementById('reportYears').textContent = Object.entries(years).map(([k,v])=>`${k} (${v})`).join(', ');
    document.getElementById('reportDepts').textContent = Object.entries(depts).map(([k,v])=>`${k} (${v})`).join(', ');
    if (ages.length) {
        const avg = (ages.reduce((a,b)=>a+b,0)/ages.length).toFixed(1);
        const min = Math.min(...ages);
        const max = Math.max(...ages);
        document.getElementById('reportAges').textContent = `${avg} / ${min} / ${max}`;
    } else {
        document.getElementById('reportAges').textContent = 'N/A';
    }
}

// Patch into the existing fetch for participants
const origFetch = window.fetch;
window.fetch = function() {
    return origFetch.apply(this, arguments).then(res => {
        if (arguments[0].includes('get_participants.php')) {
            res.clone().json().then(data => {
                if (data.success && data.participants) {
                    updateReportStats(data.participants);
                }
            });
        }
        return res;
    });
};

// PDF Download
document.getElementById('downloadPdfBtn').addEventListener('click', function() {
    window.jsPDF = window.jspdf.jsPDF;
    const doc = new jsPDF();
    
    // Set font size for title
    doc.setFontSize(16);
    doc.text('Event Attendance Report', 14, 18);
    
    // Set font size for content
    doc.setFontSize(10);
    
    let y = 30;
    const lineHeight = 7;
    const maxWidth = 180;  // Maximum width for text wrapping
    
    function addLine(label, value) {
        // Add label
        doc.text(label + ':', 14, y);
        
        // Handle long text wrapping for value
        const lines = doc.splitTextToSize(String(value), maxWidth - 50);  // 50 is the space taken by label
        doc.text(lines, 60, y);
        
        // Move y position down based on number of lines
        y += lineHeight * (lines.length || 1);
        
        // Add extra space if we're close to page bottom
        if (y > 280) {  // A4 height is about 297mm
            doc.addPage();
            y = 20;
        }
    }
    
    // Add all report fields
    addLine('Event Title', document.getElementById('reportEventTitle').textContent);
    addLine('Description', document.getElementById('reportEventDesc').textContent);
    addLine('Event Code', document.getElementById('reportEventCode').textContent);
    addLine('Organization', document.getElementById('reportEventOrg').textContent);
    addLine('Status', document.getElementById('reportEventStatus').textContent);
    addLine('Registration Status', document.getElementById('reportRegStatus').textContent);
    addLine('Registration Deadline', document.getElementById('reportRegDeadline').textContent);
    addLine('Date', document.getElementById('reportEventDate').textContent);
    addLine('Time', document.getElementById('reportEventTime').textContent);
    addLine('Total Students', document.getElementById('reportTotal').textContent);
    addLine('Male/Female', document.getElementById('reportMale').textContent + ' / ' + document.getElementById('reportFemale').textContent);
    addLine('Courses', document.getElementById('reportCourses').textContent);
    addLine('Sections', document.getElementById('reportSections').textContent);
    addLine('Years', document.getElementById('reportYears').textContent);
    addLine('Departments', document.getElementById('reportDepts').textContent);
    addLine('Age (Avg/Min/Max)', document.getElementById('reportAges').textContent);
    
    // Save the PDF
    const eventTitle = document.getElementById('reportEventTitle').textContent;
    const fileName = `event_attendance_report_${eventTitle.replace(/[^a-z0-9]/gi, '_').toLowerCase()}.pdf`;
    doc.save(fileName);
});
</script>
</html> 