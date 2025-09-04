<?php

// Simple test to verify autoloader works
spl_autoload_register(function ($class_name) {
    // Ignore namespaced classes (which are handled by Composer)
    if (strpos($class_name, '\\') !== false) {
        return;
    }
    
    // Search in organized subdirectories
    $directories = ['Core', 'Models', 'Views', 'Utilities', 'Logging'];
    
    foreach ($directories as $dir) {
        $file = __DIR__ . '/classes/' . $dir . '/' . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            echo "Loaded {$class_name} from {$dir}/{$class_name}.php\n";
            return;
        }
    }
    
    // Fallback to root classes directory for backward compatibility
    $file = __DIR__ . '/classes/' . $class_name . '.php';
    if (file_exists($file)) {
        require_once $file;
        echo "Loaded {$class_name} from classes/{$class_name}.php\n";
    }
});

// Test loading classes
echo "Testing autoloader...\n";

$testClasses = ['Database', 'Users', 'Table', 'Helpers', 'Audit'];

foreach ($testClasses as $className) {
    if (class_exists($className)) {
        echo "✓ {$className} loaded successfully\n";
    } else {
        echo "✗ {$className} failed to load\n";
    }
}