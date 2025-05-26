<?php
// Database configuration
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'event_database');

// Site configuration
if (!defined('SITE_NAME')) define('SITE_NAME', 'PLP Event Management System');
if (!defined('SITE_URL')) define('SITE_URL', 'http://localhost/Project_Event_Database');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Time zone
date_default_timezone_set('Asia/Manila');

// Helper function to get base URL
function get_base_url() {
    return SITE_URL;
}
?> 