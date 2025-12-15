<?php
/**
 * Simple Stage Mapping Test
 * 
 * Tests just the stage remapping configuration without requiring
 * full system initialization.
 */

require_once __DIR__ . '/stage_remapping.php';

echo "=== Stage Mapping Test ===\n\n";

// Test 1: Stage Mapping
echo "1. Testing Stage Mapping...\n";
$mapping = StageRemapping::getNewStageMapping();

echo "   New stage numbers:\n";
foreach ($mapping as $newStage => $data) {
    echo "   - Stage $newStage: {$data['name']} (was {$data['old_stage']})\n";
}

// Verify key requirements
$tests = [
    [$mapping[130]['name'] === 'Closed Won', "Closed Won should be stage 130"],
    [$mapping[140]['name'] === 'Closed Lost', "Closed Lost should be stage 140"],
    [$mapping[150]['name'] === 'Contracting', "Contracting should be stage 150"],
    [count($mapping) === 15, "Should have 15 stages total"]
];

foreach ($tests as [$condition, $message]) {
    if (!$condition) {
        echo "   ❌ FAIL: $message\n";
        exit(1);
    }
}
echo "   ✓ Stage mapping test passed\n\n";

// Test 2: Stage Progressions
echo "2. Testing Stage Progressions...\n";
$progressions = StageRemapping::getNewStageProgressions();

// Test Lead stage (10) progressions
$leadProgressions = $progressions[10];
$progressionTests = [
    [in_array(20, $leadProgressions), "Lead should progress to Pre-Qualification (20)"],
    [in_array(30, $leadProgressions), "Lead should progress to Qualified (30)"],
    [in_array(40, $leadProgressions), "Lead should progress to Referral (40)"],
    [in_array(50, $leadProgressions), "Lead should progress to Prospect (50)"],
    [in_array(140, $leadProgressions), "Lead should progress to Closed Lost (140)"]
];

foreach ($progressionTests as [$condition, $message]) {
    if (!$condition) {
        echo "   ❌ FAIL: $message\n";
        exit(1);
    }
}

// Test Potential Client Response (120) can go to Won/Lost/Contracting
$potentialClientProgressions = $progressions[120];
$potentialTests = [
    [in_array(130, $potentialClientProgressions), "Should be able to go to Closed Won (130)"],
    [in_array(140, $potentialClientProgressions), "Should be able to go to Closed Lost (140)"],
    [in_array(150, $potentialClientProgressions), "Should be able to go to Contracting (150)"]
];

foreach ($potentialTests as [$condition, $message]) {
    if (!$condition) {
        echo "   ❌ FAIL: $message\n";
        exit(1);
    }
}
echo "   ✓ Stage progressions test passed\n\n";

// Test 3: Module Filters
echo "3. Testing Module Filters...\n";
$filters = StageRemapping::getModuleStageFilters();

$filterTests = [
    [in_array(10, $filters['leads']), "Leads should include stage 10 (Lead)"],
    [in_array(40, $filters['leads']), "Leads should include stage 40 (Referral)"],
    [in_array(50, $filters['leads']), "Leads should include stage 50 (Prospect)"],
    [in_array(140, $filters['leads']), "Leads should include stage 140 (Closed Lost)"],
    [in_array(50, $filters['prospects']), "Prospects should include stage 50 (Prospect)"],
    [in_array(150, $filters['prospects']), "Prospects should include stage 150 (Contracting)"],
    [count($filters['referrals']) === 1 && $filters['referrals'][0] === 40, "Referrals should only include stage 40"],
    [count($filters['contracting']) === 1 && $filters['contracting'][0] === 150, "Contracting should only include stage 150"]
];

foreach ($filterTests as [$condition, $message]) {
    if (!$condition) {
        echo "   ❌ FAIL: $message\n";
        exit(1);
    }
}
echo "   ✓ Module filters test passed\n\n";

// Test 4: Trigger Stages
echo "4. Testing Trigger Stages...\n";
$triggerStages = StageRemapping::getTriggerStages();

$triggerTests = [
    [in_array(40, $triggerStages), "Stage 40 (Referral) should be a trigger stage"],
    [in_array(50, $triggerStages), "Stage 50 (Prospect) should be a trigger stage"],
    [in_array(140, $triggerStages), "Stage 140 (Closed Lost) should be a trigger stage"],
    [count($triggerStages) === 3, "Should have exactly 3 trigger stages"]
];

foreach ($triggerTests as [$condition, $message]) {
    if (!$condition) {
        echo "   ❌ FAIL: $message\n";
        exit(1);
    }
}
echo "   ✓ Trigger stages test passed\n\n";

// Test 5: Badge Classes
echo "5. Testing Badge Classes...\n";
$badgeClasses = StageRemapping::getStageBadgeClasses();

$badgeTests = [
    [isset($badgeClasses[10]), "Badge class should exist for stage 10"],
    [isset($badgeClasses[130]), "Badge class should exist for stage 130 (Closed Won)"],
    [isset($badgeClasses[140]), "Badge class should exist for stage 140 (Closed Lost)"],
    [isset($badgeClasses[150]), "Badge class should exist for stage 150 (Contracting)"],
    [count($badgeClasses) === 15, "Should have badge classes for all 15 stages"]
];

foreach ($badgeTests as [$condition, $message]) {
    if (!$condition) {
        echo "   ❌ FAIL: $message\n";
        exit(1);
    }
}
echo "   ✓ Badge classes test passed\n\n";

// Test 6: Old to New Mapping
echo "6. Testing Old to New Mapping...\n";
$oldToNew = StageRemapping::getOldToNewMapping();

$mappingTests = [
    [$oldToNew[1] === 10, "Old stage 1 should map to new stage 10"],
    [$oldToNew[13] === 150, "Old stage 13 (Contracting) should map to new stage 150"],
    [$oldToNew[14] === 130, "Old stage 14 (Closed Won) should map to new stage 130"],
    [$oldToNew[15] === 140, "Old stage 15 (Closed Lost) should map to new stage 140"],
    [count($oldToNew) === 15, "Should have mappings for all 15 old stages"]
];

foreach ($mappingTests as [$condition, $message]) {
    if (!$condition) {
        echo "   ❌ FAIL: $message\n";
        exit(1);
    }
}
echo "   ✓ Old to new mapping test passed\n\n";

echo "✅ All stage mapping tests passed successfully!\n";
echo "\nKey Achievements:\n";
echo "- ✅ 10-unit increments provide room for expansion\n";
echo "- ✅ Closed Won (130) and Closed Lost (140) come before Contracting (150)\n";
echo "- ✅ Lead dropdown includes stages 10,20,30,40,50,140\n";
echo "- ✅ Trigger stages (40,50,140) identified for future actions\n";
echo "- ✅ Module filtering properly configured\n";
echo "\nReady for migration!\n";