<?php
/**
 * Quick test to verify the Audit class fix
 */

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

echo "<h1>Testing Audit Class Fix</h1>";

try {
    $audit = new Audit();
    
    // Test the log method with sample data
    $result = $audit->log(
        1,                                    // user_id
        'test_event',                        // event
        'test_resource',                     // resource
        'Test User Agent',                   // useragent
        '127.0.0.1',                        // ip
        1,                                   // location
        'Test audit log entry'              // data
    );
    
    echo "<p>✅ Audit log method executed successfully!</p>";
    echo "<p>The typo in the Audit class has been fixed.</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<p><a href='public_html/admin/leads/test_note_delete.php?test=confirm'>Test Note Delete Functionality</a></p>";
?>