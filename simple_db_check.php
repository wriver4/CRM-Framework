<?php
/**
 * Simple database check without full system initialization
 */

// Set minimal environment
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['DOCUMENT_ROOT'] = '/run/user/1000/gvfs/sftp:host=159.203.116.150,port=222/home/democrm/public_html';

// Direct database connection
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=democrm_democrm;charset=utf8mb4',
        'democrm_democrm',
        'b3J2sy5T4JNm60',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    echo "=== Database Connection: SUCCESS ===\n";
    echo "Database: " . $pdo->query("SELECT DATABASE()")->fetchColumn() . "\n";
    echo "Version: " . $pdo->query("SELECT VERSION()")->fetchColumn() . "\n\n";
    
    // Show all tables
    echo "=== All Tables ===\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    echo "\nTotal tables: " . count($tables) . "\n\n";
    
    // Check specific email tables
    $required_tables = ['email_form_processing', 'crm_sync_queue', 'email_accounts_config'];
    echo "=== Required Email Tables ===\n";
    
    foreach ($required_tables as $table) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM `$table`");
            $stmt->execute();
            $count = $stmt->fetchColumn();
            echo "✅ $table: EXISTS (rows: $count)\n";
        } catch (Exception $e) {
            echo "❌ $table: ERROR - " . $e->getMessage() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Database Connection FAILED: " . $e->getMessage() . "\n";
}
?>