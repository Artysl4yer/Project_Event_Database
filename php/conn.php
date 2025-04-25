<?php 
$conn = mysqli_connect("localhost","root","","dbplp");

if($conn==false){
	die("Error: " . mysqli_connect_error());
}
?>