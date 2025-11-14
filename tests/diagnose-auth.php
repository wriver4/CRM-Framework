#!/usr/bin/env php
<?php
/**
 * Diagnose MySQL Authentication Issues
 * 
 * This script tests different connection methods and provides detailed diagnostics
 */

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                                                              ║\n";
echo "║           MySQL Authentication Diagnostics                   ║\n";
echo "║                                                              ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

$testUser = 'democrm_test';
$testPass = 'TestDB_2025_Secure!';
$testHost = 'localhost';

echo "Configuration:\n";
echo "  Username: $testUser\n";
echo "  Password: " . str_repeat('*', strlen($testPass)) . "\n";
echo "  Host:     $testHost\n";
echo "\n";

// Check PHP MySQL extensions
echo "═══════════════════════════════════════════════════════════════\n";
echo "Step 1: Checking PHP MySQL Extensions\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$extensions = [
    'pdo' => extension_loaded('pdo'),
    'pdo_mysql' => extension_loaded('pdo_mysql'),
    'mysqli' => extension_loaded('mysqli'),
    'mysqlnd' => extension_loaded('mysqlnd'),
];

foreach ($extensions as $ext => $loaded) {
    echo ($loaded ? "✅" : "❌") . " $ext: " . ($loaded ? "loaded" : "NOT loaded") . "\n";
}
echo "\n";

if (!$extensions['pdo_mysql']) {
    echo "❌ PDO MySQL extension is not loaded. Cannot proceed.\n";
    exit(1);
}

// Test 1: PDO with different DSN formats
echo "═══════════════════════════════════════════════════════════════\n";
echo "Step 2: Testing PDO Connection Methods\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$dsnVariants = [
    'Standard' => "mysql:host=$testHost;charset=utf8mb4",
    'With Port' => "mysql:host=$testHost;port=3306;charset=utf8mb4",
    'Unix Socket' => "mysql:unix_socket=/var/lib/mysql/mysql.sock;charset=utf8mb4",
    'Alt Socket' => "mysql:unix_socket=/tmp/mysql.sock;charset=utf8mb4",
    '127.0.0.1' => "mysql:host=127.0.0.1;charset=utf8mb4",
];

$successfulDsn = null;
$successfulPdo = null;

foreach ($dsnVariants as $name => $dsn) {
    echo "Testing: $name\n";
    echo "  DSN: $dsn\n";
    
    try {
        $pdo = new PDO($dsn, $testUser, $testPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);
        echo "  ✅ SUCCESS!\n\n";
        $successfulDsn = $dsn;
        $successfulPdo = $pdo;
        break; // Stop on first success
    } catch (PDOException $e) {
        echo "  ❌ Failed: " . $e->getMessage() . "\n";
        echo "  Error Code: " . $e->getCode() . "\n\n";
    }
}

if ($successfulPdo) {
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "Step 3: Connection Details\n";
    echo "═══════════════════════════════════════════════════════════════\n\n";
    
    echo "✅ Successfully connected using: $successfulDsn\n\n";
    
    // Get MySQL version
    try {
        $version = $successfulPdo->query('SELECT VERSION()')->fetchColumn();
        echo "MySQL Version: $version\n\n";
    } catch (PDOException $e) {
        echo "Could not get version: " . $e->getMessage() . "\n\n";
    }
    
    // Get current user info
    try {
        $currentUser = $successfulPdo->query('SELECT CURRENT_USER()')->fetchColumn();
        echo "Connected as: $currentUser\n\n";
    } catch (PDOException $e) {
        echo "Could not get current user: " . $e->getMessage() . "\n\n";
    }
    
    // Get authentication plugin info
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "Step 4: Authentication Plugin Information\n";
    echo "═══════════════════════════════════════════════════════════════\n\n";
    
    try {
        $stmt = $successfulPdo->query("SELECT User, Host, plugin, authentication_string FROM mysql.user WHERE User = '$testUser'");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($users)) {
            echo "⚠️  No users found with username '$testUser'\n\n";
        } else {
            foreach ($users as $user) {
                echo "User: {$user['User']}@{$user['Host']}\n";
                echo "  Plugin: {$user['plugin']}\n";
                echo "  Auth String: {$user['authentication_string']}\n\n";
            }
        }
    } catch (PDOException $e) {
        echo "Could not query user table: " . $e->getMessage() . "\n\n";
    }
    
    // Test database operations
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "Step 5: Testing Database Operations\n";
    echo "═══════════════════════════════════════════════════════════════\n\n";
    
    try {
        $successfulPdo->exec("CREATE DATABASE IF NOT EXISTS democrm_test");
        echo "✅ Can create democrm_test database\n";
        
        $successfulPdo->exec("USE democrm_test");
        echo "✅ Can use democrm_test database\n";
        
        $successfulPdo->exec("CREATE TABLE IF NOT EXISTS test_connection (id INT PRIMARY KEY, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
        echo "✅ Can create tables\n";
        
        $successfulPdo->exec("INSERT INTO test_connection (id) VALUES (1) ON DUPLICATE KEY UPDATE created_at = CURRENT_TIMESTAMP");
        echo "✅ Can insert data\n";
        
        $stmt = $successfulPdo->query("SELECT * FROM test_connection");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ Can read data: " . json_encode($row) . "\n";
        
        $successfulPdo->exec("DROP TABLE test_connection");
        echo "✅ Can drop tables\n\n";
        
    } catch (PDOException $e) {
        echo "❌ Database operation failed: " . $e->getMessage() . "\n\n";
    }
    
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "✅ DIAGNOSIS COMPLETE - CONNECTION WORKING!\n";
    echo "═══════════════════════════════════════════════════════════════\n\n";
    
    echo "Recommended DSN for config/testing.php:\n";
    echo "  $successfulDsn\n\n";
    
} else {
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "❌ ALL CONNECTION ATTEMPTS FAILED\n";
    echo "═══════════════════════════════════════════════════════════════\n\n";
    
    echo "Possible issues:\n";
    echo "  1. User 'democrm_test'@'localhost' may not exist\n";
    echo "  2. Password may be incorrect\n";
    echo "  3. User may exist with different host (not 'localhost')\n";
    echo "  4. MySQL server may not be running\n";
    echo "  5. Authentication plugin mismatch\n\n";
    
    echo "Next steps:\n";
    echo "  1. Verify user exists: Run in phpMyAdmin:\n";
    echo "     SELECT User, Host, plugin FROM mysql.user WHERE User = 'democrm_test';\n\n";
    echo "  2. If user doesn't exist, create it:\n";
    echo "     php tests/generate-test-user-sql.php\n\n";
    echo "  3. If user exists with different host, update config/testing.php\n\n";
    
    exit(1);
}