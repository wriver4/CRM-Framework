<?php
require '/home/democrm/config/system.php';
require LANG . '/en.php';

$roles = new Roles();
ob_start();
$roles->select_role($lang);
$output = ob_get_clean();

echo "System role 1 in dropdown: " . (strpos($output, 'value="1"') !== false ? "YES ❌" : "NO ✅") . "\n";
echo "System role 2 in dropdown: " . (strpos($output, 'value="2"') !== false ? "YES ❌" : "NO ✅") . "\n";
echo "Sales Manager (30) in dropdown: " . (strpos($output, 'value="30"') !== false ? "YES ✅" : "NO ❌") . "\n";
echo "Client Standard (160) in dropdown: " . (strpos($output, 'value="160"') !== false ? "YES ✅" : "NO ❌") . "\n";
echo "\nTotal option tags: " . substr_count($output, '<option') . "\n";
echo "Expected: 30 (32 roles - 2 system roles)\n";
?>
