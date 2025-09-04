<?php
/**
 * Simple table structure check without loading full system
 */

// Database connection details (from Database.php)
$host = 'localhost';
$database = 'democrm_democrm';
$username = 'democrm_democrm';
$password = 'b3J2sy5T4JNm60';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    echo "Connected to database successfully\n\n";
    
    // Check leads table structure
    echo "=== LEADS TABLE STRUCTURE ===\n";
    $stmt = $pdo->prepare("DESCRIBE leads");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    foreach ($columns as $column) {
        echo "  {$column['Field']}: {$column['Type']} {$column['Key']}\n";
    }
    echo "\n";
    
    // Check contacts table structure
    echo "=== CONTACTS TABLE STRUCTURE ===\n";
    $stmt = $pdo->prepare("DESCRIBE contacts");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    foreach ($columns as $column) {
        echo "  {$column['Field']}: {$column['Type']} {$column['Key']}\n";
    }
    echo "\n";
    
    // Check if phplist tables exist
    $tables = ['phplist_subscribers', 'phplist_config', 'phplist_sync_log'];
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        if ($stmt->fetch()) {
            echo "Table $table EXISTS\n";
        } else {
            echo "Table $table does NOT exist\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}