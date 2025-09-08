<?php
/**
 * Quick table existence check
 */

// Load system configuration
require_once __DIR__ . '/config/system.php';

// Initialize database
$database = new Database();
$pdo = $database->dbcrm();

echo "=== Database Table Check ===\n";
echo "Database: " . $pdo->query("SELECT DATABASE()")->fetchColumn() . "\n";
echo "Connection: OK\n\n";

// Show all tables
echo "=== All Tables in Database ===\n";
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    echo "\nTotal tables: " . count($tables) . "\n\n";
} catch (Exception $e) {
    echo "Error getting tables: " . $e->getMessage() . "\n\n";
}

// Check specific email tables
$required_tables = ['email_form_processing', 'crm_sync_queue', 'email_accounts_config'];
echo "=== Required Email Tables Check ===\n";

foreach ($required_tables as $table) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `$table`");
        $stmt->execute();
        $count = $stmt->fetchColumn();
        echo "✅ $table: EXISTS (rows: $count)\n";
    } catch (Exception $e) {
        echo "❌ $table: MISSING - " . $e->getMessage() . "\n";
    }
}

echo "\n=== Database Version ===\n";
try {
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();
    echo "Version: $version\n";
} catch (Exception $e) {
    echo "Error getting version: " . $e->getMessage() . "\n";
}
?>