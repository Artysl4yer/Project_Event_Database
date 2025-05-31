<?php
session_start();
require_once 'conn.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

header('Content-Type: application/json');

// Check if a file was uploaded
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['file'];

// Check file type
$fileType = pathinfo($file['name'], PATHINFO_EXTENSION);
if ($fileType !== 'csv') {
    echo json_encode(['success' => false, 'message' => 'Please upload a CSV file']);
    exit;
}

// Open the file
$handle = fopen($file['tmp_name'], 'r');
if (!$handle) {
    echo json_encode(['success' => false, 'message' => 'Error opening file']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Skip header row
    fgetcsv($handle);
    
    // Prepare insert statement
    $stmt = $conn->prepare("INSERT INTO student_table (ID, first_name, last_name, Course, Section, Gender, Age, Year, Dept) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $rowCount = 0;
    $errors = [];

    // Read and process each row
    while (($row = fgetcsv($handle)) !== false) {
        // Skip empty rows
        if (empty(array_filter($row))) {
            continue;
        }

        // Validate row data
        if (count($row) !== 9) {
            $errors[] = "Row " . ($rowCount + 2) . ": Invalid number of columns";
            continue;
        }

        // Extract and validate data
        $studentId = trim($row[0]);
        $firstName = ucwords(strtolower(trim($row[1])));
        $lastName = ucwords(strtolower(trim($row[2])));
        $course = strtoupper(trim($row[3]));
        $section = trim($row[4]);
        $gender = ucfirst(strtolower(trim($row[5])));
        $age = (int)trim($row[6]);
        $year = trim($row[7]);
        $dept = strtoupper(trim($row[8]));

        // Validate student ID format (XX-XXXXX)
        if (!preg_match('/^\d{2}-\d{5}$/', $studentId)) {
            $errors[] = "Row " . ($rowCount + 2) . ": Invalid Student ID format (should be XX-XXXXX)";
            continue;
        }

        // Validate age
        if ($age < 15 || $age > 99) {
            $errors[] = "Row " . ($rowCount + 2) . ": Invalid age (should be between 15 and 99)";
            continue;
        }

        // Validate gender
        if (!in_array($gender, ['Male', 'Female', 'Other'])) {
            $errors[] = "Row " . ($rowCount + 2) . ": Invalid gender (should be Male, Female, or Other)";
            continue;
        }

        // Validate year
        if (!in_array($year, ['1', '2', '3', '4'])) {
            $errors[] = "Row " . ($rowCount + 2) . ": Invalid year (should be 1, 2, 3, or 4)";
            continue;
        }

        // Try to insert the row
        try {
            $stmt->bind_param("sssssisis", 
                $studentId, $firstName, $lastName, $course, 
                $section, $gender, $age, $year, $dept
            );
            $stmt->execute();
            $rowCount++;
        } catch (Exception $e) {
            if ($e->getCode() == 1062) { // Duplicate entry
                $errors[] = "Row " . ($rowCount + 2) . ": Duplicate Student ID: " . $studentId;
            } else {
                $errors[] = "Row " . ($rowCount + 2) . ": " . $e->getMessage();
            }
        }
    }

    // If there were any errors, rollback and return error messages
    if (!empty($errors)) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => 'Import failed',
            'errors' => $errors
        ]);
        exit;
    }

    // Commit transaction if successful
    $conn->commit();
    echo json_encode([
        'success' => true,
        'message' => "Successfully imported $rowCount students",
        'rowCount' => $rowCount
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => 'Error during import: ' . $e->getMessage()
    ]);
} finally {
    fclose($handle);
    $stmt->close();
    $conn->close();
}
?>