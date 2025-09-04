<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

/**
 * IP Geolocation Helper Functions
 * 
 * Uses free geolocation services to determine country from IP address.
 * Services used:
 * - ip-api.com (free tier: 1000 requests/month)
 * - freeiplookupapi.com (free service)
 */

function get_client_ip()
{
    // Check for IP from shared internet
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP);
        if ($ip !== false) return $ip;
    }
    
    // Check for IP passed from proxy
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // Can contain multiple IPs, get the first one
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = filter_var(trim($ips[0]), FILTER_VALIDATE_IP);
        if ($ip !== false) return $ip;
    }
    
    // Check for IP from remote address
    if (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
        if ($ip !== false) return $ip;
    }
    
    return '';
}
function country_by_ip()
{
    $ip = get_client_ip();
    if ($ip == '' || $ip == '127.0.0.1' || $ip == '::1') {
        return 'Unknown';
    }
    
    // List of free geolocation services to try (in order of preference)
    $services = [
        [
            'url' => "http://ip-api.com/json/" . $ip . "?fields=countryCode",
            'parser' => function($data) {
                $source = json_decode($data);
                return ($source && isset($source->countryCode) && $source->countryCode != null) 
                    ? $source->countryCode : null;
            }
        ],
        [
            'url' => "https://freeiplookupapi.com/json/" . $ip,
            'parser' => function($data) {
                $source = json_decode($data);
                return ($source && isset($source->countryCode) && $source->countryCode != null) 
                    ? $source->countryCode : null;
            }
        ]
    ];
    
    // Create a context with timeout and user agent
    $context = stream_context_create([
        'http' => [
            'timeout' => 3, // 3 second timeout per service
            'user_agent' => 'Mozilla/5.0 (compatible; CRM-App/1.0)',
            'ignore_errors' => true
        ]
    ]);
    
    // Try each service in order
    foreach ($services as $service) {
        try {
            $response = @file_get_contents($service['url'], false, $context);
            
            if ($response !== false) {
                $countryCode = $service['parser']($response);
                if ($countryCode !== null) {
                    return $countryCode;
                }
            }
        } catch (Exception $e) {
            // Continue to next service
            continue;
        }
    }
    
    // If all services fail, return 'Unknown'
    return 'Unknown';
}


/**
 * 
 *   session.sid_length is the number of characters in the ID
 *   session.sid_bits_per_character controls the set of characters used. 
 *   From the manual: The possible values are '4' (0-9, a-f), '5' (0-9, a-v), and '6' (0-9, a-z, A-Z, '-', ','). 
 */

function isValidSessionId(string $sessionId): bool
{
    if (empty($sessionId)) {
        return false;
    }

    $sidLength = ini_get('session.sid_length');
    $bitsPerCharacter = ini_get('session.sid_bits_per_character');
    $characterClass = [
        6 => '0-9a-zA-z,-',
        5 => '0-9a-z',
        4 => '0-9a-f'
    ];

    if (array_key_exists($bitsPerCharacter, $characterClass)) {
        $pattern = '/^[' . $characterClass . ']{' . $sidLength . '}$/';
        return preg_match($pattern, $sessionId) === 1;
    }
    throw new \RuntimeException('Unknown value in session.sid_bits_per_character.');
}
