<?php

/**
 * Email Forms API Endpoint
 * Provides REST API for email form processing and lead management
 * Follows existing CRM framework patterns
 */

// Set content type
header('Content-Type: application/json');

// Enable CORS if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include required files
require_once '../../config/system.php';
require_once '../../vendor/autoload.php';

// Initialize classes
$emailProcessor = new EmailFormProcessor();
$security = new Security();

/**
 * Send JSON response
 */
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

/**
 * Send error response
 */
function sendError($message, $statusCode = 400) {
    sendResponse(['error' => $message, 'status' => $statusCode], $statusCode);
}

/**
 * Validate API key
 */
function validateApiKey() {
    $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? null;
    
    if (!$apiKey) {
        sendError('API key required', 401);
    }
    
    // For now, use a simple API key validation
    // In production, store API keys in database with proper hashing
    $validApiKeys = [
        'waveguard_api_key_2024' => true,
        // Add more API keys as needed
    ];
    
    if (!isset($validApiKeys[$apiKey])) {
        sendError('Invalid API key', 401);
    }
}

/**
 * Parse request path
 */
function parseRequestPath() {
    $path = $_SERVER['PATH_INFO'] ?? $_SERVER['REQUEST_URI'] ?? '';
    $path = parse_url($path, PHP_URL_PATH);
    $path = trim($path, '/');
    
    // Remove script name if present
    $path = preg_replace('/^.*\/api\/email_forms\.php\/?/', '', $path);
    
    return explode('/', $path);
}

/**
 * Get request body as JSON
 */
function getRequestBody() {
    $input = file_get_contents('php://input');
    return json_decode($input, true);
}

// Validate API key for all requests
validateApiKey();

// Parse request
$method = $_SERVER['REQUEST_METHOD'];
$pathParts = parseRequestPath();
$endpoint = $pathParts[0] ?? '';

try {
    switch ($method) {
        case 'GET':
            handleGetRequest($endpoint, $pathParts);
            break;
            
        case 'POST':
            handlePostRequest($endpoint, $pathParts);
            break;
            
        case 'PUT':
            handlePutRequest($endpoint, $pathParts);
            break;
            
        case 'DELETE':
            handleDeleteRequest($endpoint, $pathParts);
            break;
            
        default:
            sendError('Method not allowed', 405);
    }
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    sendError('Internal server error: ' . $e->getMessage(), 500);
}

/**
 * Handle GET requests
 */
function handleGetRequest($endpoint, $pathParts) {
    global $emailProcessor;
    
    switch ($endpoint) {
        case 'status':
            // System status
            $stats = $emailProcessor->getProcessingStats();
            $accounts = $emailProcessor->getEmailAccounts();
            
            sendResponse([
                'api_status' => 'online',
                'timestamp' => date('c'),
                'version' => '1.0.0',
                'database_status' => 'connected',
                'email_accounts' => array_map(function($account) {
                    return [
                        'email_address' => $account['email_address'],
                        'form_type' => $account['form_type'],
                        'is_active' => (bool)$account['is_active'],
                        'last_check' => $account['last_check']
                    ];
                }, $accounts),
                'processing_stats' => $stats
            ]);
            break;
            
        case 'processing':
            // Recent processing records
            $limit = (int)($_GET['limit'] ?? 20);
            $records = $emailProcessor->getRecentProcessing($limit);
            
            sendResponse([
                'processing_records' => $records,
                'count' => count($records)
            ]);
            break;
            
        case 'accounts':
            // Email accounts configuration
            $accounts = $emailProcessor->getEmailAccounts();
            sendResponse(['accounts' => $accounts]);
            break;
            
        default:
            sendError('Endpoint not found', 404);
    }
}

/**
 * Handle POST requests
 */
function handlePostRequest($endpoint, $pathParts) {
    global $emailProcessor;
    
    switch ($endpoint) {
        case 'process':
            // Trigger manual email processing
            $results = $emailProcessor->processAllEmails();
            sendResponse([
                'message' => 'Email processing completed',
                'results' => $results
            ]);
            break;
            
        case 'forms':
            // Submit form data directly (bypass email)
            $formType = $pathParts[1] ?? 'contact';
            $formData = getRequestBody();
            
            if (!$formData) {
                sendError('Form data required');
            }
            
            // Validate form type
            if (!in_array($formType, ['estimate', 'ltr', 'contact'])) {
                sendError('Invalid form type');
            }
            
            // Process form data directly
            $leadId = $emailProcessor->createLeadFromFormData($formData, $formType);
            
            sendResponse([
                'message' => 'Form submitted successfully',
                'lead_id' => $leadId,
                'form_type' => $formType
            ], 201);
            break;
            
        case 'test':
            // Test email connection
            $accountId = $pathParts[1] ?? null;
            if (!$accountId) {
                sendError('Account ID required');
            }
            
            $result = $emailProcessor->testEmailConnection((int)$accountId);
            sendResponse([
                'message' => 'Connection test completed',
                'result' => $result
            ]);
            break;
            
        default:
            sendError('Endpoint not found', 404);
    }
}

/**
 * Handle PUT requests
 */
function handlePutRequest($endpoint, $pathParts) {
    switch ($endpoint) {
        case 'accounts':
            // Update email account configuration
            $accountId = $pathParts[1] ?? null;
            if (!$accountId) {
                sendError('Account ID required');
            }
            
            $updateData = getRequestBody();
            if (!$updateData) {
                sendError('Update data required');
            }
            
            // TODO: Implement account update functionality
            sendError('Account update not implemented yet', 501);
            break;
            
        default:
            sendError('Endpoint not found', 404);
    }
}

/**
 * Handle DELETE requests
 */
function handleDeleteRequest($endpoint, $pathParts) {
    switch ($endpoint) {
        case 'processing':
            // Delete processing record
            $recordId = $pathParts[1] ?? null;
            if (!$recordId) {
                sendError('Record ID required');
            }
            
            // TODO: Implement record deletion
            sendError('Record deletion not implemented yet', 501);
            break;
            
        default:
            sendError('Endpoint not found', 404);
    }
}