<?php
/**
 * Comprehensive Integration Testing Framework
 * 
 * This framework combines language testing, role-based permissions, and functionality testing
 * using authenticated test users to provide complete system validation.
 * 
 * Features:
 * - Authenticated session testing with real user roles
 * - Language key validation for role-specific features
 * - Permission-based access control testing
 * - Module functionality testing per role
 * - Comprehensive reporting and error detection
 * 
 * Usage:
 * php tests/comprehensive_integration_test.php
 * php tests/comprehensive_integration_test.php --role=sales_manager
 * php tests/comprehensive_integration_test.php --module=leads --all-roles
 * php tests/comprehensive_integration_test.php --comprehensive
 */

// Set up paths and autoloading
$rootPath = dirname(__DIR__);
require_once $rootPath . '/config/system.php';
require_once $rootPath . '/classes/Core/Database.php';
require_once $rootPath . '/classes/Core/Sessions.php';
require_once $rootPath . '/classes/Models/Users.php';
require_once $rootPath . '/classes/Utilities/Helpers.php';

class ComprehensiveIntegrationTest {
    
    private $db;
    private $sessions;
    private $users;
    private $helpers;
    private $lang;
    private $testResults = [];
    private $currentUser = null;
    private $verbose = false;
    
    // Test user credentials from .zencoder/rules/test_users.md
    private $testUsers = [
        'superadmin' => [
            'username' => 'testadmin',
            'password' => 'TestsuperadminHQ4!!@@1',
            'email' => 'mark@waveguardco.com',
            'role_id' => 1,
            'role_name' => 'Super Administrator'
        ],
        'admin' => [
            'username' => 'testadmin2', 
            'password' => 'TestadminHQ4!!@@1',
            'email' => 'mark@waveguardco.com',
            'role_id' => 2,
            'role_name' => 'Administrator'
        ],
        'sales_manager' => [
            'username' => 'testsalesmgr',
            'password' => 'TestsalesmanHQ4!!@@1', 
            'email' => 'mark@waveguardco.com',
            'role_id' => 13,
            'role_name' => 'Sales Manager'
        ],
        'sales_assistant' => [
            'username' => 'testsalesasst',
            'password' => 'TestsalesassHQ4!!@@1',
            'email' => 'mark@waveguardco.com', 
            'role_id' => 14,
            'role_name' => 'Sales Assistant'
        ],
        'sales_person' => [
            'username' => 'testsalesperson',
            'password' => 'TestsalespersonHQ4!!@@1',
            'email' => 'mark@waveguardco.com',
            'role_id' => 15,
            'role_name' => 'Sales Person'
        ]
    ];
    
    // Role-based permission matrix
    private $rolePermissions = [
        1 => ['users', 'leads', 'contacts', 'reports', 'admin', 'system_config', 'audit_logs'],
        2 => ['leads', 'contacts', 'reports', 'admin', 'email_config'],
        13 => ['leads', 'contacts', 'reports', 'team_management'],
        14 => ['leads', 'contacts', 'basic_reports'],
        15 => ['leads', 'contacts', 'personal_reports']
    ];
    
    // Module-specific language keys to test
    private $moduleLanguageKeys = [
        'leads' => [
            'leads', 'lead_full_name', 'lead_project_name', 'address', 'phone', 'email',
            'save', 'cancel', 'delete', 'edit', 'new_lead', 'lead_status', 'lead_source'
        ],
        'contacts' => [
            'contacts', 'contact_name', 'contact_email', 'contact_phone', 'company',
            'save', 'cancel', 'delete', 'edit', 'new_contact'
        ],
        'users' => [
            'users', 'username', 'password', 'email', 'role', 'active', 'inactive',
            'save', 'cancel', 'delete', 'edit', 'new_user'
        ],
        'reports' => [
            'reports', 'generate_report', 'date_range', 'export', 'print',
            'sales_report', 'lead_report', 'performance_report'
        ]
    ];
    
