<?php
/**
 * Test that only loads the classes without the full system
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "DEBUG: Starting classes-only test...\n";

try {
    // Start session for classes that might need it
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    echo "DEBUG: Loading Composer autoloader...\n";
    require_once __DIR__ . '/../../vendor/autoload.php';
    echo "DEBUG: Composer autoloader loaded\n";
    
    echo "DEBUG: Setting up custom autoloader...\n";
    spl_autoload_register(function ($class_name) {
        // Ignore namespaced classes (which are handled by Composer)
        if (strpos($class_name, '\\') !== false) {
            return;
        }
        $file = __DIR__ . '/../../classes/' . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            echo "DEBUG: Loaded class $class_name\n";
        }
    });
    
    echo "DEBUG: Testing Database class...\n";
    $db = new Database();
    echo "DEBUG: Database instance created successfully\n";
    
    echo "DEBUG: Testing database connection...\n";
    $pdo = $db->dbcrm();
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "DEBUG: Database query result: " . print_r($result, true) . "\n";
    
    echo "DEBUG: Testing Notes class...\n";
    $notes = new Notes();
    echo "DEBUG: Notes instance created successfully\n";
    
    echo "DEBUG: Testing Leads class...\n";
    $leads = new Leads();
    echo "DEBUG: Leads instance created successfully\n";
    
    echo "DEBUG: Testing Contacts class...\n";
    $contacts = new Contacts();
    echo "DEBUG: Contacts instance created successfully\n";
    
    echo "DEBUG: Testing Users class...\n";
    $users = new Users();
    echo "DEBUG: Users instance created successfully\n";
    
    echo "DEBUG: Testing Audit class...\n";
    $audit = new Audit();
    echo "DEBUG: Audit instance created successfully\n";
    
    // Test some basic database queries
    echo "DEBUG: Testing basic database queries...\n";
    
    $lead_count = $pdo->query("SELECT COUNT(*) as count FROM leads")->fetch(PDO::FETCH_ASSOC);
    echo "DEBUG: Total leads in database: " . $lead_count['count'] . "\n";
    
    $contact_count = $pdo->query("SELECT COUNT(*) as count FROM contacts")->fetch(PDO::FETCH_ASSOC);
    echo "DEBUG: Total contacts in database: " . $contact_count['count'] . "\n";
    
    $user_count = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch(PDO::FETCH_ASSOC);
    echo "DEBUG: Total users in database: " . $user_count['count'] . "\n";
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'All class tests passed',
        'debug' => 'Classes loaded and tested successfully',
        'database_stats' => [
            'leads' => (int)$lead_count['count'],
            'contacts' => (int)$contact_count['count'],
            'users' => (int)$user_count['count']
        ]
    ]);
    
} catch (Exception $e) {
    echo "DEBUG: Exception caught: " . $e->getMessage() . "\n";
    echo "DEBUG: Stack trace: " . $e->getTraceAsString() . "\n";
    
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Exception: ' . $e->getMessage(),
        'debug' => 'Exception caught',
        'trace' => $e->getTraceAsString()
    ]);
}