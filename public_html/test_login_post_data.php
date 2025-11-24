<?php
require_once dirname(__DIR__) . '/config/system.php';

echo "REQUEST_METHOD: " . $_SERVER["REQUEST_METHOD"] . PHP_EOL;
echo "POST DATA: " . json_encode($_POST) . PHP_EOL;
echo "POST login: " . (isset($_POST['login']) ? 'SET' : 'NOT SET') . PHP_EOL;
echo "POST username: " . (isset($_POST['username']) ? 'SET: ' . $_POST['username'] : 'NOT SET') . PHP_EOL;
echo "POST password: " . (isset($_POST['password']) ? 'SET (length: ' . strlen($_POST['password']) . ')' : 'NOT SET') . PHP_EOL;
?>
