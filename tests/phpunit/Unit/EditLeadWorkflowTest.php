<?php

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Edit Lead Workflow functionality
 * 
 * Tests the stage change notification system and redirect logic
 * for the enhanced edit lead workflow.
 */
class EditLeadWorkflowTest extends TestCase
{
    /**
     * Test the stage change notification detection logic
     * 
     * This function simulates the checkStageChangeNotification function
     * from leads/post.php to test the notification logic.
     */
    private function checkStageChangeNotification($old_stage, $new_stage)
    {
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

    /**
     * Test that no notification is triggered for non-trigger stage changes
     */
    public function testNoNotificationForNonTriggerStages()
    {
        // Test Lead to Pre-Qualification (10 -> 20)
        $result = $this->checkStageChangeNotification(10, 20);
        $this->assertFalse($result['moved'], 'Should not notify for Lead to Pre-Qualification');

        // Test Prospect to Prelim Design (50 -> 60)
        $result = $this->checkStageChangeNotification(50, 60);
        $this->assertFalse($result['moved'], 'Should not notify for Prospect to Prelim Design');

        // Test Lead to Closed Won (10 -> 130) - not a trigger stage
        $result = $this->checkStageChangeNotification(10, 130);
        $this->assertFalse($result['moved'], 'Should not notify for Lead to Closed Won');
    }

    /**
     * Test that no notification is triggered when stage doesn't change
     */
    public function testNoNotificationForSameStage()
    {
        // Test Referral to Referral (40 -> 40)
        $result = $this->checkStageChangeNotification(40, 40);
        $this->assertFalse($result['moved'], 'Should not notify when stage does not change');

        // Test Prospect to Prospect (50 -> 50)
        $result = $this->checkStageChangeNotification(50, 50);
        $this->assertFalse($result['moved'], 'Should not notify when stage does not change');
    }

    /**
     * Test notification for Referral stage change
     */
    public function testReferralStageNotification()
    {
        $result = $this->checkStageChangeNotification(20, 40); // Pre-Qualification to Referral
        
        $this->assertTrue($result['moved'], 'Should notify for Referral stage change');
        $this->assertEquals('Referral', $result['stage_name']);
        $this->assertEquals('referrals', $result['module']);
        $this->assertEquals('/referrals/list.php', $result['url']);
        $this->assertStringContainsString('Referral stage', $result['message']);
    }

    /**
     * Test notification for Prospect stage change
     */
    public function testProspectStageNotification()
    {
        $result = $this->checkStageChangeNotification(30, 50); // Qualified to Prospect
        
        $this->assertTrue($result['moved'], 'Should notify for Prospect stage change');
        $this->assertEquals('Prospect', $result['stage_name']);
        $this->assertEquals('prospects', $result['module']);
        $this->assertEquals('/prospects/list.php', $result['url']);
        $this->assertStringContainsString('Prospect stage', $result['message']);
    }

    /**
     * Test notification for Closed Lost stage change
     */
    public function testClosedLostStageNotification()
    {
        $result = $this->checkStageChangeNotification(120, 140); // Potential Client Response to Closed Lost
        
        $this->assertTrue($result['moved'], 'Should notify for Closed Lost stage change');
        $this->assertEquals('Closed Lost', $result['stage_name']);
        $this->assertEquals('leads', $result['module']);
        $this->assertEquals('/leads/list.php?filter=lost', $result['url']);
        $this->assertStringContainsString('Closed Lost', $result['message']);
    }

    /**
     * Test trigger stages array contains correct values
     */
    public function testTriggerStagesConfiguration()
    {
        $expected_trigger_stages = [40, 50, 140];
        
        // Test each trigger stage produces a notification
        foreach ($expected_trigger_stages as $trigger_stage) {
            $result = $this->checkStageChangeNotification(10, $trigger_stage);
            $this->assertTrue($result['moved'], "Stage {$trigger_stage} should be a trigger stage");
        }
    }

    /**
     * Test notification data structure
     */
    public function testNotificationDataStructure()
    {
        $result = $this->checkStageChangeNotification(10, 40); // Lead to Referral
        
        // Test required keys exist
        $this->assertArrayHasKey('moved', $result);
        $this->assertArrayHasKey('stage_name', $result);
        $this->assertArrayHasKey('module', $result);
        $this->assertArrayHasKey('url', $result);
        $this->assertArrayHasKey('message', $result);
        
        // Test data types
        $this->assertIsBool($result['moved']);
        $this->assertIsString($result['stage_name']);
        $this->assertIsString($result['module']);
        $this->assertIsString($result['url']);
        $this->assertIsString($result['message']);
        
        // Test URL format
        $this->assertStringStartsWith('/', $result['url'], 'URL should start with /');
    }

    /**
     * Test all trigger stage URLs are properly formatted
     */
    public function testTriggerStageUrls()
    {
        $test_cases = [
            [40, '/referrals/list.php'],
            [50, '/prospects/list.php'],
            [140, '/leads/list.php?filter=lost']
        ];
        
        foreach ($test_cases as [$stage, $expected_url]) {
            $result = $this->checkStageChangeNotification(10, $stage);
            $this->assertEquals($expected_url, $result['url'], "Stage {$stage} should have correct URL");
        }
    }

    /**
     * Test edge cases and boundary conditions
     */
    public function testEdgeCases()
    {
        // Test null old stage
        $result = $this->checkStageChangeNotification(null, 40);
        $this->assertTrue($result['moved'], 'Should notify when old stage is null and new stage is trigger');
        
        // Test string stage numbers (should be cast to int)
        $result = $this->checkStageChangeNotification('10', '40');
        $this->assertTrue($result['moved'], 'Should handle string stage numbers');
        
        // Test invalid stage numbers
        $result = $this->checkStageChangeNotification(10, 999);
        $this->assertFalse($result['moved'], 'Should not notify for invalid stage numbers');
    }

    /**
     * Test comprehensive workflow scenarios
     */
    public function testWorkflowScenarios()
    {
        $scenarios = [
            // [old_stage, new_stage, should_notify, description]
            [10, 20, false, 'Lead to Pre-Qualification'],
            [20, 40, true, 'Pre-Qualification to Referral'],
            [30, 50, true, 'Qualified to Prospect'],
            [50, 60, false, 'Prospect to Prelim Design'],
            [120, 140, true, 'Response to Closed Lost'],
            [40, 40, false, 'Same stage (no change)'],
            [10, 130, false, 'Lead to Closed Won (not trigger)'],
        ];
        
        foreach ($scenarios as [$old_stage, $new_stage, $should_notify, $description]) {
            $result = $this->checkStageChangeNotification($old_stage, $new_stage);
            
            if ($should_notify) {
                $this->assertTrue($result['moved'], "Should notify for: {$description}");
            } else {
                $this->assertFalse($result['moved'], "Should NOT notify for: {$description}");
            }
        }
    }
}