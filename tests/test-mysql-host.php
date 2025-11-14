#!/usr/bin/env php
<?php
/**
 * Test MySQL Connection with Different Host Values
 * 
 * This will help us figure out what host value MySQL is seeing
 * when PHP CLI connects
 */

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                                                              ║\n";
echo "║           MySQL Host Detection Test                          ║\n";
echo "║                                                              ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

$username = 'democrm_democrm';
$password = 'b3J2sy5T4JNm60';
$database = 'democrm_democrm';

echo "Testing different host configurations...\n\n";

$hosts = [
    'localhost',
    '127.0.0.1',
    'king.waveguardco.net',
    '%',
];

foreach ($hosts as $host) {
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "Testing: $username@$host\n";
    echo "═══════════════════════════════════════════════════════════════\n\n";
    
    try {
        $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);
        
        echo "✅ SUCCESS! Connected using host: $host\n\n";
        
        // Get the actual connection info
        $currentUser = $pdo->query('SELECT CURRENT_USER()')->fetchColumn();
        $connectionId = $pdo->query('SELECT CONNECTION_ID()')->fetchColumn();
        
        echo "Connection Details:\n";
        echo "  Current User: $currentUser\n";
        echo "  Connection ID: $connectionId\n\n";
        
        // Check what host MySQL sees us as
        $stmt = $pdo->query("SELECT host FROM information_schema.processlist WHERE id = CONNECTION_ID()");
        $actualHost = $stmt->fetchColumn();
        echo "  MySQL sees us as: $actualHost\n\n";
        
        echo "✅ This is the correct host to use!\n\n";
        
        // We found a working connection, no need to test others
        exit(0);
        
    } catch (PDOException $e) {
        echo "❌ Failed\n";
        echo "  Error: " . $e->getMessage() . "\n";
        echo "  Code: " . $e->getCode() . "\n\n";
    }
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "❌ All connection attempts failed!\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "This means the MySQL user might be configured with a different host.\n";
echo "Please check in phpMyAdmin:\n\n";
echo "  SELECT User, Host FROM mysql.user WHERE User LIKE 'democrm%';\n\n";

exit(1);