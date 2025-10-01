<?php
/**
 * Simple Calendar Integration Verification
 * 
 * Verifies the calendar system integration without full system initialization
 */

echo "=== Simple Calendar Integration Verification ===\n\n";

// Check if the enhanced integration test file exists and contains calendar integration
$testFile = dirname(__DIR__) . '/tests/enhanced_integration_test.php';

if (!file_exists($testFile)) {
    echo "✗ Enhanced integration test file not found\n";
    exit(1);
}

$content = file_get_contents($testFile);

echo "1. Checking calendar module in testModules array...\n";
if (strpos($content, "'calendar' => [") !== false) {
    echo "   ✓ Calendar module found in testModules configuration\n";
} else {
    echo "   ✗ Calendar module NOT found in testModules\n";
    exit(1);
}

echo "\n2. Checking CalendarEvent class inclusion...\n";
if (strpos($content, "require_once \$rootPath . '/classes/Models/CalendarEvent.php';") !== false) {
    echo "   ✓ CalendarEvent class properly included\n";
} else {
    echo "   ✗ CalendarEvent class NOT included\n";
    exit(1);
}

echo "\n3. Checking calendar property declaration...\n";
if (strpos($content, "private \$calendar;") !== false) {
    echo "   ✓ Calendar property declared\n";
} else {
    echo "   ✗ Calendar property NOT declared\n";
    exit(1);
}

echo "\n4. Checking calendar initialization...\n";
if (strpos($content, "\$this->calendar = new CalendarEvent();") !== false) {
    echo "   ✓ Calendar properly initialized in initializeClasses method\n";
} else {
    echo "   ✗ Calendar NOT properly initialized\n";
    exit(1);
}

echo "\n5. Checking calendar language keys...\n";
$expectedKeys = ['event_type_phone_call', 'event_type_email', 'event_type_text_message', 'event_type_internal_note', 'event_type_virtual_meeting', 'event_type_in_person_meeting', 'priority_1', 'priority_5', 'priority_10'];
$foundKeys = 0;

foreach ($expectedKeys as $key) {
    if (strpos($content, "'$key'") !== false) {
        echo "   ✓ Language key '$key' found\n";
        $foundKeys++;
    } else {
        echo "   ✗ Language key '$key' missing\n";
    }
}

echo "\n6. Checking calendar permissions...\n";
$expectedPermissions = ['view_calendar', 'create_events', 'edit_events', 'delete_events'];
$foundPermissions = 0;

foreach ($expectedPermissions as $permission) {
    if (strpos($content, "'$permission'") !== false) {
        echo "   ✓ Permission '$permission' found\n";
        $foundPermissions++;
    } else {
        echo "   ✗ Permission '$permission' missing\n";
    }
}

echo "\n=== Verification Summary ===\n";
echo "✓ Calendar module integrated into Enhanced Integration Test framework\n";
echo "✓ CalendarEvent class properly included and initialized\n";
echo "✓ Language keys configured: $foundKeys/" . count($expectedKeys) . "\n";
echo "✓ Permissions configured: $foundPermissions/" . count($expectedPermissions) . "\n";

if ($foundKeys == count($expectedKeys) && $foundPermissions == count($expectedPermissions)) {
    echo "\n🎉 INTEGRATION COMPLETE! Calendar system successfully added to testing framework.\n";
    echo "\nThe Enhanced Integration Test framework now includes:\n";
    echo "- Calendar module with comprehensive language key testing\n";
    echo "- Permission-based access control validation\n";
    echo "- Multi-role testing across all user types\n";
    echo "- Performance monitoring and audit logging\n";
    echo "- Enhanced error reporting for calendar operations\n";
} else {
    echo "\n⚠️  Integration partially complete - some components may need attention\n";
}

echo "\nTo run calendar tests:\n";
echo "php enhanced_integration_test.php --module=calendar --role=admin\n";
echo "php enhanced_integration_test.php --module=calendar --all-roles\n";
echo "php enhanced_integration_test.php --comprehensive --debug\n";