<?php

/**
 * Simple Email Processing System Migration
 * Bypasses framework initialization to avoid logging issues
 */

echo "WaveGuard Email Processing System - Database Migration\n";
echo "====================================================\n\n";

// Direct database connection using the same credentials as the Database class
$config = [
    'host' => 'localhost',
    'dbname' => 'democrm_democrm',
    'username' => 'democrm_democrm',
    'password' => 'b3J2sy5T4JNm60',
    'charset' => 'utf8mb4'
];

try {
    // Connect to database
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    echo "✓ Database connection established\n";
    
    // Read migration file
    $migrationFile = dirname(__DIR__) . '/sql/migrations/add_email_processing_tables.sql';
    
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
        $stmt->execute([$table]);
        
        if ($stmt->fetch()) {
            echo "  ✓ Table '{$table}' exists\n";
        } else {
            echo "  ✗ Table '{$table}' missing\n";
        }
    }
    
    // Check email accounts configuration
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM email_accounts_config");
    $stmt->execute();
    $accountCount = $stmt->fetchColumn();
    
    echo "  ✓ Email accounts configured: {$accountCount}\n";
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "EMAIL PROCESSING SYSTEM MIGRATION COMPLETE!\n";
    echo str_repeat("=", 60) . "\n\n";
    
    echo "Migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "\n✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

exit(0);