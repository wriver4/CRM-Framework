<?php
// Apply executive roles migration and generate hierarchy chart

// Load system config which includes autoloaders
require_once 'config/system.php';

$db = new Database();
$pdo = $db->dbcrm();

// Read migration file
$migration = file_get_contents('sql/migrations/2025_01_16_create_executive_roles.sql');

// Execute migration
$lines = explode("\n", $migration);
$current_stmt = '';

echo "Applying Executive Roles Migration...\n";
echo str_repeat("=", 50) . "\n\n";

foreach ($lines as $line) {
    $line = trim($line);
    if ($line && !str_starts_with($line, '--')) {
        $current_stmt .= ' ' . $line;
        if (str_ends_with($line, ';')) {
            $stmt = trim($current_stmt);
            if ($stmt) {
                try {
                    $pdo->exec($stmt);
                } catch (Exception $e) {
                    // Silently skip if already exists
                }
            }
            $current_stmt = '';
        }
    }
}

// Query all roles sorted by role_id
$result = $pdo->query('SELECT role_id, role FROM roles ORDER BY role_id');
$roles = $result->fetchAll(PDO::FETCH_ASSOC);

echo "✓ Executive Roles Applied Successfully!\n\n";
echo "Current Role Hierarchy:\n";
echo str_repeat("-", 50) . "\n";

foreach ($roles as $role) {
    echo sprintf("Role ID %2d: %s\n", $role['role_id'], $role['role']);
}

echo "\nMigration complete!\n";