<?php

namespace Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Base test case with helper methods for CRM testing
 */
abstract class TestCase extends PHPUnitTestCase
{
    /**
     * Make an HTTP request to the application
     */
    protected function makeHttpRequest(string $path): array
    {
        $baseUrl = $_ENV['BASE_URL'] ?? 'https://democrm.waveguardco.net';
        $url = rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
        
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 30,
                'ignore_errors' => true,
                'header' => [
                    'User-Agent: PHPUnit Test Suite',
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
                ]
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
     * Assert that response has expected status code
     */
    protected function assertResponseStatus(array $response, int $expectedStatus): void
    {
        $this->assertEquals(
            $expectedStatus, 
            $response['status_code'], 
            "Expected status {$expectedStatus}, got {$response['status_code']}"
        );
    }
    
    /**
     * Assert that response contains expected content
     */
    protected function assertResponseContains(array $response, string $needle): void
    {
        $this->assertStringContainsString(
            $needle, 
            $response['body'], 
            "Response should contain '{$needle}'"
        );
    }
    
    /**
     * Check if we're running in remote mode
     */
    protected function isRemoteMode(): bool
    {
        return ($_ENV['TESTING_MODE'] ?? 'local') === 'remote';
    }
    
    /**
     * Get remote server configuration
     */
    protected function getRemoteConfig(): array
    {
        return [
            'host' => '159.203.116.150',
            'port' => '222',
            'user' => 'root',
            'base_url' => 'https://democrm.waveguardco.net'
        ];
    }
    
    /**
     * Skip test if not in remote mode
     */
    protected function skipIfNotRemote(): void
    {
        if (!$this->isRemoteMode()) {
            $this->markTestSkipped('Test requires remote mode');
        }
    }
    
    /**
     * Skip test if in remote mode
     */
    protected function skipIfRemote(): void
    {
        if ($this->isRemoteMode()) {
            $this->markTestSkipped('Test not applicable in remote mode');
        }
    }
    
    /**
     * Get the base URL for the application
     */
    protected function getBaseUrl(): string
    {
        return $_ENV['BASE_URL'] ?? 'https://democrm.waveguardco.net';
    }
}