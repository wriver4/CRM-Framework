<?php
/**
 * Simple test runner without PHPUnit dependency
 * Tests basic CRM functionality
 */

echo "🧪 CRM Simple Test Runner\n";
echo "========================\n\n";

$tests = [];
$passed = 0;
$failed = 0;

function test($name, $callback) {
    global $tests, $passed, $failed;
    
    echo "🔍 Testing: $name\n";
    
    try {
        $result = $callback();
        if ($result === true) {
            echo "✅ PASS: $name\n";
            $passed++;
        } else {
            echo "❌ FAIL: $name - " . ($result ?: 'Test returned false') . "\n";
            $failed++;
        }
    } catch (Exception $e) {
        echo "❌ ERROR: $name - " . $e->getMessage() . "\n";
        $failed++;
    }
    
    echo "\n";
}

// Test 1: Check if core classes exist
test("Core classes exist", function() {
    $classes = [
        __DIR__ . '/classes/Database.php',
        __DIR__ . '/classes/Helpers.php',
        __DIR__ . '/classes/Security.php',
        __DIR__ . '/classes/Leads.php',
        __DIR__ . '/classes/Contacts.php'
    ];
    
    foreach ($classes as $class) {
        if (!file_exists($class)) {
            return "Missing class file: " . basename($class);
        }
    }
    
    return true;
});

// Test 2: Check if Helpers class can be loaded
test("Helpers class loads", function() {
    // Load dependencies first
    require_once __DIR__ . '/classes/Database.php';
    require_once __DIR__ . '/classes/Helpers.php';
    
    if (!class_exists('Helpers')) {
        return "Helpers class not found after include";
    }
    
    $helpers = new Helpers();
    if (!method_exists($helpers, 'hash_password')) {
        return "hash_password method not found in Helpers class";
    }
    
    return true;
});

// Test 3: Test web server connectivity
test("Web server is accessible", function() {
    $url = 'https://democrm.waveguardco.net/login.php';
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 10,
            'ignore_errors' => true
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        return "Could not connect to $url";
    }
    
    if (strpos($response, 'login') === false) {
        return "Login page does not contain expected content";
    }
    
    return true;
});

// Test 4: Check configuration files
test("Configuration files exist", function() {
    $configs = [
        __DIR__ . '/config/system.php',
        __DIR__ . '/composer.json',
        __DIR__ . '/phpunit.xml',
        __DIR__ . '/phpunit-local.xml'
    ];
    
    foreach ($configs as $config) {
        if (!file_exists($config)) {
            return "Missing config file: " . basename($config);
        }
    }
    
    return true;
});

// Test 5: Check if vendor autoloader exists
test("Composer autoloader exists", function() {
    if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
        return "Composer autoloader not found";
    }
    
    require_once __DIR__ . '/vendor/autoload.php';
    return true;
});

// Test 6: Check test directory structure
test("Test directory structure", function() {
    $testDirs = [
        __DIR__ . '/tests/phpunit',
        __DIR__ . '/tests/phpunit/Unit',
        __DIR__ . '/tests/phpunit/Integration',
        __DIR__ . '/tests/phpunit/Feature',
        __DIR__ . '/tests/phpunit/Remote'
    ];
    
    foreach ($testDirs as $dir) {
        if (!is_dir($dir)) {
            return "Missing test directory: " . basename($dir);
        }
    }
    
    return true;
});

// Run all tests
echo "📊 Test Results:\n";
echo "================\n";
echo "✅ Passed: $passed\n";
echo "❌ Failed: $failed\n";
echo "📈 Total: " . ($passed + $failed) . "\n\n";

if ($failed === 0) {
    echo "🎉 All tests passed!\n";
    exit(0);
} else {
    echo "💥 Some tests failed!\n";
    exit(1);
}