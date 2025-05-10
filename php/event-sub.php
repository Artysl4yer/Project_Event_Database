<?php
include 'conn.php';
if($conn == false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}


$event_title = $_POST["event-title"];
$event_location = $_POST["event-location"];
$date_start = $_POST["event-date-start"];
$event_start = $_POST["event-time-start"];
$date_end = $_POST["event-date-end"];
$event_end = $_POST["event-time-end"];
$event_description = $_POST["event-description"];
$organization = $_POST["event-orgs"];
$code = $_POST['code'];


$merge_start = DateTime::createFromFormat('Y-m-d H:i', $date_start . ' ' . $event_start);
$merge_end = DateTime::createFromFormat('Y-m-d H:i', $date_end . ' ' . $event_end);



if (!$merge_start || !$merge_end) {
    die("ERROR: Invalid date or time format.");
}

$merge_start = $merge_start->format('Y-m-d H:i:s');
$merge_end = $merge_end->format('Y-m-d H:i:s');

$sql = "INSERT INTO event_table (
    event_title, event_code, event_location, date_start, event_start, date_end, event_end, event_description, organization
) VALUES (
    '$event_title', '$code', '$event_location', '$date_start', '$merge_start', '$date_end', '$merge_end', '$event_description', '$organization'
)";



if(mysqli_query($conn, $sql)){
    header("Location: ../pages/6_NewEvent.php");
    exit();
} else {
    echo "ERROR: Could not execute $sql. " . mysqli_error($conn);
}

mysqli_close($conn);

?>