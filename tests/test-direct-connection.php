#!/usr/bin/env php
<?php
/**
 * Direct MySQL Connection Test
 * Tests various connection methods to diagnose the issue
 */

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║         DIRECT MYSQL CONNECTION TEST                         ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// Test credentials
$users = [
    'production' => [
        'user' => 'democrm_democrm',
        'pass' => 'Waveguard2024!',
    ],
    'test' => [
        'user' => 'democrm_test',
        'pass' => 'TestDB_2025_Secure!',
    ],
];

$hosts = ['localhost', '127.0.0.1'];

foreach ($users as $label => $creds) {
    echo "Testing {$label} user: {$creds['user']}\n";
    echo str_repeat('─', 60) . "\n";
    
    foreach ($hosts as $host) {
        echo "  Host: $host ... ";
        
        try {
            $dsn = "mysql:host=$host;charset=utf8mb4";
            $pdo = new PDO($dsn, $creds['user'], $creds['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);
            
            // Get the actual connection info
            $stmt = $pdo->query("SELECT USER(), CURRENT_USER(), @@hostname");
            $info = $stmt->fetch(PDO::FETCH_NUM);
            
            echo "✅ SUCCESS!\n";
            echo "    USER(): {$info[0]}\n";
            echo "    CURRENT_USER(): {$info[1]}\n";
            echo "    Hostname: {$info[2]}\n";
            
            $pdo = null;
        } catch (PDOException $e) {
            echo "❌ FAILED\n";
            echo "    Error: {$e->getMessage()}\n";
        }
    }
    echo "\n";
}

echo "═══════════════════════════════════════════════════════════════\n\n";

// Check if mysqli extension is available
echo "Checking MySQL extensions:\n";
echo "  PDO MySQL: " . (extension_loaded('pdo_mysql') ? '✅ Available' : '❌ Not available') . "\n";
echo "  MySQLi: " . (extension_loaded('mysqli') ? '✅ Available' : '❌ Not available') . "\n\n";

// Try mysqli if available
if (extension_loaded('mysqli')) {
    echo "Testing with MySQLi extension:\n";
    echo str_repeat('─', 60) . "\n";
    
    foreach ($users as $label => $creds) {
        echo "  {$label} user ... ";
        
        $mysqli = @new mysqli('localhost', $creds['user'], $creds['pass']);
        
        if ($mysqli->connect_error) {
            echo "❌ FAILED\n";
            echo "    Error ({$mysqli->connect_errno}): {$mysqli->connect_error}\n";
        } else {
            echo "✅ SUCCESS!\n";
            $result = $mysqli->query("SELECT USER(), CURRENT_USER()");
            if ($result) {
                $row = $result->fetch_row();
                echo "    USER(): {$row[0]}\n";
                echo "    CURRENT_USER(): {$row[1]}\n";
            }
            $mysqli->close();
        }
    }
}

echo "\n";