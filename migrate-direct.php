<?php
/**
 * Direct Migration Executor
 */

echo "\n🔄 Applying migration to databases...\n\n";

// Databases to migrate
$databases = [
    [
        'name' => 'democrm_democrm',
        'user' => 'democrm_democrm',
        'pass' => 'b3J2sy5T4JNm60',
        'label' => 'PRODUCTION'
    ],
    [
        'name' => 'democrm_test',
        'user' => 'democrm_test',
        'pass' => 'TestDB_2025_Secure!',
        'label' => 'TEST (persistent)'
    ]
];

foreach ($databases as $db) {
    try {
        // First check if database exists
        $checkPdo = new PDO("mysql:host=localhost", $db['user'], $db['pass']);
        $result = $checkPdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$db['name']}'");
        
        if ($result->rowCount() === 0) {
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            echo "Database: {$db['label']} ({$db['name']})\n";
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
            echo "⊘ Database doesn't exist yet. Will be created on next test run.\n\n";
            continue;
        }
        
        $pdo = new PDO("mysql:host=localhost;dbname={$db['name']}", $db['user'], $db['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "Database: {$db['label']} ({$db['name']})\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // Execute migration statements directly (no parsing needed)
        $statements = [
            // Phase 1: Update roles table
            "ALTER TABLE `roles` CHANGE COLUMN `rid` `role_id` INT (11) NOT NULL",
            "ALTER TABLE `roles` CHANGE COLUMN `rname` `role` VARCHAR(50) NOT NULL",
            "ALTER TABLE `roles` DROP INDEX `rid`, DROP INDEX `rname`, ADD UNIQUE KEY `role_id` (`role_id`), ADD UNIQUE KEY `role` (`role`)",
            
            // Phase 2: Update roles_permissions table
            "ALTER TABLE `roles_permissions` DROP PRIMARY KEY",
            "ALTER TABLE `roles_permissions` CHANGE COLUMN `rid` `role_id` INT (11) NOT NULL",
            "ALTER TABLE `roles_permissions` ADD PRIMARY KEY (`role_id`, `pid`)",
            
            // Phase 3: Update users table
            "ALTER TABLE `users` CHANGE COLUMN `rid` `role_id` INT (10) UNSIGNED NOT NULL"
        ];
        
        $successCount = 0;
        $skipCount = 0;
        
        foreach ($statements as $i => $sql) {
            try {
                $pdo->exec($sql);
                echo "  ✓ Statement " . ($i + 1) . "\n";
                $successCount++;
            } catch (PDOException $e) {
                // If column already exists or doesn't exist, it might be from a previous run
                if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
                    echo "  ⊘ Statement " . ($i + 1) . " (Index already exists - skipped)\n";
                    $skipCount++;
                } elseif (strpos($e->getMessage(), 'Unknown column') !== false) {
                    echo "  ⊘ Statement " . ($i + 1) . " (Column already migrated or doesn't exist)\n";
                    $skipCount++;
                } else {
                    echo "  ❌ Statement " . ($i + 1) . " FAILED\n";
                    echo "     Error: " . $e->getMessage() . "\n";
                    echo "     SQL: " . substr($sql, 0, 80) . "...\n";
                    throw $e;
                }
            }
        }
        
        echo "\n✅ {$db['label']}: {$successCount} executed, {$skipCount} skipped\n\n";
        
    } catch (Exception $e) {
        echo "❌ {$db['label']} MIGRATION FAILED!\n";
        echo "   Error: " . $e->getMessage() . "\n\n";
        exit(1);
    }
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ MIGRATION COMPLETE!\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
?>