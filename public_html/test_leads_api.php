<?php
/**
 * Test script to debug leads API issues
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting leads API test...\n";

try {
    // Test system.php inclusion
    echo "Including system.php...\n";
    require_once dirname(__DIR__) . '/config/system.php';
    echo "System.php included successfully.\n";
    
    // Test Sessions class
    echo "Testing Sessions class...\n";
    if (class_exists('Sessions')) {
        echo "Sessions class exists.\n";
        if (method_exists('Sessions', 'isLoggedIn')) {
            echo "Sessions::isLoggedIn method exists.\n";
            $isLoggedIn = Sessions::isLoggedIn();
            echo "Sessions::isLoggedIn() returned: " . ($isLoggedIn ? 'true' : 'false') . "\n";
        } else {
            echo "ERROR: Sessions::isLoggedIn method does not exist.\n";
        }
    } else {
        echo "ERROR: Sessions class does not exist.\n";
    }
    
    // Test Leads class
    echo "Testing Leads class...\n";
    if (class_exists('Leads')) {
        echo "Leads class exists.\n";
        $leads = new Leads();
        echo "Leads instance created successfully.\n";
        
        if (method_exists($leads, 'get_leads_by_stages')) {
            echo "get_leads_by_stages method exists.\n";
        } else {
            echo "ERROR: get_leads_by_stages method does not exist.\n";
        }
    } else {
        echo "ERROR: Leads class does not exist.\n";
    }
    
    // Test stage remapping
    echo "Testing stage remapping...\n";
    require_once dirname(__DIR__) . '/scripts/stage_remapping.php';
    echo "Stage remapping included successfully.\n";
    
    if (class_exists('StageRemapping')) {
        echo "StageRemapping class exists.\n";
        $moduleFilters = StageRemapping::getModuleStageFilters();
        echo "Module filters retrieved: " . json_encode($moduleFilters) . "\n";
    } else {
        echo "ERROR: StageRemapping class does not exist.\n";
    }
    
    echo "Test completed successfully.\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}