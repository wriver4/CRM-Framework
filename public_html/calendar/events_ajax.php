<?php
/**
 * Calendar AJAX Operations
 * 
 * Handles AJAX requests for calendar functionality
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

// Set JSON headers for AJAX responses
header('Content-Type: application/json');

try {
    // Initialize calendar class (other classes are already available globally)
    $calendar = new CalendarEvent();
    
    // Get user ID from session
    $user_id = Sessions::getUserId();
    
    // Get action from POST data
    $action = $_POST['action'] ?? '';
    
    // Verify CSRF token for state-changing operations
    if (in_array($action, ['quick_create', 'toggle_status', 'bulk_delete'])) {
        if (!$nonce->verify($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid CSRF token']);
            exit;
        }
    }
    
    switch ($action) {
        case 'quick_create':
            // Quick create event from dashboard or other interfaces
            $title = $_POST['title'] ?? '';
            $start_datetime = $_POST['start_datetime'] ?? '';
            $event_type = $_POST['event_type'] ?? 'meeting';
            
            if (empty($title) || empty($start_datetime)) {
                http_response_code(400);
                echo json_encode(['error' => 'Title and start datetime required']);
                exit;
            }
            
            try {
                $event_data = [
                    'title' => $title,
                    'start_datetime' => $start_datetime,
                    'event_type' => $event_type,
                    'description' => $_POST['description'] ?? '',
                    'priority' => $_POST['priority'] ?? 'medium'
                ];
                
                $event_id = $calendar->createEvent($event_data, $user_id);
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
            
        case 'toggle_status':
            // Toggle event status (completed/pending)
            $event_id = $_POST['event_id'] ?? '';
            $status = $_POST['status'] ?? '';
            
            if (empty($event_id) || empty($status)) {
                http_response_code(400);
                echo json_encode(['error' => 'Event ID and status required']);
                exit;
            }
            
            try {
                $result = $calendar->updateEvent($event_id, ['status' => $status], $user_id);
                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Event status updated successfully'
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to update event status']);
                }
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;
            
        case 'bulk_delete':
            // Bulk delete events
            $event_ids = $_POST['event_ids'] ?? [];
            
            if (empty($event_ids) || !is_array($event_ids)) {
                http_response_code(400);
                echo json_encode(['error' => 'Event IDs array required']);
                exit;
            }
            
            try {
                $deleted_count = 0;
                foreach ($event_ids as $event_id) {
                    if ($calendar->deleteEvent($event_id, $user_id)) {
                        $deleted_count++;
                    }
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => "Successfully deleted {$deleted_count} events",
                    'deleted_count' => $deleted_count
                ]);
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;
            
        case 'get_upcoming':
            // Get upcoming events for widgets/dashboards
            $limit = (int)($_POST['limit'] ?? 5);
            $days_ahead = (int)($_POST['days_ahead'] ?? 7);
            
            try {
                $events = $calendar->getUpcomingEvents($user_id, $limit, $days_ahead);
                echo json_encode([
                    'success' => true,
                    'events' => $events
                ]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to retrieve upcoming events']);
            }
            break;
            
        case 'search':
            // Search events
            $query = $_POST['query'] ?? '';
            $start_date = $_POST['start_date'] ?? null;
            $end_date = $_POST['end_date'] ?? null;
            
            if (empty($query)) {
                http_response_code(400);
                echo json_encode(['error' => 'Search query required']);
                exit;
            }
            
            try {
                $events = $calendar->searchEvents($user_id, $query, $start_date, $end_date);
                echo json_encode([
                    'success' => true,
                    'events' => $events
                ]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Search failed']);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
    
} catch (Exception $e) {
    error_log('Calendar AJAX Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}