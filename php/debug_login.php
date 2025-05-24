<?php
include 'conn.php';

// Check if clients table exists
$result = $conn->query("SHOW TABLES LIKE 'clients'");
if ($result->num_rows == 0) {
    echo "Clients table does not exist!<br>";
    exit;
}

// Check table structure
$result = $conn->query("DESCRIBE clients");
echo "Table structure:<br>";
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "<br>";
}

// Check if any users exist
$result = $conn->query("SELECT id, username, email, name, organization FROM clients");
echo "<br>Existing users:<br>";
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . ", Username: " . $row['username'] . 
             ", Email: " . $row['email'] . ", Name: " . $row['name'] . 
             ", Organization: " . $row['organization'] . "<br>";
    }
} else {
    echo "No users found in the table!<br>";
}

// Test a specific login attempt
if (isset($_GET['test_username'])) {
    $username = $_GET['test_username'];
    $stmt = $conn->prepare("SELECT id, username, password FROM clients WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "<br>Testing login for username: " . $username . "<br>";
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        echo "User found!<br>";
        echo "Stored password hash: " . $user['password'] . "<br>";
    } else {
        echo "User not found!<br>";
    }
}

$conn->close();
?> 