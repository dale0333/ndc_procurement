<?php
// Budget-Procurement Management System Configuration
// XAMPP MacOS Version

// Database configuration - XAMPP Defaults
define('DB_HOST', 'localhost');
define('DB_NAME', 'procurement_system');
define('DB_USER', 'root');
define('DB_PASS', '');  // XAMPP usually has empty password
define('DB_CHARSET', 'utf8mb4');

// Application configuration
define('APP_NAME', 'Budget-Procurement Management System');
define('APP_URL', 'http://localhost/procurement-system');
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/');
define('MAX_UPLOAD_SIZE', 10485760); // 10MB

// Session configuration for MacOS XAMPP
ini_set('session.save_path', '/Applications/XAMPP/xamppfiles/temp');
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 0 for local development

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timezone
date_default_timezone_set('Asia/Manila');

// Error reporting - Show all errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Security headers (optional)
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
?>