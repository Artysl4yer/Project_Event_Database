<?php
// Start output buffering first
ob_start();

// Then set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable display_errors to prevent HTML output
ini_set('log_errors', 1); // Enable error logging instead
ini_set('error_log', '../logs/php_errors.log'); // Set error log file

header('Content-Type: application/json');

try {
    // Your database connection
    include 'conn.php';
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Validate required fields
    $required_fields = ['event_title', 'event_description', 'event_venue', 'organization', 
                       'event_date', 'event_time', 'event_duration', 'registration_deadline'];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Handle file upload if present
    $file_name = null;
    if (isset($_FILES['event-image']) && $_FILES['event-image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/events/';
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                throw new Exception("Failed to create upload directory");
            }
        }

        $file_name = time() . '_' . basename($_FILES['event-image']['name']);
        $target_path = $upload_dir . $file_name;

        if (!move_uploaded_file($_FILES['event-image']['tmp_name'], $target_path)) {
            throw new Exception("Failed to upload image: " . error_get_last()['message']);
        }
    }

    // Generate or get event code and ensure uniqueness
    $event_code = !empty($_POST['event_code']) ? $_POST['event_code'] : null;
    
    // Function to generate a unique event code
    function generateUniqueEventCode($conn) {
        do {
            $code = 'EVT' . date('Ymd') . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            $check = $conn->prepare("SELECT 1 FROM event_table WHERE event_code = ? UNION SELECT 1 FROM archive_table WHERE event_code = ? LIMIT 1");
            if (!$check) {
                throw new Exception("Failed to prepare event code check: " . $conn->error);
            }
            $check->bind_param("ss", $code, $code);
            $check->execute();
            $check->store_result();
            $exists = $check->num_rows > 0;
            $check->close();
        } while ($exists);
        return $code;
    }

    // If no event code provided or if provided code exists, generate a new one
    if (!$event_code) {
        $event_code = generateUniqueEventCode($conn);
    } else {
        // Check if user-supplied code exists
        $check = $conn->prepare("SELECT 1 FROM event_table WHERE event_code = ? UNION SELECT 1 FROM archive_table WHERE event_code = ? LIMIT 1");
        if (!$check) {
            throw new Exception("Failed to prepare event code check: " . $conn->error);
        }
        $check->bind_param("ss", $event_code, $event_code);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $event_code = generateUniqueEventCode($conn);
        }
        $check->close();
    }

    // Combine date and time for start
    $event_date_str = $_POST['event_date'];
    $event_time_str = $_POST['event_time'];
    $start_datetime_str = $event_date_str . ' ' . $event_time_str;
    
    try {
        // Create DateTime objects with Asia/Manila timezone
        $start_datetime_obj = new DateTime($start_datetime_str, new DateTimeZone('Asia/Manila'));
        if (!$start_datetime_obj) {
            throw new Exception('Invalid event start date or time format.');
        }

        $event_duration = intval($_POST['event_duration']);
        if ($event_duration < 1) {
            throw new Exception('Event duration must be at least 1 hour.');
        }

        $end_datetime_obj = clone $start_datetime_obj;
        $end_datetime_obj->add(new DateInterval('PT' . $event_duration . 'H'));

        // Format registration deadline with Asia/Manila timezone
        $registration_deadline_obj = new DateTime($_POST['registration_deadline'], new DateTimeZone('Asia/Manila'));
        if (!$registration_deadline_obj) {
            throw new Exception('Invalid registration deadline format.');
        }
        
        // Prepare date strings for database insertion - all in Asia/Manila timezone
        $start_datetime_for_db = $start_datetime_obj->format('Y-m-d H:i:s');
        $end_datetime_for_db = $end_datetime_obj->format('Y-m-d H:i:s');
        $registration_deadline = $registration_deadline_obj->format('Y-m-d H:i:s');

        // Store values in variables before binding
        $event_title = $_POST['event_title'];
        $event_description = $_POST['event_description'];
        $event_venue = $_POST['event_venue'];
        $organization = $_POST['organization'];
        $event_status = 'scheduled';
        $registration_status = 'open';

        // Prepare and execute the SQL
        $sql = "INSERT INTO event_table (
                event_title, event_description, event_location, organization,
                event_start, event_end, event_code, registration_deadline, event_image,
                event_status, registration_status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database prepare error: " . $conn->error);
        }

        $stmt->bind_param("sssssssssss",
            $event_title,
            $event_description,
            $event_venue,
            $organization,
            $start_datetime_for_db,
            $end_datetime_for_db,
            $event_code,
            $registration_deadline,
            $file_name,
            $event_status,
            $registration_status
        );

        if (!$stmt->execute()) {
            throw new Exception("Database execute error: " . $stmt->error);
        }

        $event_id = $conn->insert_id;

        // Insert into archive_table as well
        // Define defaults for potentially missing archive fields
        $last_status_update = date('Y-m-d H:i:s'); // Current timestamp
        $auto_close_registration = 0; // Default to false (0)
        $notes = ''; // Default to empty string

        $archive_sql = "INSERT INTO archive_table (
            event_code, event_title, event_location, date_start, event_start, date_end, event_end, event_description, event_status, organization, event_image, registration_status, last_status_update, auto_close_registration, registration_deadline, notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $archive_stmt = $conn->prepare($archive_sql);
        if (!$archive_stmt) {
            throw new Exception("Failed to prepare archive insert: " . $conn->error);
        }
        $archive_stmt->bind_param(
            "ssssssssssssssis",
            $event_code,
            $event_title,
            $event_venue,
            $_POST['event_date'],
            $start_datetime_for_db,
            $_POST['event_date'], // This is just date part for archive, might need review if archive needs full datetime
            $end_datetime_for_db,
            $event_description,
            $event_status,
            $organization,
            $file_name,
            $registration_status,
            $last_status_update,
            $auto_close_registration,
            $registration_deadline,
            $notes
        );
        if (!$archive_stmt->execute()) {
            throw new Exception("Failed to insert into archive table: " . $archive_stmt->error);
        }
        $archive_stmt->close();

        // Handle course restrictions
        if (isset($_POST['course_restrictions']) && is_array($_POST['course_restrictions'])) {
            include 'course_restrictions.php';
            saveEventCourseRestrictions($event_id, $_POST['course_restrictions']);
        }

        // Clean output buffer before sending JSON
        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Event created successfully',
            'event_id' => $event_id
        ]);

    } catch (Exception $e) {
        // Clean output buffer before sending JSON error
        ob_clean();
        error_log("Error creating event: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    } catch (Error $e) {
        // Handle PHP 7+ errors
        ob_clean();
        error_log("PHP Error creating event: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'An internal server error occurred: ' . $e->getMessage()
        ]);
    } catch (Throwable $e) {
        // Handle any other throwable errors
        ob_clean();
        error_log("Unexpected error creating event: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'An unexpected error occurred: ' . $e->getMessage()
        ]);
    }

} catch (Exception $e) {
    // Clean output buffer before sending JSON error
    ob_clean();
    error_log("Error creating event: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (Error $e) {
    // Handle PHP 7+ errors
    ob_clean();
    error_log("PHP Error creating event: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An internal server error occurred: ' . $e->getMessage()
    ]);
} catch (Throwable $e) {
    // Handle any other throwable errors
    ob_clean();
    error_log("Unexpected error creating event: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred: ' . $e->getMessage()
    ]);
}

$conn->close();
?> 