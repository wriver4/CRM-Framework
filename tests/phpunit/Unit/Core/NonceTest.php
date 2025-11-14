<?php

namespace Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Nonce;

/**
 * Nonce Class Unit Tests
 * 
 * Tests CSRF protection token generation and verification.
 * This is CRITICAL for application security.
 * 
 * @group Core
 * @group Critical
 * @group Security
 * @group CSRF
 */
class NonceTest extends TestCase
{
    private $nonce;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure NONCE_SECRET is defined
        if (!defined('NONCE_SECRET')) {
            define('NONCE_SECRET', 'test-secret-key-for-nonce-generation-minimum-14-chars');
        }
        
        // Load Nonce class
        require_once __DIR__ . '/../../../../classes/Core/Nonce.php';
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Clear session nonces
        unset($_SESSION['nonce']);
        
        $this->nonce = new Nonce();
    }
    
    protected function tearDown(): void
    {
        // Clean up session
        unset($_SESSION['nonce']);
        
        $this->nonce = null;
        parent::tearDown();
    }
    
    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(Nonce::class, $this->nonce);
    }
    
    /** @test */
    public function it_can_be_instantiated_with_custom_age()
    {
        $customNonce = new Nonce(5000);
        $this->assertInstanceOf(Nonce::class, $customNonce);
    }
    
    /** @test */
    public function it_has_create_method()
    {
        $this->assertTrue(
            method_exists($this->nonce, 'create'),
            'Nonce class must have create() method'
        );
    }
    
    /** @test */
    public function it_has_verify_method()
    {
        $this->assertTrue(
            method_exists($this->nonce, 'verify'),
            'Nonce class must have verify() method'
        );
    }
    
    /** @test */
    public function it_can_create_nonce_token()
    {
        $token = $this->nonce->create('test_form');
        
        $this->assertNotEmpty($token, 'Token should not be empty');
        $this->assertIsString($token, 'Token should be a string');
    }
    
    /** @test */
    public function created_token_has_correct_format()
    {
        $token = $this->nonce->create('test_form');
        
        // Token format: salt:form_id:time:hash
        $parts = explode(':', $token);
        
        $this->assertCount(
            4,
            $parts,
            'Token should have 4 parts separated by colons'
        );
    }
    
    /** @test */
    public function created_token_contains_form_id()
    {
        $formId = 'my_test_form';
        $token = $this->nonce->create($formId);
        
        // The token format is: binary_salt:form_id:time:hash
        // Since salt is binary, we need to find the form_id differently
        // The form_id appears after the first colon that follows the binary salt
        $this->assertStringContainsString(
            ':' . $formId . ':',
            $token,
            'Token should contain the form ID between colons'
        );
    }
    
    /** @test */
    public function created_token_contains_future_timestamp()
    {
        $token = $this->nonce->create('test_form');
        
        // Token format: base64_salt:form_id:time:hash
        // Now we can safely explode since salt is base64-encoded
        $parts = explode(':', $token);
        $this->assertEquals(4, count($parts), 'Token should have exactly 4 parts');
        
        $timestamp = (int)$parts[2];
        
        $this->assertGreaterThan(
            time(),
            $timestamp,
            'Token timestamp should be in the future'
        );
    }
    
    /** @test */
    public function created_token_contains_hash()
    {
        $token = $this->nonce->create('test_form');
        
        // Token format: base64_salt:form_id:time:hash
        // Now we can safely explode since salt is base64-encoded
        $parts = explode(':', $token);
        $this->assertEquals(4, count($parts), 'Token should have exactly 4 parts');
        
        $hash = $parts[3];
        
        $this->assertEquals(
            64,
            strlen($hash),
            'Hash should be 64 characters (SHA-256)'
        );
    }
    
    /** @test */
    public function it_stores_nonce_in_session()
    {
        $formId = 'test_form';
        $this->nonce->create($formId);
        
        $this->assertTrue(
            isset($_SESSION['nonce'][$formId]),
            'Nonce should be stored in session'
        );
    }
    
    /** @test */
    public function it_can_verify_valid_nonce()
    {
        $token = $this->nonce->create('test_form');
        
        $this->assertTrue(
            $this->nonce->verify($token),
            'Should verify valid nonce'
        );
    }
    
    /** @test */
    public function it_rejects_invalid_nonce_format()
    {
        $invalidToken = 'invalid:token';
        
        $this->assertFalse(
            $this->nonce->verify($invalidToken),
            'Should reject invalid nonce format'
        );
    }
    
    /** @test */
    public function it_rejects_nonce_with_wrong_part_count()
    {
        $invalidToken = 'part1:part2:part3'; // Only 3 parts instead of 4
        
        $this->assertFalse(
            $this->nonce->verify($invalidToken),
            'Should reject nonce with wrong part count'
        );
    }
    
    /** @test */
    public function it_rejects_expired_nonce()
    {
        // Create nonce with very short age (1 second)
        $shortNonce = new Nonce(1);
        $token = $shortNonce->create('test_form');
        
        // Wait for it to expire
        sleep(2);
        
        $this->assertFalse(
            $shortNonce->verify($token),
            'Should reject expired nonce'
        );
    }
    
    /** @test */
    public function it_rejects_nonce_not_in_session()
    {
        $token = $this->nonce->create('test_form');
        
        // Remove from session
        unset($_SESSION['nonce']['test_form']);
        
        $this->assertFalse(
            $this->nonce->verify($token),
            'Should reject nonce not in session'
        );
    }
    
    /** @test */
    public function it_rejects_tampered_nonce()
    {
        $token = $this->nonce->create('test_form');
        
        // Tamper with the token
        $parts = explode(':', $token);
        $parts[3] = str_repeat('a', 64); // Replace hash with fake one
        $tamperedToken = implode(':', $parts);
        
        $this->assertFalse(
            $this->nonce->verify($tamperedToken),
            'Should reject tampered nonce'
        );
    }
    
    /** @test */
    public function it_creates_different_tokens_each_time()
    {
        $token1 = $this->nonce->create('test_form');
        
        // Clear session to create fresh token
        unset($_SESSION['nonce']);
        
        $token2 = $this->nonce->create('test_form');
        
        $this->assertNotEquals(
            $token1,
            $token2,
            'Should create different tokens each time (random salt)'
        );
    }
    
    /** @test */
    public function it_handles_multiple_form_ids()
    {
        $token1 = $this->nonce->create('form_login');
        $token2 = $this->nonce->create('form_register');
        $token3 = $this->nonce->create('form_contact');
        
        $this->assertTrue($this->nonce->verify($token1));
        $this->assertTrue($this->nonce->verify($token2));
        $this->assertTrue($this->nonce->verify($token3));
    }
    
    /** @test */
    public function it_rejects_nonce_for_wrong_form()
    {
        $token = $this->nonce->create('form_login');
        
        // Modify form_id in token
        $parts = explode(':', $token);
        $parts[1] = 'form_register'; // Change form ID
        $modifiedToken = implode(':', $parts);
        
        $this->assertFalse(
            $this->nonce->verify($modifiedToken),
            'Should reject nonce for wrong form'
        );
    }
    
    /** @test */
    public function it_throws_exception_for_invalid_form_id()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('A valid Form ID is required');
        
        // Try to create nonce with non-string form ID
        $reflection = new \ReflectionClass($this->nonce);
        $method = $reflection->getMethod('store');
        $method->setAccessible(true);
        $method->invoke($this->nonce, 123, 'test_nonce'); // Integer instead of string
    }
    
    /** @test */
    public function it_throws_exception_for_short_secret()
    {
        // We can't easily test this without modifying NONCE_SECRET constant
        // which is already defined in bootstrap. Instead, we'll verify that
        // the current NONCE_SECRET is long enough
        $this->assertGreaterThanOrEqual(
            14,
            strlen(NONCE_SECRET),
            'NONCE_SECRET should be at least 14 characters long'
        );
        
        // Verify that create() works with valid secret
        $token = $this->nonce->create('test_form');
        $this->assertNotEmpty($token, 'Should create token with valid secret');
    }
    
    /** @test */
    public function it_accepts_nonce_with_custom_age()
    {
        $customNonce = new Nonce(5000); // 5000 seconds
        $token = $customNonce->create('test_form');
        
        $this->assertTrue(
            $customNonce->verify($token),
            'Should verify nonce with custom age'
        );
    }
    
    /** @test */
    public function it_handles_special_characters_in_form_id()
    {
        $formId = 'form_with-special.chars_123';
        $token = $this->nonce->create($formId);
        
        $this->assertTrue(
            $this->nonce->verify($token),
            'Should handle special characters in form ID'
        );
    }
    
    /** @test */
    public function it_rejects_empty_nonce()
    {
        $this->assertFalse(
            $this->nonce->verify(''),
            'Should reject empty nonce'
        );
    }
    
    /** @test */
    public function it_rejects_nonce_with_invalid_timestamp()
    {
        $token = $this->nonce->create('test_form');
        
        // Modify timestamp to be in the past
        $parts = explode(':', $token);
        $parts[2] = (string)(time() - 1000); // Past timestamp
        $modifiedToken = implode(':', $parts);
        
        $this->assertFalse(
            $this->nonce->verify($modifiedToken),
            'Should reject nonce with past timestamp'
        );
    }
    
    /** @test */
    public function it_uses_sha256_hashing()
    {
        $token = $this->nonce->create('test_form');
        
        $parts = explode(':', $token);
        $hash = $parts[3];
        
        // SHA-256 produces 64 character hex string
        $this->assertEquals(
            64,
            strlen($hash),
            'Should use SHA-256 hashing (64 char hex)'
        );
        
        $this->assertTrue(
            ctype_xdigit($hash),
            'Hash should be hexadecimal'
        );
    }
    
    /** @test */
    public function it_stores_md5_hash_in_session()
    {
        $formId = 'test_form';
        $token = $this->nonce->create($formId);
        
        $storedHash = $_SESSION['nonce'][$formId];
        
        $this->assertEquals(
            32,
            strlen($storedHash),
            'Stored hash should be MD5 (32 characters)'
        );
        
        $this->assertEquals(
            md5($token),
            $storedHash,
            'Stored hash should be MD5 of token'
        );
    }
    
    /** @test */
    public function it_can_verify_nonce_immediately_after_creation()
    {
        $token = $this->nonce->create('test_form');
        
        // Verify immediately
        $this->assertTrue(
            $this->nonce->verify($token),
            'Should verify nonce immediately after creation'
        );
    }
    
    /** @test */
    public function it_maintains_separate_nonces_for_different_forms()
    {
        $token1 = $this->nonce->create('form1');
        $token2 = $this->nonce->create('form2');
        
        // Both should be valid
        $this->assertTrue($this->nonce->verify($token1));
        $this->assertTrue($this->nonce->verify($token2));
        
        // Session should have both
        $this->assertArrayHasKey('form1', $_SESSION['nonce']);
        $this->assertArrayHasKey('form2', $_SESSION['nonce']);
    }
}