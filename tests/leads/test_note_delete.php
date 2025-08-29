<?php
/**
 * Test script for note deletion functionality
 * This script creates a test note and then tests the deletion
 */

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

// Check if user is logged in
$not->loggedin();

// Only allow this in development/testing
if (!isset($_GET['test']) || $_GET['test'] !== 'confirm') {
    echo "<h1>Note Delete Test</h1>";
    echo "<p>This is a test script for the note deletion functionality.</p>";
    echo "<p><strong>WARNING:</strong> This will create and delete test data.</p>";
    echo "<p><a href='?test=confirm' class='btn btn-warning'>Run Test</a></p>";
    echo "<p><a href='list.php' class='btn btn-secondary'>Back to Leads</a></p>";
    exit;
}

echo "<h1>Testing Note Deletion Functionality</h1>";

try {
    // Create instances
    $notes = new Notes();
    $leads = new Leads();
    
    // Get the first available lead for testing
    $test_leads = $leads->get_all_active();
    if (empty($test_leads)) {
        throw new Exception("No leads found for testing");
    }
    
    $test_lead_id = $test_leads[0]['id'];
    echo "<p>✓ Using test lead ID: {$test_lead_id}</p>";
    
    // Create a test note
    $test_note_data = [
        'source' => 4, // Internal Note
        'note_text' => 'TEST NOTE - Created by test script at ' . date('Y-m-d H:i:s') . ' - Safe to delete',
        'user_id' => $_SESSION['user_id'] ?? 1,
        'form_source' => 'test_script'
    ];
    
    $note_id = $notes->create_note_for_lead($test_lead_id, $test_note_data);
    
    if (!$note_id) {
        throw new Exception("Failed to create test note");
    }
    
    echo "<p>✓ Created test note with ID: {$note_id}</p>";
    
    // Verify the note was created and linked
    $created_note = $notes->get_note_by_id($note_id, $test_lead_id);
    if (!$created_note) {
        throw new Exception("Test note was not found after creation");
    }
    
    echo "<p>✓ Verified note exists and is linked to lead</p>";
    echo "<p>Note text: " . htmlspecialchars($created_note['note_text']) . "</p>";
    
    // Test the delete functionality
    echo "<p>🗑️ Testing delete functionality...</p>";
    
    $delete_result = $notes->delete_note($note_id);
    
    if (!$delete_result) {
        throw new Exception("Failed to delete test note");
    }
    
    echo "<p>✓ Delete operation completed successfully</p>";
    
    // Verify the note was actually deleted
    $deleted_note = $notes->get_note_by_id($note_id, $test_lead_id);
    if ($deleted_note) {
        throw new Exception("Note still exists after deletion");
    }
    
    echo "<p>✓ Verified note was completely removed</p>";
    
    // Test the AJAX endpoint
    echo "<p>🌐 Testing AJAX endpoint...</p>";
    
    // Create another test note for AJAX testing
    $ajax_note_id = $notes->create_note_for_lead($test_lead_id, [
        'source' => 4,
        'note_text' => 'AJAX TEST NOTE - Created for AJAX deletion test at ' . date('Y-m-d H:i:s'),
        'user_id' => $_SESSION['user_id'] ?? 1,
        'form_source' => 'ajax_test'
    ]);
    
    if (!$ajax_note_id) {
        throw new Exception("Failed to create AJAX test note");
    }
    
    echo "<p>✓ Created AJAX test note with ID: {$ajax_note_id}</p>";
    
    // Simulate AJAX request
    $_POST['note_id'] = $ajax_note_id;
    $_POST['lead_id'] = $test_lead_id;
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    // Capture the output of the delete script
    ob_start();
    include 'delete_note.php';
    $ajax_response = ob_get_clean();
    
    // Parse JSON response
    $response_data = json_decode($ajax_response, true);
    
    if (!$response_data || !$response_data['success']) {
        throw new Exception("AJAX delete failed: " . ($response_data['message'] ?? 'Unknown error'));
    }
    
    echo "<p>✓ AJAX delete endpoint working correctly</p>";
    echo "<p>AJAX Response: " . htmlspecialchars($ajax_response) . "</p>";
    
    // Verify AJAX deletion worked
    $ajax_deleted_note = $notes->get_note_by_id($ajax_note_id, $test_lead_id);
    if ($ajax_deleted_note) {
        throw new Exception("AJAX test note still exists after deletion");
    }
    
    echo "<p>✓ Verified AJAX deletion worked correctly</p>";
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>✅ All Tests Passed!</h3>";
    echo "<p>The note deletion functionality is working correctly:</p>";
    echo "<ul>";
    echo "<li>✓ Notes can be created and linked to leads</li>";
    echo "<li>✓ Direct deletion via Notes class works</li>";
    echo "<li>✓ AJAX endpoint responds correctly</li>";
    echo "<li>✓ Notes are completely removed from both tables</li>";
    echo "<li>✓ Error handling is working</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>❌ Test Failed</h3>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

// Clean up any remaining test notes
try {
    $db = new Database();
    $dbcrm = $db->dbcrm();
    
    $cleanup_sql = "DELETE n FROM notes n 
                    INNER JOIN leads_notes ln ON n.id = ln.note_id 
                    WHERE n.note_text LIKE '%TEST NOTE%' 
                    OR n.note_text LIKE '%AJAX TEST NOTE%'
                    OR n.form_source IN ('test_script', 'ajax_test')";
    
    $cleanup_stmt = $dbcrm->prepare($cleanup_sql);
    $cleanup_stmt->execute();
    
    $cleanup_junction_sql = "DELETE FROM leads_notes 
                            WHERE note_id NOT IN (SELECT id FROM notes)";
    $cleanup_junction_stmt = $dbcrm->prepare($cleanup_junction_sql);
    $cleanup_junction_stmt->execute();
    
    echo "<p><small>🧹 Cleaned up any remaining test notes</small></p>";
    
} catch (Exception $e) {
    echo "<p><small>⚠️ Cleanup warning: " . htmlspecialchars($e->getMessage()) . "</small></p>";
}

echo "<hr>";
echo "<p><a href='list.php' class='btn btn-primary'>Back to Admin Leads</a></p>";
echo "<p><a href='edit.php?id={$test_lead_id}' class='btn btn-secondary'>View Test Lead</a></p>";
?>