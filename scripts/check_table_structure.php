<?php
/**
 * Check existing table structure to fix foreign key issues
 */

require_once dirname(__DIR__) . '/config/system.php';

$database = new Database();
$pdo = $database->dbcrm();

echo "Checking existing table structures...\n\n";

// Check leads table
try {
    $stmt = $pdo->prepare("SHOW CREATE TABLE leads");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "=== LEADS TABLE ===\n";
    echo $result['Create Table'] . "\n\n";
} catch (Exception $e) {
    echo "Error checking leads table: " . $e->getMessage() . "\n\n";
}

// Check contacts table
try {
    $stmt = $pdo->prepare("SHOW CREATE TABLE contacts");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "=== CONTACTS TABLE ===\n";
    echo $result['Create Table'] . "\n\n";
} catch (Exception $e) {
    echo "Error checking contacts table: " . $e->getMessage() . "\n\n";
}

// Check if phplist tables already exist
$tables = ['phplist_subscribers', 'phplist_config', 'phplist_sync_log'];
foreach ($tables as $table) {
    try {
        $stmt = $pdo->prepare("SHOW CREATE TABLE $table");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "=== " . strtoupper($table) . " TABLE (EXISTS) ===\n";
        echo $result['Create Table'] . "\n\n";
    } catch (Exception $e) {
        echo "Table $table does not exist\n";
    }
}