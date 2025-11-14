<?php
// Simple script to check MySQL user hosts
// Access with: ?key=check2025

if (!isset($_GET['key']) || $_GET['key'] !== 'check2025') {
    die("Access denied");
}

header('Content-Type: text/plain');

$host = 'localhost';
$database = 'democrm_democrm';
$username = 'democrm_democrm';
$password = 'b3J2sy5T4JNm60';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    echo "Connected successfully!\n\n";
    
    // Get current connection info
    $stmt = $pdo->query("SELECT USER(), CURRENT_USER(), @@hostname");
    $result = $stmt->fetch(PDO::FETCH_NUM);
    echo "Current connection:\n";
    echo "  USER():         {$result[0]}\n";
    echo "  CURRENT_USER(): {$result[1]}\n";
    echo "  MySQL host:     {$result[2]}\n\n";
    
    // Check process list
    $stmt = $pdo->query("SELECT HOST, USER, DB FROM information_schema.PROCESSLIST WHERE ID = CONNECTION_ID()");
    $process = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Process info:\n";
    echo "  Connecting from: {$process['HOST']}\n";
    echo "  User: {$process['USER']}\n";
    echo "  Database: {$process['DB']}\n\n";
    
    // Try to list users
    try {
        $stmt = $pdo->query("SELECT User, Host, plugin FROM mysql.user WHERE User LIKE 'democrm%' ORDER BY User, Host");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "MySQL users:\n";
        foreach ($users as $user) {
            echo "  {$user['User']}@{$user['Host']} (auth: {$user['plugin']})\n";
        }
    } catch (PDOException $e) {
        echo "Cannot list users: " . $e->getMessage() . "\n";
    }
    
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}