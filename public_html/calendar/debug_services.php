<?php
/**
 * Debug Core Services - Test each service individually
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Loading system configuration (partial)...\n";

// Load only the essential parts first
if (php_sapi_name() !== 'cli') {
    session_start();
}

// Load autoloader
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

// Load custom autoloader
spl_autoload_register(function ($class_name) {
    if (strpos($class_name, '\\') !== false) {
        return;
    }
    
    $directories = ['Core', 'Models', 'Views', 'Utilities', 'Logging'];
    
    foreach ($directories as $dir) {
        $file = dirname(__DIR__, 2) . '/classes/' . $dir . '/' . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Test each service individually
echo "\nTesting core services individually:\n";

// 1. Test Database
try {
    echo "1. Testing Database class... ";
    $dbcrm = (new Database())->dbcrm();
    echo "SUCCESS\n";
} catch (Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

// 2. Test Users
try {
    echo "2. Testing Users class... ";
    $users = new Users();
    echo "SUCCESS\n";
} catch (Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

// 3. Test Audit
try {
    echo "3. Testing Audit class... ";
    $audit = new Audit();
    echo "SUCCESS\n";
} catch (Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

// 4. Test Helpers
try {
    echo "4. Testing Helpers class... ";
    $helper = new Helpers();
    echo "SUCCESS\n";
} catch (Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

// 5. Test Roles
try {
    echo "5. Testing Roles class... ";
    $roles = new Roles();
    echo "SUCCESS\n";
} catch (Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

// 6. Test Permissions
try {
    echo "6. Testing Permissions class... ";
    $permissions = new Permissions();
    echo "SUCCESS\n";
} catch (Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

// 7. Test RolesPermissions
try {
    echo "7. Testing RolesPermissions class... ";
    $rolesperms = new RolesPermissions();
    echo "SUCCESS\n";
} catch (Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

// 8. Test Nonce
try {
    echo "8. Testing Nonce class... ";
    $nonce = new Nonce();
    echo "SUCCESS\n";
} catch (Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

// 9. Test Security
try {
    echo "9. Testing Security class... ";
    $security = new Security();
    echo "SUCCESS\n";
} catch (Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

// 10. Test CalendarEvent
try {
    echo "10. Testing CalendarEvent class... ";
    $calendar = new CalendarEvent();
    echo "SUCCESS\n";
} catch (Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

echo "\nTesting complete!\n";
?>