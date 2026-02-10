<?php
// Budget-Procurement Management System - Main Entry Point

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simple redirect to login page
header('Location: login.php');
exit;
?>