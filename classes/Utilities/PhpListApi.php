<?php

/**
 * PhpListApi Utility Class
 * 
 * Handles communication with phpList REST API
 * Provides methods for subscriber management and list operations
 */
class PhpListApi
{
    private $apiUrl;
    private $username;
    private $password;
    private $timeout;
    private $debugMode;
    
    public function __construct($config = [])
    {
        $this->apiUrl = rtrim($config['phplist_api_url'] ?? '', '/');
        $this->username = $config['phplist_api_username'] ?? '';
        $this->password = $config['phplist_api_password'] ?? '';
        $this->timeout = (int)($config['api_timeout_seconds'] ?? 30);
        $this->debugMode = ($config['debug_mode'] ?? '0') === '1';
    }
    
    /**
     * Add subscriber to phpList
     * 
     * @param array $subscriberData Subscriber information
     * @return array API response with success status and data
     */
    public function addSubscriber($subscriberData)
    {
        $startTime = microtime(true);
        
        try {
            // Prepare subscriber data for phpList API
            $apiData = [
                'email' => $subscriberData['email'],
                'confirmed' => 1, // Auto-confirm since they opted in
                'htmlemail' => 1, // Enable HTML emails
            ];
            
            // Add name fields if available
            if (!empty($subscriberData['first_name'])) {
                $apiData['attribute1'] = $subscriberData['first_name']; // Assuming attribute1 is first name
            }
            
            if (!empty($subscriberData['last_name'])) {
                $apiData['attribute2'] = $subscriberData['last_name']; // Assuming attribute2 is last name
            }
            
            // Add custom attributes from segmentation data
            if (!empty($subscriberData['segmentation_data'])) {
                $segData = json_decode($subscriberData['segmentation_data'], true);
                if ($segData) {
                    // Map segmentation data to phpList attributes
                    if (!empty($segData['state'])) {
                        $apiData['attribute3'] = $segData['state']; // State
                    }
                    if (!empty($segData['city'])) {
                        $apiData['attribute4'] = $segData['city']; // City
                    }
                    if (!empty($segData['lead_source'])) {
                        $apiData['attribute5'] = $segData['lead_source']; // Lead source
                    }
                    if (!empty($segData['business_name'])) {
                        $apiData['attribute6'] = $segData['business_name']; // Business name
                    }
                }
            }
            
            // Add to lists
            if (!empty($subscriberData['phplist_lists'])) {
                $lists = json_decode($subscriberData['phplist_lists'], true);
                if (is_array($lists)) {
                    foreach ($lists as $listId) {
                        $apiData["list[$listId]"] = 'signup';
                    }
                }
            }
            
            $response = $this->makeApiRequest('POST', '/subscribers', $apiData);
            
            $processingTime = round((microtime(true) - $startTime) * 1000);
            
            if ($response['success']) {
                return [
                    'success' => true,
                    'subscriber_id' => $response['data']['id'] ?? null,
                    'message' => 'Subscriber added successfully',
                    'processing_time' => $processingTime,
                    'api_response' => $response['data']
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response['error'] ?? 'Unknown error',
                    'processing_time' => $processingTime,
                    'api_response' => $response['data'] ?? null
                ];
            }
            
        } catch (Exception $e) {
            $processingTime = round((microtime(true) - $startTime) * 1000);
            
            return [
                'success' => false,
                'error' => 'API Exception: ' . $e->getMessage(),
                'processing_time' => $processingTime
            ];
        }
    }
    
    /**
     * Update existing subscriber
     */
    public function updateSubscriber($phplistSubscriberId, $subscriberData)
    {
        $startTime = microtime(true);
        
        try {
            $apiData = [
                'email' => $subscriberData['email'],
                'confirmed' => 1,
                'htmlemail' => 1,
            ];
            
            // Add updated attributes
            if (!empty($subscriberData['first_name'])) {
                $apiData['attribute1'] = $subscriberData['first_name'];
            }
            
            if (!empty($subscriberData['last_name'])) {
                $apiData['attribute2'] = $subscriberData['last_name'];
            }
            
            $response = $this->makeApiRequest('PUT', "/subscribers/$phplistSubscriberId", $apiData);
            
            $processingTime = round((microtime(true) - $startTime) * 1000);
            
            return [
                'success' => $response['success'],
                'message' => $response['success'] ? 'Subscriber updated successfully' : ($response['error'] ?? 'Update failed'),
                'processing_time' => $processingTime,
                'api_response' => $response['data'] ?? null
            ];
            
        } catch (Exception $e) {
            $processingTime = round((microtime(true) - $startTime) * 1000);
            
            return [
                'success' => false,
                'error' => 'API Exception: ' . $e->getMessage(),
                'processing_time' => $processingTime
            ];
        }
    }
    
    /**
     * Unsubscribe subscriber
     */
    public function unsubscribeSubscriber($phplistSubscriberId)
    {
        try {
            $response = $this->makeApiRequest('DELETE', "/subscribers/$phplistSubscriberId");
            
            return [
                'success' => $response['success'],
                'message' => $response['success'] ? 'Subscriber unsubscribed successfully' : ($response['error'] ?? 'Unsubscribe failed'),
                'api_response' => $response['data'] ?? null
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'API Exception: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get subscriber information
     */
    public function getSubscriber($phplistSubscriberId)
    {
        try {
            $response = $this->makeApiRequest('GET', "/subscribers/$phplistSubscriberId");
            
            return [
                'success' => $response['success'],
                'data' => $response['data'] ?? null,
                'error' => $response['error'] ?? null
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'API Exception: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get available lists
     */
    public function getLists()
    {
        try {
            $response = $this->makeApiRequest('GET', '/lists');
            
            return [
                'success' => $response['success'],
                'data' => $response['data'] ?? [],
                'error' => $response['error'] ?? null
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'API Exception: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Test API connection
     */
    public function testConnection()
    {
        try {
            $response = $this->makeApiRequest('GET', '/lists');
            
            return [
                'success' => $response['success'],
                'message' => $response['success'] ? 'Connection successful' : 'Connection failed',
                'error' => $response['error'] ?? null
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection failed',
                'error' => 'Exception: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Make API request to phpList
     */
    private function makeApiRequest($method, $endpoint, $data = null)
    {
        if (empty($this->apiUrl) || empty($this->username) || empty($this->password)) {
            throw new Exception('phpList API credentials not configured');
        }
        
        $url = $this->apiUrl . '/api' . $endpoint;
        
        $ch = curl_init();
        
        // Basic cURL options
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false, // Adjust based on your SSL setup
            CURLOPT_USERAGENT => 'DemoCRM phpList Integration/1.0',
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => $this->username . ':' . $this->password,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json'
            ]
        ]);
        
        // Set method-specific options
        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
                
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
                
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
                
            case 'GET':
            default:
                // GET is default, no additional options needed
                break;
        }
        
        if ($this->debugMode) {
            error_log("phpList API Request: $method $url");
            if ($data) {
                error_log("phpList API Data: " . json_encode($data));
            }
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        
        curl_close($ch);
        
        if ($curlError) {
            throw new Exception("cURL Error: $curlError");
        }
        
        if ($this->debugMode) {
            error_log("phpList API Response Code: $httpCode");
            error_log("phpList API Response: $response");
        }
        
        $decodedResponse = json_decode($response, true);
        
        // Handle different HTTP response codes
        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'data' => $decodedResponse,
                'http_code' => $httpCode
            ];
        } else {
            return [
                'success' => false,
                'error' => $decodedResponse['message'] ?? "HTTP Error $httpCode",
                'data' => $decodedResponse,
                'http_code' => $httpCode
            ];
        }
    }
}