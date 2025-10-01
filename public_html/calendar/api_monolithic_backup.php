<?php
/**
 * Calendar API Endpoints
 * 
 * RESTful API for calendar events with full CRM integration
 * Follows framework security and audit patterns
 * 
 * @author CRM Framework
 * @version 1.0
 */

// Include system configuration and classes
require_once '../../config/system.php';
require_once CLASSES . 'Models/CalendarEvent.php';
require_once CLASSES . 'Core/Sessions.php';
require_once CLASSES . 'Core/Security.php';
require_once CLASSES . 'Core/Nonce.php';

// Set JSON headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Initialize classes
    $sessions = new Sessions();
    $security = new Security();
    $nonce = new Nonce();
    $calendar = new CalendarEvent();
    
    // Check if user is logged in
    if (!$sessions->isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required']);
        exit;
    }
    
    $user_id = $sessions->getUserId();
    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Get language file
    $lang = include DOCPUBLIC . '/admin/languages/en.php';
    
    // Get action from URL parameter
    $action = $_GET['action'] ?? '';
    $event_id = $_GET['id'] ?? null;
    
    switch ($method) {
        case 'GET':
            handleGetRequest($calendar, $user_id, $action, $event_id);
            break;
            
        case 'POST':
            // Verify CSRF token for state-changing operations
            if (!$nonce->verify($input['csrf_token'] ?? '')) {
                http_response_code(403);
                echo json_encode(['error' => 'Invalid CSRF token']);
                exit;
            }
            handlePostRequest($calendar, $user_id, $action, $input);
            break;
            
        case 'PUT':
            // Verify CSRF token for state-changing operations
            if (!$nonce->verify($input['csrf_token'] ?? '')) {
                http_response_code(403);
                echo json_encode(['error' => 'Invalid CSRF token']);
                exit;
            }
            handlePutRequest($calendar, $user_id, $action, $event_id, $input);
            break;
            
        case 'DELETE':
            // Verify CSRF token for state-changing operations
            $csrf_token = $_GET['csrf_token'] ?? $input['csrf_token'] ?? '';
            if (!$nonce->verify($csrf_token)) {
                http_response_code(403);
                echo json_encode(['error' => 'Invalid CSRF token']);
                exit;
            }
            handleDeleteRequest($calendar, $user_id, $action, $event_id);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
    
} catch (Exception $e) {
    error_log('Calendar API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}

/**
 * Handle GET requests
 */
function handleGetRequest($calendar, $user_id, $action, $event_id)
{
    switch ($action) {
        case 'events':
            // Get events for calendar view
            $start = $_GET['start'] ?? null;
            $end = $_GET['end'] ?? null;
            $events = $calendar->getEventsForCalendar($user_id, $start, $end);
            echo json_encode($events);
            break;
            
        case 'event':
            // Get single event
            if (!$event_id) {
                http_response_code(400);
                echo json_encode(['error' => 'Event ID required']);
                return;
            }
            
            $event = $calendar->getEventById($event_id);
            if (!$event) {
                http_response_code(404);
                echo json_encode(['error' => 'Event not found']);
                return;
            }
            
            // Check if user has access to this event
            if ($event['user_id'] != $user_id) {
                http_response_code(403);
                echo json_encode(['error' => 'Access denied']);
                return;
            }
            
            echo json_encode($event);
            break;
            
        case 'today':
            // Get today's events for dashboard
            $limit = (int)($_GET['limit'] ?? 10);
            $events = $calendar->getTodaysEvents($user_id, $limit);
            echo json_encode($events);
            break;
            
        case 'stats':
            // Get event statistics
            $date = $_GET['date'] ?? null;
            $stats = $calendar->getEventStats($user_id, $date);
            echo json_encode($stats);
            break;
            
        case 'types':
            // Get event types
            $types = $calendar->getEventTypes($lang);
            echo json_encode($types);
            break;
            
        case 'priorities':
            // Get priority levels
            $priorities = $calendar->getPriorities($lang);
            echo json_encode($priorities);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
}

/**
 * Handle POST requests (create)
 */
function handlePostRequest($calendar, $user_id, $action, $input)
{
    switch ($action) {
        case 'create':
            // Create new event
            if (!$input) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid input data']);
                return;
            }
            
            // Validate required fields
            if (empty($input['title']) || empty($input['start_datetime']) || empty($input['event_type'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields: title, start_datetime, event_type']);
                return;
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
                return;
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
}

/**
 * Handle PUT requests (update)
 */
function handlePutRequest($calendar, $user_id, $action, $event_id, $input)
{
    switch ($action) {
        case 'update':
            // Update event
            if (!$event_id) {
                http_response_code(400);
                echo json_encode(['error' => 'Event ID required']);
                return;
            }
            
            if (!$input) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid input data']);
                return;
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
                return;
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
}

/**
 * Handle DELETE requests
 */
function handleDeleteRequest($calendar, $user_id, $action, $event_id)
{
    switch ($action) {
        case 'delete':
            // Delete event
            if (!$event_id) {
                http_response_code(400);
                echo json_encode(['error' => 'Event ID required']);
                return;
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
}