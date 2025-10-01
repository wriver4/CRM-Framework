<?php
/**
 * Calendar PUT Operations
 * 
 * Handles all PUT requests for calendar event updates
 * Follows framework security and audit patterns
 * 
 * @author CRM Framework
 * @version 1.0
 */

// Include system configuration (this loads all core classes and handles authentication)
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

// Set JSON headers for API responses
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Check if user is logged in - API version (returns JSON instead of redirecting)
if (!Sessions::isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

try {
    // Initialize calendar class (other classes are already available globally)
    $calendar = new CalendarEvent();
    
    // Get user ID from session
    $user_id = Sessions::getUserId();
    
    // Only allow PUT requests
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
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
    
    // Get action and event ID from URL parameters
    $action = $_GET['action'] ?? '';
    $event_id = $_GET['id'] ?? null;
    
    switch ($action) {
        case 'update':
            // Update event
            if (!$event_id) {
                http_response_code(400);
                echo json_encode(['error' => 'Event ID required']);
                exit;
            }
            
            if (!$input) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid input data']);
                exit;
            }
            
            try {
                $result = $calendar->updateEvent($event_id, $input, $user_id);
                if ($result) {
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Event updated successfully'
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to update event']);
                }
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;
            
        case 'move':
            // Move event (drag & drop)
            if (!$event_id || !$input || empty($input['start_datetime'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Event ID and start_datetime required']);
                exit;
            }
            
            try {
                $update_data = [
                    'start_datetime' => $input['start_datetime'],
                    'end_datetime' => $input['end_datetime'] ?? null
                ];
                
                $result = $calendar->updateEvent($event_id, $update_data, $user_id);
                if ($result) {
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Event moved successfully'
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to move event']);
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
    error_log('Calendar PUT Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}