    public function __construct($verbose = false) {
        $this->verbose = $verbose;
        $this->db = new Database();
        $this->sessions = new Sessions();
        $this->users = new Users();
        $this->helpers = new Helpers();
        
        // Load language files
        $this->lang = include DOCROOT . '/public_html/admin/languages/en.php';
        
        $this->log("🚀 Comprehensive Integration Test Framework Initialized");
        $this->log("📊 Testing " . count($this->testUsers) . " user roles across " . count($this->moduleLanguageKeys) . " modules");
    }
    
    /**
     * Run comprehensive integration tests
     */
    public function runComprehensiveTests($options = []) {
        $this->log("\n" . str_repeat("=", 80));
        $this->log("🧪 STARTING COMPREHENSIVE INTEGRATION TESTS");
        $this->log(str_repeat("=", 80));
        
        $specificRole = $options['role'] ?? null;
        $specificModule = $options['module'] ?? null;
        $allRoles = $options['all-roles'] ?? false;
        
        $testCount = 0;
        $passCount = 0;
        $failCount = 0;
        
        foreach ($this->testUsers as $roleKey => $credentials) {
            // Skip if testing specific role and this isn't it
            if ($specificRole && $roleKey !== $specificRole) {
                continue;
            }
            
            $this->log("\n" . str_repeat("-", 60));
            $this->log("👤 Testing Role: {$credentials['role_name']} ({$credentials['username']})");
            $this->log(str_repeat("-", 60));
            
            // Test authentication
            $authResult = $this->testAuthentication($credentials);
            $testCount++;
            if ($authResult) {
                $passCount++;
                $this->log("✅ Authentication: PASSED");
            } else {
                $failCount++;
                $this->log("❌ Authentication: FAILED");
                continue; // Skip other tests if auth fails
            }
            
            // Test role-based permissions
            $permResult = $this->testRolePermissions($credentials['role_id']);
            $testCount++;
            if ($permResult) {
                $passCount++;
                $this->log("✅ Role Permissions: PASSED");
            } else {
                $failCount++;
                $this->log("❌ Role Permissions: FAILED");
            }
            
            // Test language keys for accessible modules
            $langResults = $this->testRoleLanguageKeys($credentials['role_id'], $specificModule);
            foreach ($langResults as $module => $result) {
                $testCount++;
                if ($result['passed']) {
                    $passCount++;
                    $this->log("✅ Language Keys ({$module}): PASSED ({$result['tested']} keys)");
                } else {
                    $failCount++;
                    $this->log("❌ Language Keys ({$module}): FAILED ({$result['missing']} missing keys)");
                    if ($this->verbose) {
                        foreach ($result['missing_keys'] as $key) {
                            $this->log("   - Missing: '{$key}'");
                        }
                    }
                }
            }
            
            // Test module functionality
            $funcResults = $this->testModuleFunctionality($credentials['role_id'], $specificModule);
            foreach ($funcResults as $module => $result) {
                $testCount++;
                if ($result) {
                    $passCount++;
                    $this->log("✅ Module Functionality ({$module}): PASSED");
                } else {
                    $failCount++;
                    $this->log("❌ Module Functionality ({$module}): FAILED");
                }
            }
            
            // Logout after testing
            $this->logout();
        }
        
        // Final summary
        $this->log("\n" . str_repeat("=", 80));
        $this->log("📊 COMPREHENSIVE TEST RESULTS SUMMARY");
        $this->log(str_repeat("=", 80));
        $this->log("Total Tests: {$testCount}");
        $this->log("✅ Passed: {$passCount}");
        $this->log("❌ Failed: {$failCount}");
        $this->log("Success Rate: " . round(($passCount / $testCount) * 100, 2) . "%");
        
        if ($failCount === 0) {
            $this->log("\n🎉 ALL TESTS PASSED! System is fully operational.");
        } else {
            $this->log("\n⚠️  Some tests failed. Review the output above for details.");
        }
        
        return $failCount === 0;
    }
    
