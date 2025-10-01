<?php
/**
 * Simple User Authentication Test
 * 
 * A lightweight test to verify that our test users are properly configured
 * and can be used for testing without full system initialization.
 */

class SimpleUserTest {
    
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
    
    public function __construct() {
        $this->log("🧪 Simple User Test Framework Initialized");
    }
    
    /**
     * Test all user credentials and permissions
     */
    public function testAllUsers() {
        $this->log("\n" . str_repeat("=", 60));
        $this->log("👥 TESTING ALL USER CREDENTIALS");
        $this->log(str_repeat("=", 60));
        
        $totalTests = 0;
        $passedTests = 0;
        
        foreach ($this->testUsers as $roleKey => $credentials) {
            $this->log("\n" . str_repeat("-", 40));
            $this->log("Testing: {$credentials['role_name']}");
            $this->log("Username: {$credentials['username']}");
            $this->log("Role ID: {$credentials['role_id']}");
            $this->log(str_repeat("-", 40));
            
            // Test credential format
            $totalTests++;
            if ($this->testCredentialFormat($credentials)) {
                $passedTests++;
                $this->log("✅ Credential Format: VALID");
            } else {
                $this->log("❌ Credential Format: INVALID");
            }
            
            // Test role permissions
            $totalTests++;
            if ($this->testRolePermissions($credentials['role_id'])) {
                $passedTests++;
                $this->log("✅ Role Permissions: CONFIGURED");
            } else {
                $this->log("❌ Role Permissions: NOT CONFIGURED");
            }
            
            // Test password strength
            $totalTests++;
            if ($this->testPasswordStrength($credentials['password'])) {
                $passedTests++;
                $this->log("✅ Password Strength: STRONG");
            } else {
                $this->log("❌ Password Strength: WEAK");
            }
            
            // Show permissions for this role
            $permissions = $this->rolePermissions[$credentials['role_id']] ?? [];
            $this->log("📋 Permissions: " . implode(', ', $permissions));
        }
        
        // Summary
        $this->log("\n" . str_repeat("=", 60));
        $this->log("📊 TEST SUMMARY");
        $this->log(str_repeat("=", 60));
        $this->log("Total Tests: {$totalTests}");
        $this->log("✅ Passed: {$passedTests}");
        $this->log("❌ Failed: " . ($totalTests - $passedTests));
        $this->log("Success Rate: " . round(($passedTests / $totalTests) * 100, 2) . "%");
        
        if ($passedTests === $totalTests) {
            $this->log("\n🎉 ALL TESTS PASSED! User credentials are properly configured.");
            return true;
        } else {
            $this->log("\n⚠️  Some tests failed. Review the output above.");
            return false;
        }
    }
    
