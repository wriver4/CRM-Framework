<?php

namespace Tests\Integration;

use Tests\TestCase;

/**
 * Local Integration tests for Leads Module
 * 
 * Tests integration without requiring remote database access
 * Focuses on class interactions and method integration
 */
class LeadsIntegrationTestLocal extends TestCase
{
    private $leads;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Skip if in remote mode (this is for local testing)
        $this->skipIfRemote();
        
        // Test class instantiation and basic integration
        $this->leads = new \Leads();
    }

    /**
     * Test that Leads class properly extends Database class
     */
    public function testLeadsClassInheritance()
    {
        $this->assertInstanceOf(\Database::class, $this->leads);
        $this->assertInstanceOf(\Leads::class, $this->leads);
    }

    /**
     * Test integration between Leads and Helpers classes
     */
    public function testLeadsHelpersIntegration()
    {
        $helpers = new \Helpers();
        
        // Test that both classes can work together
        $leadSources = $this->leads->get_lead_source_array([]);
        $contactTypes = $helpers->get_lead_contact_type_array([]);
        
        $this->assertIsArray($leadSources);
        $this->assertIsArray($contactTypes);
    }

    /**
     * Test stage management integration
     */
    public function testStageManagementIntegration()
    {
        // Test stage conversion methods work together
        $textStage = 'new_lead';
        $numericStage = $this->leads->convert_text_stage_to_number($textStage);
        
        $this->assertIsInt($numericStage);
        $this->assertGreaterThan(0, $numericStage);
        
        // Test reverse conversion
        $stageArray = $this->leads->get_lead_stage_array([]);
        $this->assertIsArray($stageArray);
        $this->assertArrayHasKey($numericStage, $stageArray);
    }

    /**
     * Test data validation integration
     */
    public function testDataValidationIntegration()
    {
        $testData = [
            'first_name' => 'Test',
            'family_name' => 'User',
            'email' => 'test@example.com',
            'cell_phone' => '555-123-4567',
            'lead_source' => 1,
            'contact_type' => 1,
            'stage' => 10
        ];

        // Test that validation methods work with data structures
        $this->assertTrue($this->leads->validate_lead_data($testData));
        
        // Test invalid data
        $invalidData = ['first_name' => '']; // Missing required fields
        $this->assertFalse($this->leads->validate_lead_data($invalidData));
    }

    /**
     * Test address building integration
     */
    public function testAddressBuildingIntegration()
    {
        $addressData = [
            'form_street_1' => '123 Main St',
            'form_street_2' => 'Apt 4B',
            'form_city' => 'Test City',
            'form_state' => 'CA',
            'form_postcode' => '90210',
            'form_country' => 'US'
        ];

        $fullAddress = $this->leads->build_full_address($addressData);
        
        $this->assertIsString($fullAddress);
        $this->assertStringContainsString('123 Main St', $fullAddress);
        $this->assertStringContainsString('Test City', $fullAddress);
        $this->assertStringContainsString('CA', $fullAddress);
    }

    /**
     * Test phone formatting integration with Helpers
     */
    public function testPhoneFormattingIntegration()
    {
        $helpers = new \Helpers();
        
        $testPhones = [
            '5551234567',
            '(555) 123-4567',
            '+1-555-123-4567'
        ];

        foreach ($testPhones as $phone) {
            $formatted = $helpers->format_phone_display($phone, 'US');
            $this->assertEquals('555-123-4567', $formatted);
        }
    }

    /**
     * Test multilingual support integration
     */
    public function testMultilingualIntegration()
    {
        // Test with English language array
        $englishLang = [
            'lead_stage_10' => 'New Lead',
            'lead_stage_20' => 'Qualified',
            'lead_stage_30' => 'Proposal',
            'lead_source_1' => 'Website',
            'contact_id_1' => 'Primary Owner'
        ];

        $stages = $this->leads->get_lead_stage_array($englishLang);
        $sources = $this->leads->get_lead_source_array($englishLang);
        
        $this->assertIsArray($stages);
        $this->assertIsArray($sources);
        $this->assertEquals('New Lead', $stages[10]);
        $this->assertEquals('Website', $sources[1]);
    }

    /**
     * Test error handling integration
     */
    public function testErrorHandlingIntegration()
    {
        // Test with invalid stage
        $invalidStage = 'invalid_stage';
        $result = $this->leads->convert_text_stage_to_number($invalidStage);
        
        // Should return default or handle gracefully
        $this->assertIsInt($result);
        
        // Test with empty data
        $emptyData = [];
        $validation = $this->leads->validate_lead_data($emptyData);
        $this->assertFalse($validation);
    }

    /**
     * Test method chaining and workflow integration
     */
    public function testWorkflowIntegration()
    {
        $testData = [
            'first_name' => 'Integration',
            'family_name' => 'Test',
            'email' => 'integration@test.com',
            'cell_phone' => '555-999-0001',
            'lead_source' => 1,
            'contact_type' => 1,
            'stage' => 10,
            'form_street_1' => '123 Test St',
            'form_city' => 'Test City',
            'form_state' => 'CA',
            'form_postcode' => '90210',
            'form_country' => 'US'
        ];

        // Test that data flows through validation and processing
        $isValid = $this->leads->validate_lead_data($testData);
        $this->assertTrue($isValid);

        $fullAddress = $this->leads->build_full_address($testData);
        $this->assertStringContainsString('123 Test St', $fullAddress);

        $stage = $this->leads->get_stage_display_name(10, []);
        $this->assertIsString($stage);
    }

    /**
     * Test performance of integrated operations
     */
    public function testIntegrationPerformance()
    {
        $startTime = microtime(true);
        
        // Perform multiple integrated operations
        for ($i = 0; $i < 100; $i++) {
            $this->leads->get_lead_stage_array([]);
            $this->leads->get_lead_source_array([]);
            $this->leads->convert_text_stage_to_number('new_lead');
        }
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Should complete in reasonable time
        $this->assertLessThan(2.0, $executionTime, 'Integration operations should be performant');
    }
}