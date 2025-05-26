<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// Test if tables exist
$tables = ['event_table', 'participants_table'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows == 0) {
        die("Table '$table' does not exist in database '" . DB_NAME . "'");
    }
}

// Add file column if it doesn't exist
$check_column = $conn->query("SHOW COLUMNS FROM event_table LIKE 'file'");
if ($check_column->num_rows == 0) {
    $alter_query = "ALTER TABLE event_table ADD COLUMN file VARCHAR(255) DEFAULT '../images-icon/plm_courtyard.png'";
    if (!$conn->query($alter_query)) {
        die("Error adding file column: " . $conn->error);
    }
}

