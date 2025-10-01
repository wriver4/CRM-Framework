<?php
/**
 * Language Keys Test Framework
 * Tests for missing language keys in the leads module and associated files
 * Enhanced with authenticated session testing and role-based validation
 */

// Include required classes for authentication
require_once dirname(__DIR__) . '/config/system.php';
require_once dirname(__DIR__) . '/classes/Core/Database.php';
require_once dirname(__DIR__) . '/classes/Core/Sessions.php';

class LanguageTest {
    private $rootPath;
    private $languageFiles = [];
    private $errors = [];
    private $warnings = [];
    private $fixes = [];
    private $sessions;
    private $authenticatedUser = null;
    
    // Test user credentials from .zencoder/rules/test_users.md
    private $testUsers = [
        'superadmin' => [
            'username' => 'testadmin',
            'password' => 'TestsuperadminHQ4!!@@1',
            'role_id' => 1,
            'role_name' => 'Super Administrator'
        ],
        'admin' => [
            'username' => 'testadmin2', 
            'password' => 'TestadminHQ4!!@@1',
            'role_id' => 2,
            'role_name' => 'Administrator'
        ],
        'sales_manager' => [
            'username' => 'testsalesmgr',
            'password' => 'TestsalesmanHQ4!!@@1', 
            'role_id' => 13,
            'role_name' => 'Sales Manager'
        ],
        'sales_assistant' => [
            'username' => 'testsalesasst',
            'password' => 'TestsalesassHQ4!!@@1',
            'role_id' => 14,
            'role_name' => 'Sales Assistant'
        ],
        'sales_person' => [
            'username' => 'testsalesperson',
            'password' => 'TestsalespersonHQ4!!@@1',
            'role_id' => 15,
            'role_name' => 'Sales Person'
        ]
    ];
    
    public function __construct($rootPath) {
        $this->rootPath = rtrim($rootPath, '/');
        $this->sessions = new Sessions();
        $this->loadLanguageFiles();
    }
    
