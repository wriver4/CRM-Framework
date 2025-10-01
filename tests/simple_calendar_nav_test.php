<?php
/**
 * Simple Calendar Navigation Test
 * 
 * Tests calendar navigation integration without full system initialization
 */

echo "Simple Calendar Navigation Test\n";
echo str_repeat("=", 40) . "\n\n";

$errors = [];
$docroot = dirname(__DIR__);

// Test 1: Check if CALENDAR constant is defined in system.php
echo "1. Testing CALENDAR constant in system.php... ";
$systemFile = $docroot . '/config/system.php';
if (file_exists($systemFile)) {
    $content = file_get_contents($systemFile);
    if (strpos($content, 'define("CALENDAR", URL . "/calendar");') !== false) {
        echo "✓ PASS\n";
    } else {
        $errors[] = "CALENDAR constant not found in system.php";
        echo "✗ FAIL\n";
    }
} else {
    $errors[] = "system.php file not found";
    echo "✗ FAIL\n";
}

// Test 2: Check English translation
echo "2. Testing English translation... ";
$enFile = $docroot . '/public_html/admin/languages/en.php';
if (file_exists($enFile)) {
    $content = file_get_contents($enFile);
    if (strpos($content, "'navbar_calendar' => \"Calendar\"") !== false) {
        echo "✓ PASS\n";
    } else {
        $errors[] = "English translation for navbar_calendar not found";
        echo "✗ FAIL\n";
    }
} else {
    $errors[] = "English language file not found";
    echo "✗ FAIL\n";
}

// Test 3: Check Spanish translation
echo "3. Testing Spanish translation... ";
$esFile = $docroot . '/public_html/admin/languages/es.php';
if (file_exists($esFile)) {
    $content = file_get_contents($esFile);
    if (strpos($content, "'navbar_calendar' => \"Calendario\"") !== false) {
        echo "✓ PASS\n";
    } else {
        $errors[] = "Spanish translation for navbar_calendar not found";
        echo "✗ FAIL\n";
    }
} else {
    $errors[] = "Spanish language file not found";
    echo "✗ FAIL\n";
}

// Test 4: Check navigation template exists
echo "4. Testing navigation template... ";
$navItemFile = $docroot . '/public_html/templates/nav_item_calendar.php';
if (file_exists($navItemFile)) {
    $content = file_get_contents($navItemFile);
    if (strpos($content, 'navbar_calendar') !== false && strpos($content, 'CALENDAR') !== false) {
        echo "✓ PASS\n";
    } else {
        $errors[] = "Navigation template missing required content";
        echo "✗ FAIL\n";
    }
} else {
    $errors[] = "Navigation template file not found";
    echo "✗ FAIL\n";
}

// Test 5: Check navigation template is included
echo "5. Testing navigation inclusion... ";
$navFile = $docroot . '/public_html/templates/nav.php';
if (file_exists($navFile)) {
    $content = file_get_contents($navFile);
    if (strpos($content, "require_once 'nav_item_calendar.php';") !== false) {
        echo "✓ PASS\n";
    } else {
        $errors[] = "Calendar navigation not included in nav.php";
        echo "✗ FAIL\n";
    }
} else {
    $errors[] = "Main navigation file not found";
    echo "✗ FAIL\n";
}

// Test 6: Check calendar directory variable is set
echo "6. Testing calendar directory variable... ";
$calendarIndexFile = $docroot . '/public_html/calendar/index.php';
if (file_exists($calendarIndexFile)) {
    $content = file_get_contents($calendarIndexFile);
    if (strpos($content, '$dir = \'calendar\';') !== false) {
        echo "✓ PASS\n";
    } else {
        $errors[] = "Calendar directory variable not set in index.php";
        echo "✗ FAIL\n";
    }
} else {
    $errors[] = "Calendar index.php file not found";
    echo "✗ FAIL\n";
}

// Display results
echo "\n" . str_repeat("=", 40) . "\n";
echo "TEST RESULTS\n";
echo str_repeat("=", 40) . "\n";

if (empty($errors)) {
    echo "✓ ALL TESTS PASSED!\n";
    echo "Calendar navigation has been successfully integrated.\n\n";
    echo "Summary of changes made:\n";
    echo "- Added CALENDAR constant to config/system.php\n";
    echo "- Added 'navbar_calendar' translations to en.php and es.php\n";
    echo "- Created nav_item_calendar.php template\n";
    echo "- Included calendar navigation in nav.php\n";
    echo "- Set \$dir variable in calendar/index.php\n";
} else {
    echo "✗ " . count($errors) . " TEST(S) FAILED:\n\n";
    foreach ($errors as $error) {
        echo "- $error\n";
    }
}
echo "\n";