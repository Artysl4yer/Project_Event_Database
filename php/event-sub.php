<?php
include 'conn.php';

if($conn == false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

$event_title = $_GET["event-title"];
$event_description = $_GET["event-description"];

$sql = "INSERT INTO event_table SET event_title='$event_title', event_description='$event_description', event_date='$event_date', organization='$event_orgs', remarks='$event_remarks', speaker='$event_speaker'";


if(mysqli_query($conn, $sql)){
    header("Location: ../pages/4_Event.php");
} else {
    echo "ERROR: Could not execute $sql. " . mysqli_error($conn);
}

mysqli_close($conn);
?>