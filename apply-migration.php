<?php
/**
 * Database Migration Script
 * Applies the rid -> role_id migration to both production and test databases
 */

// Production database migration
echo "\n🔄 Applying migration to PRODUCTION database...\n";
$productionMigration = applyMigration('democrm_democrm', 'democrm_democrm', 'b3J2sy5T4JNm60');

if (!$productionMigration) {
    echo "❌ Production migration failed!\n";
    exit(1);
}

echo "✅ Production database migrated successfully!\n\n";

// Test database migration
echo "🔄 Checking TEST database...\n";

$testConfig = [
    'persistent' => [
        'name' => 'democrm_test',
        'user' => 'democrm_test',
        'pass' => 'TestDB_2025_Secure!',
    ],
    'ephemeral' => [
        'name' => 'democrm_test_ephemeral',
        'user' => 'democrm_test',
        'pass' => 'TestDB_2025_Secure!',
    ],
];

foreach ($testConfig as $mode => $config) {
    echo "\n🔄 Checking {$mode} test database ({$config['name']})...\n";
    if (databaseExists($config['name'], $config['user'], $config['pass'])) {
        echo "  → Found. Applying migration...\n";
        if (applyMigration($config['name'], $config['user'], $config['pass'])) {
            echo "  ✅ {$mode} test database migrated!\n";
        } else {
            echo "  ⚠️  Failed to migrate {$mode} test database. Will be recreated on next test run.\n";
        }
    } else {
        echo "  → Not found. Will be created on next test run with correct schema.\n";
    }
}

echo "\n✅ Migration script complete!\n\n";

// Functions
function applyMigration($dbName, $user, $pass) {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname={$dbName}", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $sqlFile = '/home/democrm/sql/migrations/2025_01_13_rename_rid_to_role_id.sql';
        
        if (!file_exists($sqlFile)) {
            echo "  ❌ Migration file not found: $sqlFile\n";
            return false;
        }
        
        $sql = file_get_contents($sqlFile);
        
        // Remove multi-line comments first
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        
        // Split by semicolon
        $statements = preg_split('/;(?=(?:[^\']*\'[^\']*\')*[^\']*$)/', $sql);
        
        $executedCount = 0;
        foreach ($statements as $statement) {
            $statement = trim($statement);
            
            // Skip empty lines and comments
            if (empty($statement)) continue;
            if (strpos($statement, '--') === 0) continue;
            
            try {
                $pdo->exec($statement . ';');
                $executedCount++;
            } catch (PDOException $e) {
                // Some statements may fail if already applied, that's okay
                if (strpos($e->getMessage(), 'Duplicate key name') !== false ||
                    strpos($e->getMessage(), 'already exists') !== false) {
                    echo "  ⚠️  Column or index already exists (expected if already migrated)\n";
                    return true;
                }
                echo "  ❌ SQL Error: " . $e->getMessage() . "\n";
                echo "  Statement: " . substr($statement, 0, 100) . "...\n";
                return false;
            }
        }
        
        echo "  ✓ Executed {$executedCount} SQL statements\n";
        return true;
        
    } catch (PDOException $e) {
        echo "  ❌ Connection error: " . $e->getMessage() . "\n";
        return false;
    }
}

function databaseExists($dbName, $user, $pass) {
    try {
        $pdo = new PDO("mysql:host=localhost", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $result = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$dbName}'");
        return $result->rowCount() > 0;
        
    } catch (PDOException $e) {
        return false;
    }
}