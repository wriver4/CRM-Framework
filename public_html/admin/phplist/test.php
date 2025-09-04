<?php
// Simple test file to debug 500 error
echo "Starting test...\n";

try {
    echo "1. Testing basic PHP...\n";
    
    echo "2. Testing config include...\n";
    require_once dirname(dirname($_SERVER['DOCUMENT_ROOT'])) . '/config/system.php';
    echo "Config loaded successfully\n";
    
    echo "3. Testing database connection...\n";
    $database = new Database();
    $pdo = $database->dbcrm();
    echo "Database connected successfully\n";
    
    echo "4. Testing user authentication...\n";
    $not->loggedin();
    echo "User authentication passed\n";
    
    echo "5. Testing language file...\n";
    $lang = include __DIR__ . '/../languages/en.php';
    echo "Language file loaded successfully\n";
    
    echo "6. Testing migration file path...\n";
    $migrationFile = dirname(dirname(dirname(__DIR__))) . '/sql/migrations/simple_safe_migration.sql';
    echo "Migration file path: $migrationFile\n";
    
    if (file_exists($migrationFile)) {
        echo "Migration file exists\n";
    } else {
        echo "Migration file NOT found\n";
    }
    
    echo "7. Testing template paths...\n";
    $headerPath = dirname(dirname(__DIR__)) . '/templates/header.php';
    echo "Header path: $headerPath\n";
    
    if (file_exists($headerPath)) {
        echo "Header template exists\n";
    } else {
        echo "Header template NOT found\n";
    }
    
    echo "\nAll tests passed!\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}