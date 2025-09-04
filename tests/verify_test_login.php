<?php
/**
 * Verify test users can login via PHP (before running Playwright tests)
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
    
    $testUsers = [
        ['username' => 'testadmin', 'password' => 'testpass123'],
        ['username' => 'testadmin2', 'password' => 'testpass123'],
        ['username' => 'testsalesmgr', 'password' => 'testpass123'],
        ['username' => 'testsalesasst', 'password' => 'testpass123'],
        ['username' => 'testsalesperson', 'password' => 'testpass123']
    ];
    
    echo "🔐 Verifying test user login credentials...\n";
    echo str_repeat("-", 60) . "\n";
    
    foreach ($testUsers as $testUser) {
        // Get user from database
        $stmt = $pdo->prepare('SELECT id, username, password, full_name, status FROM users WHERE username = ?');
        $stmt->execute([$testUser['username']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            echo "❌ User '{$testUser['username']}' not found\n";
            continue;
        }
        
        if (!$user['status']) {
            echo "⚠️  User '{$testUser['username']}' is inactive\n";
            continue;
        }
        
        // Verify password
        if (password_verify($testUser['password'], $user['password'])) {
            echo "✅ {$testUser['username']} - Password verified\n";
        } else {
            echo "❌ {$testUser['username']} - Password verification failed\n";
        }
    }
    
    echo str_repeat("-", 60) . "\n";
    echo "🎯 Login verification complete!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}