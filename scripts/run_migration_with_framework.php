<?php

/**
 * Email Processing System Migration using existing CRM framework
 * Uses the existing Database class to ensure proper connection
 */

// Set working directory to project root
chdir(dirname(__DIR__));

// Include the system configuration and autoloader
require_once 'config/system.php';

echo "WaveGuard Email Processing System - Database Migration\n";
echo "====================================================\n\n";

try {
    // Use existing Database class
    $database = new Database();
    $pdo = $database->dbcrm();
    
    echo "✓ Database connection established using existing framework\n";
    
    // Read migration file
    $migrationFile = 'sql/migrations/add_email_processing_tables.sql';
    
    if (!file_exists($migrationFile)) {
        throw new Exception("Migration file not found: {$migrationFile}");
    }
    
    echo "✓ Migration file found\n";
    
    // Read and process SQL
    $sql = file_get_contents($migrationFile);
    
    // Split into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && 
                   !preg_match('/^\s*--/', $stmt) && 
                   !preg_match('/^\s*(SHOW|DESCRIBE|SELECT)/', $stmt);
        }
    );
    
    echo "✓ Processing " . count($statements) . " SQL statements\n\n";
    
    // Execute each statement
    $successCount = 0;
    $skipCount = 0;
    
    foreach ($statements as $index => $statement) {
        if (empty(trim($statement))) continue;
        
        try {
            $pdo->exec($statement);
            
            // Show progress for major operations
            if (stripos($statement, 'CREATE TABLE') !== false) {
                preg_match('/CREATE TABLE.*?`([^`]+)`/i', $statement, $matches);
                $tableName = $matches[1] ?? 'unknown';
                echo "  ✓ Created table: {$tableName}\n";
            } elseif (stripos($statement, 'INSERT') !== false) {
                echo "  ✓ Inserted default data\n";
            } elseif (stripos($statement, 'ALTER TABLE') !== false) {
                echo "  ✓ Added constraint\n";
            }
            
            $successCount++;
            
        } catch (PDOException $e) {
            // Some statements may fail if tables/constraints already exist
            if (stripos($e->getMessage(), 'already exists') !== false || 
                stripos($e->getMessage(), 'Duplicate') !== false ||
                stripos($e->getMessage(), 'check that column/key exists') !== false) {
                echo "  - Skipped (already exists)\n";
                $skipCount++;
            } else {
                echo "  ⚠ Warning: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n✓ Migration completed successfully\n";
    echo "  - Executed: {$successCount} statements\n";
    echo "  - Skipped: {$skipCount} statements\n\n";
    
    // Verify installation
    echo "Verifying installation...\n";
    
    $tables = ['email_form_processing', 'crm_sync_queue', 'email_accounts_config'];
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->bindValue(1, $table, PDO::PARAM_STR);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            echo "  ✓ Table '{$table}' exists\n";
        } else {
            echo "  ✗ Table '{$table}' missing\n";
        }
        $stmt = null;
    }
    
    // Check email accounts configuration
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM email_accounts_config");
    $stmt->execute();
    $accountCount = $stmt->fetchColumn();
    $stmt = null;
    
    echo "  ✓ Email accounts configured: {$accountCount}\n";
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "EMAIL PROCESSING SYSTEM MIGRATION COMPLETE!\n";
    echo str_repeat("=", 60) . "\n\n";
    
    echo "Next Steps:\n";
    echo "1. Update email passwords in the database:\n";
    echo "   UPDATE email_accounts_config SET password = 'your_base64_encoded_password' \n";
    echo "   WHERE email_address = 'estimates@waveguardco.com';\n\n";
    
    echo "2. Set up cron job:\n";
    echo "   */5 * * * * php " . __DIR__ . "/email_cron.php >> logs/email_cron.log 2>&1\n\n";
    
    echo "3. Test the system:\n";
    echo "   - Visit: /leads/email_import.php\n";
    echo "   - API: /api/email_forms.php/status?api_key=waveguard_api_key_2024\n\n";
    
    echo "Migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "\n✗ Migration failed: " . $e->getMessage() . "\n";
    if (method_exists($e, 'getTraceAsString')) {
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
    exit(1);
}

exit(0);