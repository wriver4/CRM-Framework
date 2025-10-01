<?php
/**
 * Leads API Working Copy (from Calendar)
 * 
 * Provides backward compatibility for existing API calls
 * Redirects to new framework-compliant structure
 * 
 * @author CRM Framework
 * @version 1.0
 * @deprecated Use specific endpoint files (get.php, post.php, etc.) instead
 */

// Get the request method and redirect to appropriate file
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

// Check authentication for API endpoint
if (!Sessions::isLoggedIn()) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$query_string = $_SERVER['QUERY_STRING'] ?? '';

switch ($method) {
    case 'GET':
        // Include the get.php file directly to maintain data and headers
        include 'get.php';
        break;
        
    case 'POST':
        // For POST requests, we need to preserve the body data
        // Include the post.php file directly to maintain data
        include 'post.php';
        break;
        
    case 'PUT':
        // Include the put.php file directly to maintain data
        include 'put.php';
        break;
        
    case 'DELETE':
        // Include the delete.php file directly to maintain data
        include 'delete.php';
        break;
        
    case 'OPTIONS':
        // Handle preflight requests
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        http_response_code(200);
        break;
        
    default:
        header('Content-Type: application/json');
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}