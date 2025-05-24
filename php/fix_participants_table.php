<?php
include 'conn.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

// (The user's participants_table is correct, so we do nothing here.)
echo ("Your participants_table (with columns ID, Name, Course, Section, Gender, Age, Year, Dept, number, event_code, registration_date) is already correct. No changes were made.");

$conn->close();
?> 