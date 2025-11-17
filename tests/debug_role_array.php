<?php
require '/home/democrm/config/system.php';
require LANG . '/en.php';

$roles = new Roles();
$role_array = $roles->get_role_array($lang);

echo "Role array keys and values:\n";
echo "═════════════════════════════════════════\n";
foreach ($role_array as $key => $value) {
    $type = gettype($key);
    echo "Key: $key (type: $type) => $value\n";
}

echo "\n\nArray details:\n";
echo "Total keys: " . count($role_array) . "\n";
echo "First 5 keys:\n";
$first_five = array_slice($role_array, 0, 5, true);
foreach ($first_five as $k => $v) {
    echo "  $k => $v\n";
}

echo "\n\nChecking exclusion logic:\n";
$exclude_roles = ['1', '2'];
foreach (array_slice($role_array, 0, 5, true) as $key => $value) {
    $excluded = in_array($key, $exclude_roles, true);
    echo "Key '$key': excluded = " . ($excluded ? "YES" : "NO") . "\n";
}
?>
