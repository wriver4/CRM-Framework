#!/usr/bin/env php
<?php
/**
 * Check what host PHP CLI connects from
 * This helps us understand what host to grant MySQL access for
 */

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                                                              ║\n";
echo "║           CONNECTION HOST DIAGNOSTIC                         ║\n";
echo "║                                                              ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

echo "System Information:\n";
echo "  Hostname: " . gethostname() . "\n";
echo "  PHP SAPI: " . php_sapi_name() . "\n";
echo "  User: " . get_current_user() . "\n";
echo "  UID: " . getmyuid() . "\n";
echo "  GID: " . getmygid() . "\n\n";

// Try to get IP addresses
echo "Network Information:\n";
$hostname = gethostname();
$ip = gethostbyname($hostname);
echo "  IP from hostname: $ip\n";

// Check localhost resolution
$localhost_ip = gethostbyname('localhost');
echo "  localhost resolves to: $localhost_ip\n\n";

// Try to connect and see what MySQL sees
echo "Attempting MySQL connection to see what host MySQL detects...\n\n";

$credentials = [
    'production' => [
        'host' => 'localhost',
        'user' => 'democrm_democrm',
        'pass' => 'b3J2sy5T4JNm60',
        'db' => 'democrm_democrm'
    ],
    'test' => [
        'host' => 'localhost',
        'user' => 'democrm_test',
        'pass' => 'TestDB_2025_Secure!',
        'db' => 'democrm_test'
    ]
];

foreach ($credentials as $type => $cred) {
    echo "Testing $type credentials ({$cred['user']})...\n";
    
    try {
        $pdo = new PDO(
            "mysql:host={$cred['host']};charset=utf8mb4",
            $cred['user'],
            $cred['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        echo "  ✅ Connection successful!\n";
        
        // Get connection details
        $stmt = $pdo->query("SELECT USER(), CURRENT_USER(), @@hostname, CONNECTION_ID()");
        $result = $stmt->fetch(PDO::FETCH_NUM);
        
        echo "  Connection details:\n";
        echo "    USER():         {$result[0]}\n";
        echo "    CURRENT_USER(): {$result[1]}\n";
        echo "    MySQL hostname: {$result[2]}\n";
        echo "    Connection ID:  {$result[3]}\n";
        
        // Try to get process list to see our connection
        try {
            $stmt = $pdo->query("SELECT * FROM information_schema.PROCESSLIST WHERE ID = CONNECTION_ID()");
            $process = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($process) {
                echo "  Process info:\n";
                echo "    Host: {$process['HOST']}\n";
                echo "    User: {$process['USER']}\n";
                echo "    DB: {$process['DB']}\n";
            }
        } catch (PDOException $e) {
            echo "  Cannot query process list: " . $e->getMessage() . "\n";
        }
        
    } catch (PDOException $e) {
        echo "  ❌ Connection failed!\n";
        echo "  Error: " . $e->getMessage() . "\n";
        
        if (strpos($e->getMessage(), 'Access denied') !== false) {
            echo "\n  This user cannot connect from this context.\n";
            echo "  The MySQL user might be restricted to specific hosts.\n";
        }
    }
    
    echo "\n";
}

echo "═══════════════════════════════════════════════════════════════\n\n";

echo "NEXT STEPS:\n";
echo "───────────\n";
echo "1. If production connects but test doesn't:\n";
echo "   → The test user needs to be created with the same host as production\n\n";
echo "2. If neither connects:\n";
echo "   → MySQL users are restricted to web server context only\n";
echo "   → You may need to create users with host matching the connection source\n\n";
echo "3. Check MySQL users with:\n";
echo "   SELECT User, Host FROM mysql.user WHERE User LIKE 'democrm%';\n\n";