<?php
// Prevent any output before headers
ob_start();

// Disable error display but enable logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/login_errors.log');

// Set error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error [$errno]: $errstr in $errfile on line $errline");
    return true;
});

// Set exception handler
set_exception_handler(function($e) {
    error_log("Uncaught Exception: " . $e->getMessage());
    sendResponse(false, "An unexpected error occurred: " . $e->getMessage());
});

session_start();
require_once '../php/conn.php';

// Set header to return JSON
header('Content-Type: application/json');

// Function to send JSON response
function sendResponse($success, $message, $redirect = null) {
    // Clear any previous output
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Set headers
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    
    // Log the response
    error_log("Sending response - Success: " . ($success ? 'true' : 'false') . ", Message: " . $message);
    
    // Send response
    $response = ['success' => $success, 'message' => $message];
    if ($redirect !== null) {
        $response['redirect'] = $redirect;
    }
    echo json_encode($response);
    exit;
}

try {
    // Log the request
    error_log("Login/Register request received: " . print_r($_POST, true));

    if (isset($_POST['register']) && ($_POST['register'] === '1' || $_POST['register'] === 'Register')) {
        // Validate required fields
        $required_fields = ['firstname', 'lastname', 'student_id', 'email', 'password'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                sendResponse(false, 'All fields are required.');
            }
        }

        // Combine first and last name
        $name = trim($_POST['firstname']) . ' ' . trim($_POST['lastname']);
        $student_id = str_replace('-', '', $_POST['student_id']);
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Check database connection
        if ($conn->connect_error) {
            error_log("Database connection failed: " . $conn->connect_error);
            sendResponse(false, 'Database connection failed. Please try again later.');
        }

        // Check if email or student_id exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR student_id = ?");
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            sendResponse(false, 'An error occurred during registration. Please try again.');
        }

        $stmt->bind_param("ss", $email, $student_id);
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            sendResponse(false, 'An error occurred during registration. Please try again.');
        }

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            sendResponse(false, 'Email or Student ID already exists.');
        }

        // Insert the new user
        $stmt = $conn->prepare("INSERT INTO users (name, email, student_id, password, role) VALUES (?, ?, ?, ?, 'student')");
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            sendResponse(false, 'An error occurred during registration. Please try again.');
        }

        $stmt->bind_param("ssss", $name, $email, $student_id, $password);
        
        if ($stmt->execute()) {
            sendResponse(true, 'Registration successful! Please login.');
        } else {
            error_log("Insert failed: " . $stmt->error);
            sendResponse(false, 'Registration failed. Please try again.');
        }
    }

    if (isset($_POST['login']) || isset($_POST['identifier'])) {
        // Validate required login fields
        if (!isset($_POST['identifier']) || !isset($_POST['password']) || 
            empty($_POST['identifier']) || empty($_POST['password'])) {
            sendResponse(false, 'Both identifier and password are required.');
        }

        $identifier = str_replace('-', '', $_POST['identifier']);
        $password = $_POST['password'];

        // Check database connection
        if ($conn->connect_error) {
            error_log("Database connection failed: " . $conn->connect_error);
            sendResponse(false, 'Database connection failed. Please try again later.');
        }

        // Check student_id or email
        $stmt = $conn->prepare("SELECT * FROM users WHERE student_id = ? OR email = ?");
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            sendResponse(false, 'An error occurred during login. Please try again.');
        }

        $stmt->bind_param("ss", $identifier, $identifier);
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            sendResponse(false, 'Login query failed. Please try again.');
        }

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['name'] = $user['name'];
                $_SESSION['student_id'] = $user['student_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                $redirect = '';
                switch ($user['role']) {
                    case 'admin':
                        $redirect = '../pages/admin-home.php';
                        break;
                    case 'student':
                        $redirect = '../pages/student-home.php';
                        break;
                    case 'coordinator':
                        $redirect = '../pages/4_Event.php';
                        break;
                    default:
                        $redirect = '../pages/student-home.php';
                }
                
                sendResponse(true, 'Login successful', $redirect);
            }
        }

        sendResponse(false, 'Invalid Student ID/Email or password.');
    }

    // If no valid action was specified
    sendResponse(false, 'Invalid request');
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    sendResponse(false, 'An error occurred. Please try again.');
} finally {
    // Close database connection if it exists
    if (isset($conn)) {
        $conn->close();
    }
}
?>
