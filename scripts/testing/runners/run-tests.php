<?php
/**
 * Simple PHPUnit test runner for CRM application
 * This script manually loads PHPUnit and runs our tests
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Set up environment variables for testing
$_ENV['BASE_URL'] = 'https://democrm.waveguardco.net';
$_ENV['TESTING_MODE'] = 'remote';
$_ENV['TEST_USER_EMAIL'] = 'test@example.com';
$_ENV['TEST_USER_PASSWORD'] = 'testpassword';

// Check if PHPUnit is available
if (!class_exists('PHPUnit\Framework\TestCase')) {
    echo "❌ PHPUnit not found in autoloader\n";
    echo "Available classes:\n";
    $classes = get_declared_classes();
    $phpunitClasses = array_filter($classes, function($class) {
        return strpos($class, 'PHPUnit') !== false;
    });
    if (empty($phpunitClasses)) {
        echo "No PHPUnit classes found\n";
    } else {
        foreach ($phpunitClasses as $class) {
            echo "  - $class\n";
        }
    }
    exit(1);
}

echo "✅ PHPUnit found in autoloader\n";

// Try to run a simple test
try {
    // Create a simple test runner
    $testSuite = new PHPUnit\Framework\TestSuite('CRM Test Suite');
    
    // Add test files
    $testFiles = [
        __DIR__ . '/tests/phpunit/Unit/HelpersTest.php',
        __DIR__ . '/tests/phpunit/Integration/DatabaseTest.php',
        __DIR__ . '/tests/phpunit/Feature/LoginTest.php',
        __DIR__ . '/tests/phpunit/Remote/RemoteServerTest.php'
    ];
    
    foreach ($testFiles as $testFile) {
        if (file_exists($testFile)) {
            echo "📁 Adding test file: " . basename($testFile) . "\n";
            require_once $testFile;
        } else {
            echo "⚠️  Test file not found: " . basename($testFile) . "\n";
        }
    }
    
    // Add test classes to suite
    $testClasses = [
        'Tests\Unit\HelpersTest',
        'Tests\Integration\DatabaseTest',
        'Tests\Feature\LoginTest',
        'Tests\Remote\RemoteServerTest'
    ];
    
    foreach ($testClasses as $testClass) {
        if (class_exists($testClass)) {
            echo "🧪 Adding test class: $testClass\n";
            $testSuite->addTestSuite($testClass);
        } else {
            echo "⚠️  Test class not found: $testClass\n";
        }
    }
    
    // Run the tests
    $runner = new PHPUnit\TextUI\TestRunner();
    echo "\n🚀 Running tests...\n\n";
    $result = $runner->run($testSuite);
    
    echo "\n✅ Test execution completed\n";
    
} catch (Exception $e) {
    echo "❌ Error running tests: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}