<?php
include 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $response = ['success' => false, 'message' => ''];
    
    // Check if file is a CSV
    $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($fileType !== 'csv') {
        $response['message'] = 'Only CSV files are allowed.';
        echo json_encode($response);
        exit();
    }
    
    // Check file size (5MB max)
    if ($file['size'] > 5000000) {
        $response['message'] = 'File is too large. Maximum size is 5MB.';
        echo json_encode($response);
        exit();
    }
    
    // Open the CSV file
    if (($handle = fopen($file['tmp_name'], "r")) !== FALSE) {
        $header = fgetcsv($handle); // Get the header row
        
        // Validate required columns
        $requiredColumns = ['ID', 'first_name', 'last_name', 'Course', 'Section', 'Gender', 'Age', 'Year', 'Dept'];
        $headerMap = array_flip($header);
        $missingColumns = array_diff($requiredColumns, array_keys($headerMap));
        
        if (!empty($missingColumns)) {
            $response['message'] = 'Missing required columns: ' . implode(', ', $missingColumns);
            echo json_encode($response);
            exit();
        }
        
        $successCount = 0;
        $errorCount = 0;
        $rowNumber = 2; // Start from row 2 (after header)
        
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            while (($data = fgetcsv($handle)) !== FALSE) {
                if (count($data) !== count($header)) {
                    throw new Exception("Invalid data format at row $rowNumber");
                }
                
                // Map CSV data to columns
                $id = $data[$headerMap['ID']];
                $first_name = $data[$headerMap['first_name']];
                $last_name = $data[$headerMap['last_name']];
                $course = $data[$headerMap['Course']];
                $section = $data[$headerMap['Section']];
                $gender = $data[$headerMap['Gender']];
                $age = $data[$headerMap['Age']];
                $year = $data[$headerMap['Year']];
                $dept = $data[$headerMap['Dept']];
                
                // Validate data
                if (empty($id) || empty($first_name) || empty($last_name) || empty($course) || 
                    empty($section) || empty($gender) || empty($year) || empty($dept)) {
                    throw new Exception("Missing required data at row $rowNumber");
                }
                
                // Check if ID already exists
                $stmt = $conn->prepare("SELECT ID FROM participants_table WHERE ID = ?");
                $stmt->bind_param("s", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    // Update existing record
                    $stmt = $conn->prepare("UPDATE participants_table SET first_name=?, last_name=?, Course=?, Section=?, Gender=?, Age=?, Year=?, Dept=? WHERE ID=?");
                    $stmt->bind_param("sssssssss", $first_name, $last_name, $course, $section, $gender, $age, $year, $dept, $id);
                } else {
                    // Insert new record
                    $stmt = $conn->prepare("INSERT INTO participants_table (ID, first_name, last_name, Course, Section, Gender, Age, Year, Dept) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssssssss", $id, $first_name, $last_name, $course, $section, $gender, $age, $year, $dept);
                }
                
                if ($stmt->execute()) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
                
                $rowNumber++;
            }
            
            $conn->commit();
            $response['success'] = true;
            $response['message'] = "Import completed successfully. $successCount records processed, $errorCount errors.";
            
        } catch (Exception $e) {
            $conn->rollback();
            $response['message'] = 'Error: ' . $e->getMessage();
        }
        
        fclose($handle);
    } else {
        $response['message'] = 'Error opening the file.';
    }
    
    echo json_encode($response);
    exit();
}

// If not POST request, redirect to student table
header('Location: ../pages/7_StudentTable.php');
exit();
?>