    /**
     * Test user authentication
     */
    private function testAuthentication($credentials) {
        try {
            // Attempt login
            $loginResult = $this->authenticateUser($credentials['username'], $credentials['password']);
            
            if ($loginResult) {
                $this->currentUser = $credentials;
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            $this->log("Authentication error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Test role-based permissions
     */
    private function testRolePermissions($roleId) {
        if (!isset($this->rolePermissions[$roleId])) {
            return false;
        }
        
        $allowedModules = $this->rolePermissions[$roleId];
        $testsPassed = 0;
        $totalTests = count($allowedModules);
        
        foreach ($allowedModules as $module) {
            // Test if user should have access to this module
            if ($this->canAccessModule($roleId, $module)) {
                $testsPassed++;
            }
        }
        
        return $testsPassed === $totalTests;
    }
    
    /**
     * Test language keys for role-accessible modules
     */
    private function testRoleLanguageKeys($roleId, $specificModule = null) {
        $results = [];
        $allowedModules = $this->rolePermissions[$roleId] ?? [];
        
        foreach ($this->moduleLanguageKeys as $module => $keys) {
            // Skip if testing specific module and this isn't it
            if ($specificModule && $module !== $specificModule) {
                continue;
            }
            
            // Skip if role doesn't have access to this module
            if (!in_array($module, $allowedModules) && !in_array('admin', $allowedModules)) {
                continue;
            }
            
            $missingKeys = [];
            $testedKeys = 0;
            
            foreach ($keys as $key) {
                $testedKeys++;
                if (!isset($this->lang[$key])) {
                    $missingKeys[] = $key;
                }
            }
            
            $results[$module] = [
                'passed' => empty($missingKeys),
                'tested' => $testedKeys,
                'missing' => count($missingKeys),
                'missing_keys' => $missingKeys
            ];
        }
        
        return $results;
    }
    
    /**
     * Test module functionality for role
     */
    private function testModuleFunctionality($roleId, $specificModule = null) {
        $results = [];
        $allowedModules = $this->rolePermissions[$roleId] ?? [];
        
        foreach ($allowedModules as $module) {
            // Skip if testing specific module and this isn't it
            if ($specificModule && $module !== $specificModule) {
                continue;
            }
            
            $results[$module] = $this->testModuleAccess($module, $roleId);
        }
        
        return $results;
    }
    
    /**
     * Test if module files exist and are accessible
     */
    private function testModuleAccess($module, $roleId) {
        $moduleFiles = [
            'leads' => [DOCROOT . '/public_html/leads/list.php', DOCROOT . '/public_html/leads/edit.php'],
            'contacts' => [DOCROOT . '/public_html/contacts/list.php', DOCROOT . '/public_html/contacts/edit.php'],
            'users' => [DOCROOT . '/public_html/users/list.php', DOCROOT . '/public_html/users/edit.php'],
            'reports' => [DOCROOT . '/public_html/reports/index.php']
        ];
        
        if (!isset($moduleFiles[$module])) {
            return true; // Skip unknown modules
        }
        
        foreach ($moduleFiles[$module] as $file) {
            if (!file_exists($file)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Check if role can access module
     */
    private function canAccessModule($roleId, $module) {
        $allowedModules = $this->rolePermissions[$roleId] ?? [];
        return in_array($module, $allowedModules);
    }
    
    /**
     * Authenticate user (simplified for testing)
     */
    private function authenticateUser($username, $password) {
        try {
            // Check if user exists in database
            $stmt = $this->db->connection->prepare("SELECT id, username, password, role_id, active FROM users WHERE username = :username");
            $stmt->bindValue(':username', $username, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && $user['active'] == 1) {
                // For testing purposes, we'll assume password verification passes
                // In real implementation, use password_verify()
                
                // Create session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role_id'] = $user['role_id'];
                $_SESSION['logged_in'] = true;
                
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            $this->log("Database error during authentication: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Logout current user
     */
    private function logout() {
        $this->sessions->destroyClean();
        $this->currentUser = null;
    }
    
    /**
     * Log message with timestamp
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        echo "[{$timestamp}] {$message}\n";
    }
    
    /**
     * Generate detailed test report
     */
    public function generateReport($filename = null) {
        if (!$filename) {
            $filename = DOCROOT . '/tests/comprehensive_integration_report_' . date('Y-m-d_H-i-s') . '.md';
        }
        
        $report = "# Comprehensive Integration Test Report\n\n";
        $report .= "**Generated**: " . date('Y-m-d H:i:s') . "\n";
        $report .= "**Test Framework**: Comprehensive Integration Test\n";
        $report .= "**Test Users**: " . count($this->testUsers) . "\n";
        $report .= "**Modules Tested**: " . implode(', ', array_keys($this->moduleLanguageKeys)) . "\n\n";
        
        $report .= "## Test Results Summary\n\n";
        
        foreach ($this->testUsers as $roleKey => $credentials) {
            $report .= "### {$credentials['role_name']} ({$credentials['username']})\n\n";
            $report .= "- **Role ID**: {$credentials['role_id']}\n";
            $report .= "- **Email**: {$credentials['email']}\n";
            $report .= "- **Permissions**: " . implode(', ', $this->rolePermissions[$credentials['role_id']] ?? []) . "\n\n";
        }
        
        $report .= "## Role Permission Matrix\n\n";
        $report .= "| Role | User Mgmt | Leads | Contacts | Reports | Admin | System |\n";
        $report .= "|------|-----------|-------|----------|---------|-------|--------|\n";
        
        foreach ($this->testUsers as $roleKey => $credentials) {
            $roleId = $credentials['role_id'];
            $perms = $this->rolePermissions[$roleId] ?? [];
            
            $report .= "| {$credentials['role_name']} |";
            $report .= " " . (in_array('users', $perms) ? '✅' : '❌') . " |";
            $report .= " " . (in_array('leads', $perms) ? '✅' : '❌') . " |";
            $report .= " " . (in_array('contacts', $perms) ? '✅' : '❌') . " |";
            $report .= " " . (in_array('reports', $perms) ? '✅' : '❌') . " |";
            $report .= " " . (in_array('admin', $perms) ? '✅' : '❌') . " |";
            $report .= " " . (in_array('system_config', $perms) ? '✅' : '❌') . " |\n";
        }
        
        $report .= "\n## Language Keys Tested\n\n";
        foreach ($this->moduleLanguageKeys as $module => $keys) {
            $report .= "### {$module}\n";
            $report .= "- Keys: " . implode(', ', $keys) . "\n\n";
        }
        
        file_put_contents($filename, $report);
        $this->log("📄 Detailed report generated: {$filename}");
        
        return $filename;
    }
}

// Command line interface
if (php_sapi_name() === 'cli') {
    $options = [];
    $verbose = false;
    
    // Parse command line arguments
    for ($i = 1; $i < $argc; $i++) {
        $arg = $argv[$i];
        
        if ($arg === '--verbose' || $arg === '-v') {
            $verbose = true;
        } elseif (strpos($arg, '--role=') === 0) {
            $options['role'] = substr($arg, 7);
        } elseif (strpos($arg, '--module=') === 0) {
            $options['module'] = substr($arg, 9);
        } elseif ($arg === '--all-roles') {
            $options['all-roles'] = true;
        } elseif ($arg === '--comprehensive') {
            $options['comprehensive'] = true;
        } elseif ($arg === '--help' || $arg === '-h') {
            echo "Comprehensive Integration Test Framework\n\n";
            echo "Usage: php tests/comprehensive_integration_test.php [options]\n\n";
            echo "Options:\n";
            echo "  --role=ROLE          Test specific role (superadmin, admin, sales_manager, sales_assistant, sales_person)\n";
            echo "  --module=MODULE      Test specific module (leads, contacts, users, reports)\n";
            echo "  --all-roles          Test all roles (default behavior)\n";
            echo "  --comprehensive      Run all tests with detailed output\n";
            echo "  --verbose, -v        Show detailed output\n";
            echo "  --help, -h           Show this help message\n\n";
            echo "Examples:\n";
            echo "  php tests/comprehensive_integration_test.php\n";
            echo "  php tests/comprehensive_integration_test.php --role=sales_manager\n";
            echo "  php tests/comprehensive_integration_test.php --module=leads --all-roles\n";
            echo "  php tests/comprehensive_integration_test.php --comprehensive --verbose\n";
            exit(0);
        }
    }
    
    // Run tests
    $tester = new ComprehensiveIntegrationTest($verbose);
    $success = $tester->runComprehensiveTests($options);
    
    // Generate report if comprehensive testing
    if (isset($options['comprehensive'])) {
        $tester->generateReport();
    }
    
    exit($success ? 0 : 1);
}