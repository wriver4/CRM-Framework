<?php
/**
 * Check MySQL Users - Web Version
 * 
 * This runs from the web context to check MySQL user configuration
 * Access via: https://democrm.waveguardco.net/check-mysql-users-web.php
 */

// Security check
if (!isset($_GET['allow'])) {
    die("Access denied. Add ?allow=1 to URL to proceed.");
}

header('Content-Type: text/plain; charset=utf-8');

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                                                              ║\n";
echo "║           MYSQL USER DIAGNOSTIC (WEB CONTEXT)                ║\n";
echo "║                                                              ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// Production credentials
$host = 'localhost';
$database = 'democrm_democrm';
$username = 'democrm_democrm';
$password = 'b3J2sy5T4JNm60';

echo "Testing production database connection...\n\n";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    echo "✅ Production connection successful!\n\n";
    
    // Check current user
    $stmt = $pdo->query("SELECT USER(), CURRENT_USER(), @@hostname");
    $result = $stmt->fetch(PDO::FETCH_NUM);
    echo "Connection info:\n";
    echo "  USER():         {$result[0]}\n";
    echo "  CURRENT_USER(): {$result[1]}\n";
    echo "  Hostname:       {$result[2]}\n\n";
    
    // Try to query mysql.user table
    echo "Checking MySQL users...\n";
    try {
        $stmt = $pdo->query("SELECT User, Host, plugin FROM mysql.user WHERE User LIKE 'democrm%' OR User LIKE 'test%' ORDER BY User, Host");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($users) > 0) {
            echo "Found " . count($users) . " users:\n\n";
            foreach ($users as $user) {
                echo "  • {$user['User']}@{$user['Host']} (auth: {$user['plugin']})\n";
            }
        } else {
            echo "No matching users found.\n";
        }
    } catch (PDOException $e) {
        echo "❌ Cannot query mysql.user table\n";
        echo "Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
    echo "Checking for test user specifically...\n";
    try {
        $stmt = $pdo->query("SELECT User, Host FROM mysql.user WHERE User = 'democrm_test'");
        $testUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($testUsers) > 0) {
            echo "✅ Test user exists:\n";
            foreach ($testUsers as $user) {
                echo "  • {$user['User']}@{$user['Host']}\n";
                
                // Try to show grants
                try {
                    $stmt = $pdo->query("SHOW GRANTS FOR '{$user['User']}'@'{$user['Host']}'");
                    $grants = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    echo "\n  Grants:\n";
                    foreach ($grants as $grant) {
                        echo "    - $grant\n";
                    }
                } catch (PDOException $e) {
                    echo "  Cannot show grants: " . $e->getMessage() . "\n";
                }
            }
        } else {
            echo "❌ Test user 'democrm_test' does NOT exist\n";
            echo "\nYou need to create it with:\n\n";
            echo "CREATE USER 'democrm_test'@'localhost' IDENTIFIED BY 'TestDB_2025_Secure!';\n";
            echo "GRANT ALL PRIVILEGES ON `democrm_test`.* TO 'democrm_test'@'localhost';\n";
            echo "GRANT ALL PRIVILEGES ON `democrm_test_%`.* TO 'democrm_test'@'localhost';\n";
            echo "GRANT CREATE, DROP ON *.* TO 'democrm_test'@'localhost';\n";
            echo "FLUSH PRIVILEGES;\n";
        }
    } catch (PDOException $e) {
        echo "❌ Error checking for test user: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
    echo "Checking production user grants...\n";
    try {
        $stmt = $pdo->query("SHOW GRANTS FOR CURRENT_USER()");
        $grants = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Production user grants:\n";
        foreach ($grants as $grant) {
            echo "  • $grant\n";
        }
    } catch (PDOException $e) {
        echo "❌ Cannot show grants: " . $e->getMessage() . "\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Production connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\nDiagnostic complete.\n";
echo "\nTo delete this file after use:\n";
echo "rm " . __FILE__ . "\n";