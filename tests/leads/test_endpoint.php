<?php
/**
 * Simple test endpoint to verify path and basic functionality
 */

header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'message' => 'Test endpoint is working',
    'method' => $_SERVER['REQUEST_METHOD'],
    'timestamp' => date('Y-m-d H:i:s')
]);
?>