<?php 
$conn = mysqli_connect("localhost","root","","event_database");

if($conn==false){
	die("Error: " . mysqli_connect_error());
    echo "failed";
}

?>