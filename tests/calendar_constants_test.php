<?php
/**
 * Calendar Constants Test
 * 
 * Tests that calendar files use correct constants
 */

echo "Calendar Constants Test\n";
echo str_repeat("=", 30) . "\n\n";

$errors = [];
$docroot = dirname(__DIR__);

// Test calendar files for correct constants
$calendarFiles = [
    'public_html/calendar/index.php',
    'public_html/calendar/api.php', 
    'public_html/calendar/dashboard_widget.php',
    'classes/Models/CalendarEvent.php'
];

foreach ($calendarFiles as $file) {
    $fullPath = $docroot . '/' . $file;
    echo "Testing $file... ";
    
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        
        // Check for incorrect DOCCLASSES usage
        if (strpos($content, 'DOCCLASSES') !== false) {
            $errors[] = "$file still contains DOCCLASSES constant";
            echo "✗ FAIL (DOCCLASSES found)\n";
        }
        // Check for correct CLASSES usage
        else if (strpos($content, 'CLASSES') !== false) {
            echo "✓ PASS\n";
        }
        else {
            echo "- N/A (no class includes)\n";
        }
    } else {
        $errors[] = "$file not found";
        echo "✗ FAIL (file not found)\n";
    }
}

// Display results
echo "\n" . str_repeat("=", 30) . "\n";
echo "TEST RESULTS\n";
echo str_repeat("=", 30) . "\n";

if (empty($errors)) {
    echo "✓ ALL TESTS PASSED!\n";
    echo "All calendar files use correct constants.\n";
} else {
    echo "✗ " . count($errors) . " ERROR(S) FOUND:\n\n";
    foreach ($errors as $error) {
        echo "- $error\n";
    }
}
echo "\n";