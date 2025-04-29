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



$sql = "INSERT INTO event_table (
    event_title, event_location, date_start, event_start, date_end, event_end, event_description, organization
) VALUES (
    '$event_title', '$event_location', '$date_start', '$event_start', '$date_end', '$event_end', '$event_description', '$organization'
)";

if(mysqli_query($conn, $sql)){
    header("Location: ../pages/4_Event.php");
} else {
    echo "ERROR: Could not execute $sql. " . mysqli_error($conn);
}

mysqli_close($conn);
?>