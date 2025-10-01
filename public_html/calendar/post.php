<?php
/**
 * Calendar POST Operations
 * 
 * Handles all POST requests for calendar event creation
 * Follows framework security and audit patterns
 * 
 * @author CRM Framework
 * @version 1.0
 */

// Include system configuration (this loads all core classes and handles authentication)
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

// Check if user is logged in - API version (returns JSON instead of redirecting)
if (!Sessions::isLoggedIn()) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

// Set JSON headers for API responses
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Initialize calendar class (other classes are already available globally)
    $calendar = new CalendarEvent();
    
    // Get user ID from session
    $user_id = Sessions::getUserId();
    
    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }
    
    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Verify CSRF token for state-changing operations
    if (!$nonce->verify($input['csrf_token'] ?? '')) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid CSRF token']);
        exit;
    }
    
    // Get action from URL parameter
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'create':
            // Create new event
            if (!$input) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid input data']);
                exit;
            }
            
            // Validate required fields
            if (empty($input['title']) || empty($input['start_datetime']) || empty($input['event_type'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields: title, start_datetime, event_type']);
                exit;
            }
            
            try {
                $event_id = $calendar->createEvent($input, $user_id);
                if ($event_id) {
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Event created successfully',
                        'event_id' => $event_id
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to create event']);
                }
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;
            
        case 'from_next_action':
            // Create event from Next Action data
            if (!$input || empty($input['lead_id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Lead ID required']);
                exit;
            }
            
            try {
                $event_id = $calendar->createEventFromNextAction($input['lead_id'], $input, $user_id);
                if ($event_id) {
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Event created from Next Action',
                        'event_id' => $event_id
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to create event from Next Action']);
                }
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
    
} catch (Exception $e) {
    error_log('Calendar POST Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}