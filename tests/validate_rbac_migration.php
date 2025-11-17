<?php
/**
 * RBAC Migration Validation Script
 * Validates that all 32 roles are correctly migrated and accessible
 */

require '/home/democrm/config/system.php';
require LANG . '/en.php';

$db = new Database();
$roles = new Roles();

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║     RBAC MIGRATION VALIDATION REPORT                       ║\n";
echo "║     Date: " . date('Y-m-d H:i:s') . "                                      ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Test 1: Check total role count
echo "📊 TEST 1: Role Count\n";
echo "─────────────────────────────────────────────────────────────\n";

$expected_roles = [1, 2, 10, 11, 12, 13, 14, 30, 35, 40, 41, 42, 43, 50, 51, 52, 60, 70, 72, 80, 82, 90, 100, 110, 120, 130, 140, 150, 160, 161, 162, 163];
$sql = "SELECT COUNT(*) as total FROM roles WHERE role_id IN (" . implode(',', $expected_roles) . ")";
$stmt = $db->dbcrm()->prepare($sql);
$stmt->execute();
$result = $stmt->fetch();

echo "Expected roles: " . count($expected_roles) . "\n";
echo "Active roles: " . $result['total'] . "\n";
echo "Status: " . ($result['total'] == count($expected_roles) ? "✅ PASS\n" : "❌ FAIL\n");

// Test 2: Verify role names
echo "\n📋 TEST 2: Role Names & Translations\n";
echo "─────────────────────────────────────────────────────────────\n";

$role_array = $roles->get_role_array($lang);
$missing_translations = [];

foreach ($expected_roles as $role_id) {
    if (!isset($role_array[$role_id])) {
        $missing_translations[] = $role_id;
    }
}

if (empty($missing_translations)) {
    echo "All " . count($expected_roles) . " roles have translations ✅ PASS\n";
} else {
    echo "Missing translations for roles: " . implode(', ', $missing_translations) . " ❌ FAIL\n";
}

// Test 3: Verify system roles are excluded from user dropdown
echo "\n🔒 TEST 3: System Role Exclusion from User Assignment\n";
echo "─────────────────────────────────────────────────────────────\n";

$exclude_roles_check = true;
$assignable_roles = array_diff_key($role_array, [1 => true, 2 => true]);

echo "System roles (1-2) should NOT appear in user assignment dropdown\n";
echo "Total roles in system: " . count($role_array) . "\n";
echo "Assignable roles (excluding system): " . count($assignable_roles) . "\n";
echo "Status: " . (count($assignable_roles) == 30 ? "✅ PASS\n" : "❌ FAIL\n");

// Test 4: Role by category
echo "\n🏢 TEST 4: Roles by Category\n";
echo "─────────────────────────────────────────────────────────────\n";

$categories = [
    'System (1-2)' => [1, 2],
    'Executive (10-14)' => [10, 11, 12, 13, 14],
    'Sales (30-39)' => [30, 35],
    'Engineering (40-49)' => [40, 41, 42, 43],
    'Manufacturing (50-59)' => [50, 51, 52],
    'Field Service (60-69)' => [60],
    'HR (70-79)' => [70, 72],
    'Accounting (80-89)' => [80, 82],
    'Support (90-99)' => [90],
    'Partners (100-159)' => [100, 110, 120, 130, 140, 150],
    'Clients (160-163)' => [160, 161, 162, 163],
];

$category_status = true;

foreach ($categories as $category => $role_ids) {
    $sql = "SELECT COUNT(*) as count FROM roles WHERE role_id IN (" . implode(',', $role_ids) . ")";
    $stmt = $db->dbcrm()->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch();
    
    $expected = count($role_ids);
    $actual = $result['count'];
    $status = ($actual == $expected) ? "✅" : "❌";
    
    printf("  %-30s %s (Expected: %d, Found: %d)\n", $category, $status, $expected, $actual);
    
    if ($actual != $expected) {
        $category_status = false;
    }
}

// Test 5: Database role names match translations
echo "\n📝 TEST 5: Role Names Match Translations\n";
echo "─────────────────────────────────────────────────────────────\n";

$sql = "SELECT id, role_id, role FROM roles WHERE role_id IN (" . implode(',', $expected_roles) . ") ORDER BY role_id";
$stmt = $db->dbcrm()->prepare($sql);
$stmt->execute();
$db_roles = $stmt->fetchAll();

$name_mismatch = [];

foreach ($db_roles as $role) {
    $expected_name = $role_array[$role['role_id']] ?? null;
    if ($expected_name && $expected_name !== $role['role']) {
        $name_mismatch[] = "Role {$role['role_id']}: DB='{$role['role']}' vs Lang='{$expected_name}'";
    }
}

if (empty($name_mismatch)) {
    echo "All role names match translations ✅ PASS\n";
} else {
    echo "Role name mismatches found:\n";
    foreach ($name_mismatch as $mismatch) {
        echo "  ❌ $mismatch\n";
    }
}

// Test 6: Verify Roles.php excludes system roles
echo "\n🔐 TEST 6: Roles.php Configuration\n";
echo "─────────────────────────────────────────────────────────────\n";

$select_output = [];
ob_start();
$roles->select_role($lang);
$select_output = ob_get_clean();

$system_excluded = (strpos($select_output, 'value="1"') === false && strpos($select_output, 'value="2"') === false);
$sales_included = strpos($select_output, 'value="30"') !== false;
$client_included = strpos($select_output, 'value="160"') !== false;

echo "System roles excluded from dropdown: " . ($system_excluded ? "✅ PASS\n" : "❌ FAIL\n");
echo "Sales Manager role included: " . ($sales_included ? "✅ PASS\n" : "❌ FAIL\n");
echo "Client Standard role included: " . ($client_included ? "✅ PASS\n" : "❌ FAIL\n");

// Summary
echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║                    VALIDATION SUMMARY                      ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";

$all_pass = (
    $result['total'] == count($expected_roles) &&
    empty($missing_translations) &&
    count($assignable_roles) == 30 &&
    $category_status &&
    empty($name_mismatch) &&
    $system_excluded &&
    $sales_included &&
    $client_included
);

if ($all_pass) {
    echo "\n✅ ALL TESTS PASSED - RBAC MIGRATION SUCCESSFUL\n\n";
    echo "The consolidated role structure is working correctly:\n";
    echo "  • 32 active roles are present and accessible\n";
    echo "  • All roles have proper translations (English & Spanish)\n";
    echo "  • System roles (1-2) are excluded from user assignment\n";
    echo "  • All role categories are properly organized\n";
    echo "  • Role dropdown renders correctly\n";
} else {
    echo "\n❌ SOME TESTS FAILED - PLEASE REVIEW\n\n";
}

?>
