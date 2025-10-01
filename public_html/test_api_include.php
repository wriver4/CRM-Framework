<?php
/**
 * Test API include mechanism
 */

echo "Starting API include test...\n";

try {
    echo "Including system.php...\n";
    require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
    echo "System.php included successfully.\n";
    
    echo "Testing include of leads/get.php...\n";
    include 'leads/get.php';
    echo "Include completed.\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
}