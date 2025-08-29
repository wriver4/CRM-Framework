<?php
// Minimal delete test - no dependencies
header('Content-Type: application/json');

// Log to a simple file
$log_path = dirname($_SERVER['DOCUMENT_ROOT']) . '/logs/minimal_test.log';
file_put_contents($log_path, 
    "Request: " . date('Y-m-d H:i:s') . " Method: " . $_SERVER['REQUEST_METHOD'] . 
    " POST: " . print_r($_POST, true) . 
    " GET: " . print_r($_GET, true) . 
    " INPUT: " . file_get_contents('php://input') . "\n", 
    FILE_APPEND);

// Get data from both POST and raw input
$post_data = $_POST;
$raw_input = file_get_contents('php://input');
$get_data = $_GET;

echo json_encode([
    'success' => true,
    'message' => 'Minimal delete endpoint working',
    'method' => $_SERVER['REQUEST_METHOD'],
    'post_data' => $post_data,
    'get_data' => $get_data,
    'raw_input' => $raw_input,
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set'
]);
?>