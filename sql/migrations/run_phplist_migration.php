<?php
/**
 * phpList Migration Runner
 * 
 * This script runs the phpList database migration
 * Creates the necessary tables for phpList integration
 */

require_once dirname(dirname(__DIR__)) . '/config/system.php';

// Initialize database connection
$database = new Database();
$pdo = $database->dbcrm();

echo "Starting phpList migration...\n";

try {
    // Read the migration SQL file (use fixed version)
    $migrationFile = __DIR__ . '/create_phplist_subscribers_table_fixed.sql';
    
    if (!file_exists($migrationFile)) {
        throw new Exception("Migration file not found: $migrationFile");
    }
    
    $sql = file_get_contents($migrationFile);
    
    if ($sql === false) {
        throw new Exception("Failed to read migration file");
    }
    
    // Split SQL into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^\s*--/', $stmt);
        }
    );
    
    echo "Found " . count($statements) . " SQL statements to execute\n";
    
    // Begin transaction
    $pdo->beginTransaction();
    
    $executedCount = 0;
    
    foreach ($statements as $statement) {
        if (empty(trim($statement))) {
            continue;
        }
        
        echo "Executing: " . substr(trim($statement), 0, 50) . "...\n";
        
        try {
            $pdo->exec($statement);
            $executedCount++;
        } catch (PDOException $e) {
            // Check if it's a "table already exists" error
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "  Warning: Table already exists, skipping...\n";
                continue;
            } else {
                throw $e;
            }
        }
    }
    
    // Commit transaction
    $pdo->commit();
    
    echo "\nMigration completed successfully!\n";
    echo "Executed $executedCount SQL statements\n";
    
    // Verify tables were created
    echo "\nVerifying table creation...\n";
    
    $tables = ['phplist_subscribers', 'phplist_config', 'phplist_sync_log'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        
        if ($stmt->fetch()) {
            echo "  ✓ Table '$table' exists\n";
        } else {
            echo "  ✗ Table '$table' not found\n";
        }
    }
    
    // Check if configuration data was inserted
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM phplist_config");
    $stmt->execute();
    $configCount = $stmt->fetchColumn();
    
    echo "  ✓ Configuration records: $configCount\n";
    
    echo "\nphpList integration is ready to use!\n";
    echo "Next steps:\n";
    echo "1. Configure phpList settings in the admin panel\n";
    echo "2. Set up the cron job for syncing: */15 * * * * php " . dirname(__DIR__) . "/scripts/phplist_sync.php\n";
    echo "3. Test the integration by creating a new lead with 'get_updates' enabled\n";
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }
    
    echo "\nMigration failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\nMigration completed successfully!\n";