<?php 
$conn = mysqli_connect("192.168.109.101","akie","akie123456","eventdatabase");

if($conn==false){
	die("Error: " . mysqli_connect_error());
    echo "failed";
}

?>