<?php
/**
 * Debug path resolution
 */

echo "Path debugging:\n";
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "dirname(DOCUMENT_ROOT): " . dirname($_SERVER['DOCUMENT_ROOT']) . "\n";
echo "Expected config path: " . dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php' . "\n";
echo "Config file exists: " . (file_exists(dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php') ? 'YES' : 'NO') . "\n";

// Check if the actual config file exists
$config_path = '/run/user/1000/gvfs/sftp:host=159.203.116.150,port=222/home/democrm/config/system.php';
echo "Actual config path: " . $config_path . "\n";
echo "Actual config exists: " . (file_exists($config_path) ? 'YES' : 'NO') . "\n";