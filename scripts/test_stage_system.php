<?php
/**
 * Test Script for New Stage System
 * 
 * This script tests the new stage numbering system and verifies
 * that all components work correctly together.
 */

require_once dirname(__DIR__) . '/config/system.php';
require_once __DIR__ . '/stage_remapping.php';

class StageSystemTest {
    private $leads;
    
    public function __construct() {
        $this->leads = new Leads();
    }
    
    public function runAllTests() {
        echo "=== Stage System Test Suite ===\n\n";
        
        $this->testStageMapping();
        $this->testStageProgressions();
        $this->testModuleFilters();
        $this->testTriggerStages();
        $this->testBadgeClasses();
        $this->testLeadsClassIntegration();
        
        echo "\n=== All Tests Completed ===\n";
    }
    
    private function testStageMapping() {
        echo "1. Testing Stage Mapping...\n";
        
        $mapping = StageRemapping::getNewStageMapping();
        
        echo "   New stage numbers:\n";
        foreach ($mapping as $newStage => $data) {
            echo "   - Stage $newStage: {$data['name']} (was {$data['old_stage']})\n";
        }
        
        // Test key requirements
        $this->assert($mapping[130]['name'] === 'Closed Won', "Closed Won should be stage 130");
        $this->assert($mapping[140]['name'] === 'Closed Lost', "Closed Lost should be stage 140");
        $this->assert($mapping[150]['name'] === 'Contracting', "Contracting should be stage 150");
        
        echo "   ✓ Stage mapping test passed\n\n";
    }
    
    private function testStageProgressions() {
        echo "2. Testing Stage Progressions...\n";
        
        $progressions = StageRemapping::getNewStageProgressions();
        
        // Test Lead stage (10) can go to required stages
        $leadProgressions = $progressions[10];
        $this->assert(in_array(20, $leadProgressions), "Lead should progress to Pre-Qualification");
        $this->assert(in_array(30, $leadProgressions), "Lead should progress to Qualified");
        $this->assert(in_array(40, $leadProgressions), "Lead should progress to Referral");
        $this->assert(in_array(50, $leadProgressions), "Lead should progress to Prospect");
        $this->assert(in_array(140, $leadProgressions), "Lead should progress to Closed Lost");
        
        // Test Closed Won/Lost before Contracting
        $potentialClientProgressions = $progressions[120]; // Potential Client Response
        $this->assert(in_array(130, $potentialClientProgressions), "Should be able to go to Closed Won");
        $this->assert(in_array(140, $potentialClientProgressions), "Should be able to go to Closed Lost");
        $this->assert(in_array(150, $potentialClientProgressions), "Should be able to go to Contracting");
        
        echo "   ✓ Stage progressions test passed\n\n";
    }
    
    private function testModuleFilters() {
        echo "3. Testing Module Filters...\n";
        
        $filters = StageRemapping::getModuleStageFilters();
        
        // Test leads module includes correct stages
        $leadsStages = $filters['leads'];
        $this->assert(in_array(10, $leadsStages), "Leads should include stage 10 (Lead)");
        $this->assert(in_array(40, $leadsStages), "Leads should include stage 40 (Referral)");
        $this->assert(in_array(50, $leadsStages), "Leads should include stage 50 (Prospect)");
        $this->assert(in_array(140, $leadsStages), "Leads should include stage 140 (Closed Lost)");
        
        // Test prospects module
        $prospectsStages = $filters['prospects'];
        $this->assert(in_array(50, $prospectsStages), "Prospects should include stage 50 (Prospect)");
        $this->assert(in_array(150, $prospectsStages), "Prospects should include stage 150 (Contracting)");
        
        // Test referrals module
        $referralsStages = $filters['referrals'];
        $this->assert(count($referralsStages) === 1 && $referralsStages[0] === 40, "Referrals should only include stage 40");
        
        // Test contracting module
        $contractingStages = $filters['contracting'];
        $this->assert(count($contractingStages) === 1 && $contractingStages[0] === 150, "Contracting should only include stage 150");
        
        echo "   ✓ Module filters test passed\n\n";
    }
    
    private function testTriggerStages() {
        echo "4. Testing Trigger Stages...\n";
        
        $triggerStages = StageRemapping::getTriggerStages();
        
        $this->assert(in_array(40, $triggerStages), "Stage 40 (Referral) should be a trigger stage");
        $this->assert(in_array(50, $triggerStages), "Stage 50 (Prospect) should be a trigger stage");
        $this->assert(in_array(140, $triggerStages), "Stage 140 (Closed Lost) should be a trigger stage");
        
        echo "   ✓ Trigger stages test passed\n\n";
    }
    
    private function testBadgeClasses() {
        echo "5. Testing Badge Classes...\n";
        
        $badgeClasses = StageRemapping::getStageBadgeClasses();
        
        $this->assert(isset($badgeClasses[10]), "Badge class should exist for stage 10");
        $this->assert(isset($badgeClasses[130]), "Badge class should exist for stage 130 (Closed Won)");
        $this->assert(isset($badgeClasses[140]), "Badge class should exist for stage 140 (Closed Lost)");
        $this->assert(isset($badgeClasses[150]), "Badge class should exist for stage 150 (Contracting)");
        
        echo "   ✓ Badge classes test passed\n\n";
    }
    
    private function testLeadsClassIntegration() {
        echo "6. Testing Leads Class Integration...\n";
        
        // Test stage array
        $stageArray = $this->leads->get_lead_stage_array();
        $this->assert(isset($stageArray[10]), "Leads class should return stage 10");
        $this->assert(isset($stageArray[130]), "Leads class should return stage 130 (Closed Won)");
        $this->assert(isset($stageArray[140]), "Leads class should return stage 140 (Closed Lost)");
        $this->assert(isset($stageArray[150]), "Leads class should return stage 150 (Contracting)");
        
        // Test stage progressions
        $progressions = $this->leads->get_valid_next_stages(10);
        $this->assert(in_array(40, $progressions), "Lead stage should allow progression to Referral");
        $this->assert(in_array(50, $progressions), "Lead stage should allow progression to Prospect");
        $this->assert(in_array(140, $progressions), "Lead stage should allow progression to Closed Lost");
        
        // Test trigger stages
        $this->assert($this->leads->is_trigger_stage(40), "Stage 40 should be identified as trigger stage");
        $this->assert($this->leads->is_trigger_stage(50), "Stage 50 should be identified as trigger stage");
        $this->assert($this->leads->is_trigger_stage(140), "Stage 140 should be identified as trigger stage");
        
        // Test badge classes
        $badgeClass = $this->leads->get_stage_badge_class(10);
        $this->assert(!empty($badgeClass), "Badge class should be returned for stage 10");
        
        echo "   ✓ Leads class integration test passed\n\n";
    }
    
    private function assert($condition, $message) {
        if (!$condition) {
            throw new Exception("Assertion failed: $message");
        }
    }
}

// Run tests if called directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    try {
        $test = new StageSystemTest();
        $test->runAllTests();
        echo "✅ All tests passed successfully!\n";
    } catch (Exception $e) {
        echo "❌ Test failed: " . $e->getMessage() . "\n";
        exit(1);
    }
}