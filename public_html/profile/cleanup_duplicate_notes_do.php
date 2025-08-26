<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();

// Check if user has admin permissions using rid
if (!isset($_SESSION['rid']) || $_SESSION['rid'] != 1) {
    $_SESSION['error_message'] = 'Access denied. Admin privileges required.';
    header('Location: /dashboard.php');
    exit;
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error_message'] = 'Security token mismatch. Please try again.';
    header('Location: cleanup_duplicate_notes.php');
    exit;
}

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cleanup_duplicate_notes.php');
    exit;
}

$database = new Database();
$pdo = $database->dbcrm();
$action = $_POST['action'] ?? '';

// Initialize response variables
$success = false;
$message = '';
$stats = [
    'notes_removed' => 0,
    'junction_updated' => 0,
    'groups_processed' => 0,
    'backup_created' => false
];

try {
    $pdo->beginTransaction();
    
    // Create backup tables
    $backup_timestamp = date('Y_m_d_H_i_s');
    $notes_backup_table = "notes_backup_cleanup_{$backup_timestamp}";
    $junction_backup_table = "leads_notes_backup_cleanup_{$backup_timestamp}";
    
    $pdo->exec("CREATE TABLE {$notes_backup_table} AS SELECT * FROM notes");
    $pdo->exec("CREATE TABLE {$junction_backup_table} AS SELECT * FROM leads_notes");
    $stats['backup_created'] = true;
    
    if ($action === 'safe_cleanup') {
        // Process all safe cleanup candidates
        $stats = processSafeCleanup($pdo);
        $message = "Safe cleanup completed successfully. Processed {$stats['groups_processed']} duplicate groups, removed {$stats['notes_removed']} duplicate notes.";
        $success = true;
        
    } elseif ($action === 'cleanup_single') {
        // Process single duplicate group
        $lead_id = (int)$_POST['lead_id'];
        $note_text = $_POST['note_text'];
        $note_ids = $_POST['note_ids'];
        
        if ($lead_id && $note_text && $note_ids) {
            $stats = processSingleCleanup($pdo, $lead_id, $note_text, $note_ids);
            $message = "Cleanup completed for lead #{$lead_id}. Removed {$stats['notes_removed']} duplicate notes.";
            $success = true;
        } else {
            throw new Exception('Missing required parameters for single cleanup');
        }
        
    } else {
        throw new Exception('Invalid action specified');
    }
    
    $pdo->commit();
    
    // Log the cleanup activity
    $logit = new Logit();
    $logit->log_activity(
        $_SESSION['user_id'], 
        'duplicate_notes_cleanup', 
        json_encode([
            'action' => $action,
            'stats' => $stats,
            'backup_tables' => [$notes_backup_table, $junction_backup_table]
        ])
    );
    
} catch (Exception $e) {
    $pdo->rollBack();
    $success = false;
    $message = 'Cleanup failed: ' . $e->getMessage();
    error_log('Duplicate notes cleanup error: ' . $e->getMessage());
}

// Set session messages
if ($success) {
    $_SESSION['success_message'] = $message;
    if ($stats['backup_created']) {
        $_SESSION['info_message'] = "Backup tables created: {$notes_backup_table}, {$junction_backup_table}";
    }
} else {
    $_SESSION['error_message'] = $message;
}

header('Location: cleanup_duplicate_notes.php');
exit;

/**
 * Process all safe cleanup candidates
 */
function processSafeCleanup($pdo) {
    $stats = ['notes_removed' => 0, 'junction_updated' => 0, 'groups_processed' => 0];
    
    // Get safe cleanup candidates
    $query = "
        SELECT 
            ln.lead_id,
            n.note_text,
            GROUP_CONCAT(DISTINCT n.id ORDER BY n.id) as note_ids,
            COUNT(DISTINCT n.id) as duplicate_count,
            MIN(n.date_created) as first_created,
            MAX(n.date_created) as last_created,
            TIMESTAMPDIFF(MINUTE, MIN(n.date_created), MAX(n.date_created)) as minutes_between
        FROM leads_notes ln
        INNER JOIN notes n ON ln.note_id = n.id
        WHERE n.note_text != '' AND n.note_text IS NOT NULL
        GROUP BY ln.lead_id, n.note_text
        HAVING COUNT(DISTINCT n.id) > 1
            AND TIMESTAMPDIFF(MINUTE, MIN(n.date_created), MAX(n.date_created)) <= 10
            AND COUNT(DISTINCT n.id) <= 5
            AND CHAR_LENGTH(n.note_text) >= 5
        ORDER BY ln.lead_id
    ";
    
    $stmt = $pdo->query($query);
    $candidates = $stmt->fetchAll();
    
    foreach ($candidates as $candidate) {
        $group_stats = processSingleCleanup(
            $pdo, 
            $candidate['lead_id'], 
            $candidate['note_text'], 
            $candidate['note_ids']
        );
        
        $stats['notes_removed'] += $group_stats['notes_removed'];
        $stats['junction_updated'] += $group_stats['junction_updated'];
        $stats['groups_processed']++;
    }
    
    return $stats;
}

