<?php
/**
 * Create test users for Playwright testing
 */

require_once __DIR__ . '/../vendor/autoload.php';

spl_autoload_register(function ($class_name) {
    if (strpos($class_name, '\\') !== false) {
        return;
    }
    
    // Search in organized subdirectories
    $directories = ['Core', 'Models', 'Views', 'Utilities', 'Logging'];
    
    foreach ($directories as $dir) {
        $file = __DIR__ . '/../classes/' . $dir . '/' . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
    
    // Fallback to root classes directory for backward compatibility
    $file = __DIR__ . '/../classes/' . $class_name . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

try {
    $db = new Database();
    $pdo = $db->dbcrm();
    
    // Test users to create
    $testUsers = [
        [
            'username' => 'testadmin',
            'password' => 'testpass123',
            'email' => 'testadmin@example.com',
            'full_name' => 'Test Super Administrator',
            'rid' => 1, // Super Administrator
            'status' => 1
        ],
        [
            'username' => 'testadmin2',
            'password' => 'testpass123',
            'email' => 'testadmin2@example.com',
            'full_name' => 'Test Administrator',
            'rid' => 2, // Administrator
            'status' => 1
        ],
        [
            'username' => 'testsalesmgr',
            'password' => 'testpass123',
            'email' => 'testsalesmgr@example.com',
            'full_name' => 'Test Sales Manager',
            'rid' => 13, // Sales Manager
            'status' => 1
        ],
        [
            'username' => 'testsalesasst',
            'password' => 'testpass123',
            'email' => 'testsalesasst@example.com',
            'full_name' => 'Test Sales Assistant',
            'rid' => 14, // Sales Assistant
            'status' => 1
        ],
        [
            'username' => 'testsalesperson',
            'password' => 'testpass123',
            'email' => 'testsalesperson@example.com',
            'full_name' => 'Test Sales Person',
            'rid' => 15, // Sales Person
            'status' => 1
        ]
    ];
    
    echo "🔧 Creating test users for Playwright testing...\n";
    echo str_repeat("-", 80) . "\n";
    
    foreach ($testUsers as $user) {
        // Check if user already exists
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$user['username'], $user['email']]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            echo "⚠️  User '{$user['username']}' already exists (ID: {$existing['id']})\n";
            continue;
        }
        
        // Hash the password
        $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
        
        // Insert new user
        $stmt = $pdo->prepare('
            INSERT INTO users (username, password, email, full_name, rid, status, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ');
        
        $success = $stmt->execute([
            $user['username'],
            $hashedPassword,
            $user['email'],
            $user['full_name'],
            $user['rid'],
            $user['status']
        ]);
        
        if ($success) {
            $userId = $pdo->lastInsertId();
            echo "✅ Created user '{$user['username']}' (ID: $userId) - Role ID: {$user['rid']}\n";
        } else {
            echo "❌ Failed to create user '{$user['username']}'\n";
        }
    }
    
    echo str_repeat("-", 80) . "\n";
    echo "🎯 Test user creation completed!\n\n";
    
    // Display created test users
    echo "📋 Test Users Summary:\n";
    echo str_repeat("-", 80) . "\n";
    
    $stmt = $pdo->query("
        SELECT u.id, u.username, u.email, u.full_name, r.rname as role_name, u.status
        FROM users u 
        LEFT JOIN roles r ON u.rid = r.rid 
        WHERE u.username LIKE 'test%' 
        ORDER BY u.rid
    ");
    
    while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $status = $user['status'] ? 'Active' : 'Inactive';
        echo sprintf("%-15s | %-25s | %-25s | %s\n", 
            $user['username'], 
            $user['email'], 
            $user['role_name'],
            $status
        );
    }
    
    echo str_repeat("-", 80) . "\n";
    echo "🔑 All test users use password: testpass123\n";
    echo "🌐 Ready for Playwright testing!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}