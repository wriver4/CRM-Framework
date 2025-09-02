<?php

/**
 * PHPUnit Bootstrap file for CRM application
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('UTC');

// Define test environment
$_ENV['APP_ENV'] = 'testing';
$_ENV['BASE_URL'] = $_ENV['BASE_URL'] ?? 'https://democrm.waveguardco.net';

// Load our base TestCase class
require_once __DIR__ . '/phpunit/TestCase.php';

// Autoloader for test classes
spl_autoload_register(function ($class) {
    // Handle Tests namespace
    if (strpos($class, 'Tests\\') === 0) {
        $file = __DIR__ . '/phpunit/' . str_replace(['Tests\\', '\\'], ['', '/'], $class) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

echo "✅ Test bootstrap loaded\n";