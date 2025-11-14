<?php

/**
 * Verify Test Database User
 * 
 * This script helps diagnose test database connection issues
 */

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                                                              ║\n";
echo "║           TEST USER VERIFICATION                             ║\n";
echo "║                                                              ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

$testUser = 'democrm_test';
$testPass = 'TestDB_2025_Secure!';
$testHost = 'localhost';

echo "Testing connection with:\n";
echo "  Username: $testUser\n";
echo "  Password: " . str_repeat('*', strlen($testPass)) . "\n";
echo "  Host:     $testHost\n";
echo "\n";

// Test 1: Try to connect
echo "Test 1: Attempting connection...\n";
try {
    $dsn = "mysql:host=$testHost;charset=utf8mb4";
    $pdo = new PDO($dsn, $testUser, $testPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "✅ Connection successful!\n\n";
    
    // Test 2: Check privileges
    echo "Test 2: Checking privileges...\n";
    $stmt = $pdo->query("SHOW GRANTS FOR CURRENT_USER()");
    $grants = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Current grants:\n";
    foreach ($grants as $grant) {
        echo "  • $grant\n";
    }
    echo "\n";
    
    // Test 3: Try to create database
    echo "Test 3: Testing CREATE DATABASE privilege...\n";
    try {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS democrm_test");
        echo "✅ Can create democrm_test database\n";
        
        // Test 4: Try to use database
        echo "Test 4: Testing USE database...\n";
        $pdo->exec("USE democrm_test");
        echo "✅ Can use democrm_test database\n";
        
        // Test 5: Try to create table
        echo "Test 5: Testing CREATE TABLE privilege...\n";
        $pdo->exec("CREATE TABLE IF NOT EXISTS test_table (id INT PRIMARY KEY)");
        echo "✅ Can create tables\n";
        
        // Cleanup
        $pdo->exec("DROP TABLE IF EXISTS test_table");
        
    } catch (PDOException $e) {
        echo "❌ Database operations failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
    echo "╔══════════════════════════════════════════════════════════════╗\n";
    echo "║                                                              ║\n";
    echo "║           ✅ VERIFICATION COMPLETE                           ║\n";
    echo "║                                                              ║\n";
    echo "╚══════════════════════════════════════════════════════════════╝\n";
    echo "\n";
    
} catch (PDOException $e) {
    echo "❌ Connection failed!\n\n";
    echo "Error details:\n";
    echo "  Code:    " . $e->getCode() . "\n";
    echo "  Message: " . $e->getMessage() . "\n";
    echo "\n";
    
    echo "╔══════════════════════════════════════════════════════════════╗\n";
    echo "║                                                              ║\n";
    echo "║           ⚠️  ACTION REQUIRED                                ║\n";
    echo "║                                                              ║\n";
    echo "╚══════════════════════════════════════════════════════════════╝\n";
    echo "\n";
    echo "The test database user needs to be created. Please:\n\n";
    echo "1. Create user in CWP:\n";
    echo "   Username: democrm_test\n";
    echo "   Password: TestDB_2025_Secure!\n";
    echo "\n";
    echo "2. Run these SQL statements in phpMyAdmin:\n\n";
    echo "   GRANT ALL PRIVILEGES ON `democrm_test`.* TO 'democrm_test'@'localhost';\n";
    echo "   GRANT ALL PRIVILEGES ON `democrm_test_%`.* TO 'democrm_test'@'localhost';\n";
    echo "   GRANT CREATE, DROP ON *.* TO 'democrm_test'@'localhost';\n";
    echo "   FLUSH PRIVILEGES;\n";
    echo "\n";
    
    exit(1);
}