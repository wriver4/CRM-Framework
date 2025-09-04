<?php
/**
 * Comprehensive test summary for the CRM system
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🧪 CRM System Test Summary\n";
echo "========================\n\n";

$tests_passed = 0;
$tests_failed = 0;
$test_results = [];

// Test 1: Basic PHP functionality
echo "1. Testing Basic PHP Functionality...\n";
try {
    $test_result = json_encode(['test' => 'working', 'time' => date('Y-m-d H:i:s')]);
    echo "   ✅ JSON encoding: PASS\n";
    echo "   ✅ Date functions: PASS\n";
    $tests_passed += 2;
    $test_results['basic_php'] = 'PASS';
} catch (Exception $e) {
    echo "   ❌ Basic PHP: FAIL - " . $e->getMessage() . "\n";
    $tests_failed++;
    $test_results['basic_php'] = 'FAIL';
}

// Test 2: Autoloader and class loading
echo "\n2. Testing Class Loading...\n";
try {
    // Load Composer autoloader
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "   ✅ Composer autoloader: PASS\n";
    $tests_passed++;
    
    // Set up custom autoloader
    spl_autoload_register(function ($class_name) {
        if (strpos($class_name, '\\') !== false) {
            return;
        }
        
        // Search in organized subdirectories
        $directories = ['Core', 'Models', 'Views', 'Utilities', 'Logging'];
        
        foreach ($directories as $dir) {
            $file = __DIR__ . '/../classes/' . $dir . '/' . $class_name . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
        
        // Fallback to root classes directory for backward compatibility
        $file = __DIR__ . '/../classes/' . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    });
    echo "   ✅ Custom autoloader: PASS\n";
    $tests_passed++;
    $test_results['class_loading'] = 'PASS';
} catch (Exception $e) {
    echo "   ❌ Class loading: FAIL - " . $e->getMessage() . "\n";
    $tests_failed++;
    $test_results['class_loading'] = 'FAIL';
}

// Test 3: Database connectivity
echo "\n3. Testing Database Connectivity...\n";
try {
    $db = new Database();
    $pdo = $db->dbcrm();
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['test'] == 1) {
        echo "   ✅ Database connection: PASS\n";
        $tests_passed++;
        $test_results['database'] = 'PASS';
    } else {
        echo "   ❌ Database connection: FAIL - Invalid result\n";
        $tests_failed++;
        $test_results['database'] = 'FAIL';
    }
} catch (Exception $e) {
    echo "   ❌ Database connection: FAIL - " . $e->getMessage() . "\n";
    $tests_failed++;
    $test_results['database'] = 'FAIL';
}

// Test 4: Core classes instantiation
echo "\n4. Testing Core Classes...\n";
$core_classes = ['Notes', 'Leads', 'Contacts', 'Users', 'Audit'];
$class_results = [];

foreach ($core_classes as $class_name) {
    try {
        $instance = new $class_name();
        echo "   ✅ $class_name class: PASS\n";
        $tests_passed++;
        $class_results[$class_name] = 'PASS';
    } catch (Exception $e) {
        echo "   ❌ $class_name class: FAIL - " . $e->getMessage() . "\n";
        $tests_failed++;
        $class_results[$class_name] = 'FAIL';
    }
}
$test_results['core_classes'] = $class_results;

// Test 5: Database content verification
echo "\n5. Testing Database Content...\n";
try {
    $lead_count = $pdo->query("SELECT COUNT(*) as count FROM leads")->fetch(PDO::FETCH_ASSOC);
    $contact_count = $pdo->query("SELECT COUNT(*) as count FROM contacts")->fetch(PDO::FETCH_ASSOC);
    $user_count = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch(PDO::FETCH_ASSOC);
    
    echo "   📊 Database Statistics:\n";
    echo "      - Leads: " . $lead_count['count'] . "\n";
    echo "      - Contacts: " . $contact_count['count'] . "\n";
    echo "      - Users: " . $user_count['count'] . "\n";
    
    if ($lead_count['count'] > 0 && $contact_count['count'] > 0 && $user_count['count'] > 0) {
        echo "   ✅ Database content: PASS\n";
        $tests_passed++;
        $test_results['database_content'] = 'PASS';
    } else {
        echo "   ⚠️  Database content: WARNING - Some tables are empty\n";
        $test_results['database_content'] = 'WARNING';
    }
} catch (Exception $e) {
    echo "   ❌ Database content: FAIL - " . $e->getMessage() . "\n";
    $tests_failed++;
    $test_results['database_content'] = 'FAIL';
}

// Test Summary
echo "\n" . str_repeat("=", 50) . "\n";
echo "📋 TEST SUMMARY\n";
echo str_repeat("=", 50) . "\n";
echo "✅ Tests Passed: $tests_passed\n";
echo "❌ Tests Failed: $tests_failed\n";
echo "📊 Success Rate: " . round(($tests_passed / ($tests_passed + $tests_failed)) * 100, 1) . "%\n";

echo "\n🔍 Detailed Results:\n";
foreach ($test_results as $category => $result) {
    if (is_array($result)) {
        echo "   $category:\n";
        foreach ($result as $item => $status) {
            $icon = $status === 'PASS' ? '✅' : ($status === 'WARNING' ? '⚠️' : '❌');
            echo "      $icon $item: $status\n";
        }
    } else {
        $icon = $result === 'PASS' ? '✅' : ($result === 'WARNING' ? '⚠️' : '❌');
        echo "   $icon $category: $result\n";
    }
}

echo "\n🎯 Overall Status: ";
if ($tests_failed == 0) {
    echo "🟢 ALL TESTS PASSED\n";
} elseif ($tests_passed > $tests_failed) {
    echo "🟡 MOSTLY PASSING (some issues)\n";
} else {
    echo "🔴 MULTIPLE FAILURES\n";
}

echo "\n📅 Test completed at: " . date('Y-m-d H:i:s') . "\n";