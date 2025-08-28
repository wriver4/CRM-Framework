<?php
/**
 * Simple check to see current contacts.lead_id data before migration
 */

// Include the config to get database connection details
require_once __DIR__ . '/../config/system.php';

try {
    // Create PDO connection using the same method as the application
    $dsn = "mysql:host=localhost;dbname=democrm_democrm;charset=utf8mb4";
    $pdo = new PDO($dsn, 'democrm_democrm', 'b3J2sy5T4JNm60', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "✅ Database connection successful!\n\n";
    
    // Check current lead_id column structure
    echo "Current contacts table structure for lead_id:\n";
    $stmt = $pdo->prepare("DESCRIBE contacts");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $column) {
        if ($column['Field'] === 'lead_id') {
            echo "  lead_id: {$column['Type']} {$column['Null']} {$column['Key']} {$column['Default']}\n";
            break;
        }
    }
    
    // Check sample data
    echo "\nSample lead_id values:\n";
    $stmt = $pdo->prepare("SELECT id, lead_id, first_name, family_name FROM contacts LIMIT 5");
    $stmt->execute();
    $results = $stmt->fetchAll();
    
    foreach ($results as $row) {
        echo "  Contact {$row['id']}: lead_id='{$row['lead_id']}' ({$row['first_name']} {$row['family_name']})\n";
    }
    
    // Check for non-numeric values
    echo "\nChecking for non-numeric lead_id values:\n";
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM contacts WHERE lead_id NOT REGEXP '^[0-9]+$' OR lead_id = '' OR lead_id IS NULL");
    $stmt->execute();
    $count = $stmt->fetch()['count'];
    echo "  Found {$count} non-numeric lead_id values\n";
    
    if ($count > 0) {
        $stmt = $pdo->prepare("SELECT id, lead_id, first_name, family_name FROM contacts WHERE lead_id NOT REGEXP '^[0-9]+$' OR lead_id = '' OR lead_id IS NULL LIMIT 5");
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        echo "  Examples:\n";
        foreach ($results as $row) {
            $leadId = $row['lead_id'] ?? 'NULL';
            echo "    Contact {$row['id']}: lead_id='{$leadId}' ({$row['first_name']} {$row['family_name']})\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "Please check database credentials in config/system.php\n";
}
?>