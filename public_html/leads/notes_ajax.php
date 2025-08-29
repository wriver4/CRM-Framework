<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$lead_id = (int)($input['lead_id'] ?? 0);

if ($lead_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid lead ID']);
    exit;
}

try {
    $notes_class = new Notes();
    
    switch ($action) {
        case 'get_notes':
            $search = $input['search'] ?? '';
            $order = $input['order'] ?? 'DESC';
            
            $notes = $notes_class->get_notes_by_lead($lead_id, $search, $order);
            $count = $notes_class->get_notes_count_by_lead($lead_id, $search);
            
            // Format notes for display
            $formatted_notes = [];
            foreach ($notes as $note) {
                $formatted_notes[] = [
                    'id' => $note['id'],
                    'source' => $note['source'],
                    'source_name' => $notes_class->get_note_source_array()[$note['source']] ?? 'Unknown',
                    'source_badge' => $notes_class->get_source_badge_class($note['source']),
                    'note_text' => $note['note_text'],
                    'date_created' => $note['date_created'],
                    'date_formatted' => date('M j, Y \a\t g:i A', strtotime($note['date_created'])),
                    'user_name' => $note['full_name'] ?? $note['username'] ?? 'System',
                    'form_source' => $note['form_source'],
                    'contact_id' => $note['contact_id'],
                    'contact_name' => $note['contact_name'] ?? null
                ];
            }
            
            echo json_encode([
                'success' => true,
                'notes' => $formatted_notes,
                'total_count' => $count,
                'search' => $search,
                'order' => $order
            ]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}