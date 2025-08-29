<?php
// Ultra simple test - no includes, no classes
header('Content-Type: application/json');
echo json_encode([
    'status' => 'working', 
    'time' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'],
    'location' => '/admin/leads/'
]);
?>