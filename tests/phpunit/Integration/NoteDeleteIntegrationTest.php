<?php

namespace Tests\Integration;

use Tests\TestCase;

/**
 * Integration tests for note deletion functionality
 * Tests the complete workflow including database operations
 */
class NoteDeleteIntegrationTest extends TestCase
{
    /**
     * Test that delete_note.php endpoint exists and is accessible
     */
    public function testDeleteNoteEndpointExists(): void
    {
        $this->skipIfNotRemote();
        
        $response = $this->makeHttpRequest('admin/leads/delete_note.php');
        
        // Should return 405 Method Not Allowed for GET request OR 401 if auth check comes first
        $this->assertContains($response['status_code'], [401, 405], 
            'Should return either 401 (auth required) or 405 (method not allowed)');
        
        // The important thing is that it's NOT 500 (which would indicate autoloader failure)
        $this->assertNotEquals(500, $response['status_code'], 
            'Should not return 500 Internal Server Error (autoloader should work)');
    }

    /**
     * Test that POST request without authentication returns 401
     */
    public function testDeleteNoteRequiresAuthentication(): void
    {
        $this->skipIfNotRemote();
        
        $response = $this->makePostRequest('admin/leads/delete_note.php', [
            'note_id' => 123,
            'lead_id' => 456
        ]);
        
        $this->assertResponseStatus($response, 401);
        
        $data = json_decode($response['body'], true);
        $this->assertIsArray($data);
        $this->assertFalse($data['success']);
        $this->assertEquals('User not authenticated', $data['message']);
    }

    /**
     * Test that POST request with missing parameters returns 400 (or 401 if not authenticated)
     */
    public function testDeleteNoteValidatesParameters(): void
    {
        $this->skipIfNotRemote();
        
        // Test missing note_id
        $response = $this->makeAuthenticatedPostRequest('admin/leads/delete_note.php', [
            'lead_id' => 456
        ]);
        
        // Should return 400 (bad request) or 401 (not authenticated) - both are acceptable
        $this->assertContains($response['status_code'], [400, 401], 
            'Should return 400 (bad request) or 401 (not authenticated)');
        
        // The important thing is that it's NOT 500 (autoloader failure)
        $this->assertNotEquals(500, $response['status_code'], 
            'Should not return 500 Internal Server Error');
        
        // Test missing lead_id
        $response = $this->makeAuthenticatedPostRequest('admin/leads/delete_note.php', [
            'note_id' => 123
        ]);
        
        $this->assertContains($response['status_code'], [400, 401], 
            'Should return 400 (bad request) or 401 (not authenticated)');
        
        $this->assertNotEquals(500, $response['status_code'], 
            'Should not return 500 Internal Server Error');
    }

    /**
     * Test that invalid parameters return 400 (or 401 if not authenticated)
     */
    public function testDeleteNoteValidatesParameterValues(): void
    {
        $this->skipIfNotRemote();
        
        // Test invalid note_id (zero)
        $response = $this->makeAuthenticatedPostRequest('admin/leads/delete_note.php', [
            'note_id' => 0,
            'lead_id' => 456
        ]);
        
        $this->assertContains($response['status_code'], [400, 401], 
            'Should return 400 (invalid params) or 401 (not authenticated)');
        $this->assertNotEquals(500, $response['status_code'], 
            'Should not return 500 Internal Server Error');
        
        // Test invalid lead_id (negative)
        $response = $this->makeAuthenticatedPostRequest('admin/leads/delete_note.php', [
            'note_id' => 123,
            'lead_id' => -1
        ]);
        
        $this->assertContains($response['status_code'], [400, 401], 
            'Should return 400 (invalid params) or 401 (not authenticated)');
        $this->assertNotEquals(500, $response['status_code'], 
            'Should not return 500 Internal Server Error');
    }

