<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: 1_Login.php");
    exit;
}

include '../php/conn.php';

$event_id = isset($_GET['id']) ? $_GET['id'] : null;
if (!$event_id) {
    header("Location: events.php");
    exit;
}

// Get event details
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    header("Location: events.php");
    exit;
}

// Get attendance count
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM event_attendance WHERE event_id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$attendance = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2><?php echo htmlspecialchars($event['title']); ?></h2>
                <a href="event_attendance.php?event_id=<?php echo $event_id; ?>" 
                   class="btn btn-primary">Take Attendance</a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h4>Event Details</h4>
                        <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($event['date'])); ?></p>
                        <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($event['time'])); ?></p>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                        <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5>Attendance Statistics</h5>
                                <p><strong>Total Attendees:</strong> <?php echo $attendance['count']; ?></p>
                                <a href="view_attendance.php?event_id=<?php echo $event_id; ?>" 
                                   class="btn btn-info">View Attendance List</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-3">
            <a href="events.php" class="btn btn-secondary">Back to Events</a>
        </div>
    </div>
</body>
</html> 