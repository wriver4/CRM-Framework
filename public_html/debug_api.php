<?php
/**
 * Debug API endpoints
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing API endpoints...\n\n";

// Test system.php loading
echo "1. Testing system.php loading:\n";
try {
    require_once '/run/user/1000/gvfs/sftp:host=159.203.116.150,port=222/home/democrm/config/system.php';
    echo "✓ system.php loaded successfully\n";
} catch (Exception $e) {
    echo "✗ Error loading system.php: " . $e->getMessage() . "\n";
    exit;
}

// Test Sessions class
echo "\n2. Testing Sessions class:\n";
try {
    if (class_exists('Sessions')) {
        echo "✓ Sessions class exists\n";
        // Don't test login status as it might not be logged in
    } else {
        echo "✗ Sessions class not found\n";
    }
} catch (Exception $e) {
    echo "✗ Error with Sessions class: " . $e->getMessage() . "\n";
}

// Test Contacts class
echo "\n3. Testing Contacts class:\n";
try {
    if (class_exists('Contacts')) {
        echo "✓ Contacts class exists\n";
        $contacts = new Contacts();
        echo "✓ Contacts instance created\n";
    } else {
        echo "✗ Contacts class not found\n";
    }
} catch (Exception $e) {
    echo "✗ Error with Contacts class: " . $e->getMessage() . "\n";
}

// Test Leads class
echo "\n4. Testing Leads class:\n";
try {
    if (class_exists('Leads')) {
        echo "✓ Leads class exists\n";
        $leads = new Leads();
        echo "✓ Leads instance created\n";
    } else {
        echo "✗ Leads class not found\n";
    }
} catch (Exception $e) {
    echo "✗ Error with Leads class: " . $e->getMessage() . "\n";
}

// Test CalendarEvent class
echo "\n5. Testing CalendarEvent class:\n";
try {
    require_once CLASSES . 'Models/CalendarEvent.php';
    if (class_exists('CalendarEvent')) {
        echo "✓ CalendarEvent class exists\n";
        $calendar = new CalendarEvent();
        echo "✓ CalendarEvent instance created\n";
    } else {
        echo "✗ CalendarEvent class not found\n";
    }
} catch (Exception $e) {
    echo "✗ Error with CalendarEvent class: " . $e->getMessage() . "\n";
}

echo "\nDebug complete.\n";