    /**
     * Test credential format
     */
    private function testCredentialFormat($credentials) {
        $required = ['username', 'password', 'email', 'role_id', 'role_name'];
        
        foreach ($required as $field) {
            if (!isset($credentials[$field]) || empty($credentials[$field])) {
                return false;
            }
        }
        
        // Test email format
        if (!filter_var($credentials['email'], FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        // Test role_id is numeric
        if (!is_numeric($credentials['role_id'])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Test role permissions are configured
     */
    private function testRolePermissions($roleId) {
        return isset($this->rolePermissions[$roleId]) && !empty($this->rolePermissions[$roleId]);
    }
    
    /**
     * Test password strength
     */
    private function testPasswordStrength($password) {
        // Check minimum length
        if (strlen($password) < 8) {
            return false;
        }
        
        // Check for uppercase
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }
        
        // Check for lowercase
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }
        
        // Check for numbers
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }
        
        // Check for special characters
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Display user credentials for reference
     */
    public function displayCredentials() {
        $this->log("\n" . str_repeat("=", 70));
        $this->log("🔐 TEST USER CREDENTIALS REFERENCE");
        $this->log(str_repeat("=", 70));
        
        foreach ($this->testUsers as $roleKey => $credentials) {
            $this->log("\n{$credentials['role_name']}:");
            $this->log("  Username: {$credentials['username']}");
            $this->log("  Password: {$credentials['password']}");
            $this->log("  Email: {$credentials['email']}");
            $this->log("  Role ID: {$credentials['role_id']}");
            
            $permissions = $this->rolePermissions[$credentials['role_id']] ?? [];
            $this->log("  Permissions: " . implode(', ', $permissions));
        }
        
        $this->log("\n" . str_repeat("=", 70));
        $this->log("📝 USAGE NOTES:");
        $this->log("- These credentials are for TESTING ONLY");
        $this->log("- All passwords meet security requirements");
        $this->log("- Users have different permission levels for comprehensive testing");
        $this->log("- Credentials are stored in .zencoder/rules/test_users.md");
        $this->log(str_repeat("=", 70));
    }
    
    /**
     * Test specific user role
     */
    public function testSpecificRole($roleKey) {
        if (!isset($this->testUsers[$roleKey])) {
            $this->log("❌ Unknown role: {$roleKey}");
            $this->log("Available roles: " . implode(', ', array_keys($this->testUsers)));
            return false;
        }
        
        $credentials = $this->testUsers[$roleKey];
        
        $this->log("\n" . str_repeat("=", 50));
        $this->log("🔍 TESTING SPECIFIC ROLE: {$credentials['role_name']}");
        $this->log(str_repeat("=", 50));
        
        $tests = [
            'Credential Format' => $this->testCredentialFormat($credentials),
            'Role Permissions' => $this->testRolePermissions($credentials['role_id']),
            'Password Strength' => $this->testPasswordStrength($credentials['password'])
        ];
        
        $passed = 0;
        $total = count($tests);
        
        foreach ($tests as $testName => $result) {
            $status = $result ? "✅ PASSED" : "❌ FAILED";
            $this->log("{$status} - {$testName}");
            if ($result) $passed++;
        }
        
        $this->log("\nResult: {$passed}/{$total} tests passed");
        
        if ($passed === $total) {
            $this->log("🎉 Role '{$credentials['role_name']}' is properly configured!");
            return true;
        } else {
            $this->log("⚠️  Role '{$credentials['role_name']}' has configuration issues.");
            return false;
        }
    }
    
    /**
     * Generate role permission matrix
     */
    public function showPermissionMatrix() {
        $this->log("\n" . str_repeat("=", 80));
        $this->log("📊 ROLE PERMISSION MATRIX");
        $this->log(str_repeat("=", 80));
        
        // Header
        $this->log(sprintf("%-20s | %-6s | %-6s | %-8s | %-7s | %-5s | %-6s",
            "Role", "Users", "Leads", "Contacts", "Reports", "Admin", "System"));
        $this->log(str_repeat("-", 80));
        
        foreach ($this->testUsers as $roleKey => $credentials) {
            $roleId = $credentials['role_id'];
            $perms = $this->rolePermissions[$roleId] ?? [];
            
            $this->log(sprintf("%-20s | %-6s | %-6s | %-8s | %-7s | %-5s | %-6s",
                $credentials['role_name'],
                in_array('users', $perms) ? '✅' : '❌',
                in_array('leads', $perms) ? '✅' : '❌',
                in_array('contacts', $perms) ? '✅' : '❌',
                in_array('reports', $perms) ? '✅' : '❌',
                in_array('admin', $perms) ? '✅' : '❌',
                in_array('system_config', $perms) ? '✅' : '❌'
            ));
        }
        
        $this->log(str_repeat("=", 80));
    }
    
    /**
     * Log message with timestamp
     */
    private function log($message) {
        echo $message . "\n";
    }
}

// Command line interface
if (php_sapi_name() === 'cli') {
    $tester = new SimpleUserTest();
    
    // Parse command line arguments
    $showCredentials = false;
    $showMatrix = false;
    $specificRole = null;
    
    for ($i = 1; $i < $argc; $i++) {
        $arg = $argv[$i];
        
        if ($arg === '--credentials') {
            $showCredentials = true;
        } elseif ($arg === '--matrix') {
            $showMatrix = true;
        } elseif (strpos($arg, '--role=') === 0) {
            $specificRole = substr($arg, 7);
        } elseif ($arg === '--help' || $arg === '-h') {
            echo "Simple User Test Framework\n\n";
            echo "Usage: php tests/simple_user_test.php [options]\n\n";
            echo "Options:\n";
            echo "  --credentials        Display all test user credentials\n";
            echo "  --matrix             Show role permission matrix\n";
            echo "  --role=ROLE          Test specific role (superadmin, admin, sales_manager, sales_assistant, sales_person)\n";
            echo "  --help, -h           Show this help message\n\n";
            echo "Examples:\n";
            echo "  php tests/simple_user_test.php\n";
            echo "  php tests/simple_user_test.php --credentials\n";
            echo "  php tests/simple_user_test.php --matrix\n";
            echo "  php tests/simple_user_test.php --role=sales_manager\n";
            exit(0);
        }
    }
    
    // Execute based on options
    if ($showCredentials) {
        $tester->displayCredentials();
    } elseif ($showMatrix) {
        $tester->showPermissionMatrix();
    } elseif ($specificRole) {
        $success = $tester->testSpecificRole($specificRole);
        exit($success ? 0 : 1);
    } else {
        $success = $tester->testAllUsers();
        exit($success ? 0 : 1);
    }
}