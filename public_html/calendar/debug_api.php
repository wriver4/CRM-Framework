<?php
/**
 * Debug API - Simple test to identify calendar API issues
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test 1: Check if we can load the system configuration
try {
    echo "Loading system configuration...\n";
    require_once dirname(__DIR__, 2) . '/config/system.php';
    echo "System configuration loaded successfully!\n";
} catch (Exception $e) {
    echo "Error loading system configuration: " . $e->getMessage() . "\n";
    exit;
}

// Test 2: Check if Sessions class is available
try {
    echo "Testing Sessions class...\n";
    $isLoggedIn = Sessions::isLoggedIn();
    $userId = Sessions::getUserId();
    echo "Sessions class working! Logged in: " . ($isLoggedIn ? 'Yes' : 'No') . ", User ID: " . ($userId ?: 'None') . "\n";
} catch (Exception $e) {
    echo "Error with Sessions class: " . $e->getMessage() . "\n";
}

// Test 3: Check if CalendarEvent class is available
try {
    echo "Testing CalendarEvent class...\n";
    $calendar = new CalendarEvent();
    echo "CalendarEvent class instantiated successfully!\n";
} catch (Exception $e) {
    echo "Error with CalendarEvent class: " . $e->getMessage() . "\n";
}

// Test 4: Check session variables
echo "Session variables:\n";
foreach ($_SESSION as $key => $value) {
    if (is_array($value)) {
        echo "  $key: (array with " . count($value) . " items)\n";
    } else {
        echo "  $key: " . substr(print_r($value, true), 0, 50) . "\n";
    }
}

// Test 5: Try the actual API call
try {
    header('Content-Type: application/json');
    if (Sessions::isLoggedIn()) {
        $calendar = new CalendarEvent();
        $user_id = Sessions::getUserId();
        $lang = include dirname(__DIR__) . '/admin/languages/en.php';
        
        // Test getEventsForCalendar method
        $events = $calendar->getEventsForCalendar($user_id, null, null, $lang);
        echo json_encode(['success' => true, 'events' => $events]);
    } else {
        echo json_encode(['error' => 'Not logged in']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'API test failed: ' . $e->getMessage()]);
}
?>