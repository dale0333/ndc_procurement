<?php
// Setup Script for Budget-Procurement Management System
// Run this once after database import

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Budget-Procurement System Setup</h2>";

// Check if we can connect to database
require_once 'config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to database<br>";
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() == 0) {
        echo "❌ Users table doesn't exist. Please import database/schema.sql first.<br>";
        exit;
    }
    
    echo "✅ Users table exists<br>";
    
    // Generate correct password hash for 'admin123'
    $correct_hash = password_hash('admin123', PASSWORD_DEFAULT);
    
    echo "<br><strong>Resetting all user passwords to 'admin123'...</strong><br>";
    
    // Update all users with correct password hash
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ?");
    $stmt->execute([$correct_hash]);
    
    $affected = $stmt->rowCount();
    echo "✅ Updated passwords for $affected users<br><br>";
    
    // Show updated users
    $stmt = $pdo->query("SELECT username, role, department FROM users ORDER BY id");
    
    echo "<h3>Current Users (Password: admin123 for all):</h3>";
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr><th>Username</th><th>Role</th><th>Department</th></tr>";
    
    while ($row = $stmt->fetch()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . htmlspecialchars($row['role']) . "</td>";
        echo "<td>" . htmlspecialchars($row['department']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<br><div style='background: #d4edda; padding: 15px; border-radius: 5px;'>";
    echo "<strong>✅ SETUP COMPLETE!</strong><br>";
    echo "You can now login with:<br>";
    echo "- Username: <strong>admin</strong><br>";
    echo "- Password: <strong>admin123</strong><br>";
    echo "</div>";
    
    echo "<br><a href='login.php' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Go to Login Page</a>";
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Check your database configuration in config/config.php<br>";
}
?>