<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();

if ($_POST) {
    // Get PDO connection
    $database = new Database();
    $pdo = $database->dbcrm();
    
    $lead_id = (int)$_POST['lead_id'];
    $notes = $_POST['notes'] ?? '';
    $lead_lost_notes = $_POST['lead_lost_notes'] ?? '';
    $action = $_POST['action'] ?? 'save';
    $next_id = $_POST['next_id'] ?? null;
    
    $success = false;
    $message = '';
    
    if ($lead_id > 0) {
        try {
            switch ($action) {
                case 'clear_lead_lost_notes':
                    $sql = "UPDATE leads SET lead_lost_notes = NULL WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $success = $stmt->execute([$lead_id]);
                    $message = $success ? 'Lead lost notes cleared successfully.' : 'Error clearing lead lost notes.';
                    break;
                    
                case 'save':
                case 'save_and_next':
                    $sql = "UPDATE leads SET notes = ?, lead_lost_notes = ? WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $success = $stmt->execute([$notes, $lead_lost_notes, $lead_id]);
                    $message = $success ? 'Changes saved successfully.' : 'Error saving changes.';
                    break;
            }
        } catch (PDOException $e) {
            $success = false;
            $message = 'Database error: ' . $e->getMessage();
        }
        
        if ($success) {
            // If save_and_next and there's a next ID, redirect to next record
            if ($action === 'save_and_next' && $next_id) {
                header("Location: compare_notes.php?id=" . $next_id . "&saved=1");
                exit;
            }
            
            // Otherwise redirect back to current record
            header("Location: compare_notes.php?id=" . $lead_id . "&saved=1");
            exit;
        } else {
            // Error occurred, redirect back with error
            header("Location: compare_notes.php?id=" . $lead_id . "&error=1");
            exit;
        }
    }
}

// If we get here, something went wrong
header("Location: compare_notes.php?error=1");
exit;
