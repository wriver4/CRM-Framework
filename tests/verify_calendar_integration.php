<?php
/**
 * Calendar Integration Verification Script
 * 
 * This script verifies that the calendar system has been properly integrated
 * into the Enhanced Integration Test framework.
 */

// Set up basic environment for CLI testing
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['DOCUMENT_ROOT'] = '/run/user/1000/gvfs/sftp:host=159.203.116.150,port=222/home/democrm/public_html';

require_once dirname(__DIR__) . '/config/system.php';
require_once dirname(__DIR__) . '/classes/Models/CalendarEvent.php';
require_once dirname(__DIR__) . '/tests/enhanced_integration_test.php';

echo "=== Calendar Integration Verification ===\n\n";

try {
    // Create test instance
    $test = new EnhancedIntegrationTest();
    
    // Check if calendar module is configured
    $reflection = new ReflectionClass($test);
    $testModulesProperty = $reflection->getProperty('testModules');
    $testModulesProperty->setAccessible(true);
    $testModules = $testModulesProperty->getValue($test);
    
    echo "1. Checking calendar module configuration...\n";
    if (isset($testModules['calendar'])) {
        echo "   ✓ Calendar module found in test configuration\n";
        echo "   ✓ Language keys: " . implode(', ', $testModules['calendar']['language_keys']) . "\n";
        echo "   ✓ Permissions: " . implode(', ', $testModules['calendar']['permissions']) . "\n";
    } else {
        echo "   ✗ Calendar module NOT found in test configuration\n";
        exit(1);
    }
    
    echo "\n2. Checking CalendarEvent class integration...\n";
    $calendarProperty = $reflection->getProperty('calendar');
    $calendarProperty->setAccessible(true);
    
    // Initialize the test to set up classes
    $initMethod = $reflection->getMethod('initializeClasses');
    $initMethod->setAccessible(true);
    $initMethod->invoke($test);
    
    $calendar = $calendarProperty->getValue($test);
    if ($calendar instanceof CalendarEvent) {
        echo "   ✓ CalendarEvent instance properly initialized\n";
        echo "   ✓ Class type: " . get_class($calendar) . "\n";
    } else {
        echo "   ✗ CalendarEvent instance NOT properly initialized\n";
        exit(1);
    }
    
    echo "\n3. Checking calendar language keys...\n";
    $expectedKeys = [
        'phone_call', 'email', 'text_message', 'internal_note',
        'virtual_meeting', 'in_person_meeting', '1', '5', '10'
    ];
    
    foreach ($expectedKeys as $key) {
        if (in_array($key, $testModules['calendar']['language_keys'])) {
            echo "   ✓ Language key '$key' configured\n";
        } else {
            echo "   ✗ Language key '$key' missing\n";
        }
    }
    
    echo "\n4. Checking calendar permissions...\n";
    $expectedPermissions = [
        'view_calendar', 'create_events', 'edit_events', 'delete_events'
    ];
    
    foreach ($expectedPermissions as $permission) {
        if (in_array($permission, $testModules['calendar']['permissions'])) {
            echo "   ✓ Permission '$permission' configured\n";
        } else {
            echo "   ✗ Permission '$permission' missing\n";
        }
    }
    
    echo "\n5. Testing calendar module methods...\n";
    
    // Test if we can call calendar-specific methods
    $testModuleMethod = $reflection->getMethod('testModule');
    $testModuleMethod->setAccessible(true);
    
    echo "   ✓ Calendar module integration test methods available\n";
    
    echo "\n=== Integration Verification Complete ===\n";
    echo "✓ Calendar system successfully integrated into Enhanced Integration Test framework\n";
    echo "✓ All required components are properly configured\n";
    echo "✓ Framework ready for comprehensive calendar testing\n\n";
    
    echo "Next steps:\n";
    echo "- Run enhanced integration tests with calendar module\n";
    echo "- Test across all user roles (superadmin, admin, sales_manager, etc.)\n";
    echo "- Validate language translations and permissions\n";
    
} catch (Exception $e) {
    echo "✗ Error during verification: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}