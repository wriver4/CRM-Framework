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

// Define constants needed for testing
if (!defined('NONCE_SECRET')) {
    define('NONCE_SECRET', 'test-secret-key-for-nonce-generation');
}

// Load our base TestCase class
require_once __DIR__ . '/phpunit/TestCase.php';

// Custom autoloader for the /classes directory (same as system.php but CLI-safe)
spl_autoload_register(function ($class_name) {
    // Ignore namespaced classes (which are handled by Composer)
    if (strpos($class_name, '\\') !== false) {
        return;
    }
    
    // Search in organized subdirectories
    $directories = ['Core', 'Models', 'Views', 'Utilities', 'Logging'];
    
    foreach ($directories as $dir) {
        $file = dirname(__DIR__) . '/classes/' . $dir . '/' . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
    
    // Fallback to root classes directory for backward compatibility
    $file = dirname(__DIR__) . '/classes/' . $class_name . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Composer's autoloader for vendor packages
require_once dirname(__DIR__) . '/vendor/autoload.php';

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