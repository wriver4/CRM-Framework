<?php

/**
 * Test Database Setup Script
 * 
 * Creates and configures test database for PHPUnit and Playwright tests
 * 
 * Usage:
 *   php tests/setup-test-database.php [--mode=persistent|ephemeral] [--seed=minimal|standard|full]
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Parse command line arguments
$options = getopt('', ['mode::', 'seed::', 'reset', 'destroy', 'help']);

if (isset($options['help'])) {
    echo <<<HELP

Test Database Setup Script
===========================

Usage:
  php tests/setup-test-database.php [options]

Options:
  --mode=MODE       Database mode: persistent (default) or ephemeral
  --seed=DATASET    Seed dataset: minimal, standard (default), or full
  --reset           Reset existing test database
  --destroy         Destroy test database
  --help            Show this help message

Examples:
  php tests/setup-test-database.php --mode=persistent --seed=standard
  php tests/setup-test-database.php --reset
  php tests/setup-test-database.php --destroy

HELP;
    exit(0);
}

$mode = $options['mode'] ?? 'persistent';
$seedDataset = $options['seed'] ?? 'standard';
$shouldReset = isset($options['reset']);
$shouldDestroy = isset($options['destroy']);

// Load configuration
$_ENV['APP_ENV'] = 'testing';
$_ENV['TESTING_MODE'] = 'true';
putenv('TESTING_MODE=true');

$configFile = __DIR__ . '/../config/testing.php';
if (!file_exists($configFile)) {
    die("❌ Testing configuration not found: $configFile\n");
}

$config = require $configFile;

// Get database configuration based on mode
$dbConfig = $mode === 'ephemeral' 
    ? $config['database']['ephemeral'] 
    : $config['database']['persistent'];

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                                                              ║\n";
echo "║           TEST DATABASE SETUP                                ║\n";
echo "║                                                              ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

echo "Configuration:\n";
echo "  Mode:     $mode\n";
echo "  Host:     {$dbConfig['host']}\n";
echo "  Database: {$dbConfig['name']}\n";
echo "  Dataset:  $seedDataset\n";
echo "\n";

try {
    // Connect to MySQL without database
    $dsn = "mysql:host={$dbConfig['host']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    echo "✅ Connected to MySQL server\n";
    
    // Handle destroy option
    if ($shouldDestroy) {
        echo "\n⚠️  Destroying test database...\n";
        $pdo->exec("DROP DATABASE IF EXISTS `{$dbConfig['name']}`");
        echo "✅ Test database destroyed\n\n";
        exit(0);
    }
    
    // Check if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE '{$dbConfig['name']}'");
    $exists = $stmt->rowCount() > 0;
    
    if ($exists && !$shouldReset) {
        echo "ℹ️  Test database already exists. Use --reset to recreate.\n\n";
        exit(0);
    }
    
    if ($exists && $shouldReset) {
        echo "🔄 Resetting test database...\n";
        $pdo->exec("DROP DATABASE `{$dbConfig['name']}`");
        echo "✅ Dropped existing database\n";
    }
    
    // Create database
    echo "📦 Creating test database...\n";
    $pdo->exec("CREATE DATABASE `{$dbConfig['name']}` CHARACTER SET {$dbConfig['charset']} COLLATE utf8mb4_general_ci");
    echo "✅ Database created\n";
    
    // Use the new database
    $pdo->exec("USE `{$dbConfig['name']}`");
    
    // Import schema from production structure
    echo "📋 Importing schema...\n";
    $schemaFile = __DIR__ . '/../sql/democrm_democrm_structure.sql';
    
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: $schemaFile");
    }
    
    $schema = file_get_contents($schemaFile);
    
    // Remove database creation and use statements
    $schema = preg_replace('/CREATE DATABASE.*?;/i', '', $schema);
    $schema = preg_replace('/USE .*?;/i', '', $schema);
    
    // Execute schema
    $pdo->exec($schema);
    echo "✅ Schema imported\n";
    
    // Seed test data
    if ($config['seeding']['enabled']) {
        echo "\n🌱 Seeding test data ($seedDataset dataset)...\n";
        
        $dataset = $config['seeding']['datasets'][$seedDataset] ?? $config['seeding']['datasets']['standard'];
        
        // Seed users
        if (isset($dataset['users'])) {
            echo "  → Creating {$dataset['users']} test users...\n";
            seedUsers($pdo, $dataset['users']);
        }
        
        // Seed roles
        if (isset($dataset['roles'])) {
            echo "  → Creating {$dataset['roles']} test roles...\n";
            seedRoles($pdo, $dataset['roles']);
        }
        
        // Seed permissions
        if (isset($dataset['permissions'])) {
            echo "  → Creating {$dataset['permissions']} test permissions...\n";
            seedPermissions($pdo, $dataset['permissions']);
        }
        
        // Seed RBAC data
        if ($config['seeding']['rbac']) {
            echo "  → Setting up RBAC test data...\n";
            seedRbacData($pdo, $config['seeding']['rbac']);
        }
        
        // Seed leads
        if (isset($dataset['leads'])) {
            echo "  → Creating {$dataset['leads']} test leads...\n";
            seedLeads($pdo, $dataset['leads']);
        }
        
        // Seed contacts
        if (isset($dataset['contacts'])) {
            echo "  → Creating {$dataset['contacts']} test contacts...\n";
            seedContacts($pdo, $dataset['contacts']);
        }
        
        echo "✅ Test data seeded\n";
    }
    
    // Create snapshot if enabled
    if ($config['database']['snapshots']['enabled'] && $config['database']['snapshots']['auto_snapshot']) {
        echo "\n📸 Creating database snapshot...\n";
        createSnapshot($dbConfig, 'initial');
        echo "✅ Snapshot created\n";
    }
    
    echo "\n";
    echo "╔══════════════════════════════════════════════════════════════╗\n";
    echo "║                                                              ║\n";
    echo "║           ✅ TEST DATABASE SETUP COMPLETE                    ║\n";
    echo "║                                                              ║\n";
    echo "╚══════════════════════════════════════════════════════════════╝\n";
    echo "\n";
    echo "You can now run tests with:\n";
    echo "  vendor/bin/phpunit\n";
    echo "  npx playwright test\n";
    echo "\n";
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

// ============================================
// SEEDING FUNCTIONS
// ============================================

function seedUsers(PDO $pdo, int $count): void
{
    $stmt = $pdo->prepare("
        INSERT INTO users (username, password, full_name, email, role_id, state_id) 
        VALUES (:username, :password, :full_name, :email, :role_id, :state_id)
    ");
    
    for ($i = 1; $i <= $count; $i++) {
        $stmt->execute([
            'username' => "test_user_$i",
            'password' => password_hash('test_password', PASSWORD_DEFAULT),
            'full_name' => "Test User $i",
            'email' => "test_user_$i@test.com",
            'role_id' => ($i === 1) ? 1 : 2, // First user is admin
            'state_id' => 1, // Active
        ]);
    }
}

function seedRoles(PDO $pdo, int $count): void
{
    $roles = ['Admin', 'Manager', 'User', 'Viewer', 'Restricted'];
    
    $stmt = $pdo->prepare("
        INSERT INTO roles (role, updated_at, created_at) 
        VALUES (:name, NOW(), NOW())
    ");
    
    for ($i = 0; $i < min($count, count($roles)); $i++) {
        $stmt->execute(['name' => $roles[$i]]);
    }
}

function seedPermissions(PDO $pdo, int $count): void
{
    $permissions = [
        'leads.access', 'leads.view', 'leads.create', 'leads.edit', 'leads.delete',
        'contacts.access', 'contacts.view', 'contacts.create', 'contacts.edit', 'contacts.delete',
        'admin.access', 'admin.users', 'admin.roles', 'admin.permissions',
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO permissions (pobject, pdescription, updated_at, created_at) 
        VALUES (:object, :description, NOW(), NOW())
    ");
    
    for ($i = 0; $i < min($count, count($permissions)); $i++) {
        $stmt->execute([
            'object' => $permissions[$i],
            'description' => ucfirst(str_replace('.', ' ', $permissions[$i])),
        ]);
    }
}

function seedRbacData(PDO $pdo, array $rbacConfig): void
{
    // Create test roles
    foreach ($rbacConfig['test_roles'] as $roleName => $description) {
        $pdo->prepare("INSERT INTO roles (role, updated_at, created_at) VALUES (?, NOW(), NOW())")
            ->execute([$roleName]);
    }
    
    // Create test permissions
    foreach ($rbacConfig['test_permissions'] as $permission) {
        $pdo->prepare("INSERT INTO permissions (pobject, pdescription, updated_at, created_at) VALUES (?, ?, NOW(), NOW())")
            ->execute([$permission, ucfirst(str_replace('.', ' ', $permission))]);
    }
}

function seedLeads(PDO $pdo, int $count): void
{
    $stmt = $pdo->prepare("
        INSERT INTO leads (first_name, family_name, cell_phone, email, stage, created_at) 
        VALUES (:first_name, :family_name, :phone, :email, :stage, NOW())
    ");
    
    $stages = ['New', 'Contacted', 'Qualified', 'Proposal', 'Closed Won', 'Closed Lost'];
    
    for ($i = 1; $i <= $count; $i++) {
        $stmt->execute([
            'first_name' => "Lead",
            'family_name' => "Test $i",
            'phone' => sprintf('555-%04d', $i),
            'email' => "lead_test_$i@test.com",
            'stage' => $stages[array_rand($stages)],
        ]);
    }
}

function seedContacts(PDO $pdo, int $count): void
{
    $stmt = $pdo->prepare("
        INSERT INTO contacts (first_name, family_name, cell_phone, personal_email, created_at) 
        VALUES (:first_name, :family_name, :phone, :email, NOW())
    ");
    
    for ($i = 1; $i <= $count; $i++) {
        $stmt->execute([
            'first_name' => "Contact",
            'family_name' => "Test $i",
            'phone' => sprintf('555-%04d', 1000 + $i),
            'email' => "contact_test_$i@test.com",
        ]);
    }
}

function createSnapshot(array $dbConfig, string $name): void
{
    $snapshotDir = __DIR__ . '/snapshots';
    if (!is_dir($snapshotDir)) {
        mkdir($snapshotDir, 0755, true);
    }
    
    $snapshotFile = "$snapshotDir/$name.sql";
    
    $command = sprintf(
        'mysqldump -h%s -u%s -p%s %s > %s 2>/dev/null',
        escapeshellarg($dbConfig['host']),
        escapeshellarg($dbConfig['username']),
        escapeshellarg($dbConfig['password']),
        escapeshellarg($dbConfig['name']),
        escapeshellarg($snapshotFile)
    );
    
    exec($command, $output, $returnCode);
    
    if ($returnCode !== 0) {
        throw new Exception("Failed to create snapshot");
    }
}