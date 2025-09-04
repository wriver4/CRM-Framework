<?php
// Debug the path first
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "dirname(DOCUMENT_ROOT): " . dirname($_SERVER['DOCUMENT_ROOT']) . "\n";
echo "Config path: " . dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php' . "\n";

// Check if config file exists
$config_path = dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
if (file_exists($config_path)) {
    echo "Config file exists: YES\n";
    require_once $config_path;
} else {
    echo "Config file exists: NO\n";
    // Try alternative path
    $alt_path = '/run/user/1000/gvfs/sftp:host=159.203.116.150,port=222/home/democrm/config/system.php';
    echo "Trying alternative path: $alt_path\n";
    if (file_exists($alt_path)) {
        echo "Alternative config file exists: YES\n";
        require_once $alt_path;
    } else {
        echo "Alternative config file exists: NO\n";
        exit;
    }
}

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