<?php

namespace Tests\Integration;

use Tests\TestCase;

/**
 * Integration tests for Leads Module
 * 
 * Tests the integration between different components of the leads system:
 * - Database interactions
 * - Model-View integration
 * - Bridge table relationships
 * - Stage management workflow
 */
class LeadsIntegrationTest extends TestCase
{
    private $leads;
    private $testLeadId;
    private $testData;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Skip if not in remote mode (requires actual database)
        $this->skipIfNotRemote();
        
        $this->leads = new \Leads();
        
        // Test data for creating leads
        $this->testData = [
            'lead_source' => 1,
            'first_name' => 'Integration',
            'family_name' => 'Test',
            'cell_phone' => '555-999-0001',
            'email' => 'integration.test@example.com',
            'contact_type' => 1,
            'form_street_1' => '123 Test St',
            'form_city' => 'Test City',
            'form_state' => 'CA',
            'form_postcode' => '90210',
            'form_country' => 'US',
            'stage' => 10,
            'last_edited_by' => 1
        ];
    }

    protected function tearDown(): void
    {
        // Clean up test data
        if ($this->testLeadId) {
            try {
                $this->leads->delete_lead($this->testLeadId);
            } catch (Exception $e) {
                // Ignore cleanup errors
            }
        }
        
        parent::tearDown();
    }

    /**
     * Test complete lead creation workflow
     */
    public function testCompleteLeadCreationWorkflow()
    {
        // Create a new lead
        $leadId = $this->leads->create_lead($this->testData);
        $this->testLeadId = $leadId;
        
        $this->assertNotFalse($leadId, 'Lead creation should succeed');
        $this->assertIsNumeric($leadId, 'Lead ID should be numeric');
        
        // Retrieve the created lead
        $retrievedLead = $this->leads->get_lead_by_id($leadId);
        
        $this->assertNotEmpty($retrievedLead, 'Should be able to retrieve created lead');
        $this->assertEquals($this->testData['first_name'], $retrievedLead[0]['first_name']);
        $this->assertEquals($this->testData['email'], $retrievedLead[0]['email']);
        $this->assertEquals($this->testData['stage'], $retrievedLead[0]['stage']);
    }

    /**
     * Test lead update workflow
     */
    public function testLeadUpdateWorkflow()
    {
        // Create a lead first
        $leadId = $this->leads->create_lead($this->testData);
        $this->testLeadId = $leadId;
        
        // Update the lead
        $updateData = [
            'first_name' => 'Updated Integration',
            'stage' => 20,
            'cell_phone' => '555-999-0002',
            'last_edited_by' => 1
        ];
        
        $updateResult = $this->leads->update_lead($leadId, $updateData);
        $this->assertTrue($updateResult, 'Lead update should succeed');
        
        // Verify the update
        $updatedLead = $this->leads->get_lead_by_id($leadId);
        $this->assertEquals('Updated Integration', $updatedLead[0]['first_name']);
        $this->assertEquals(20, $updatedLead[0]['stage']);
        $this->assertEquals('555-999-0002', $updatedLead[0]['cell_phone']);
    }

    /**
     * Test stage change workflow and notifications
     */
    public function testStageChangeWorkflow()
    {
        // Create a lead in Lead stage (10)
        $leadId = $this->leads->create_lead($this->testData);
        $this->testLeadId = $leadId;
        
        // Move to Referral stage (40) - should trigger notification
        $updateData = [
            'stage' => 40,
            'last_edited_by' => 1
        ];
        
        $updateResult = $this->leads->update_lead($leadId, $updateData);
        $this->assertTrue($updateResult);
        
        // Verify stage change
        $updatedLead = $this->leads->get_lead_by_id($leadId);
        $this->assertEquals(40, $updatedLead[0]['stage']);
        
        // Test stage display
        $stageDisplay = $this->leads->get_stage_display_name(40);
        $this->assertNotEmpty($stageDisplay);
        $this->assertNotEquals('Unknown', $stageDisplay);
    }

    /**
     * Test leads filtering by stage
     */
    public function testLeadsFilteringByStage()
    {
        // Create leads in different stages
        $leadData1 = array_merge($this->testData, [
            'email' => 'test1@example.com',
            'stage' => 10
        ]);
        $leadData2 = array_merge($this->testData, [
            'email' => 'test2@example.com',
            'stage' => 40
        ]);
        
        $leadId1 = $this->leads->create_lead($leadData1);
        $leadId2 = $this->leads->create_lead($leadData2);
        
        // Store for cleanup
        $this->testLeadId = $leadId1; // Will clean up one, the other should be cleaned up manually
        
        // Test filtering by single stage
        $stage10Leads = $this->leads->get_leads_by_stage(10);
        $stage40Leads = $this->leads->get_leads_by_stage(40);
        
        $this->assertNotEmpty($stage10Leads, 'Should find leads in stage 10');
        $this->assertNotEmpty($stage40Leads, 'Should find leads in stage 40');
        
        // Verify the leads are in correct stages
        $found10 = false;
        $found40 = false;
        
        foreach ($stage10Leads as $lead) {
            if ($lead['id'] == $leadId1) {
                $found10 = true;
                $this->assertEquals(10, $lead['stage']);
            }
        }
        
        foreach ($stage40Leads as $lead) {
            if ($lead['id'] == $leadId2) {
                $found40 = true;
                $this->assertEquals(40, $lead['stage']);
            }
        }
        
        $this->assertTrue($found10, 'Should find lead 1 in stage 10 results');
        $this->assertTrue($found40, 'Should find lead 2 in stage 40 results');
        
        // Clean up second lead
        $this->leads->delete_lead($leadId2);
    }

    /**
     * Test leads filtering by multiple stages
     */
    public function testLeadsFilteringByMultipleStages()
    {
        // Create leads in different stages
        $leadData1 = array_merge($this->testData, [
            'email' => 'multi1@example.com',
            'stage' => 10
        ]);
        $leadData2 = array_merge($this->testData, [
            'email' => 'multi2@example.com',
            'stage' => 20
        ]);
        $leadData3 = array_merge($this->testData, [
            'email' => 'multi3@example.com',
            'stage' => 140
        ]);
        
        $leadId1 = $this->leads->create_lead($leadData1);
        $leadId2 = $this->leads->create_lead($leadData2);
        $leadId3 = $this->leads->create_lead($leadData3);
        
        $this->testLeadId = $leadId1; // Store for cleanup
        
        // Test filtering by multiple stages
        $multipleStageLeads = $this->leads->get_leads_by_stages([10, 20]);
        
        $this->assertNotEmpty($multipleStageLeads, 'Should find leads in multiple stages');
        
        // Verify results contain leads from both stages but not stage 140
        $foundStages = [];
        $foundIds = [];
        
        foreach ($multipleStageLeads as $lead) {
            if (in_array($lead['id'], [$leadId1, $leadId2, $leadId3])) {
                $foundStages[] = $lead['stage'];
                $foundIds[] = $lead['id'];
            }
        }
        
        $this->assertContains(10, $foundStages, 'Should find stage 10 leads');
        $this->assertContains(20, $foundStages, 'Should find stage 20 leads');
        $this->assertNotContains(140, $foundStages, 'Should not find stage 140 leads');
        
        $this->assertContains($leadId1, $foundIds, 'Should find lead 1');
        $this->assertContains($leadId2, $foundIds, 'Should find lead 2');
        $this->assertNotContains($leadId3, $foundIds, 'Should not find lead 3');
        
        // Clean up
        $this->leads->delete_lead($leadId2);
        $this->leads->delete_lead($leadId3);
    }

    /**
     * Test full address building from components
     */
    public function testFullAddressBuilding()
    {
        // Create lead with address components
        $addressData = array_merge($this->testData, [
            'form_street_1' => '456 Integration Ave',
            'form_street_2' => 'Suite 100',
            'form_city' => 'Test City',
            'form_state' => 'NY',
            'form_postcode' => '10001',
            'form_country' => 'US'
        ]);
        
        $leadId = $this->leads->create_lead($addressData);
        $this->testLeadId = $leadId;
        
        // Retrieve and check full address
        $retrievedLead = $this->leads->get_lead_by_id($leadId);
        $fullAddress = $retrievedLead[0]['full_address'];
        
        $this->assertNotEmpty($fullAddress, 'Full address should be built');
        $this->assertStringContainsString('456 Integration Ave', $fullAddress);
        $this->assertStringContainsString('Suite 100', $fullAddress);
        $this->assertStringContainsString('Test City', $fullAddress);
        $this->assertStringContainsString('NY', $fullAddress);
        $this->assertStringContainsString('10001', $fullAddress);
    }

    /**
     * Test bridge table integration
     */
    public function testBridgeTableIntegration()
    {
        // Create a lead
        $leadId = $this->leads->create_lead($this->testData);
        $this->testLeadId = $leadId;
        
        // Get complete lead data (should include bridge table data)
        $completeData = $this->leads->get_lead_by_id($leadId);
        
        $this->assertNotEmpty($completeData, 'Should retrieve complete lead data');
        $this->assertIsArray($completeData[0], 'Lead data should be an array');
        
        // Check for bridge table structure
        $leadData = $completeData[0];
        $this->assertArrayHasKey('id', $leadData, 'Should have lead ID');
        $this->assertArrayHasKey('first_name', $leadData, 'Should have first name');
        $this->assertArrayHasKey('stage', $leadData, 'Should have stage');
    }

    /**
     * Test lead list view integration
     */
    public function testLeadListViewIntegration()
    {
        // Create test leads
        $leadId1 = $this->leads->create_lead(array_merge($this->testData, [
            'email' => 'listview1@example.com',
            'stage' => 10
        ]));
        $leadId2 = $this->leads->create_lead(array_merge($this->testData, [
            'email' => 'listview2@example.com',
            'stage' => 20
        ]));
        
        $this->testLeadId = $leadId1;
        
        // Get leads for list view
        $listLeads = $this->leads->get_leads_by_stages([10, 20, 30]);
        
        $this->assertNotEmpty($listLeads, 'Should retrieve leads for list view');
        
        // Test that LeadsList can be instantiated with the data
        $lang = [
            'action' => 'Action',
            'lead_id' => 'Lead #',
            'lead_stage' => 'Stage',
            'full_name' => 'Full Name',
            'lead_cell_phone' => 'Phone',
            'lead_email' => 'Email',
            'full_address' => 'Address'
        ];
        
        // This would normally create the LeadsList object
        // $leadsList = new LeadsList($listLeads, $lang);
        // But we can't easily test the view rendering without output buffering
        
        // Instead, test that the data structure is correct for the view
        foreach ($listLeads as $lead) {
            $this->assertArrayHasKey('lead_id', $lead, 'Lead should have lead_id for display');
            $this->assertArrayHasKey('stage', $lead, 'Lead should have stage for display');
            $this->assertArrayHasKey('first_name', $lead, 'Lead should have first_name for display');
            $this->assertArrayHasKey('family_name', $lead, 'Lead should have family_name for display');
        }
        
        // Clean up
        $this->leads->delete_lead($leadId2);
    }

    /**
     * Test phone number formatting integration
     */
    public function testPhoneNumberFormattingIntegration()
    {
        // Create leads with different phone formats
        $phoneTestData = [
            ['phone' => '5551234567', 'country' => 'US', 'expected' => '555-123-4567'],
            ['phone' => '4165551234', 'country' => 'CA', 'expected' => '+1 416-555-1234'],
            ['phone' => '2012345678', 'country' => 'GB', 'expected' => '+44 20-1234-5678']
        ];
        
        $createdLeads = [];
        
        foreach ($phoneTestData as $index => $testCase) {
            $leadData = array_merge($this->testData, [
                'cell_phone' => $testCase['phone'],
                'form_country' => $testCase['country'],
                'email' => "phone{$index}@example.com"
            ]);
            
            $leadId = $this->leads->create_lead($leadData);
            $createdLeads[] = $leadId;
            
            // Retrieve and test formatting
            $retrievedLead = $this->leads->get_lead_by_id($leadId);
            $this->assertEquals($testCase['phone'], $retrievedLead[0]['cell_phone'], 'Phone should be stored as entered');
            $this->assertEquals($testCase['country'], $retrievedLead[0]['form_country'], 'Country should be stored correctly');
        }
        
        // Store first lead for cleanup
        $this->testLeadId = $createdLeads[0];
        
        // Clean up other leads
        for ($i = 1; $i < count($createdLeads); $i++) {
            $this->leads->delete_lead($createdLeads[$i]);
        }
    }

    /**
     * Test data consistency across operations
     */
    public function testDataConsistency()
    {
        // Create a lead
        $leadId = $this->leads->create_lead($this->testData);
        $this->testLeadId = $leadId;
        
        // Retrieve immediately
        $lead1 = $this->leads->get_lead_by_id($leadId);
        
        // Update the lead
        $updateData = [
            'first_name' => 'Consistency Test',
            'last_edited_by' => 1
        ];
        $this->leads->update_lead($leadId, $updateData);
        
        // Retrieve again
        $lead2 = $this->leads->get_lead_by_id($leadId);
        
        // Test consistency
        $this->assertEquals($lead1[0]['id'], $lead2[0]['id'], 'ID should remain consistent');
        $this->assertEquals($lead1[0]['email'], $lead2[0]['email'], 'Email should remain consistent');
        $this->assertEquals('Consistency Test', $lead2[0]['first_name'], 'First name should be updated');
        $this->assertNotEquals($lead1[0]['updated_at'], $lead2[0]['updated_at'], 'Updated timestamp should change');
    }

    /**
     * Test error handling in integration scenarios
     */
    public function testErrorHandlingIntegration()
    {
        // Test creating lead with invalid data
        $invalidData = [
            'invalid_field' => 'should be ignored',
            'first_name' => 'Error Test'
        ];
        
        $result = $this->leads->create_lead($invalidData);
        
        // Should still succeed with valid fields
        $this->assertNotFalse($result, 'Should create lead even with some invalid fields');
        
        if ($result) {
            $this->testLeadId = $result;
            
            // Verify only valid data was stored
            $retrievedLead = $this->leads->get_lead_by_id($result);
            $this->assertEquals('Error Test', $retrievedLead[0]['first_name']);
            $this->assertArrayNotHasKey('invalid_field', $retrievedLead[0]);
        }
    }

    /**
     * Test performance with realistic data volumes
     */
    public function testPerformanceWithRealisticVolumes()
    {
        $startTime = microtime(true);
        
        // Create multiple leads
        $createdLeads = [];
        for ($i = 0; $i < 10; $i++) {
            $leadData = array_merge($this->testData, [
                'email' => "perf{$i}@example.com",
                'first_name' => "Performance{$i}"
            ]);
            
            $leadId = $this->leads->create_lead($leadData);
            $createdLeads[] = $leadId;
        }
        
        // Retrieve all leads
        $allLeads = $this->leads->get_leads_by_stages([10, 20, 30]);
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Should complete in reasonable time (under 5 seconds)
        $this->assertLessThan(5.0, $executionTime, 'Operations should complete in reasonable time');
        $this->assertNotEmpty($allLeads, 'Should retrieve leads successfully');
        
        // Store first for cleanup
        $this->testLeadId = $createdLeads[0];
        
        // Clean up other leads
        for ($i = 1; $i < count($createdLeads); $i++) {
            $this->leads->delete_lead($createdLeads[$i]);
        }
    }
}