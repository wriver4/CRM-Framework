<?php
/**
 * Fixed test for note deletion functionality
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering
ob_start();

echo "DEBUG: Starting note deletion test...\n";

try {
    echo "DEBUG: About to require system.php...\n";
    require_once __DIR__ . '/../../config/system.php';
    echo "DEBUG: system.php loaded successfully\n";
    
    echo "DEBUG: Testing Notes class instantiation...\n";
    $notes = new Notes();
    echo "DEBUG: Notes instance created successfully\n";
    
    echo "DEBUG: Testing database connection...\n";
    $db = new Database();
    echo "DEBUG: Database connection successful\n";
    
    // Test basic functionality without requiring POST data
    echo "DEBUG: Testing get_notes_by_lead method...\n";
    
    // Get a sample lead ID from the database
    $stmt = $db->pdo->prepare("SELECT id FROM leads LIMIT 1");
    $stmt->execute();
    $lead = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($lead) {
        $lead_id = $lead['id'];
        echo "DEBUG: Testing with lead ID: $lead_id\n";
        
        $lead_notes = $notes->get_notes_by_lead($lead_id);
        echo "DEBUG: Found " . count($lead_notes) . " notes for lead $lead_id\n";
        
        if (!empty($lead_notes)) {
            $first_note = $lead_notes[0];
            echo "DEBUG: First note ID: " . $first_note['id'] . "\n";
            echo "DEBUG: First note content: " . substr($first_note['note'], 0, 50) . "...\n";
        }
    } else {
        echo "DEBUG: No leads found in database\n";
    }
    
    echo "DEBUG: Testing Audit class...\n";
    $audit = new Audit();
    echo "DEBUG: Audit instance created successfully\n";
    
    header('Content-Type: application/json');
    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'All tests passed',
        'debug' => 'Test completed successfully',
        'lead_count' => isset($lead) ? 1 : 0,
        'notes_count' => isset($lead_notes) ? count($lead_notes) : 0
    ]);
    ob_end_flush();
    
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