<?php
include 'conn.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if($conn == false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

// Get and sanitize input
$event_title = $conn->real_escape_string($_POST["event-title"]);
$event_location = $conn->real_escape_string($_POST["event-location"]);
$date_start = $conn->real_escape_string($_POST["event-date-start"]);
$event_start = $conn->real_escape_string($_POST["event-time-start"]);
$date_end = $conn->real_escape_string($_POST["event-date-end"]);
$event_end = $conn->real_escape_string($_POST["event-time-end"]);
$event_description = $conn->real_escape_string($_POST["event-description"]);
$organization = $conn->real_escape_string($_POST["event-orgs"]);
$status = $conn->real_escape_string($_POST['event-status']);
$code = $conn->real_escape_string($_POST['code']);

// Validate date/time format
$merge_start = DateTime::createFromFormat('Y-m-d H:i', $date_start . ' ' . $event_start);
$merge_end = DateTime::createFromFormat('Y-m-d H:i', $date_end . ' ' . $event_end);

if (!$merge_start || !$merge_end) {
    die("ERROR: Invalid date or time format.");
}

$merge_start = $merge_start->format('Y-m-d H:i:s');
$merge_end = $merge_end->format('Y-m-d H:i:s');

$sql = "INSERT INTO event_table (
    event_title, event_code, event_location, date_start, event_start, date_end, event_end, event_description, organization, event_status
) VALUES (
    '$event_title', '$code', '$event_location', '$date_start', '$merge_start', '$date_end', '$merge_end', '$event_description', '$organization', '$status'
)";

if(mysqli_query($conn, $sql)){
    header("Location: ../pages/6_NewEvent.php");
    exit();
} else {
    echo "ERROR: Could not execute $sql. " . mysqli_error($conn);
}

$conn->close();
?>