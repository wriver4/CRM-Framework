<?php
/**
 * Session Test for Calendar API
 * Check what session data is available
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Output session data for debugging
$session_data = [
    'session_status' => session_status(),
    'session_id' => session_id(),
    'session_data' => $_SESSION ?? [],
    'loggedin_check' => isset($_SESSION['loggedin']) ? $_SESSION['loggedin'] : 'not set',
    'user_id_check' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set'
];

echo json_encode($session_data, JSON_PRETTY_PRINT);
?>