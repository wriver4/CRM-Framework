#!/usr/bin/env php
<?php
/**
 * Check MySQL Users via Production Connection
 * 
 * Since we can't connect with the test user, let's use the production
 * credentials to inspect the MySQL user table
 */

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                                                              ║\n";
echo "║           MySQL User Table Inspector                         ║\n";
echo "║                                                              ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Production credentials from Database class
$prodHost = 'localhost';
$prodDatabase = 'democrm_democrm';
$prodUsername = 'democrm_democrm';
$prodPassword = 'b3J2sy5T4JNm60';

echo "Connecting with production credentials...\n\n";

try {
    $dsn = "mysql:host=$prodHost;dbname=$prodDatabase;charset=utf8mb4";
    
    $pdo = new PDO($dsn, $prodUsername, $prodPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    echo "✅ Connected successfully!\n\n";
    
    // Check all democrm users
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "All democrm* Users in MySQL\n";
    echo "═══════════════════════════════════════════════════════════════\n\n";
    
    $stmt = $pdo->query("
        SELECT 
            User, 
            Host, 
            plugin,
            authentication_string,
            password_expired,
            account_locked
        FROM mysql.user 
        WHERE User LIKE 'democrm%'
        ORDER BY User, Host
    ");
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "⚠️  No democrm users found!\n\n";
    } else {
        foreach ($users as $user) {
            echo "User: {$user['User']}@{$user['Host']}\n";
            echo "  Plugin:           {$user['plugin']}\n";
            echo "  Auth String:      " . substr($user['authentication_string'], 0, 50) . "...\n";
            echo "  Password Expired: {$user['password_expired']}\n";
            echo "  Account Locked:   {$user['account_locked']}\n";
            echo "\n";
        }
    }
    
    // Check specifically for test user
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "Test User Details\n";
    echo "═══════════════════════════════════════════════════════════════\n\n";
    
    $stmt = $pdo->prepare("
        SELECT 
            User, 
            Host, 
            plugin,
            authentication_string
        FROM mysql.user 
        WHERE User = ?
    ");
    $stmt->execute(['democrm_test']);
    $testUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($testUsers)) {
        echo "❌ User 'democrm_test' does NOT exist in mysql.user table!\n\n";
        echo "This explains the connection failure.\n\n";
        echo "Action Required:\n";
        echo "  Run: php tests/generate-test-user-sql.php\n";
        echo "  Then execute the generated SQL in phpMyAdmin\n\n";
    } else {
        echo "Found " . count($testUsers) . " test user(s):\n\n";
        foreach ($testUsers as $user) {
            echo "User: {$user['User']}@{$user['Host']}\n";
            echo "  Plugin:      {$user['plugin']}\n";
            echo "  Auth String: {$user['authentication_string']}\n\n";
            
            // Verify the password hash
            $expectedHash = '*6D4ADF073FB0AADD25FCF73C815D1CEB7A17DE1F';
            if ($user['authentication_string'] === $expectedHash) {
                echo "  ✅ Password hash matches expected value\n";
            } else {
                echo "  ❌ Password hash DOES NOT match!\n";
                echo "     Expected: $expectedHash\n";
                echo "     Got:      {$user['authentication_string']}\n";
            }
            echo "\n";
        }
        
        // If user exists but connection fails, check grants
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "Grants for Test User\n";
        echo "═══════════════════════════════════════════════════════════════\n\n";
        
        foreach ($testUsers as $user) {
            echo "Grants for '{$user['User']}'@'{$user['Host']}':\n";
            try {
                $stmt = $pdo->query("SHOW GRANTS FOR '{$user['User']}'@'{$user['Host']}'");
                $grants = $stmt->fetchAll(PDO::FETCH_COLUMN);
                foreach ($grants as $grant) {
                    echo "  • $grant\n";
                }
                echo "\n";
            } catch (PDOException $e) {
                echo "  ❌ Could not get grants: " . $e->getMessage() . "\n\n";
            }
        }
    }
    
    // Check MySQL version and default auth plugin
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "MySQL Server Information\n";
    echo "═══════════════════════════════════════════════════════════════\n\n";
    
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();
    echo "MySQL Version: $version\n\n";
    
    try {
        $defaultAuth = $pdo->query("SELECT @@default_authentication_plugin")->fetchColumn();
        echo "Default Auth Plugin: $defaultAuth\n\n";
    } catch (PDOException $e) {
        echo "Could not get default auth plugin (might be older MySQL version)\n\n";
    }
    
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "✅ Inspection Complete\n";
    echo "═══════════════════════════════════════════════════════════════\n\n";
    
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n\n";
    exit(1);
}