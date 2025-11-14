<?php
/**
 * Test Database Connection - Web Accessible
 * 
 * This script can be run from a web browser to test the database connection
 * URL: https://your-domain.com/test-db-connection.php
 * 
 * SECURITY: Delete this file after testing!
 */

// Prevent running in production accidentally
$allowedIPs = ['127.0.0.1', '::1']; // Add your IP if needed
$clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// Comment out the IP check if you need to run from browser
// if (!in_array($clientIP, $allowedIPs) && php_sapi_name() !== 'cli') {
//     die('Access denied. This script can only be run from localhost or CLI.');
// }

header('Content-Type: text/plain; charset=utf-8');

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                                                              ║\n";
echo "║           Database Connection Test (Server-Side)             ║\n";
echo "║                                                              ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

echo "Client IP: $clientIP\n";
echo "SAPI: " . php_sapi_name() . "\n\n";

// Test 1: Production Database
echo "═══════════════════════════════════════════════════════════════\n";
echo "Test 1: Production Database Connection\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$prodHost = 'localhost';
$prodDatabase = 'democrm_democrm';
$prodUsername = 'democrm_democrm';
$prodPassword = 'b3J2sy5T4JNm60';

try {
    $dsn = "mysql:host=$prodHost;dbname=$prodDatabase;charset=utf8mb4";
    $pdo = new PDO($dsn, $prodUsername, $prodPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    echo "✅ Production connection successful!\n\n";
    
    $currentUser = $pdo->query('SELECT CURRENT_USER()')->fetchColumn();
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();
    
    echo "  Current User: $currentUser\n";
    echo "  MySQL Version: $version\n\n";
    
} catch (PDOException $e) {
    echo "❌ Production connection failed!\n";
    echo "  Error: " . $e->getMessage() . "\n";
    echo "  Code: " . $e->getCode() . "\n\n";
    $pdo = null;
}

// Test 2: Test Database User
echo "═══════════════════════════════════════════════════════════════\n";
echo "Test 2: Test Database User Connection\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$testHost = 'localhost';
$testUsername = 'democrm_test';
$testPassword = 'TestDB_2025_Secure!';

try {
    $dsn = "mysql:host=$testHost;charset=utf8mb4";
    $testPdo = new PDO($dsn, $testUsername, $testPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    echo "✅ Test user connection successful!\n\n";
    
    $currentUser = $testPdo->query('SELECT CURRENT_USER()')->fetchColumn();
    echo "  Current User: $currentUser\n\n";
    
    // Test database operations
    echo "Testing database operations...\n";
    
    $testPdo->exec("CREATE DATABASE IF NOT EXISTS democrm_test");
    echo "  ✅ Can create democrm_test database\n";
    
    $testPdo->exec("USE democrm_test");
    echo "  ✅ Can use democrm_test database\n";
    
    $testPdo->exec("CREATE TABLE IF NOT EXISTS test_table (id INT PRIMARY KEY, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    echo "  ✅ Can create tables\n";
    
    $testPdo->exec("INSERT INTO test_table (id) VALUES (1) ON DUPLICATE KEY UPDATE created_at = CURRENT_TIMESTAMP");
    echo "  ✅ Can insert data\n";
    
    $stmt = $testPdo->query("SELECT * FROM test_table");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "  ✅ Can read data\n";
    
    $testPdo->exec("DROP TABLE test_table");
    echo "  ✅ Can drop tables\n\n";
    
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "✅ ALL TESTS PASSED!\n";
    echo "═══════════════════════════════════════════════════════════════\n\n";
    
    echo "The test database user is working correctly.\n";
    echo "You can now run the test suite.\n\n";
    
} catch (PDOException $e) {
    echo "❌ Test user connection failed!\n";
    echo "  Error: " . $e->getMessage() . "\n";
    echo "  Code: " . $e->getCode() . "\n\n";
    
    if ($pdo) {
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "Checking MySQL User Table\n";
        echo "═══════════════════════════════════════════════════════════════\n\n";
        
        try {
            $stmt = $pdo->query("SELECT User, Host, plugin FROM mysql.user WHERE User LIKE 'democrm%'");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "Existing democrm users:\n\n";
            foreach ($users as $user) {
                echo "  • {$user['User']}@{$user['Host']} (plugin: {$user['plugin']})\n";
            }
            echo "\n";
            
            $stmt = $pdo->query("SELECT User, Host FROM mysql.user WHERE User = 'democrm_test'");
            $testUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($testUsers)) {
                echo "❌ User 'democrm_test' does NOT exist!\n\n";
                echo "ACTION REQUIRED:\n";
                echo "  1. Run: php tests/generate-test-user-sql.php\n";
                echo "  2. Execute the generated SQL in phpMyAdmin\n\n";
            } else {
                echo "✅ User 'democrm_test' exists with these hosts:\n";
                foreach ($testUsers as $user) {
                    echo "  • {$user['User']}@{$user['Host']}\n";
                }
                echo "\n";
                echo "⚠️  User exists but connection failed.\n";
                echo "    This might be a password or privilege issue.\n\n";
            }
            
        } catch (PDOException $e) {
            echo "Could not query user table: " . $e->getMessage() . "\n\n";
        }
    }
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "⚠️  SECURITY WARNING\n";
echo "═══════════════════════════════════════════════════════════════\n\n";
echo "This file contains database credentials and should be deleted\n";
echo "after testing is complete!\n\n";
echo "Delete this file: public_html/test-db-connection.php\n\n";