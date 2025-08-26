<?php
/**
 * Admin Lead Note Delete Handler
 * Handles AJAX requests to delete notes from leads
 * Fixed version that bypasses problematic system initialization
 */

// Enable error logging
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', dirname($_SERVER['DOCUMENT_ROOT']) . '/logs/delete_note_errors.log');

// Log request start
error_log("=== DELETE NOTE REQUEST STARTED (FIXED VERSION) ===");
error_log("Time: " . date('Y-m-d H:i:s'));
error_log("Method: " . $_SERVER['REQUEST_METHOD']);
error_log("POST data: " . print_r($_POST, true));

// Start output buffering to ensure clean JSON response
ob_start();

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in (AJAX-friendly)
if (!isset($_SESSION) || !isset($_SESSION['loggedin'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    ob_end_flush();
    exit;
}

// Clear any output that might have been generated
ob_clean();

// Set JSON response header
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    ob_end_flush();
    exit;
}

// Check for required parameters
if (!isset($_POST['note_id']) || !isset($_POST['lead_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    ob_end_flush();
    exit;
}

$note_id = (int)$_POST['note_id'];
$lead_id = (int)$_POST['lead_id'];

// Validate parameters
if ($note_id <= 0 || $lead_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    ob_end_flush();
    exit;
}

try {
    // Custom autoloader for classes
    spl_autoload_register(function ($class_name) {
        if (strpos($class_name, '\\') !== false) {
            return;
        }
        $file = dirname($_SERVER['DOCUMENT_ROOT']) . '/classes/' . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    });

    error_log("Creating Database connection directly...");
    
    // Create database connection directly
    $database = new Database();
    $pdo = $database->dbcrm();
    error_log("Database connection established successfully");
    
    // Verify the note exists and belongs to this lead
    error_log("Verifying note $note_id belongs to lead $lead_id");
    $verify_sql = "SELECT n.id, n.note_text, ln.lead_id 
                   FROM notes n
                   INNER JOIN leads_notes ln ON n.id = ln.note_id
                   WHERE n.id = :note_id AND ln.lead_id = :lead_id";
    
    $verify_stmt = $pdo->prepare($verify_sql);
    $verify_stmt->execute([':note_id' => $note_id, ':lead_id' => $lead_id]);
    $note = $verify_stmt->fetch();
    
    if (!$note) {
        error_log("Note not found or doesn't belong to this lead");
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Note not found or does not belong to this lead']);
        ob_end_flush();
        exit;
    }
    
    error_log("Note verified successfully, proceeding with deletion");
    
    // Begin transaction for safe deletion
    $pdo->beginTransaction();
    
    try {
        // Delete the lead links first
        $link_sql = "DELETE FROM leads_notes WHERE note_id = :note_id";
        $link_stmt = $pdo->prepare($link_sql);
        $link_result = $link_stmt->execute([':note_id' => $note_id]);
        error_log("Lead-note link deletion result: " . ($link_result ? "success" : "failed"));
        
        // Delete the note
        $note_sql = "DELETE FROM notes WHERE id = :id";
        $note_stmt = $pdo->prepare($note_sql);
        $note_result = $note_stmt->execute([':id' => $note_id]);
        error_log("Note deletion result: " . ($note_result ? "success" : "failed"));
        
        if ($link_result && $note_result) {
            $pdo->commit();
            error_log("Transaction committed successfully");
            
            // Try to log audit (but don't fail if audit fails)
            try {
                error_log("Attempting to create audit log entry...");
                $audit_sql = "INSERT INTO audit (user_id, event, resource, useragent, ip, location, data, created_at) 
                              VALUES (:user_id, :event, :resource, :useragent, :ip, :location, :data, NOW())";
                $audit_stmt = $pdo->prepare($audit_sql);
                $audit_result = $audit_stmt->execute([
                    ':user_id' => $_SESSION['user_id'] ?? 1,
                    ':event' => 'note_delete',
                    ':resource' => "lead_{$lead_id}_note_{$note_id}",
                    ':useragent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                    ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
                    ':location' => $lead_id,
                    ':data' => "Note deleted from lead #{$lead_id}: " . substr($note['note_text'], 0, 50) . '...'
                ]);
                error_log("Audit log result: " . ($audit_result ? "success" : "failed"));
            } catch (Exception $audit_e) {
                error_log("Audit error (non-fatal): " . $audit_e->getMessage());
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'Note deleted successfully'
            ]);
            ob_end_flush();
        } else {
            $pdo->rollBack();
            error_log("Deletion failed, transaction rolled back");
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to delete note'
            ]);
            ob_end_flush();
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Transaction error: " . $e->getMessage());
        throw $e;
    }
    
} catch (Exception $e) {
    // Log the error
    error_log("Note deletion error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred while deleting the note: ' . $e->getMessage()
    ]);
    ob_end_flush();
}
?>