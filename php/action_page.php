<?php
include 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
    $search = '%' . $_GET['search'] . '%';
    
    $sql = "SELECT * FROM participants_table 
            WHERE ID LIKE ? 
            OR first_name LIKE ? 
            OR last_name LIKE ? 
            OR Course LIKE ? 
            OR Section LIKE ? 
            OR Gender LIKE ? 
            OR Age LIKE ? 
            OR Year LIKE ? 
            OR Dept LIKE ?
            ORDER BY number DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssss", $search, $search, $search, $search, $search, $search, $search, $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $participants = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $participants[] = [
                'number' => $row['number'],
                'ID' => htmlspecialchars($row['ID']),
                'name' => htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']),
                'Course' => htmlspecialchars($row['Course']),
                'Section' => htmlspecialchars($row['Section']),
                'Gender' => htmlspecialchars($row['Gender']),
                'Age' => $row['Age'],
                'Year' => htmlspecialchars($row['Year']),
                'Dept' => htmlspecialchars($row['Dept'])
            ];
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => $participants]);
    exit();
}

header('Location: ../pages/7_StudentTable.php');
exit();
?> 