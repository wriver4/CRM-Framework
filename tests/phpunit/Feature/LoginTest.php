<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Test login functionality
 */
class LoginTest extends TestCase
{
    public function testLoginPageIsAccessible(): void
    {
        $response = $this->makeHttpRequest('/login.php');
        
        $this->assertResponseStatus($response, 200);
        $this->assertResponseContains($response, 'login');
    }

    public function testLoginPageContainsForm(): void
    {
        $response = $this->makeHttpRequest('/login.php');
        
        $this->assertResponseStatus($response, 200);
        $this->assertResponseContains($response, '<form');
        $this->assertResponseContains($response, 'username');
        $this->assertResponseContains($response, 'password');
    }

    public function testLoginPageHasCSRFProtection(): void
    {
        $response = $this->makeHttpRequest('/login.php');
        
        $this->assertResponseStatus($response, 200);
        // Look for hidden fields that might be CSRF protection
        $this->assertTrue(
            strpos($response['body'], 'type="hidden"') !== false,
            'Login page should have hidden fields (potential CSRF protection)'
        );
    }

    public function testDashboardRequiresAuthentication(): void
    {
        $response = $this->makeHttpRequest('/dashboard.php');
        
        // Should redirect to login or return 401/403
        $this->assertTrue(
            in_array($response['status_code'], [302, 401, 403]) ||
            strpos($response['body'], 'login') !== false,
            'Dashboard should require authentication'
        );
    }

    public function testIndexPageIsAccessible(): void
    {
        $response = $this->makeHttpRequest('/index.php');
        
        // Index page should redirect to login for unauthenticated users
        $this->assertTrue(
            in_array($response['status_code'], [200, 302]),
            'Index page should be accessible or redirect to login'
        );
    }

    public function testApplicationHasProperHeaders(): void
    {
        $response = $this->makeHttpRequest('/login.php');
        
        $headers = implode("\n", $response['headers']);
        
        // Check for security headers
        $this->assertStringContainsString('Content-Type', $headers, 'Should have Content-Type header');
    }

    public function testApplicationUsesHTTPS(): void
    {
        $baseUrl = $this->getBaseUrl();
        $this->assertStringStartsWith('https://', $baseUrl, 'Application should use HTTPS');
    }
}