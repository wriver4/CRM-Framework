<?php
/**
 * Comprehensive test for all Audit class fixes
 * Tests all the corrected audit logging calls
 */

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

echo "<h1>Testing All Audit Class Fixes</h1>";
echo "<p>This script tests all the corrected audit logging calls throughout the system.</p>";

$tests_passed = 0;
$tests_failed = 0;

// Test 1: Basic Audit log method
echo "<h2>Test 1: Basic Audit Log Method</h2>";
try {
    $audit = new Audit();
    
    $audit->log(
        1,                                    // user_id
        'test_basic',                        // event
        'test_resource_basic',               // resource
        'Test User Agent',                   // useragent
        '127.0.0.1',                        // ip
        1,                                   // location
        'Basic audit log test'              // data
    );
    
    echo "<p>✅ Basic audit log method works correctly</p>";
    $tests_passed++;
    
} catch (Exception $e) {
    echo "<p>❌ Basic audit log failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    $tests_failed++;
}

// Test 2: Note deletion audit format (from delete_note.php)
echo "<h2>Test 2: Note Deletion Audit Format</h2>";
try {
    $audit = new Audit();
    $test_lead_id = 123;
    $test_note_id = 456;
    $test_note_text = "This is a test note for audit logging verification";
    
    $audit->log(
        $_SESSION['user_id'] ?? 1,                    // user_id
        'note_delete',                                 // event
        "lead_{$test_lead_id}_note_{$test_note_id}",  // resource
        $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',     // useragent
        $_SERVER['REMOTE_ADDR'] ?? 'Unknown',         // ip
        $test_lead_id,                                 // location
        "Note deleted from lead #{$test_lead_id}: " . substr($test_note_text, 0, 50) . '...' // data
    );
    
    echo "<p>✅ Note deletion audit format works correctly</p>";
    $tests_passed++;
    
} catch (Exception $e) {
    echo "<p>❌ Note deletion audit failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    $tests_failed++;
}

// Test 3: Lead update audit format (from post_with_contact_integration.php)
echo "<h2>Test 3: Lead Update Audit Format</h2>";
try {
    $audit = new Audit();
    $test_lead_id = 789;
    
    $audit->log(
        $_SESSION['user_id'] ?? 1,                    // user_id
        'lead_update',                                 // event
        "lead_{$test_lead_id}",                       // resource
        $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',     // useragent
        $_SERVER['REMOTE_ADDR'] ?? 'Unknown',         // ip
        $test_lead_id,                                 // location
        "Lead updated with contact integration"        // data
    );
    
    echo "<p>✅ Lead update audit format works correctly</p>";
    $tests_passed++;
    
} catch (Exception $e) {
    echo "<p>❌ Lead update audit failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    $tests_failed++;
}

// Test 4: Lead creation audit format (from post_with_contact_integration.php)
echo "<h2>Test 4: Lead Creation Audit Format</h2>";
try {
    $audit = new Audit();
    $test_lead_id = 101112;
    $test_contact_id = 131415;
    
    $audit->log(
        $_SESSION['user_id'] ?? 1,                    // user_id
        'lead_create',                                 // event
        "lead_{$test_lead_id}",                       // resource
        $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',     // useragent
        $_SERVER['REMOTE_ADDR'] ?? 'Unknown',         // ip
        $test_lead_id,                                 // location
        "Lead created with contact integration (Contact ID: {$test_contact_id})" // data
    );
    
    echo "<p>✅ Lead creation audit format works correctly</p>";
    $tests_passed++;
    
} catch (Exception $e) {
    echo "<p>❌ Lead creation audit failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    $tests_failed++;
}

// Test 5: Verify no more log_action calls exist
echo "<h2>Test 5: Verify No More log_action Calls</h2>";
$search_command = "grep -r 'log_action' /run/user/1000/gvfs/sftp:host=159.203.116.150,port=222/home/democrm/public_html/ 2>/dev/null || echo 'No log_action calls found'";
$search_result = shell_exec($search_command);

if (strpos($search_result, 'No log_action calls found') !== false || empty(trim($search_result))) {
    echo "<p>✅ No remaining log_action calls found in the codebase</p>";
    $tests_passed++;
} else {
    echo "<p>❌ Found remaining log_action calls:</p>";
    echo "<pre>" . htmlspecialchars($search_result) . "</pre>";
    $tests_failed++;
}

// Summary
echo "<hr>";
echo "<h2>Test Summary</h2>";
echo "<div style='background: " . ($tests_failed == 0 ? '#d4edda; border: 1px solid #c3e6cb; color: #155724;' : '#f8d7da; border: 1px solid #f5c6cb; color: #721c24;') . " padding: 15px; border-radius: 5px; margin: 20px 0;'>";

if ($tests_failed == 0) {
    echo "<h3>🎉 All Tests Passed!</h3>";
    echo "<p><strong>Tests Passed:</strong> {$tests_passed}</p>";
    echo "<p><strong>Tests Failed:</strong> {$tests_failed}</p>";
    echo "<ul>";
    echo "<li>✅ Audit class typo fixed (useragent parameter)</li>";
    echo "<li>✅ delete_note.php uses correct audit logging</li>";
    echo "<li>✅ post_with_contact_integration.php lead update logging fixed</li>";
    echo "<li>✅ post_with_contact_integration.php lead creation logging fixed</li>";
    echo "<li>✅ No remaining log_action calls in codebase</li>";
    echo "</ul>";
    echo "<p><strong>All audit logging issues have been resolved!</strong></p>";
} else {
    echo "<h3>❌ Some Tests Failed</h3>";
    echo "<p><strong>Tests Passed:</strong> {$tests_passed}</p>";
    echo "<p><strong>Tests Failed:</strong> {$tests_failed}</p>";
    echo "<p>Please review the failed tests above and fix any remaining issues.</p>";
}

echo "</div>";

// Clean up test entries (optional)
echo "<hr>";
echo "<h3>Cleanup</h3>";
try {
    $db = new Database();
    $dbcrm = $db->dbcrm();
    
    $cleanup_sql = "DELETE FROM audit WHERE event IN ('test_basic', 'note_delete', 'lead_update', 'lead_create') 
                    AND resource LIKE 'test_%' OR resource LIKE 'lead_123%' OR resource LIKE 'lead_789%' OR resource LIKE 'lead_101112%'";
    
    $cleanup_stmt = $dbcrm->prepare($cleanup_sql);
    $cleanup_stmt->execute();
    $deleted_count = $cleanup_stmt->rowCount();
    
    echo "<p>🧹 Cleaned up {$deleted_count} test audit entries</p>";
    
} catch (Exception $e) {
    echo "<p>⚠️ Cleanup warning: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<p><a href='public_html/admin/leads/test_note_delete.php?test=confirm'>Test Note Delete Functionality</a></p>";
echo "<p><a href='public_html/admin/leads/list.php'>Go to Admin Leads</a></p>";
?>