<?php
// Simple database test without full system initialization
$host = 'localhost';
$database = 'democrm_democrm';
$username = 'democrm_democrm';
$password = 'b3J2sy5T4JNm60';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    echo "Database connection: OK\n";
} catch (Exception $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
    exit;
}

// Check if tables exist
$tables_to_check = ['notes', 'leads_notes', 'leads'];
foreach ($tables_to_check as $table) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        $result = $stmt->fetch();
        if ($result) {
            echo "$table table: EXISTS\n";
        } else {
            echo "$table table: MISSING\n";
        }
    } catch (Exception $e) {
        echo "Error checking $table table: " . $e->getMessage() . "\n";
    }
}

// Check table structures
try {
    echo "\nTable structures:\n";
    
    $stmt = $pdo->query("DESCRIBE notes");
    echo "notes structure:\n";
    while ($row = $stmt->fetch()) {
        echo "  " . $row['Field'] . " - " . $row['Type'] . "\n";
    }
    
    $stmt = $pdo->query("DESCRIBE leads_notes");
    echo "leads_notes structure:\n";
    while ($row = $stmt->fetch()) {
        echo "  " . $row['Field'] . " - " . $row['Type'] . "\n";
    }
} catch (Exception $e) {
    echo "Error checking table structure: " . $e->getMessage() . "\n";
}

// Test a simple query
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM notes");
    $result = $stmt->fetch();
    echo "\nTotal notes in database: " . $result['count'] . "\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM leads_notes");
    $result = $stmt->fetch();
    echo "Total lead-note links: " . $result['count'] . "\n";
    
    // Test getting notes for lead ID 1
    $stmt = $pdo->prepare("SELECT n.*, ln.lead_id FROM notes n INNER JOIN leads_notes ln ON n.id = ln.note_id WHERE ln.lead_id = ?");
    $stmt->execute([1]);
    $notes = $stmt->fetchAll();
    echo "Notes for lead 1: " . count($notes) . "\n";
    
} catch (Exception $e) {
    echo "Error running queries: " . $e->getMessage() . "\n";
}