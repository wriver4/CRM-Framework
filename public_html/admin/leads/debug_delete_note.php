<?php
/**
 * Debug version of delete_note.php to identify 500 error cause
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering
ob_start();

echo "DEBUG: Starting delete_note debug...\n";

try {
    echo "DEBUG: About to require system.php...\n";
    require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
    echo "DEBUG: system.php loaded successfully\n";
    
    echo "DEBUG: Checking session...\n";
    if (!isset($_SESSION)) {
        echo "DEBUG: No session found\n";
    } else {
        echo "DEBUG: Session exists\n";
        if (!isset($_SESSION['loggedin'])) {
            echo "DEBUG: Not logged in\n";
        } else {
            echo "DEBUG: User is logged in\n";
        }
    }
    
    echo "DEBUG: Checking request method: " . $_SERVER['REQUEST_METHOD'] . "\n";
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo "DEBUG: Not a POST request\n";
        http_response_code(405);
        header('Content-Type: application/json');
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Method not allowed', 'debug' => 'Not POST']);
        ob_end_flush();
        exit;
    }
    
    echo "DEBUG: Checking POST parameters...\n";
    echo "DEBUG: POST data: " . print_r($_POST, true) . "\n";
    
    if (!isset($_POST['note_id']) || !isset($_POST['lead_id'])) {
        echo "DEBUG: Missing parameters\n";
        http_response_code(400);
        header('Content-Type: application/json');
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Missing required parameters', 'debug' => 'Missing params']);
        ob_end_flush();
        exit;
    }
    
    $note_id = (int)$_POST['note_id'];
    $lead_id = (int)$_POST['lead_id'];
    
    echo "DEBUG: note_id = $note_id, lead_id = $lead_id\n";
    
    if ($note_id <= 0 || $lead_id <= 0) {
        echo "DEBUG: Invalid parameters\n";
        http_response_code(400);
        header('Content-Type: application/json');
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Invalid parameters', 'debug' => 'Invalid params']);
        ob_end_flush();
        exit;
    }
    
    echo "DEBUG: About to create Notes instance...\n";
    $notes = new Notes();
    echo "DEBUG: Notes instance created successfully\n";
    
    echo "DEBUG: About to get note by ID...\n";
    $note = $notes->get_note_by_id($note_id, $lead_id);
    echo "DEBUG: get_note_by_id result: " . print_r($note, true) . "\n";
    
    if (!$note) {
        echo "DEBUG: Note not found\n";
        http_response_code(404);
        header('Content-Type: application/json');
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Note not found', 'debug' => 'Note not found']);
        ob_end_flush();
        exit;
    }
    
    echo "DEBUG: About to delete note...\n";
    $result = $notes->delete_note($note_id);
    echo "DEBUG: delete_note result: " . ($result ? 'true' : 'false') . "\n";
    
    if ($result) {
        echo "DEBUG: Note deleted successfully\n";
        
        // Try to create audit log
        try {
            echo "DEBUG: About to create Audit instance...\n";
            $audit = new Audit();
            echo "DEBUG: Audit instance created\n";
            
            $audit->log(
                $_SESSION['user_id'] ?? 1,
                'note_delete',
                "lead_{$lead_id}_note_{$note_id}",
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                $lead_id,
                "Note deleted from lead #{$lead_id}"
            );
            echo "DEBUG: Audit log created\n";
        } catch (Exception $audit_e) {
            echo "DEBUG: Audit error: " . $audit_e->getMessage() . "\n";
        }
        
        header('Content-Type: application/json');
        ob_clean();
        echo json_encode([
            'success' => true, 
            'message' => 'Note deleted successfully',
            'debug' => 'Success'
        ]);
        ob_end_flush();
    } else {
        echo "DEBUG: Failed to delete note\n";
        http_response_code(500);
        header('Content-Type: application/json');
        ob_clean();
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to delete note',
            'debug' => 'Delete failed'
        ]);
        ob_end_flush();
    }
    
} catch (Exception $e) {
    echo "DEBUG: Exception caught: " . $e->getMessage() . "\n";
    echo "DEBUG: Stack trace: " . $e->getTraceAsString() . "\n";
    
    http_response_code(500);
    header('Content-Type: application/json');
    ob_clean();
    echo json_encode([
        'success' => false, 
        'message' => 'Exception: ' . $e->getMessage(),
        'debug' => 'Exception caught',
        'trace' => $e->getTraceAsString()
    ]);
    ob_end_flush();
}
?>