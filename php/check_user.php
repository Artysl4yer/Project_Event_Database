<?php
require_once 'conn.php';

// First, check if the role column exists
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
if ($result->num_rows === 0) {
    // Add role column if it doesn't exist
    $conn->query("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'student'");
    echo "Added role column to users table\n";
}

// Get all users from the database
$query = "SELECT student_id, email, password, role FROM users";
$result = $conn->query($query);

echo "<h2>Users in Database:</h2>";
echo "<pre>";
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "Student ID: " . $row['student_id'] . "\n";
        echo "Email: " . $row['email'] . "\n";
        echo "Current Role: " . ($row['role'] ?? 'not set') . "\n";
        echo "Password Hash: " . $row['password'] . "\n";
        echo "----------------------------------------\n";
    }
} else {
    echo "No users found in database\n";
}
echo "</pre>";

// Form to update user role
echo "<h2>Update User Role</h2>";
echo "<form method='POST'>";
echo "<select name='student_id'>";
$result->data_seek(0);
while ($row = $result->fetch_assoc()) {
    echo "<option value='" . htmlspecialchars($row['student_id']) . "'>" . 
         htmlspecialchars($row['student_id']) . " (" . htmlspecialchars($row['email']) . ")</option>";
}
echo "</select>";
echo "<select name='role'>";
echo "<option value='student'>Student</option>";
echo "<option value='coordinator'>Coordinator</option>";
echo "<option value='admin'>Admin</option>";
echo "</select>";
echo "<input type='submit' name='update_role' value='Update Role'>";
echo "</form>";

// Handle role update
if (isset($_POST['update_role'])) {
    $student_id = $_POST['student_id'];
    $role = $_POST['role'];
    
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE student_id = ?");
    $stmt->bind_param("ss", $role, $student_id);
    
    if ($stmt->execute()) {
        echo "<p style='color: green;'>Role updated successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error updating role: " . $conn->error . "</p>";
    }
}

// Test password verification
$test_password = "dar";
echo "<h2>Testing Password Verification:</h2>";
echo "<pre>";
$result->data_seek(0);
while ($row = $result->fetch_assoc()) {
    echo "Testing for user: " . $row['email'] . "\n";
    echo "Password 'dar' verification: " . (password_verify($test_password, $row['password']) ? "SUCCESS" : "FAILED") . "\n";
    echo "----------------------------------------\n";
}
echo "</pre>";
?> 