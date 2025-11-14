<?php

/**
 * Testing Framework Verification Script
 * 
 * Verifies that the testing framework is properly set up
 */

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                                                              ║\n";
echo "║           TESTING FRAMEWORK VERIFICATION                     ║\n";
echo "║                                                              ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

$errors = [];
$warnings = [];
$success = [];

// Check 1: Required files exist
echo "📁 Checking required files...\n";

$requiredFiles = [
    'classes/Core/Database.php' => 'Production Database class',
    'classes/Core/TestDatabase.php' => 'Test Database class',
    'config/testing.php' => 'Test configuration',
    'tests/phpunit/DatabaseTestCase.php' => 'Database test case',
    'tests/phpunit/Helpers/RbacTestHelper.php' => 'RBAC test helper',
    'tests/setup-test-database.php' => 'Test database setup script',
    'tests/playwright/rbac-helper.js' => 'Playwright RBAC helper',
    'tests/playwright/rbac-permissions.spec.js' => 'Playwright RBAC tests',
    'phpunit.xml' => 'PHPUnit configuration',
];

foreach ($requiredFiles as $file => $description) {
    if (file_exists($file)) {
        $success[] = "  ✅ $description";
    } else {
        $errors[] = "  ❌ Missing: $file ($description)";
    }
}

echo implode("\n", $success) . "\n";
if (!empty($errors)) {
    echo implode("\n", $errors) . "\n";
}
echo "\n";

// Check 2: Database class has no test code
echo "🔍 Checking production Database class...\n";

$databaseContent = file_get_contents('classes/Core/Database.php');

$testPatterns = [
    'isTestMode',
    'testConfig',
    'loadTestDatabaseConfig',
    'TESTING_MODE',
];

$foundTestCode = false;
foreach ($testPatterns as $pattern) {
    if (strpos($databaseContent, $pattern) !== false) {
        $errors[] = "  ❌ Production Database class contains test code: $pattern";
        $foundTestCode = true;
    }
}

if (!$foundTestCode) {
    echo "  ✅ Production Database class is clean (no test code)\n";
} else {
    echo "  ❌ Production Database class contains test code\n";
}
echo "\n";

// Check 3: TestDatabase class exists and extends Database
echo "🧪 Checking TestDatabase class...\n";

if (file_exists('classes/Core/TestDatabase.php')) {
    $testDbContent = file_get_contents('classes/Core/TestDatabase.php');
    
    if (strpos($testDbContent, 'extends Database') !== false) {
        echo "  ✅ TestDatabase extends Database\n";
    } else {
        $errors[] = "  ❌ TestDatabase does not extend Database";
    }
    
    if (strpos($testDbContent, 'testdbcrm') !== false) {
        echo "  ✅ TestDatabase has testdbcrm() method\n";
    } else {
        $warnings[] = "  ⚠️  TestDatabase missing testdbcrm() method";
    }
} else {
    $errors[] = "  ❌ TestDatabase class not found";
}
echo "\n";

// Check 4: Test configuration
echo "⚙️  Checking test configuration...\n";

if (file_exists('config/testing.php')) {
    $testConfig = require 'config/testing.php';
    
    if (isset($testConfig['database']['persistent'])) {
        echo "  ✅ Persistent database config found\n";
    } else {
        $errors[] = "  ❌ Persistent database config missing";
    }
    
    if (isset($testConfig['database']['ephemeral'])) {
        echo "  ✅ Ephemeral database config found\n";
    } else {
        $errors[] = "  ❌ Ephemeral database config missing";
    }
    
    if (isset($testConfig['seeding']['datasets'])) {
        $datasets = array_keys($testConfig['seeding']['datasets']);
        echo "  ✅ Datasets available: " . implode(', ', $datasets) . "\n";
    } else {
        $warnings[] = "  ⚠️  No seed datasets configured";
    }
} else {
    $errors[] = "  ❌ Test configuration not found";
}
echo "\n";

// Check 5: PHPUnit configuration
echo "🧪 Checking PHPUnit configuration...\n";

if (file_exists('phpunit.xml')) {
    $phpunitXml = file_get_contents('phpunit.xml');
    
    if (strpos($phpunitXml, 'TEST_DB_NAME') !== false) {
        echo "  ✅ Test database environment variables configured\n";
    } else {
        $errors[] = "  ❌ Test database environment variables missing";
    }
    
    if (strpos($phpunitXml, 'TESTING_MODE') !== false) {
        echo "  ✅ Testing mode environment variable configured\n";
    } else {
        $warnings[] = "  ⚠️  Testing mode environment variable missing";
    }
} else {
    $errors[] = "  ❌ phpunit.xml not found";
}
echo "\n";

// Check 6: Composer autoload
echo "📦 Checking Composer setup...\n";

if (file_exists('vendor/autoload.php')) {
    echo "  ✅ Composer autoload exists\n";
    
    require_once 'vendor/autoload.php';
    
    if (class_exists('Database')) {
        echo "  ✅ Database class can be loaded\n";
    } else {
        $errors[] = "  ❌ Database class cannot be loaded";
    }
    
    if (class_exists('TestDatabase')) {
        echo "  ✅ TestDatabase class can be loaded\n";
    } else {
        $warnings[] = "  ⚠️  TestDatabase class cannot be loaded (may need composer dump-autoload)";
    }
} else {
    $errors[] = "  ❌ Composer not installed (run: composer install)";
}
echo "\n";

