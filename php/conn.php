<?php 
$conn = mysqli_connect("localhost","root","","eventdatabase");

if($conn==false){
	die("Error: " . mysqli_connect_error());
    echo "failed";
}

?>