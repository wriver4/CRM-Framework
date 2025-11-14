<?php

namespace Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Security;

/**
 * Security Class Unit Tests
 * 
 * Tests password hashing, verification, and authentication checks.
 * This is CRITICAL for application security.
 * 
 * @group Core
 * @group Critical
 * @group Security
 */
class SecurityTest extends TestCase
{
    private $security;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Load required classes
        require_once __DIR__ . '/../../../../classes/Core/Database.php';
        require_once __DIR__ . '/../../../../classes/Core/Security.php';
        
        $this->security = new Security();
        
        // Start session if not already started (needed for login checks)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    protected function tearDown(): void
    {
        // Clean up session
        $_SESSION = [];
        
        $this->security = null;
        parent::tearDown();
    }
    
    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(Security::class, $this->security);
    }
    
    /** @test */
    public function it_extends_database_class()
    {
        $this->assertInstanceOf(
            \Database::class,
            $this->security,
            'Security should extend Database class'
        );
    }
    
    /** @test */
    public function it_has_hash_method()
    {
        $this->assertTrue(
            method_exists(Security::class, 'hash'),
            'Security class must have hash() method'
        );
    }
    
    /** @test */
    public function it_has_verify_method()
    {
        $this->assertTrue(
            method_exists(Security::class, 'verify'),
            'Security class must have verify() method'
        );
    }
    
    /** @test */
    public function it_can_hash_password()
    {
        $password = 'test_password_123';
        $hash = Security::hash($password);
        
        $this->assertNotEmpty($hash, 'Hash should not be empty');
        $this->assertNotEquals($password, $hash, 'Hash should not equal plain password');
        $this->assertGreaterThan(50, strlen($hash), 'Hash should be at least 50 characters');
    }
    
    /** @test */
    public function it_creates_different_hashes_for_same_password()
    {
        $password = 'test_password_123';
        
        $hash1 = Security::hash($password);
        $hash2 = Security::hash($password);
        
        $this->assertNotEquals(
            $hash1,
            $hash2,
            'Same password should produce different hashes (salt)'
        );
    }
    
    /** @test */
    public function it_can_verify_correct_password()
    {
        $password = 'test_password_123';
        $hash = Security::hash($password);
        
        $this->assertTrue(
            Security::verify($password, $hash),
            'Should verify correct password'
        );
    }
    
    /** @test */
    public function it_rejects_incorrect_password()
    {
        $password = 'test_password_123';
        $wrongPassword = 'wrong_password';
        $hash = Security::hash($password);
        
        $this->assertFalse(
            Security::verify($wrongPassword, $hash),
            'Should reject incorrect password'
        );
    }
    
    /** @test */
    public function it_handles_empty_password()
    {
        $emptyPassword = '';
        $hash = Security::hash($emptyPassword);
        
        $this->assertNotEmpty($hash, 'Should hash empty password');
        $this->assertTrue(
            Security::verify($emptyPassword, $hash),
            'Should verify empty password'
        );
    }
    
    /** @test */
    public function it_handles_special_characters_in_password()
    {
        $specialPassword = 'P@ssw0rd!#$%^&*()_+-=[]{}|;:,.<>?';
        $hash = Security::hash($specialPassword);
        
        $this->assertTrue(
            Security::verify($specialPassword, $hash),
            'Should handle special characters in password'
        );
    }
    
    /** @test */
    public function it_handles_unicode_characters_in_password()
    {
        $unicodePassword = 'пароль密码🔐';
        $hash = Security::hash($unicodePassword);
        
        $this->assertTrue(
            Security::verify($unicodePassword, $hash),
            'Should handle Unicode characters in password'
        );
    }
    
    /** @test */
    public function it_handles_very_long_password()
    {
        $longPassword = str_repeat('a', 1000);
        $hash = Security::hash($longPassword);
        
        $this->assertTrue(
            Security::verify($longPassword, $hash),
            'Should handle very long passwords'
        );
    }
    
    /** @test */
    public function it_is_case_sensitive()
    {
        $password = 'TestPassword123';
        $hash = Security::hash($password);
        
        $this->assertTrue(
            Security::verify('TestPassword123', $hash),
            'Should verify exact case'
        );
        
        $this->assertFalse(
            Security::verify('testpassword123', $hash),
            'Should reject different case'
        );
        
        $this->assertFalse(
            Security::verify('TESTPASSWORD123', $hash),
            'Should reject different case'
        );
    }
    
    /** @test */
    public function it_has_check_user_login_method()
    {
        $this->assertTrue(
            method_exists($this->security, 'check_user_login'),
            'Security class must have check_user_login() method'
        );
    }
    
    /** @test */
    public function it_has_check_user_permissions_method()
    {
        $this->assertTrue(
            method_exists($this->security, 'check_user_permissions'),
            'Security class must have check_user_permissions() method'
        );
    }
    
    /** @test */
    public function check_user_permissions_returns_false_when_not_logged_in()
    {
        // Ensure user is not logged in
        unset($_SESSION['loggedin']);
        unset($_SESSION['user_id']);
        
        $result = $this->security->check_user_permissions('leads', 'read', false);
        
        $this->assertFalse(
            $result,
            'Should return false when user is not logged in'
        );
    }
    
    /** @test */
    public function check_user_permissions_returns_true_for_logged_in_user()
    {
        // Simulate logged in user
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = 1;
        $_SESSION['user_role'] = 'user';
        
        $result = $this->security->check_user_permissions('leads', 'read', false);
        
        $this->assertTrue(
            $result,
            'Should return true for logged in user with read permission'
        );
    }
    
    /** @test */
    public function check_user_permissions_returns_true_for_admin()
    {
        // Simulate admin user
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = 1;
        $_SESSION['user_role'] = 'admin';
        
        $result = $this->security->check_user_permissions('leads', 'delete', false);
        
        $this->assertTrue(
            $result,
            'Admin should have all permissions'
        );
    }
    
    /** @test */
    public function check_user_permissions_returns_true_for_administrator()
    {
        // Simulate administrator user
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = 1;
        $_SESSION['user_role'] = 'administrator';
        
        $result = $this->security->check_user_permissions('admin', 'write', false);
        
        $this->assertTrue(
            $result,
            'Administrator should have all permissions'
        );
    }
    
    /** @test */
    public function it_handles_different_permission_actions()
    {
        // Simulate logged in user
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = 1;
        $_SESSION['user_role'] = 'user';
        
        $actions = ['read', 'write', 'delete', 'create', 'update'];
        
        foreach ($actions as $action) {
            $result = $this->security->check_user_permissions('leads', $action, false);
            $this->assertTrue(
                $result,
                "Should handle '$action' permission"
            );
        }
    }
    
    /** @test */
    public function it_handles_different_modules()
    {
        // Simulate logged in user
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = 1;
        $_SESSION['user_role'] = 'user';
        
        $modules = ['leads', 'contacts', 'users', 'admin', 'dashboard'];
        
        foreach ($modules as $module) {
            $result = $this->security->check_user_permissions($module, 'read', false);
            $this->assertTrue(
                $result,
                "Should handle '$module' module"
            );
        }
    }
    
    /** @test */
    public function hash_uses_bcrypt_algorithm()
    {
        $password = 'test_password';
        $hash = Security::hash($password);
        
        // Bcrypt hashes start with $2y$
        $this->assertStringStartsWith(
            '$2y$',
            $hash,
            'Should use bcrypt algorithm (PASSWORD_DEFAULT)'
        );
    }
    
    /** @test */
    public function verify_returns_false_for_invalid_hash()
    {
        $password = 'test_password';
        $invalidHash = 'not_a_valid_hash';
        
        $this->assertFalse(
            Security::verify($password, $invalidHash),
            'Should return false for invalid hash format'
        );
    }
    
    /** @test */
    public function verify_returns_false_for_empty_hash()
    {
        $password = 'test_password';
        $emptyHash = '';
        
        $this->assertFalse(
            Security::verify($password, $emptyHash),
            'Should return false for empty hash'
        );
    }
    
    /** @test */
    public function it_handles_null_password_gracefully()
    {
        // Test with null password - should not throw exception
        $hash = Security::hash(null);
        $this->assertNotEmpty($hash);
        
        // Verify should handle null
        $this->assertTrue(Security::verify(null, $hash));
    }
}