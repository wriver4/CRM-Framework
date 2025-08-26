<?php
/**
 * Simple test endpoint in root directory
 */

header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'message' => 'Root test endpoint is working',
    'method' => $_SERVER['REQUEST_METHOD'],
    'timestamp' => date('Y-m-d H:i:s'),
    'path' => __FILE__
]);
?>