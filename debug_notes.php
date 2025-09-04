<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

// Test database connection
try {
    $db = new Database();
    $pdo = $db->dbcrm();
    echo "Database connection: OK\n";
} catch (Exception $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
    exit;
}

// Check if leads_notes table exists
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'leads_notes'");
    $result = $stmt->fetch();
    if ($result) {
        echo "leads_notes table: EXISTS\n";
    } else {
        echo "leads_notes table: MISSING\n";
        
        // Check if notes table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'notes'");
        $result = $stmt->fetch();
        if ($result) {
            echo "notes table: EXISTS\n";
        } else {
            echo "notes table: MISSING\n";
        }
        exit;
    }
} catch (Exception $e) {
    echo "Error checking tables: " . $e->getMessage() . "\n";
    exit;
}

// Test Notes class
try {
    $notes = new Notes();
    echo "Notes class: OK\n";
} catch (Exception $e) {
    echo "Notes class failed: " . $e->getMessage() . "\n";
    exit;
}

// Test getting notes for a lead (use lead ID 1 as test)
$test_lead_id = 1;
try {
    $notes_result = $notes->get_notes_by_lead($test_lead_id);
    $notes_count = $notes->get_notes_count_by_lead($test_lead_id);
    
    echo "Notes for lead $test_lead_id: " . count($notes_result) . " found\n";
    echo "Notes count: $notes_count\n";
    
    if (!empty($notes_result)) {
        echo "Sample note:\n";
        print_r($notes_result[0]);
    }
} catch (Exception $e) {
    echo "Error getting notes: " . $e->getMessage() . "\n";
}

// Check table structure
try {
    echo "\nTable structures:\n";
    
    $stmt = $pdo->query("DESCRIBE leads_notes");
    echo "leads_notes structure:\n";
    while ($row = $stmt->fetch()) {
        echo "  " . $row['Field'] . " - " . $row['Type'] . "\n";
    }
    
    $stmt = $pdo->query("DESCRIBE notes");
    echo "notes structure:\n";
    while ($row = $stmt->fetch()) {
        echo "  " . $row['Field'] . " - " . $row['Type'] . "\n";
    }
} catch (Exception $e) {
    echo "Error checking table structure: " . $e->getMessage() . "\n";
}