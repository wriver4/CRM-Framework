<?php
/**
 * Simple test to verify stage migration is working
 */

// Include the stage remapping class
require_once __DIR__ . '/stage_remapping.php';

echo "=== Stage Migration Test ===\n\n";

// Test 1: Check new stage mapping
echo "1. Testing new stage mapping:\n";
$newStages = StageRemapping::getNewStageMapping();
foreach ($newStages as $stageNumber => $data) {
    echo "   Stage $stageNumber: {$data['name']} (was {$data['old_stage']})\n";
}
echo "\n";

// Test 2: Check stage badge classes
echo "2. Testing stage badge classes:\n";
$badgeClasses = StageRemapping::getStageBadgeClasses();
foreach ($badgeClasses as $stageNumber => $badgeClass) {
    echo "   Stage $stageNumber: $badgeClass\n";
}
echo "\n";

// Test 3: Check trigger stages
echo "3. Testing trigger stages:\n";
$triggerStages = StageRemapping::getTriggerStages();
foreach ($triggerStages as $stageNumber) {
    $stageName = $newStages[$stageNumber]['name'] ?? 'Unknown';
    echo "   Stage $stageNumber: $stageName\n";
}
echo "\n";

// Test 4: Check module filters
echo "4. Testing module filters:\n";
$moduleFilters = StageRemapping::getModuleStageFilters();
foreach ($moduleFilters as $module => $stages) {
    echo "   $module: " . implode(', ', $stages) . "\n";
}
echo "\n";

// Test 5: Check old to new mapping
echo "5. Testing old to new stage conversion:\n";
$oldToNew = StageRemapping::getOldToNewMapping();
foreach ($oldToNew as $oldStage => $newStage) {
    $stageName = $newStages[$newStage]['name'] ?? 'Unknown';
    echo "   Old $oldStage -> New $newStage ($stageName)\n";
}
echo "\n";

echo "=== All tests completed successfully! ===\n";