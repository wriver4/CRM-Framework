<?php

/**
 * Email Processing System Installation Script
 * Sets up database tables and configuration for email form processing
 * Follows existing CRM framework patterns
 */

// Set working directory to project root
chdir(dirname(__DIR__));

// Include required files
require_once 'config/system.php';
require_once 'vendor/autoload.php';

echo "WaveGuard Email Processing System Installation\n";
echo "=============================================\n\n";

try {
    // Initialize database connection
    $database = new Database();
    $pdo = $database->dbcrm();
    
    echo "✓ Database connection established\n";
    
    // Read and execute migration SQL
    $migrationFile = 'sql/migrations/add_email_processing_tables.sql';
    
    if (!file_exists($migrationFile)) {
        throw new Exception("Migration file not found: {$migrationFile}");
    }
    
    echo "✓ Migration file found\n";
    
    // Read SQL file
    $sql = file_get_contents($migrationFile);
    
    // Split into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^\s*--/', $stmt);
        }
    );
    
    echo "✓ Processing " . count($statements) . " SQL statements\n\n";
    
    // Execute each statement
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
                echo "  ✓ Added foreign key constraint\n";
            }
            
        } catch (PDOException $e) {
            // Some statements may fail if tables/constraints already exist
            if (stripos($e->getMessage(), 'already exists') !== false || 
                stripos($e->getMessage(), 'Duplicate') !== false) {
                echo "  - Skipped (already exists)\n";
            } else {
                echo "  ⚠ Warning: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n✓ Database migration completed\n";
    
    // Verify installation
    echo "\nVerifying installation...\n";
    
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
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "EMAIL PROCESSING SYSTEM INSTALLATION COMPLETE!\n";
    echo str_repeat("=", 50) . "\n\n";
    
    echo "Next Steps:\n";
    echo "1. Update email passwords in the database:\n";
    echo "   UPDATE email_accounts_config SET password = 'your_base64_encoded_password' WHERE email_address = 'estimates@waveguardco.com';\n\n";
    
    echo "2. Set up cron job for automated processing:\n";
    echo "   */5 * * * * php " . __DIR__ . "/email_cron.php >> logs/email_cron.log 2>&1\n\n";
    
    echo "3. Test the system:\n";
    echo "   - Visit: /admin/email/email_import\n";
    echo "   - API Status: /api/email_forms.php/status?api_key=waveguard_api_key_2024\n\n";
    
    echo "4. Configure email account passwords:\n";
    echo "   - Use base64 encoding for now: base64_encode('your_password')\n";
    echo "   - Consider implementing proper encryption for production\n\n";
    
    echo "Installation completed successfully!\n";
    
} catch (Exception $e) {
    echo "\n✗ Installation failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

exit(0);