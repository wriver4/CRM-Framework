<?php
require_once __DIR__ . '/../vendor/autoload.php';

spl_autoload_register(function ($class_name) {
    if (strpos($class_name, '\\') !== false) {
        return;
    }
    $file = __DIR__ . '/../classes/' . $class_name . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

try {
    $db = new Database();
    $pdo = $db->dbcrm();
    $stmt = $pdo->query('SELECT id, username, email, full_name, status FROM users LIMIT 10');
    
    echo "Available users in database:\n";
    echo str_repeat("-", 80) . "\n";
    
    while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $status = $user['status'] ? 'Active' : 'Inactive';
        echo sprintf("ID: %-3s | Username: %-15s | Email: %-25s | Name: %-20s | Status: %s\n", 
            $user['id'], 
            $user['username'], 
            $user['email'], 
            $user['full_name'],
            $status
        );
    }
    
    echo str_repeat("-", 80) . "\n";
    
    // Check if testuser exists
    $stmt = $pdo->prepare('SELECT id, username, email FROM users WHERE username = ? OR email = ?');
    $stmt->execute(['testuser', 'testuser@example.com']);
    $testUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($testUser) {
        echo "✅ Test user found: " . $testUser['username'] . " (" . $testUser['email'] . ")\n";
    } else {
        echo "❌ Test user 'testuser' not found\n";
        echo "💡 You may need to create a test user or use existing credentials\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}