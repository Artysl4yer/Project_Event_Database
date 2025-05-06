<?php 
$conn = mysqli_connect("192.168.0.20","akie","akie123456","eventdatabase");

if($conn==false){
	die("Error: " . mysqli_connect_error());
    echo "failed";
}

?>