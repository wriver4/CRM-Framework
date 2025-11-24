<?php
require_once dirname(__DIR__) . '/config/system.php';

// Log every login attempt
$log_entry = date('Y-m-d H:i:s') . ' - ';
$log_entry .= 'REQUEST_METHOD: ' . $_SERVER['REQUEST_METHOD'] . ' | ';
$log_entry .= 'LOGIN_FIELD: ' . (isset($_POST['login']) ? $_POST['login'] : 'MISSING') . ' | ';
$log_entry .= 'USERNAME: ' . (isset($_POST['username']) ? $_POST['username'] : 'MISSING') . ' | ';
$log_entry .= 'PASSWORD_LENGTH: ' . (isset($_POST['password']) ? strlen($_POST['password']) : 'MISSING') . PHP_EOL;

file_put_contents('/home/democrm/logs/login_attempts.log', $log_entry, FILE_APPEND);

echo "Logged\n";
?>
