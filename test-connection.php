<?php
// Test database connection
require_once 'config/config.php';

echo "<h2>Testing XAMPP Setup</h2>";

// Test 1: Check PHP Version
echo "<h3>1. PHP Version: " . phpversion() . "</h3>";

// Test 2: Check Database Configuration
echo "<h3>2. Database Configuration:</h3>";
echo "DB_HOST: " . DB_HOST . "<br>";
echo "DB_NAME: " . DB_NAME . "<br>";
echo "DB_USER: " . DB_USER . "<br>";

// Test 3: Try to connect
echo "<h3>3. Database Connection Test:</h3>";
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ SUCCESS: Connected to database!<br>";
    
    // Test 4: Check if database exists
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "✅ Database has " . count($tables) . " tables<br>";
        echo "Tables: " . implode(", ", $tables);
    } else {
        echo "⚠️ Database is empty. Import schema.sql<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "<br>";
    echo "<br><strong>Solution:</strong><br>";
    echo "1. Open XAMPP Control Panel<br>";
    echo "2. Start MySQL service<br>";
    echo "3. Import database: mysql -u root -p < database/schema.sql<br>";
}

// Test 5: Check file permissions
echo "<h3>4. File Permissions:</h3>";
$uploads_dir = UPLOAD_DIR;
if (is_dir($uploads_dir)) {
    echo "✅ uploads/ directory exists<br>";
    if (is_writable($uploads_dir)) {
        echo "✅ uploads/ directory is writable<br>";
    } else {
        echo "❌ uploads/ directory is NOT writable<br>";
        echo "Run: chmod 755 uploads/ (Linux/Mac) or check folder permissions (Windows)<br>";
    }
} else {
    echo "❌ uploads/ directory doesn't exist<br>";
    echo "Create it: mkdir uploads<br>";
}

// Test 6: Check required PHP extensions
echo "<h3>5. Required PHP Extensions:</h3>";
$required = ['pdo_mysql', 'session', 'mbstring'];
foreach ($required as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ $ext is loaded<br>";
    } else {
        echo "❌ $ext is NOT loaded<br>";
    }
}
?>