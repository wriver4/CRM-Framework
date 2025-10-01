<?php
/**
 * Simple migration runner for database changes
 */

// Database connection settings
$host = 'localhost';
$dbname = 'democrm_democrm';
$username = 'democrm_democrm';
$password = 'b3J2sy5T4JNm60';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Running Database Migrations ===\n";
    
    // Run user timezone migration
    echo "1. Adding timezone column to users table...\n";
    $userMigration = file_get_contents(__DIR__ . '/sql/migrations/2024_12_20_add_user_timezone.sql');
    $pdo->exec($userMigration);
    echo "   ✅ User timezone migration completed\n";
    
    // Run calendar integration migration
    echo "2. Creating calendar tables...\n";
    $calendarMigration = file_get_contents(__DIR__ . '/sql/migrations/2024_12_20_calendar_integration.sql');
    $pdo->exec($calendarMigration);
    echo "   ✅ Calendar integration migration completed\n";
    
    echo "\n=== All Migrations Completed Successfully ===\n";
    
} catch (PDOException $e) {
    echo "Migration Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>