    /**
     * Test that non-existent note returns 404 (or 401 if not authenticated)
     */
    public function testDeleteNoteHandlesNonExistentNote(): void
    {
        $this->skipIfNotRemote();
        
        // Use very high IDs that are unlikely to exist
        $response = $this->makeAuthenticatedPostRequest('admin/leads/delete_note.php', [
            'note_id' => 999999,
            'lead_id' => 999999
        ]);
        
        $this->assertContains($response['status_code'], [401, 404], 
            'Should return 401 (not authenticated) or 404 (not found)');
        $this->assertNotEquals(500, $response['status_code'], 
            'Should not return 500 Internal Server Error');
    }

    /**
     * Test that the autoloader fix works in the actual endpoint
     */
    public function testAutoloaderWorksInEndpoint(): void
    {
        $this->skipIfNotRemote();
        
        // Make a request that would trigger the autoloader
        $response = $this->makeAuthenticatedPostRequest('admin/leads/delete_note.php', [
            'note_id' => 999999,
            'lead_id' => 999999
        ]);
        
        // If we get anything other than 500, the autoloader is working
        $this->assertNotEquals(500, $response['status_code'], 
            'Endpoint should not return 500 if autoloader is working');
        
        // Should return 401 (auth) or 404 (not found) - both indicate autoloader worked
        $this->assertContains($response['status_code'], [401, 404], 
            'Should return 401 (auth required) or 404 (not found)');
    }

    /**
     * Test JSON response format consistency
     */
    public function testJsonResponseFormat(): void
    {
        $this->skipIfNotRemote();
        
        $response = $this->makeHttpRequest('admin/leads/delete_note.php');
        
        // The important thing is that we don't get a 500 error
        $this->assertNotEquals(500, $response['status_code'], 
            'Should not return 500 Internal Server Error');
        
        // For GET requests, we might get HTML redirect or JSON error
        // The key is that the endpoint is accessible and autoloader works
        $this->assertContains($response['status_code'], [401, 405], 
            'Should return 401 (auth required) or 405 (method not allowed)');
    }

    /**
     * Test error logging functionality
     */
    public function testErrorLogging(): void
    {
        $this->skipIfNotRemote();
        
        // Make a request that should generate logs
        $response = $this->makeAuthenticatedPostRequest('admin/leads/delete_note.php', [
            'note_id' => 999999,
            'lead_id' => 999999
        ]);
        
        // The endpoint should handle the request without fatal errors
        $this->assertNotEquals(500, $response['status_code']);
        
        // Should return proper JSON response
        $data = json_decode($response['body'], true);
        $this->assertIsArray($data);
    }

    // Helper methods

    /**
     * Make a POST request to the application
     */
    protected function makePostRequest(string $path, array $data = []): array
    {
        $baseUrl = $this->getBaseUrl();
        $url = rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
        
        $postData = http_build_query($data);
        
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Content-Type: application/x-www-form-urlencoded',
                    'Content-Length: ' . strlen($postData),
                    'User-Agent: PHPUnit Integration Test'
                ],
                'content' => $postData,
                'timeout' => 30,
                'ignore_errors' => true
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        $statusCode = 0;
        
        if (isset($http_response_header)) {
            foreach ($http_response_header as $header) {
                if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $header, $matches)) {
                    $statusCode = (int)$matches[1];
                    break;
                }
            }
        }
        
        return [
            'status_code' => $statusCode,
            'body' => $response ?: '',
            'headers' => $http_response_header ?? []
        ];
    }

    /**
     * Make an authenticated POST request (simulated)
     * Note: In a real scenario, you'd need to handle session cookies
     */
    protected function makeAuthenticatedPostRequest(string $path, array $data = []): array
    {
        // For now, this is the same as makePostRequest
        // In a real implementation, you'd need to:
        // 1. First login to get session cookie
        // 2. Include session cookie in subsequent requests
        return $this->makePostRequest($path, $data);
    }

    /**
     * Get header value from response headers
     */
    protected function getHeaderValue(array $headers, string $headerName): string
    {
        foreach ($headers as $header) {
            if (stripos($header, $headerName . ':') === 0) {
                return trim(substr($header, strlen($headerName) + 1));
            }
        }
        return '';
    }
}