<?php
/**
 * Calendar GET Operations
 * 
 * Handles all GET requests for calendar data retrieval
 * Uses framework patterns with simplified authentication
 * 
 * @author CRM Framework
 * @version 1.1
 */

// Start session if not already started
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Set JSON headers for API responses
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Simple authentication check using session variables directly (following framework pattern)
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Define constants needed for CalendarEvent class (only if not already defined)
    if (!defined('DOCROOT')) {
        define("DOCROOT", dirname(__DIR__, 2));
    }
    if (!defined('CLASSES')) {
        define("CLASSES", DOCROOT . "/classes/");
    }
    
    // Load required classes manually (avoiding full system.php)
    require_once CLASSES . 'Core/Database.php';
    require_once CLASSES . 'Models/CalendarEvent.php';
    
    // Initialize calendar class
    $calendar = new CalendarEvent();
    
    // Get user ID from session (following framework pattern)
    $user_id = $_SESSION['user_id'] ?? 1;
    
    // Get language file (simplified version)
    $lang = [];
    $lang_file = DOCROOT . '/public_html/admin/languages/en.php';
    if (file_exists($lang_file)) {
        $lang = include $lang_file;
    }
    
    // Get action from URL parameter
    $action = $_GET['action'] ?? '';
    $event_id = $_GET['id'] ?? null;
    
    switch ($action) {
        case 'events':
            // Get events for calendar view
            $start = $_GET['start'] ?? null;
            $end = $_GET['end'] ?? null;
            $events = $calendar->getEventsForCalendar($user_id, $start, $end, $lang);
            echo json_encode($events);
            break;
            
        case 'event':
            // Get single event
            if (!$event_id) {
                http_response_code(400);
                echo json_encode(['error' => 'Event ID required']);
                exit;
            }
            
            $event = $calendar->getEventById($event_id);
            if (!$event) {
                http_response_code(404);
                echo json_encode(['error' => 'Event not found']);
                exit;
            }
            
            // Check if user has access to this event
            if ($event['user_id'] != $user_id) {
                http_response_code(403);
                echo json_encode(['error' => 'Access denied']);
                exit;
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
    
} catch (Exception $e) {
    error_log('Calendar GET Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error', 'debug' => $e->getMessage()]);
}