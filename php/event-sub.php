<?php
include 'conn.php';

if($conn == false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

$event_title = $_POST["event-title"];
$even_location = $_POST["event-location"];
$date_start = $_POST["date-start"];
$event_start = $_POST["event-start"];
$date_end = $_POST["date-end"];
$event_end = $_POST["event-end"];
$event_description = $_POST["event-description"];
$organization = $_POST["organization"];



$sql = "INSERT INTO event_table SET event-title=''";


if(mysqli_query($conn, $sql)){
    header("Location: ../pages/4_Event.php");
} else {
    echo "ERROR: Could not execute $sql. " . mysqli_error($conn);
}

mysqli_close($conn);
?>