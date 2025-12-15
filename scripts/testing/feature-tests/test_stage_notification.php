<?php
/**
 * Test script for stage change notification system
 */

// Include the stage remapping class
require_once __DIR__ . '/stage_remapping.php';

echo "=== Stage Change Notification Test ===\n\n";

// Simulate the checkStageChangeNotification function from post.php
function checkStageChangeNotification($old_stage, $new_stage) {
    $trigger_stages = [40, 50, 140]; // Referral, Prospect, Closed Lost
    
    // Only notify if stage actually changed and new stage is a trigger stage
    if ($old_stage != $new_stage && in_array((int)$new_stage, $trigger_stages)) {
        switch ((int)$new_stage) {
            case 40: // Referral
                return [
                    'moved' => true,
                    'stage_name' => 'Referral',
                    'module' => 'referrals',
                    'url' => '/referrals/list.php',
                    'message' => 'This lead has been moved to Referral stage and is now available in the Referrals module.'
                ];
            case 50: // Prospect
                return [
                    'moved' => true,
                    'stage_name' => 'Prospect',
                    'module' => 'prospects',
                    'url' => '/prospects/list.php',
                    'message' => 'This lead has been moved to Prospect stage and is now available in the Prospects module.'
                ];
            case 140: // Closed Lost
                return [
                    'moved' => true,
                    'stage_name' => 'Closed Lost',
                    'module' => 'leads',
                    'url' => '/leads/list.php?filter=lost',
                    'message' => 'This lead has been marked as Closed Lost and can be found in the Lost leads filter.'
                ];
        }
    }
    
    return ['moved' => false];
}

// Test scenarios
$test_scenarios = [
    ['old' => 10, 'new' => 20, 'description' => 'Lead to Pre-Qualification (no notification)'],
    ['old' => 20, 'new' => 40, 'description' => 'Pre-Qualification to Referral (should notify)'],
    ['old' => 30, 'new' => 50, 'description' => 'Qualified to Prospect (should notify)'],
    ['old' => 50, 'new' => 60, 'description' => 'Prospect to Prelim Design (no notification)'],
    ['old' => 120, 'new' => 140, 'description' => 'Potential Client Response to Closed Lost (should notify)'],
    ['old' => 40, 'new' => 40, 'description' => 'Referral to Referral (no change, no notification)'],
    ['old' => 10, 'new' => 130, 'description' => 'Lead to Closed Won (no notification - not a trigger stage)'],
];

foreach ($test_scenarios as $i => $scenario) {
    echo ($i + 1) . ". Testing: {$scenario['description']}\n";
    echo "   Old Stage: {$scenario['old']}, New Stage: {$scenario['new']}\n";
    
    $result = checkStageChangeNotification($scenario['old'], $scenario['new']);
    
    if ($result['moved']) {
        echo "   ✅ NOTIFICATION: Moving to {$result['stage_name']}\n";
        echo "   📍 URL: {$result['url']}\n";
        echo "   💬 Message: {$result['message']}\n";
    } else {
        echo "   ❌ No notification (expected behavior)\n";
    }
    echo "\n";
}

echo "=== Test completed! ===\n";
echo "\nExpected Results:\n";
echo "- Scenarios 2, 3, and 5 should show notifications\n";
echo "- All other scenarios should show no notification\n";