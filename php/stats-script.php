<?php
    include 'conn.php';
    if($conn == false){
        die("ERROR: Could not connect. " . mysqli_connect_error());
    }


    $sql = "SELECT * FROM participants_table";
    $res = $mysqli->query($sql);
    $data = [];
    while($row = $res.fetch_assoc()){
        array_push($data, $row);
    }

    echo json_encode($data);

?>