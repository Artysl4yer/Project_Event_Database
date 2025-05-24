<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: 1_Login.php");
    exit;
}

include '../php/conn.php';

// Get all events
$sql = "SELECT e.*, 
        (SELECT COUNT(*) FROM event_attendance WHERE event_id = e.id) as attendee_count 
        FROM events e 
        ORDER BY e.date DESC, e.time DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .event-card {
            margin-bottom: 20px;
        }
        .attendance-badge {
            font-size: 0.9em;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Events</h2>
            <a href="create_event.php" class="btn btn-primary">Create New Event</a>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="row">
                <?php while($event = $result->fetch_assoc()): ?>
                    <div class="col-md-6">
                        <div class="card event-card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?php echo htmlspecialchars($event['title']); ?>
                                    <span class="badge bg-info attendance-badge">
                                        <?php echo $event['attendee_count']; ?> attendees
                                    </span>
                                </h5>
                                <p class="card-text">
                                    <strong>Date:</strong> <?php echo date('F j, Y', strtotime($event['date'])); ?><br>
                                    <strong>Time:</strong> <?php echo date('g:i A', strtotime($event['time'])); ?><br>
                                    <strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?>
                                </p>
                                <div class="btn-group">
                                    <a href="event_details.php?id=<?php echo $event['id']; ?>" 
                                       class="btn btn-info">View Details</a>
                                    <a href="event_attendance.php?event_id=<?php echo $event['id']; ?>" 
                                       class="btn btn-success">Take Attendance</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                No events found. <a href="create_event.php">Create your first event</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?> 