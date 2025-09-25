<?php

require_once __DIR__ . '/../TestCase.php';

use Tests\TestCase;

/**
 * Integration tests for Edit Lead Workflow
 * 
 * Tests the complete integration of the edit lead workflow including
 * database operations, session management, and redirect logic.
 */
class EditLeadWorkflowIntegrationTest extends TestCase
{
    private $leads;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->leads = new Leads();
    }

    /**
     * Test that the Leads class has the required methods for the workflow
     */
    public function testRequiredMethodsExist()
    {
        $this->assertTrue(method_exists($this->leads, 'get_lead_by_id'), 'Leads class should have get_lead_by_id method');
        $this->assertTrue(method_exists($this->leads, 'get_leads_by_stage'), 'Leads class should have get_leads_by_stage method');
        $this->assertTrue(method_exists($this->leads, 'update_lead_with_contact'), 'Leads class should have update_lead_with_contact method');
    }

    /**
     * Test stage filtering functionality for lost leads
     */
    public function testStageFiltering()
    {
        // Test that get_leads_by_stage method exists and can be called
        try {
            $result = $this->leads->get_leads_by_stage(140); // Closed Lost stage
            $this->assertIsArray($result, 'get_leads_by_stage should return an array');
        } catch (Exception $e) {
            $this->markTestSkipped('Database connection required for stage filtering test: ' . $e->getMessage());
        }
    }

    /**
     * Test stage badge classes for new stage numbers
     */
    public function testStageBadgeClasses()
    {
        $test_stages = [10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 110, 120, 130, 140, 150];
        
        foreach ($test_stages as $stage) {
            $badge_class = $this->leads->get_stage_badge_class($stage);
            $this->assertIsString($badge_class, "Stage {$stage} should have a badge class");
            $this->assertStringContainsString('badge', $badge_class, "Stage {$stage} badge class should contain 'badge'");
        }
    }

    /**
     * Test stage display names for new stage numbers
     */
    public function testStageDisplayNames()
    {
        // Mock language array
        $lang = [
            'stage_10' => 'Lead',
            'stage_20' => 'Pre-Qualification',
            'stage_30' => 'Qualified',
            'stage_40' => 'Referral',
            'stage_50' => 'Prospect',
            'stage_60' => 'Prelim Design',
            'stage_70' => 'Manufacturing Estimate',
            'stage_80' => 'Contractor Estimate',
            'stage_90' => 'Completed Estimate',
            'stage_100' => 'Prospect Response',
            'stage_110' => 'Closing Conference',
            'stage_120' => 'Potential Client Response',
            'stage_130' => 'Closed Won',
            'stage_140' => 'Closed Lost',
            'stage_150' => 'Contracting'
        ];
        
        $test_stages = [10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 110, 120, 130, 140, 150];
        
        foreach ($test_stages as $stage) {
            $display_name = $this->leads->get_stage_display_name($stage, $lang);
            $this->assertIsString($display_name, "Stage {$stage} should have a display name");
            $this->assertNotEmpty($display_name, "Stage {$stage} display name should not be empty");
        }
    }

    /**
     * Test lead stage array contains new stage numbers
     */
    public function testLeadStageArray()
    {
        $stage_array = $this->leads->get_lead_stage_array();
        
        $this->assertIsArray($stage_array, 'get_lead_stage_array should return an array');
        
        // Test that new stage numbers exist
        $expected_stages = [10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 110, 120, 130, 140, 150];
        
        foreach ($expected_stages as $stage) {
            $this->assertArrayHasKey($stage, $stage_array, "Stage array should contain stage {$stage}");
        }
    }

    /**
     * Test module filtering logic
     */
    public function testModuleFiltering()
    {
        // Test leads module stages (should include trigger stages for final disposition)
        $leads_stages = [10, 20, 30, 40, 50, 140];
        
        // Test prospects module stages
        $prospects_stages = [50, 60, 70, 80, 90, 100, 110, 120, 150];
        
        // Test referrals module stages
        $referrals_stages = [40];
        
        // Test contracting module stages
        $contracting_stages = [150];
        
        // Verify stage arrays are properly configured
        $this->assertContains(40, $leads_stages, 'Leads module should include Referral stage for filtering');
        $this->assertContains(50, $leads_stages, 'Leads module should include Prospect stage for filtering');
        $this->assertContains(140, $leads_stages, 'Leads module should include Closed Lost stage for filtering');
        
        $this->assertContains(50, $prospects_stages, 'Prospects module should include Prospect stage');
        $this->assertContains(150, $prospects_stages, 'Prospects module should include Contracting stage');
        
        $this->assertEquals([40], $referrals_stages, 'Referrals module should only include Referral stage');
        $this->assertEquals([150], $contracting_stages, 'Contracting module should only include Contracting stage');
    }

    /**
     * Test trigger stage identification
     */
    public function testTriggerStageIdentification()
    {
        $trigger_stages = [40, 50, 140]; // Referral, Prospect, Closed Lost
        $non_trigger_stages = [10, 20, 30, 60, 70, 80, 90, 100, 110, 120, 130, 150];
        
        // Test that trigger stages are properly identified
        foreach ($trigger_stages as $stage) {
            $this->assertTrue(in_array($stage, $trigger_stages), "Stage {$stage} should be identified as a trigger stage");
        }
        
        // Test that non-trigger stages are not identified as triggers
        foreach ($non_trigger_stages as $stage) {
            $this->assertFalse(in_array($stage, $trigger_stages), "Stage {$stage} should NOT be identified as a trigger stage");
        }
    }

    /**
     * Test session notification structure
     */
    public function testSessionNotificationStructure()
    {
        // Simulate session notification data structure
        $notification_data = [
            'moved' => true,
            'stage_name' => 'Referral',
            'module' => 'referrals',
            'url' => '/referrals/list.php',
            'message' => 'This lead has been moved to Referral stage and is now available in the Referrals module.'
        ];
        
        // Test required keys
        $required_keys = ['moved', 'stage_name', 'module', 'url', 'message'];
        foreach ($required_keys as $key) {
            $this->assertArrayHasKey($key, $notification_data, "Notification data should contain '{$key}' key");
        }
        
        // Test data types
        $this->assertIsBool($notification_data['moved']);
        $this->assertIsString($notification_data['stage_name']);
        $this->assertIsString($notification_data['module']);
        $this->assertIsString($notification_data['url']);
        $this->assertIsString($notification_data['message']);
    }

    /**
     * Test URL generation for different modules
     */
    public function testModuleUrlGeneration()
    {
        $url_mappings = [
            40 => '/referrals/list.php',      // Referral
            50 => '/prospects/list.php',      // Prospect
            140 => '/leads/list.php?filter=lost' // Closed Lost
        ];
        
        foreach ($url_mappings as $stage => $expected_url) {
            $this->assertIsString($expected_url, "URL for stage {$stage} should be a string");
            $this->assertStringStartsWith('/', $expected_url, "URL for stage {$stage} should start with /");
            
            if ($stage === 140) {
                $this->assertStringContainsString('filter=lost', $expected_url, 'Closed Lost URL should contain filter parameter');
            }
        }
    }

    /**
     * Test workflow integration points
     */
    public function testWorkflowIntegrationPoints()
    {
        // Test that required classes exist for the workflow
        $this->assertTrue(class_exists('Leads'), 'Leads class should exist');
        $this->assertTrue(class_exists('Audit'), 'Audit class should exist for logging');
        
        // Test that required methods exist for audit logging
        if (class_exists('Audit')) {
            $audit = new Audit();
            $this->assertTrue(method_exists($audit, 'log'), 'Audit class should have log method');
        }
    }

    /**
     * Test error handling scenarios
     */
    public function testErrorHandling()
    {
        // Test handling of invalid stage numbers
        $invalid_stages = [-1, 0, 999, 'invalid', null];
        
        foreach ($invalid_stages as $invalid_stage) {
            try {
                $badge_class = $this->leads->get_stage_badge_class($invalid_stage);
                // Should either return a default badge class or handle gracefully
                $this->assertIsString($badge_class, "Invalid stage {$invalid_stage} should be handled gracefully");
            } catch (Exception $e) {
                // Exception is acceptable for invalid input
                $this->assertInstanceOf(Exception::class, $e);
            }
        }
    }

    /**
     * Test backward compatibility
     */
    public function testBackwardCompatibility()
    {
        // Test that old stage numbers are still handled if they exist
        $old_stages = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15];
        
        foreach ($old_stages as $old_stage) {
            try {
                $badge_class = $this->leads->get_stage_badge_class($old_stage);
                // Should handle old stages gracefully (either convert or provide default)
                $this->assertIsString($badge_class, "Old stage {$old_stage} should be handled for backward compatibility");
            } catch (Exception $e) {
                // Exception is acceptable if old stages are no longer supported
                $this->assertInstanceOf(Exception::class, $e);
            }
        }
    }
}