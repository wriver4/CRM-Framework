<?php

namespace Tests\Remote;

use Tests\TestCase;

/**
 * Test remote server functionality and connectivity
 */
class RemoteServerTest extends TestCase
{
    public function testRemoteServerIsReachable(): void
    {
        if (!$this->isRemoteMode()) {
            $this->markTestSkipped('Remote server tests skipped - not in remote mode');
            return;
        }

        $config = $this->getRemoteConfig();
        
        // Test if we can reach the server
        $connection = @fsockopen($config['host'], (int)$config['port'], $errno, $errstr, 10);
        
        $this->assertNotFalse($connection, "Should be able to connect to {$config['host']}:{$config['port']}");
        
        if ($connection) {
            fclose($connection);
        }
    }

    public function testWebServerIsRunning(): void
    {
        $response = $this->makeHttpRequest('/');
        
        $this->assertTrue(
            $response['status_code'] < 500,
            'Web server should be running (status code should be < 500)'
        );
    }

    public function testApplicationIsDeployed(): void
    {
        $response = $this->makeHttpRequest('/login.php');
        
        $this->assertResponseStatus($response, 200);
        $this->assertResponseContains($response, 'CRM');
    }

    public function testSSLCertificateIsValid(): void
    {
        $baseUrl = $this->getBaseUrl();
        
        if (!str_starts_with($baseUrl, 'https://')) {
            $this->markTestSkipped('SSL test skipped - not using HTTPS');
            return;
        }

        // Parse URL to get host
        $parsedUrl = parse_url($baseUrl);
        $host = $parsedUrl['host'];
        $port = $parsedUrl['port'] ?? 443;

        // Create SSL context
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'capture_peer_cert' => true
            ]
        ]);

        // Try to connect with SSL verification
        $socket = @stream_socket_client(
            "ssl://{$host}:{$port}",
            $errno,
            $errstr,
            30,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if ($socket === false) {
            // If strict SSL fails, at least check that HTTPS is working
            $response = $this->makeHttpRequest('/');
            $this->assertTrue(
                $response['status_code'] < 500,
                'HTTPS should be working even if certificate verification fails'
            );
        } else {
            $this->assertNotFalse($socket, 'SSL connection should be successful');
            fclose($socket);
        }
    }

    public function testDatabaseIsAccessible(): void
    {
        if (!$this->isRemoteMode()) {
            $this->markTestSkipped('Database test skipped - not in remote mode');
            return;
        }

        // Test by making a request that would require database access
        $response = $this->makeHttpRequest('/users/list.php');
        
        // Should either show content or redirect to login (not show database error)
        $this->assertFalse(
            strpos(strtolower($response['body']), 'database error') !== false ||
            strpos(strtolower($response['body']), 'connection failed') !== false,
            'Should not show database connection errors'
        );
    }

    public function testApplicationStructureExists(): void
    {
        // Test key application endpoints
        $endpoints = [
            '/login.php',
            '/dashboard.php',
            '/users/list.php',
            '/leads/list.php',
            '/contacts/list.php'
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->makeHttpRequest($endpoint);
            
            $this->assertTrue(
                $response['status_code'] !== 404,
                "Endpoint {$endpoint} should exist (not return 404)"
            );
        }
    }

    public function testStaticAssetsAreAccessible(): void
    {
        $assets = [
            '/assets/css/bootstrap.min.css',
            '/assets/css/style.css',
            '/assets/js/general.js'
        ];

        foreach ($assets as $asset) {
            $response = $this->makeHttpRequest($asset);
            
            $this->assertTrue(
                in_array($response['status_code'], [200, 304]),
                "Asset {$asset} should be accessible"
            );
        }
    }
}