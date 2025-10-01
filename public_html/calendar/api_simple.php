<?php
/**
 * Simplified Calendar API
 * Bypasses full system initialization to avoid Whoops/Monolog issues
 */

// Basic error handling
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to browser
ini_set('log_errors', 1);
ini_set('error_log', dirname(__DIR__, 2) . '/logs/php_errors.log');

// Start session
session_start();

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

// Basic autoloader
spl_autoload_register(function ($class_name) {
    if (strpos($class_name, '\\') !== false) {
        return;
    }
    
    $directories = ['Core', 'Models', 'Views', 'Utilities', 'Logging'];
    
    foreach ($directories as $dir) {
        $file = dirname(__DIR__, 2) . '/classes/' . $dir . '/' . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
    
    $file = dirname(__DIR__, 2) . '/classes/' . $class_name . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

try {
    // Check if user is logged in (simplified check)
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required']);
        exit;
    }
    
    // Only allow GET requests for now
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }
    
    // Initialize calendar class
    $calendar = new CalendarEvent();
    $user_id = $_SESSION['user_id'];
    
    // Get action from URL parameter
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'events':
            // Get events for calendar view
            $start = $_GET['start'] ?? null;
            $end = $_GET['end'] ?? null;
            $events = $calendar->getEventsForCalendar($user_id, $start, $end);
            echo json_encode($events);
            break;
            
        case 'test':
            // Simple test endpoint
            echo json_encode([
                'status' => 'success',
                'message' => 'Calendar API is working',
                'user_id' => $user_id,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
    
} catch (Exception $e) {
    error_log('Calendar API Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error', 'debug' => $e->getMessage()]);
}
?>