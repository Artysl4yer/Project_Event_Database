<?php 
if (!session_id()) { 
    session_start(); 
} 

include_once 'conn.php'; 

$res_status = $res_msg = ''; 
if (isset($_POST['importSubmit'])) { 
    $csvMimes = array(
        'text/x-comma-separated-values', 'text/comma-separated-values',
        'application/octet-stream', 'application/vnd.ms-excel',
        'application/x-csv', 'text/x-csv', 'text/csv',
        'application/csv', 'application/excel', 'application/vnd.msexcel'
    ); 

    if (!empty($_FILES['file']['name']) && in_array($_FILES['file']['type'], $csvMimes)) { 
        if (is_uploaded_file($_FILES['file']['tmp_name'])) { 
            $csvFile = fopen($_FILES['file']['tmp_name'], 'r'); 
            fgetcsv($csvFile); // Skip the header

            while (($line = fgetcsv($csvFile)) !== FALSE) { 
                $line_arr = !empty($line) ? array_filter($line) : ''; 
                if (count($line) >= 9) {
                    $event_title     = trim($line[0]); 
                    $event_code      = trim($line[1]); 
                    $event_location  = trim($line[2]); 
                    $date_start = date('Y-m-d', strtotime(trim($line[3])));
                    $event_start = date('Y-m-d H:i:s', strtotime(trim($line[4])));
                    $date_end = date('Y-m-d', strtotime(trim($line[3])));
                    $event_end = date('Y-m-d H:i:s', strtotime(trim($line[4])));
                    $event_description = trim($line[7]); 
                    $organization    = trim($line[8]);


                    // Always insert new event (no duplicate check)
                    $conn->query("INSERT INTO event_table (
                        event_title, event_code, event_location,
                        date_start, event_start, date_end, event_end,
                        event_description, organization
                    ) VALUES (
                        '".$conn->real_escape_string($event_title)."',
                        '".$conn->real_escape_string($event_code)."',
                        '".$conn->real_escape_string($event_location)."',
                        '".$conn->real_escape_string($date_start)."',
                        '".$conn->real_escape_string($event_start)."',
                        '".$conn->real_escape_string($date_end)."',
                        '".$conn->real_escape_string($event_end)."',
                        '".$conn->real_escape_string($event_description)."',
                        '".$conn->real_escape_string($organization)."'
                    )"); 
                } 
            } 

            fclose($csvFile); 
            $res_status = 'success'; 
            $res_msg = 'Events data has been imported successfully.'; 
        } else { 
            $res_status = 'danger'; 
            $res_msg = 'File upload failed. Please try again.'; 
        } 
    } else { 
        $res_status = 'danger'; 
        $res_msg = 'Please upload a valid CSV file.'; 
    } 

    $_SESSION['response'] = array( 
        'status' => $res_status, 
        'msg' => $res_msg 
    ); 
} 

header("Location: ../pages/6_NewEvent.php"); 
exit(); 
?>