    /**
     * Authenticate as a test user for session-based testing
     */
    public function authenticateAsTestUser($userRole = 'superadmin') {
        if (!isset($this->testUsers[$userRole])) {
            echo "❌ Unknown test user role: {$userRole}\n";
            return false;
        }
        
        $credentials = $this->testUsers[$userRole];
        
        try {
            // Simulate authentication by setting session variables
            $_SESSION['user_id'] = $credentials['role_id']; // Using role_id as user_id for testing
            $_SESSION['username'] = $credentials['username'];
            $_SESSION['role_id'] = $credentials['role_id'];
            $_SESSION['logged_in'] = true;
            
            $this->authenticatedUser = $credentials;
            echo "✅ Authenticated as: {$credentials['role_name']} ({$credentials['username']})\n";
            return true;
            
        } catch (Exception $e) {
            echo "❌ Authentication failed: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Logout current test user
     */
    public function logout() {
        if ($this->authenticatedUser) {
            $this->sessions->destroyClean();
            echo "✅ Logged out: {$this->authenticatedUser['role_name']}\n";
            $this->authenticatedUser = null;
        }
    }
    
    /**
     * Test language keys with authenticated user sessions
     */
    public function testWithAuthentication($userRole = 'superadmin', $focusModule = 'leads') {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "🔐 AUTHENTICATED LANGUAGE TESTING\n";
        echo str_repeat("=", 60) . "\n";
        
        // Authenticate as test user
        if (!$this->authenticateAsTestUser($userRole)) {
            return false;
        }
        
        // Run language tests with authenticated session
        $result = $this->testLanguageKeys($focusModule, true);
        
        // Logout
        $this->logout();
        
        return $result;
    }
    
    /**
     * Test all user roles for language completeness
     */
    public function testAllUserRoles($focusModule = 'leads') {
        echo "\n" . str_repeat("=", 70) . "\n";
        echo "👥 MULTI-ROLE LANGUAGE TESTING\n";
        echo str_repeat("=", 70) . "\n";
        
        $overallSuccess = true;
        $roleResults = [];
        
        foreach ($this->testUsers as $roleKey => $credentials) {
            echo "\n" . str_repeat("-", 50) . "\n";
            echo "Testing Role: {$credentials['role_name']}\n";
            echo str_repeat("-", 50) . "\n";
            
            $success = $this->testWithAuthentication($roleKey, $focusModule);
            $roleResults[$roleKey] = $success;
            
            if (!$success) {
                $overallSuccess = false;
            }
        }
        
        // Summary
        echo "\n" . str_repeat("=", 70) . "\n";
        echo "📊 MULTI-ROLE TEST SUMMARY\n";
        echo str_repeat("=", 70) . "\n";
        
        foreach ($roleResults as $roleKey => $success) {
            $status = $success ? "✅ PASSED" : "❌ FAILED";
            $roleName = $this->testUsers[$roleKey]['role_name'];
            echo "{$status} - {$roleName}\n";
        }
        
        if ($overallSuccess) {
            echo "\n🎉 All user roles passed language testing!\n";
        } else {
            echo "\n⚠️  Some user roles failed language testing. Review output above.\n";
        }
        
        return $overallSuccess;
    }
    
    /**
     * Load all available language files
     */
    private function loadLanguageFiles() {
        $langDir = $this->rootPath . '/public_html/admin/languages';
        
        // Only load main language files (avoid list.php and other system files)
        $languageFiles = ['en.php', 'es.php'];
        
        foreach ($languageFiles as $fileName) {
            $file = $langDir . '/' . $fileName;
            if (file_exists($file)) {
                $langCode = basename($file, '.php');
                
                // Safely include the language file
                $lang = [];
                include $file;
                $this->languageFiles[$langCode] = $lang ?? [];
                echo "✓ Loaded language file: {$langCode} (" . count($this->languageFiles[$langCode]) . " keys)\n";
            } else {
                echo "⚠️  Language file not found: {$fileName}\n";
            }
        }
    }
    
    /**
     * Extract language keys used in a PHP file
     */
    private function extractLanguageKeys($filePath) {
        if (!file_exists($filePath)) {
            $this->errors[] = "File not found: {$filePath}";
            return [];
        }
        
        $content = file_get_contents($filePath);
        $keys = [];
        
        // Pattern to match $lang['key'] usage
        preg_match_all('/\$lang\[[\'"](.*?)[\'"]\]/', $content, $matches);
        
        if (!empty($matches[1])) {
            foreach ($matches[1] as $key) {
                $keys[] = $key;
            }
        }
        
        return array_unique($keys);
    }
    
    /**
     * Get line numbers where language keys are used
     */
    private function getKeyLineNumbers($filePath, $key) {
        if (!file_exists($filePath)) {
            return [];
        }
        
        $lines = file($filePath, FILE_IGNORE_NEW_LINES);
        $lineNumbers = [];
        
        foreach ($lines as $lineNum => $line) {
            if (strpos($line, "\$lang['{$key}']") !== false || 
                strpos($line, "\$lang[\"{$key}\"]") !== false) {
                $lineNumbers[] = $lineNum + 1; // Convert to 1-based line numbers
            }
        }
        
        return $lineNumbers;
    }
    
    /**
     * Test leads module files for missing language keys
     */
    public function testLeadsModule() {
        echo "\n=== TESTING LEADS MODULE ===\n";
        
        $leadsFiles = [
            $this->rootPath . '/public_html/leads/edit.php',
            $this->rootPath . '/public_html/leads/index.php',
            $this->rootPath . '/public_html/leads/new.php',
            $this->rootPath . '/public_html/leads/get.php'
        ];
        
        foreach ($leadsFiles as $file) {
            if (file_exists($file)) {
                $this->testFile($file);
            } else {
                $this->warnings[] = "File not found: " . basename($file);
            }
        }
    }
    
    /**
     * Test additional modules for language issues
     */
    public function testAdditionalModules() {
        echo "\n=== TESTING ADDITIONAL MODULES ===\n";
        
        $additionalFiles = [
            // Contacts module
            $this->rootPath . '/public_html/contacts/edit.php',
            $this->rootPath . '/public_html/contacts/new.php',
            $this->rootPath . '/public_html/contacts/index.php',
            
            // Properties module  
            $this->rootPath . '/public_html/properties/edit.php',
            $this->rootPath . '/public_html/properties/new.php',
            $this->rootPath . '/public_html/properties/index.php',
            
            // Reports module
            $this->rootPath . '/public_html/reports/index.php',
            
            // Admin module
            $this->rootPath . '/public_html/admin/index.php',
        ];
        
        foreach ($additionalFiles as $file) {
            if (file_exists($file)) {
                $this->testFile($file);
            } else {
                $this->warnings[] = "Additional file not found: " . basename($file);
            }
        }
    }
    
    /**
     * Test for potential language key patterns that might be missing
     */
    public function testCommonPatterns() {
        echo "\n=== TESTING COMMON LANGUAGE PATTERNS ===\n";
        
        // Common patterns that should exist in both languages
        $commonPatterns = [
            'save', 'cancel', 'delete', 'edit', 'add', 'remove',
            'yes', 'no', 'confirm', 'warning', 'error', 'success',
            'required', 'optional', 'loading', 'search', 'filter'
        ];
        
        foreach ($this->languageFiles as $langCode => $langArray) {
            $missing = [];
            foreach ($commonPatterns as $pattern) {
                if (!isset($langArray[$pattern])) {
                    $missing[] = $pattern;
                }
            }
            
            if (!empty($missing)) {
                echo "⚠️  Common patterns missing in {$langCode}.php: " . implode(', ', $missing) . "\n";
            } else {
                echo "✓ All common patterns present in {$langCode}.php\n";
            }
        }
    }
    
    /**
     * Test a specific file for missing language keys
     */
    public function testFile($filePath) {
        $fileName = basename($filePath);
        echo "\n--- Testing: {$fileName} ---\n";
        
        $usedKeys = $this->extractLanguageKeys($filePath);
        echo "Found " . count($usedKeys) . " language keys in use\n";
        
        if (empty($usedKeys)) {
            echo "No language keys found in {$fileName}\n";
            return;
        }
        
        // Check each language file for missing keys
        foreach ($this->languageFiles as $langCode => $langArray) {
            $missingKeys = [];
            
            foreach ($usedKeys as $key) {
                if (!isset($langArray[$key])) {
                    $missingKeys[] = $key;
                }
            }
            
            if (!empty($missingKeys)) {
                echo "❌ Missing keys in {$langCode}.php:\n";
                foreach ($missingKeys as $key) {
                    $lineNumbers = $this->getKeyLineNumbers($filePath, $key);
                    $lineInfo = !empty($lineNumbers) ? " (lines: " . implode(', ', $lineNumbers) . ")" : "";
                    echo "   - '{$key}'{$lineInfo}\n";
                    
                    $this->errors[] = [
                        'file' => $filePath,
                        'language' => $langCode,
                        'key' => $key,
                        'lines' => $lineNumbers
                    ];
                }
            } else {
                echo "✓ All keys present in {$langCode}.php\n";
            }
        }
    }
    
    /**
     * Suggest fixes for missing language keys
     */
    public function suggestFixes() {
        echo "\n=== SUGGESTED FIXES ===\n";
        
        if (empty($this->errors)) {
            echo "✅ No missing language keys found!\n";
            return;
        }
        
        // Group errors by language and key
        $fixesByLang = [];
        foreach ($this->errors as $error) {
            $lang = $error['language'];
            $key = $error['key'];
            
            if (!isset($fixesByLang[$lang])) {
                $fixesByLang[$lang] = [];
            }
            
            if (!isset($fixesByLang[$lang][$key])) {
                $fixesByLang[$lang][$key] = [];
            }
            
            $fixesByLang[$lang][$key][] = [
                'file' => basename($error['file']),
                'lines' => $error['lines']
            ];
        }
        
        foreach ($fixesByLang as $langCode => $keys) {
            echo "\n--- Fixes needed for {$langCode}.php ---\n";
            
            foreach ($keys as $key => $usages) {
                // Suggest appropriate translation
                $suggestedValue = $this->suggestTranslation($key, $langCode);
                echo "Add: '{$key}' => '{$suggestedValue}',\n";
                
                echo "  Used in:\n";
                foreach ($usages as $usage) {
                    $lineInfo = !empty($usage['lines']) ? " (lines: " . implode(', ', $usage['lines']) . ")" : "";
                    echo "    - {$usage['file']}{$lineInfo}\n";
                }
                echo "\n";
                
                $this->fixes[] = [
                    'language' => $langCode,
                    'key' => $key,
                    'value' => $suggestedValue
                ];
            }
        }
    }
    
    /**
     * Suggest translation based on key name and language
     */
    private function suggestTranslation($key, $langCode) {
        // Common translations
        $translations = [
            'en' => [
                'address' => 'Address',
                'lead_address' => 'Lead Address',
                'property_address' => 'Property Address',
                'full_address' => 'Full Address',
                'street_address' => 'Street Address',
                'business_address' => 'Business Address'
            ],
            'es' => [
                'address' => 'Dirección',
                'lead_address' => 'Dirección del Cliente Potencial',
                'property_address' => 'Dirección de Propiedad',
                'full_address' => 'Dirección Completa',
                'street_address' => 'Dirección',
                'business_address' => 'Dirección Comercial'
            ]
        ];
        
        if (isset($translations[$langCode][$key])) {
            return $translations[$langCode][$key];
        }
        
        // Fallback: capitalize and replace underscores
        return ucwords(str_replace('_', ' ', $key));
    }
    
    /**
     * Apply fixes automatically
     */
    public function applyFixes() {
        echo "\n=== APPLYING FIXES ===\n";
        
        if (empty($this->fixes)) {
            echo "No fixes to apply.\n";
            return;
        }
        
        $fixesByLang = [];
        foreach ($this->fixes as $fix) {
            $fixesByLang[$fix['language']][] = $fix;
        }
        
        foreach ($fixesByLang as $langCode => $langFixes) {
            $langFile = $this->rootPath . "/public_html/admin/languages/{$langCode}.php";
            
            if (!file_exists($langFile)) {
                echo "❌ Language file not found: {$langFile}\n";
                continue;
            }
            
            echo "Updating {$langCode}.php...\n";
            
            // Read current file
            $content = file_get_contents($langFile);
            
            // Find the end of the array (before the closing ];)
            $insertPosition = strrpos($content, '];');
            
            if ($insertPosition === false) {
                echo "❌ Could not find array end in {$langCode}.php\n";
                continue;
            }
            
            // Prepare new entries
            $newEntries = "";
            foreach ($langFixes as $fix) {
                $newEntries .= "  '{$fix['key']}' => '{$fix['value']}',\n";
                echo "  + Added: '{$fix['key']}' => '{$fix['value']}'\n";
            }
            
            // Insert new entries
            $newContent = substr($content, 0, $insertPosition) . $newEntries . substr($content, $insertPosition);
            
            // Write back to file
            if (file_put_contents($langFile, $newContent)) {
                echo "✅ Successfully updated {$langCode}.php\n";
                
                // Set proper ownership
                exec("chown democrm:democrm {$langFile}");
            } else {
                echo "❌ Failed to update {$langCode}.php\n";
            }
        }
    }
    
    /**
     * Run complete test suite
     */
    public function runTests($comprehensive = false) {
        echo "=== LANGUAGE KEYS TEST FRAMEWORK ===\n";
        echo "Testing for missing language keys...\n";
        
        // Always test leads module (primary focus)
        $this->testLeadsModule();
        
        // Run additional tests if requested
        if ($comprehensive) {
            $this->testAdditionalModules();
            $this->testCommonPatterns();
        }
        
        $this->suggestFixes();
        
        // Summary
        echo "\n=== SUMMARY ===\n";
        echo "Errors found: " . count($this->errors) . "\n";
        echo "Warnings: " . count($this->warnings) . "\n";
        echo "Fixes suggested: " . count($this->fixes) . "\n";
        
        if (!empty($this->warnings)) {
            echo "\nWarnings:\n";
            foreach ($this->warnings as $warning) {
                echo "⚠️  {$warning}\n";
            }
        }
        
        return count($this->errors) === 0;
    }
    
    /**
     * Run focused test on leads module only
     */
    public function runLeadsTest() {
        return $this->runTests(false);
    }
    
    /**
     * Run comprehensive test on all modules
     */
    public function runComprehensiveTest() {
        return $this->runTests(true);
    }
}

// Command line interface
if (php_sapi_name() === 'cli') {
    $rootPath = '/run/user/1000/gvfs/sftp:host=159.203.116.150,port=222/home/democrm';
    $test = new LanguageTest($rootPath);
    
    // Parse command line arguments
    $comprehensive = false;
    $testUsers = false;
    $specificRole = null;
    $allRoles = false;
    $module = 'leads';
    
    for ($i = 1; $i < $argc; $i++) {
        $arg = $argv[$i];
        
        if ($arg === '--comprehensive') {
            $comprehensive = true;
        } elseif ($arg === '--test-users') {
            $testUsers = true;
        } elseif ($arg === '--all-roles') {
            $allRoles = true;
        } elseif (strpos($arg, '--role=') === 0) {
            $specificRole = substr($arg, 7);
        } elseif (strpos($arg, '--module=') === 0) {
            $module = substr($arg, 9);
        } elseif ($arg === '--help' || $arg === '-h') {
            echo "Enhanced Language Test Framework\n\n";
            echo "Usage: php tests/language_test.php [options]\n\n";
            echo "Options:\n";
            echo "  --comprehensive      Run comprehensive test across all modules\n";
            echo "  --test-users         Test with authenticated user sessions\n";
            echo "  --all-roles          Test all user roles (requires --test-users)\n";
            echo "  --role=ROLE          Test specific role (superadmin, admin, sales_manager, sales_assistant, sales_person)\n";
            echo "  --module=MODULE      Focus on specific module (leads, contacts, users, reports)\n";
            echo "  --help, -h           Show this help message\n\n";
            echo "Examples:\n";
            echo "  php tests/language_test.php\n";
            echo "  php tests/language_test.php --comprehensive\n";
            echo "  php tests/language_test.php --test-users --role=sales_manager\n";
            echo "  php tests/language_test.php --test-users --all-roles --module=leads\n";
            echo "  php tests/language_test.php --comprehensive --test-users\n";
            exit(0);
        }
    }
    
    $success = false;
    
    // Run tests based on options
    if ($testUsers) {
        if ($allRoles) {
            echo "Running authenticated language tests for all user roles...\n";
            $success = $test->testAllUserRoles($module);
        } elseif ($specificRole) {
            echo "Running authenticated language test for role: {$specificRole}...\n";
            $success = $test->testWithAuthentication($specificRole, $module);
        } else {
            echo "Running authenticated language test with default superadmin role...\n";
            $success = $test->testWithAuthentication('superadmin', $module);
        }
    } else {
        // Traditional testing without authentication
        if ($comprehensive) {
            echo "Running comprehensive test across all modules...\n";
            $success = $test->runComprehensiveTest();
        } else {
            echo "Running focused test on {$module} module...\n";
            $success = $test->runLeadsTest();
        }
    }
    
    // Ask user if they want to apply fixes (only for non-authenticated tests)
    if (!$success && !$testUsers) {
        echo "\n=== AUTO-FIX OPTION ===\n";
        echo "Would you like to automatically apply the suggested fixes? (y/n): ";
        $handle = fopen("php://stdin", "r");
        $input = trim(fgets($handle));
        fclose($handle);
        
        if (strtolower($input) === 'y' || strtolower($input) === 'yes') {
            $test->applyFixes();
            echo "\n✅ Fixes applied! Please test the application.\n";
        } else {
            echo "\n📝 Fixes not applied. You can manually add the suggested language keys.\n";
        }
    } elseif ($success) {
        echo "\n🎉 All tests passed! No language issues found.\n";
    }
    
    exit($success ? 0 : 1);
}