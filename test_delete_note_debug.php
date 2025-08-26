<?php
/**
 * Debug script for testing delete_note.php functionality
 */

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname($_SERVER['DOCUMENT_ROOT']) . '/logs/debug_test.log');

echo "=== DELETE NOTE DEBUG TEST ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";

try {
    echo "1. Loading system configuration...\n";
    require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
    echo "   ✓ System configuration loaded successfully\n";
    
    echo "2. Creating Notes instance...\n";
    $notes = new Notes();
    echo "   ✓ Notes instance created successfully\n";
    
    // Test with actual data from error log
    $test_note_id = 3223;
    $test_lead_id = 3;
    
    echo "3. Testing get_note_by_id($test_note_id, $test_lead_id)...\n";
    $note = $notes->get_note_by_id($test_note_id, $test_lead_id);
    
    if ($note) {
        echo "   ✓ Note found: " . substr($note['note_text'], 0, 50) . "...\n";
        
        echo "4. Testing delete_note($test_note_id)...\n";
        $result = $notes->delete_note($test_note_id);
        
        if ($result) {
            echo "   ✓ Note deleted successfully\n";
        } else {
            echo "   ✗ Failed to delete note\n";
        }
        
    } else {
        echo "   ✗ Note not found or doesn't belong to this lead\n";
    }
    
    echo "5. Testing Audit class...\n";
    $audit = new Audit();
    echo "   ✓ Audit instance created successfully\n";
    
    echo "\n=== TEST COMPLETED SUCCESSFULLY ===\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>