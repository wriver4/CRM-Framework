<?php
/**
 * Debug the delete note functionality
 */

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

// Check if user is logged in
$not->loggedin();

echo "<h1>Debug Delete Note Functionality</h1>";

// Test 1: Check if we can create a test note and then delete it
echo "<h2>Test 1: Create and Delete Test Note</h2>";

try {
    $notes = new Notes();
    $leads = new Leads();
    
    // Get the first lead for testing
    $test_leads = $leads->get_leads(1); // Get first lead
    if (empty($test_leads)) {
        echo "<p>❌ No leads found for testing</p>";
        exit;
    }
    
    $test_lead_id = $test_leads[0]['id'];
    echo "<p>Using test lead ID: {$test_lead_id}</p>";
    
    // Create a test note
    $test_note_text = "TEST NOTE FOR DELETION - " . date('Y-m-d H:i:s');
    $test_note_id = $notes->create_note($test_lead_id, $test_note_text, 1, $_SESSION['user_id'] ?? 1);
    
    if ($test_note_id) {
        echo "<p>✅ Created test note with ID: {$test_note_id}</p>";
        
        // Verify the note exists
        $created_note = $notes->get_note_by_id($test_note_id, $test_lead_id);
        if ($created_note) {
            echo "<p>✅ Note verified in database</p>";
            echo "<pre>" . print_r($created_note, true) . "</pre>";
            
            // Now test deletion
            $delete_result = $notes->delete_note($test_note_id);
            if ($delete_result) {
                echo "<p>✅ Note deleted successfully</p>";
                
                // Verify deletion
                $deleted_note = $notes->get_note_by_id($test_note_id, $test_lead_id);
                if (!$deleted_note) {
                    echo "<p>✅ Note confirmed deleted from database</p>";
                } else {
                    echo "<p>❌ Note still exists in database after deletion</p>";
                }
            } else {
                echo "<p>❌ Failed to delete note</p>";
            }
        } else {
            echo "<p>❌ Created note not found in database</p>";
        }
    } else {
        echo "<p>❌ Failed to create test note</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 2: Test the AJAX endpoint directly
echo "<hr><h2>Test 2: AJAX Endpoint Test</h2>";

?>

<div id="ajax-test-results"></div>

<button type="button" class="btn btn-primary" onclick="testAjaxEndpoint()">Test AJAX Endpoint</button>

<script>
function testAjaxEndpoint() {
    const resultsDiv = document.getElementById('ajax-test-results');
    resultsDiv.innerHTML = '<p>Testing AJAX endpoint...</p>';
    
    // Test with invalid note ID (should return error)
    const formData = new FormData();
    formData.append('note_id', 999999);
    formData.append('lead_id', <?= $test_lead_id ?? 1 ?>);
    
    console.log('Testing AJAX endpoint...');
    
    fetch('admin/leads/delete_note.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', [...response.headers.entries()]);
        return response.text();
    })
    .then(text => {
        console.log('Raw response:', text);
        resultsDiv.innerHTML += '<h4>Raw Response:</h4><pre>' + text + '</pre>';
        
        try {
            const data = JSON.parse(text);
            console.log('Parsed JSON:', data);
            resultsDiv.innerHTML += '<h4>Parsed JSON:</h4><pre>' + JSON.stringify(data, null, 2) + '</pre>';
            
            if (data.success === false && data.message) {
                resultsDiv.innerHTML += '<p>✅ AJAX endpoint working correctly (returned expected error)</p>';
            } else {
                resultsDiv.innerHTML += '<p>⚠️ Unexpected response format</p>';
            }
        } catch (e) {
            console.error('JSON parse error:', e);
            resultsDiv.innerHTML += '<p>❌ Invalid JSON response</p>';
            resultsDiv.innerHTML += '<p>JSON Error: ' + e.message + '</p>';
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        resultsDiv.innerHTML += '<p>❌ Network error: ' + error.message + '</p>';
    });
}
</script>

<hr>
<h2>Test 3: JavaScript Console Test</h2>
<p>Open browser developer tools (F12) and run this in the console:</p>
<pre><code>
// Test if the delete function exists on the edit page
if (typeof deleteNote === 'function') {
    console.log('✅ deleteNote function exists');
} else {
    console.log('❌ deleteNote function not found');
}

// Test DOM selection
const deleteButtons = document.querySelectorAll('.delete-note-btn');
console.log('Delete buttons found:', deleteButtons.length);

const timelineItems = document.querySelectorAll('.timeline-item');
console.log('Timeline items found:', timelineItems.length);
</code></pre>

<hr>
<p><a href="admin/leads/list.php" class="btn btn-success">Go to Admin Leads</a></p>
<p><a href="admin/leads/edit.php?id=<?= $test_lead_id ?? 1 ?>" class="btn btn-info">Test on Lead Edit Page</a></p>

<?php
// Clean up any test notes that might have been left behind
try {
    $cleanup_sql = "DELETE FROM notes WHERE note_text LIKE 'TEST NOTE FOR DELETION%'";
    $db = new Database();
    $dbcrm = $db->dbcrm();
    $stmt = $dbcrm->prepare($cleanup_sql);
    $stmt->execute();
    $deleted = $stmt->rowCount();
    if ($deleted > 0) {
        echo "<p><small>Cleaned up {$deleted} test notes</small></p>";
    }
} catch (Exception $e) {
    // Ignore cleanup errors
}
?>