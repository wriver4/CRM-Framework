<?php
/**
 * Simple test page for delete note functionality
 */

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

// Check if user is logged in
$not->loggedin();

// Get a test lead with notes
$leads = new Leads();
$notes = new Notes();

$test_leads = $leads->get_leads(10); // Get first 10 leads
$test_lead = null;
$test_notes = [];

// Find a lead with notes
foreach ($test_leads as $lead) {
    $lead_notes = $notes->get_notes_by_lead_id($lead['id']);
    if (!empty($lead_notes)) {
        $test_lead = $lead;
        $test_notes = $lead_notes;
        break;
    }
}

if (!$test_lead) {
    echo "<h1>No leads with notes found for testing</h1>";
    echo "<p><a href='list.php'>Go back to leads list</a></p>";
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Delete Note - Simple</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Simple Delete Note Test</h1>
        <p><strong>Testing Lead:</strong> <?= htmlspecialchars($test_lead['first_name'] . ' ' . $test_lead['family_name']) ?> (ID: <?= $test_lead['id'] ?>)</p>
        
        <div class="alert alert-info">
            <strong>Instructions:</strong>
            <ol>
                <li>Open browser developer tools (F12)</li>
                <li>Go to Console tab</li>
                <li>Click a delete button below</li>
                <li>Watch the console for debug messages</li>
            </ol>
        </div>
        
        <div id="alert-container"></div>
        
        <h3>Notes (<?= count($test_notes) ?>)</h3>
        
        <div class="timeline">
            <?php foreach ($test_notes as $note): ?>
            <div class="timeline-item mb-4" style="border: 1px solid #dee2e6; padding: 15px; border-radius: 8px;">
                <div class="timeline-content">
                    <div class="note-text mb-2" style="background: #f8f9fa; padding: 12px; border-radius: 8px;">
                        <?= nl2br(htmlspecialchars($note['note_text'])) ?>
                    </div>
                    <div class="d-flex justify-content-between align-items-end">
                        <small class="text-muted">
                            <i class="fa-solid fa-user fa-sm me-1"></i>
                            <?= htmlspecialchars($note['full_name'] ?? $note['username'] ?? 'System') ?>
                            - <?= date('M j, Y g:i A', strtotime($note['created_at'])) ?>
                        </small>
                        <button type="button" 
                                class="btn btn-outline-danger btn-sm delete-note-btn" 
                                data-note-id="<?= $note['id'] ?>" 
                                data-lead-id="<?= $test_lead['id'] ?>"
                                title="Delete this note">
                            <i class="fa-solid fa-trash fa-sm"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="mt-4">
            <a href="list.php" class="btn btn-secondary">Back to Leads</a>
            <a href="edit.php?id=<?= $test_lead['id'] ?>" class="btn btn-primary">Edit This Lead</a>
            <button type="button" class="btn btn-info" onclick="testConsole()">Test Console Functions</button>
        </div>
    </div>

    <script>
        console.log('Simple delete test page loaded');
        
        // Test console functions
        function testConsole() {
            console.log('=== CONSOLE TEST START ===');
            console.log('Delete buttons found:', document.querySelectorAll('.delete-note-btn').length);
            console.log('Timeline items found:', document.querySelectorAll('.timeline-item').length);
            console.log('=== CONSOLE TEST END ===');
        }
        
        // Handle note deletion - simplified version
        document.addEventListener('click', function(e) {
            if (e.target.closest('.delete-note-btn')) {
                console.log('=== DELETE BUTTON CLICKED ===');
                
                const button = e.target.closest('.delete-note-btn');
                const noteId = button.getAttribute('data-note-id');
                const leadId = button.getAttribute('data-lead-id');
                
                console.log('Note ID:', noteId);
                console.log('Lead ID:', leadId);
                console.log('Button element:', button);
                
                // Find the note element
                const noteElement = button.closest('.timeline-item');
                console.log('Note element found:', noteElement);
                
                // Confirm deletion
                if (confirm('Are you sure you want to delete this note? This action cannot be undone.')) {
                    console.log('User confirmed deletion');
                    deleteNote(noteId, leadId, button, noteElement);
                } else {
                    console.log('User cancelled deletion');
                }
            }
        });
        
        // Simplified delete function
        function deleteNote(noteId, leadId, button, noteElement) {
            console.log('=== STARTING DELETE PROCESS ===');
            
            // Disable button and show loading state
            button.disabled = true;
            const originalContent = button.innerHTML;
            button.innerHTML = '<i class="fa-solid fa-spinner fa-spin fa-sm"></i> Deleting...';
            
            // Create form data
            const formData = new FormData();
            formData.append('note_id', noteId);
            formData.append('lead_id', leadId);
            
            console.log('Form data created:', {note_id: noteId, lead_id: leadId});
            
            // Send AJAX request
            console.log('Sending AJAX request to: /admin/leads/delete_note.php');
            
            fetch('/admin/leads/delete_note.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response received:', response);
                console.log('Response status:', response.status);
                console.log('Response ok:', response.ok);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                console.log('Raw response text:', text);
                
                try {
                    const data = JSON.parse(text);
                    console.log('Parsed JSON data:', data);
                    
                    if (data.success) {
                        console.log('Delete successful, removing element...');
                        
                        // Add visual feedback
                        noteElement.style.backgroundColor = '#d4edda';
                        noteElement.style.borderColor = '#c3e6cb';
                        
                        // Animate removal
                        setTimeout(() => {
                            noteElement.style.transition = 'all 0.5s ease-out';
                            noteElement.style.opacity = '0';
                            noteElement.style.transform = 'translateX(-50px)';
                            
                            setTimeout(() => {
                                noteElement.remove();
                                console.log('✅ Note element removed from DOM');
                                showAlert('success', 'Note deleted successfully!');
                                
                                // Update count
                                const remainingNotes = document.querySelectorAll('.timeline-item').length;
                                console.log('Remaining notes:', remainingNotes);
                                
                            }, 500);
                        }, 100);
                        
                    } else {
                        console.error('Delete failed:', data.message);
                        
                        // Re-enable button
                        button.disabled = false;
                        button.innerHTML = originalContent;
                        
                        showAlert('danger', data.message || 'Failed to delete note');
                    }
                    
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    console.error('Response was:', text);
                    
                    // Re-enable button
                    button.disabled = false;
                    button.innerHTML = originalContent;
                    
                    showAlert('danger', 'Invalid response from server');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                
                // Re-enable button
                button.disabled = false;
                button.innerHTML = originalContent;
                
                showAlert('danger', 'Network error: ' + error.message);
            });
        }
        
        // Simple alert function
        function showAlert(type, message) {
            console.log('Showing alert:', type, message);
            
            const container = document.getElementById('alert-container');
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            container.appendChild(alertDiv);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
        
        console.log('Simple delete test script loaded successfully');
    </script>
</body>
</html>