<?php
include 'conn.php';

// Function to check table structure
function check_table_structure($conn, $table_name) {
    $result = $conn->query("SHOW TABLES LIKE '$table_name'");
    if ($result->num_rows === 0) {
        return [
            'exists' => false,
            'message' => "Table '$table_name' does not exist"
        ];
    }

    $columns = $conn->query("SHOW COLUMNS FROM $table_name");
    $structure = [];
    while ($column = $columns->fetch_assoc()) {
        $structure[] = [
            'field' => $column['Field'],
            'type' => $column['Type'],
            'null' => $column['Null'],
            'key' => $column['Key'],
            'default' => $column['Default'],
            'extra' => $column['Extra']
        ];
    }

    return [
        'exists' => true,
        'columns' => $structure
    ];
}

// Check both tables
$tables = ['event_table', 'participants_table'];
$results = [];

foreach ($tables as $table) {
    $results[$table] = check_table_structure($conn, $table);
}

// Output results
header('Content-Type: application/json');
echo json_encode($results, JSON_PRETTY_PRINT);

$conn->close();
?> 