// Check 7: Test database connection (optional)
echo "🔌 Checking test database connection...\n";

if (file_exists('phpunit.xml')) {
    $xml = simplexml_load_file('phpunit.xml');
    $testDbHost = null;
    $testDbName = null;
    $testDbUser = null;
    $testDbPass = null;
    
    foreach ($xml->php->env as $env) {
        $name = (string)$env['name'];
        $value = (string)$env['value'];
        
        if ($name === 'TEST_DB_HOST') $testDbHost = $value;
        if ($name === 'TEST_DB_NAME') $testDbName = $value;
        if ($name === 'TEST_DB_USER') $testDbUser = $value;
        if ($name === 'TEST_DB_PASS') $testDbPass = $value;
    }
    
    if ($testDbHost && $testDbUser && $testDbPass) {
        try {
            $dsn = "mysql:host=$testDbHost;charset=utf8mb4";
            $pdo = new PDO($dsn, $testDbUser, $testDbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            
            echo "  ✅ Can connect to MySQL server\n";
            
            // Check if test database exists
            $stmt = $pdo->query("SHOW DATABASES LIKE '$testDbName'");
            if ($stmt->rowCount() > 0) {
                echo "  ✅ Test database '$testDbName' exists\n";
                
                // Check table count
                $pdo->exec("USE `$testDbName`");
                $stmt = $pdo->query("SHOW TABLES");
                $tableCount = $stmt->rowCount();
                
                if ($tableCount > 0) {
                    echo "  ✅ Test database has $tableCount tables\n";
                } else {
                    $warnings[] = "  ⚠️  Test database is empty (run: php tests/setup-test-database.php)";
                }
            } else {
                $warnings[] = "  ⚠️  Test database '$testDbName' does not exist (run: php tests/setup-test-database.php)";
            }
            
        } catch (PDOException $e) {
            $warnings[] = "  ⚠️  Cannot connect to test database: " . $e->getMessage();
            $warnings[] = "  ℹ️  Run: mysql -u root -p < tests/create-test-db-user.sql";
        }
    } else {
        $warnings[] = "  ⚠️  Test database credentials not configured in phpunit.xml";
    }
} else {
    $warnings[] = "  ⚠️  Cannot check database connection (phpunit.xml missing)";
}
echo "\n";

// Check 8: Documentation
echo "📚 Checking documentation...\n";

$docs = [
    'TESTING_FRAMEWORK_README.md',
    'TESTING_QUICK_START.md',
    'TEST_DATA_GENERATION_GUIDE.md',
    'PRODUCTION_ZERO_OVERHEAD_SUMMARY.md',
    'RBAC_MIGRATION_PLAN.md',
    'IMPLEMENTATION_CHECKLIST.md',
];

$foundDocs = 0;
foreach ($docs as $doc) {
    if (file_exists($doc)) {
        $foundDocs++;
    }
}

echo "  ✅ Found $foundDocs/" . count($docs) . " documentation files\n";
echo "\n";

// Summary
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                                                              ║\n";
echo "║                      SUMMARY                                 ║\n";
echo "║                                                              ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

if (empty($errors)) {
    echo "✅ All critical checks passed!\n";
} else {
    echo "❌ Found " . count($errors) . " error(s):\n";
    foreach ($errors as $error) {
        echo "$error\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "⚠️  Found " . count($warnings) . " warning(s):\n";
    foreach ($warnings as $warning) {
        echo "$warning\n";
    }
    echo "\n";
}

// Next steps
if (!empty($errors) || !empty($warnings)) {
    echo "📋 Next Steps:\n";
    echo "\n";
    
    if (strpos(implode(' ', $warnings), 'composer dump-autoload') !== false) {
        echo "1. Run: composer dump-autoload\n";
    }
    
    if (strpos(implode(' ', $warnings), 'create-test-db-user.sql') !== false) {
        echo "2. Create test database user:\n";
        echo "   mysql -u root -p < tests/create-test-db-user.sql\n";
    }
    
    if (strpos(implode(' ', $warnings), 'setup-test-database.php') !== false) {
        echo "3. Setup test database:\n";
        echo "   php tests/setup-test-database.php --mode=persistent --seed=standard\n";
    }
    
    echo "\n";
} else {
    echo "🎉 Testing framework is ready to use!\n";
    echo "\n";
    echo "Run tests with:\n";
    echo "  vendor/bin/phpunit\n";
    echo "  npx playwright test\n";
    echo "\n";
}

echo "For more information, see:\n";
echo "  - TESTING_QUICK_START.md\n";
echo "  - TEST_DATA_GENERATION_GUIDE.md\n";
echo "  - PRODUCTION_ZERO_OVERHEAD_SUMMARY.md\n";
echo "\n";

exit(empty($errors) ? 0 : 1);