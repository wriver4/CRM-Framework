<?php
/**
 * Test the delete_note.php AJAX endpoint
 * This script tests the JSON response format
 */

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

// Check if user is logged in
$not->loggedin();

echo "<h1>Testing Delete Note AJAX Endpoint</h1>";

// Test with invalid method (GET)
echo "<h2>Test 1: Invalid Method (GET)</h2>";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, URL . '/admin/leads/delete_note.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> {$http_code}</p>";
echo "<p><strong>Response:</strong> <code>" . htmlspecialchars($response) . "</code></p>";

$json_data = json_decode($response, true);
if ($json_data) {
    echo "<p>✅ Valid JSON response</p>";
    echo "<pre>" . print_r($json_data, true) . "</pre>";
} else {
    echo "<p>❌ Invalid JSON response</p>";
}

// Test with missing parameters
echo "<hr><h2>Test 2: Missing Parameters</h2>";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, URL . '/admin/leads/delete_note.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, []);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> {$http_code}</p>";
echo "<p><strong>Response:</strong> <code>" . htmlspecialchars($response) . "</code></p>";

$json_data = json_decode($response, true);
if ($json_data) {
    echo "<p>✅ Valid JSON response</p>";
    echo "<pre>" . print_r($json_data, true) . "</pre>";
} else {
    echo "<p>❌ Invalid JSON response</p>";
}

// Test with invalid note ID
echo "<hr><h2>Test 3: Invalid Note ID</h2>";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, URL . '/admin/leads/delete_note.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'note_id' => 999999,
    'lead_id' => 1
]);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>HTTP Code:</strong> {$http_code}</p>";
echo "<p><strong>Response:</strong> <code>" . htmlspecialchars($response) . "</code></p>";

$json_data = json_decode($response, true);
if ($json_data) {
    echo "<p>✅ Valid JSON response</p>";
    echo "<pre>" . print_r($json_data, true) . "</pre>";
} else {
    echo "<p>❌ Invalid JSON response</p>";
    echo "<p><strong>JSON Error:</strong> " . json_last_error_msg() . "</p>";
}

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p>The AJAX endpoint should return clean JSON responses for all test cases.</p>";
echo "<p>If any test shows 'Invalid JSON response', there may be extra output being generated.</p>";

echo "<p><a href='public_html/admin/leads/list.php'>Go to Admin Leads</a></p>";
?>