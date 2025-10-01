<?php
/**
 * Calendar DELETE Operations
 * 
 * Handles all DELETE requests for calendar event removal
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
header('Access-Control-Allow-Methods: DELETE, OPTIONS');
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
    
    // Only allow DELETE requests
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }
    
    // Get input data for CSRF token
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Verify CSRF token for state-changing operations
    $csrf_token = $_GET['csrf_token'] ?? $input['csrf_token'] ?? '';
    if (!$nonce->verify($csrf_token)) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid CSRF token']);
        exit;
    }
    
    // Get action and event ID from URL parameters
    $action = $_GET['action'] ?? '';
    $event_id = $_GET['id'] ?? null;
    
    switch ($action) {
        case 'delete':
            // Delete event
            if (!$event_id) {
                http_response_code(400);
                echo json_encode(['error' => 'Event ID required']);
                exit;
            }
            
            try {
                $result = $calendar->deleteEvent($event_id, $user_id);
                if ($result) {
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Event deleted successfully'
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to delete event']);
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
    error_log('Calendar DELETE Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}