/**
 * Process cleanup for a single duplicate group
 */
function processSingleCleanup($pdo, $lead_id, $note_text, $note_ids) {
    $stats = ['notes_removed' => 0, 'junction_updated' => 0];
    
    // Parse note IDs
    $note_id_array = explode(',', $note_ids);
    $note_id_array = array_map('trim', $note_id_array);
    $note_id_array = array_filter($note_id_array, 'is_numeric');
    
    if (count($note_id_array) < 2) {
        throw new Exception('Invalid note IDs for cleanup');
    }
    
    // Keep the first (oldest) note, remove the others
    $keep_note_id = (int)$note_id_array[0];
    $remove_note_ids = array_slice($note_id_array, 1);
    
    // Validate that we're working with the correct lead and notes
    $validation_query = "
        SELECT COUNT(*) as count 
        FROM leads_notes ln 
        INNER JOIN notes n ON ln.note_id = n.id 
        WHERE ln.lead_id = :lead_id 
            AND n.note_text = :note_text 
            AND n.id IN (" . implode(',', array_map('intval', $note_id_array)) . ")
    ";
    
    $validation_stmt = $pdo->prepare($validation_query);
    $validation_stmt->execute([
        ':lead_id' => $lead_id,
        ':note_text' => $note_text
    ]);
    
    $validation_result = $validation_stmt->fetch();
    if ($validation_result['count'] != count($note_id_array)) {
        throw new Exception('Validation failed: Note IDs do not match expected content');
    }
    
    // Update junction table to point all references to the kept note
    $update_query = "
        UPDATE leads_notes 
        SET note_id = :keep_note_id 
        WHERE lead_id = :lead_id 
            AND note_id IN (" . implode(',', array_map('intval', $remove_note_ids)) . ")
    ";
    
    $update_stmt = $pdo->prepare($update_query);
    $update_stmt->execute([
        ':keep_note_id' => $keep_note_id,
        ':lead_id' => $lead_id
    ]);
    
    $stats['junction_updated'] = $update_stmt->rowCount();
    
    // Remove duplicate junction records (same lead_id + note_id combination)
    $cleanup_junction_query = "
        DELETE ln1 FROM leads_notes ln1
        INNER JOIN leads_notes ln2 ON ln1.lead_id = ln2.lead_id 
            AND ln1.note_id = ln2.note_id 
            AND ln1.id > ln2.id
        WHERE ln1.lead_id = :lead_id AND ln1.note_id = :keep_note_id
    ";
    
    $cleanup_stmt = $pdo->prepare($cleanup_junction_query);
    $cleanup_stmt->execute([
        ':lead_id' => $lead_id,
        ':keep_note_id' => $keep_note_id
    ]);
    
    // Delete the duplicate notes
    if (!empty($remove_note_ids)) {
        $delete_query = "
            DELETE FROM notes 
            WHERE id IN (" . implode(',', array_map('intval', $remove_note_ids)) . ")
        ";
        
        $delete_stmt = $pdo->prepare($delete_query);
        $delete_stmt->execute();
        
        $stats['notes_removed'] = $delete_stmt->rowCount();
    }
    
    return $stats;
}

/**
 * Enhanced logging class integration
 */
class DuplicateNotesLogger {
    private $logit;
    
    public function __construct() {
        $this->logit = new Logit();
    }
    
    public function logCleanup($user_id, $action, $stats, $backup_tables = []) {
        $log_data = [
            'action' => $action,
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $user_id,
            'statistics' => $stats,
            'backup_tables' => $backup_tables,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        
        $this->logit->log_activity(
            $user_id,
            'duplicate_notes_cleanup',
            json_encode($log_data)
        );
    }
}
?>