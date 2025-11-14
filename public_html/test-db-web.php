<?php
/**
 * Simple Database Test - Web Version
 * Tests if MySQL connection works from web context
 */

// Security check
if (!isset($_GET['allow'])) {
    die("Access denied. Add ?allow=1 to URL to proceed.");
}

header('Content-Type: text/plain; charset=utf-8');

echo "MySQL Connection Test (Web Context)\n";
echo "====================================\n\n";

// Test production credentials
$host = 'localhost';
$database = 'democrm_democrm';
$username = 'democrm_democrm';
$password = 'b3J2sy5T4JNm60';

echo "Testing production database connection...\n";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    echo "✅ Production connection successful!\n\n";
    
    // Check current user
    $stmt = $pdo->query("SELECT USER(), CURRENT_USER()");
    $result = $stmt->fetch(PDO::FETCH_NUM);
    echo "Connected as:\n";
    echo "  USER():         {$result[0]}\n";
    echo "  CURRENT_USER(): {$result[1]}\n\n";
    
} catch (PDOException $e) {
    echo "❌ Production connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Test test credentials
$test_username = 'democrm_test';
$test_password = 'TestDB_2025_Secure!';
$test_database = 'democrm_test';

echo "Testing test database connection...\n";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$test_database;charset=utf8mb4", $test_username, $test_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    echo "✅ Test connection successful!\n\n";
    
    // Check current user
    $stmt = $pdo->query("SELECT USER(), CURRENT_USER()");
    $result = $stmt->fetch(PDO::FETCH_NUM);
    echo "Connected as:\n";
    echo "  USER():         {$result[0]}\n";
    echo "  CURRENT_USER(): {$result[1]}\n\n";
    
} catch (PDOException $e) {
    echo "❌ Test connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    
    if (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "This means the test user either:\n";
        echo "  1. Does not exist\n";
        echo "  2. Has wrong password\n";
        echo "  3. Is not allowed to connect from this host\n\n";
        echo "Please create the user in phpMyAdmin with the SQL from:\n";
        echo "tests/create-test-user-instructions.md\n";
    }
}

echo "\nDone.\n";