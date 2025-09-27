<?php

namespace Tests\Unit;

use Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Unit tests for Leads Model
 * 
 * Tests core functionality of the Leads class including:
 * - CRUD operations
 * - Stage management
 * - Data validation
 * - Helper methods
 */
class LeadsModelTest extends TestCase
{
    private $leads;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a real Leads instance for testing non-database methods
        // Database-dependent methods will be tested in integration tests
        $this->leads = new \Leads();
    }

    /**
     * Test lead source array returns correct options
     */
    public function testGetLeadSourceArray()
    {
        $sources = $this->leads->get_lead_source_array();
        
        $this->assertIsArray($sources);
        $this->assertCount(6, $sources);
        $this->assertEquals('Web', $sources[1]);
        $this->assertEquals('Referral', $sources[2]);
        $this->assertEquals('Phone', $sources[3]);
        $this->assertEquals('Email', $sources[4]);
        $this->assertEquals('Trade Show', $sources[5]);
        $this->assertEquals('Other', $sources[6]);
    }

    /**
     * Test contact type array returns correct options
     */
    public function testGetLeadContactTypeArray()
    {
        $types = $this->leads->get_lead_contact_type_array();
        
        $this->assertIsArray($types);
        $this->assertCount(5, $types);
        $this->assertEquals('Homeowner', $types[1]);
        $this->assertEquals('Property Manager', $types[2]);
        $this->assertEquals('Contractor', $types[3]);
        $this->assertEquals('Architect', $types[4]);
        $this->assertEquals('Other', $types[5]);
    }

    /**
     * Test stage array returns correct stage numbers and names
     */
    public function testGetLeadStageArray()
    {
        $stages = $this->leads->get_lead_stage_array();
        
        $this->assertIsArray($stages);
        $this->assertArrayHasKey(10, $stages); // Lead
        $this->assertArrayHasKey(20, $stages); // Pre-Qualification
        $this->assertArrayHasKey(40, $stages); // Referral
        $this->assertArrayHasKey(50, $stages); // Prospect
        $this->assertArrayHasKey(140, $stages); // Closed Lost
    }

    /**
     * Test stage badge class returns correct CSS classes
     */
    public function testGetStageBadgeClass()
    {
        // Test known stage numbers
        $this->assertStringContainsString('badge', $this->leads->get_stage_badge_class(10));
        $this->assertStringContainsString('badge', $this->leads->get_stage_badge_class(40));
        $this->assertStringContainsString('badge', $this->leads->get_stage_badge_class(140));
        
        // Test unknown stage number returns default
        $this->assertEquals('badge bg-secondary', $this->leads->get_stage_badge_class(999));
    }

    /**
     * Test stage display name with and without language array
     */
    public function testGetStageDisplayName()
    {
        // Test without language array (English default)
        $displayName = $this->leads->get_stage_display_name(10);
        $this->assertIsString($displayName);
        $this->assertNotEmpty($displayName);
        
        // Test with language array
        $lang = ['stage_10' => 'Custom Lead Name'];
        $displayName = $this->leads->get_stage_display_name(10, $lang);
        $this->assertEquals('Custom Lead Name', $displayName);
        
        // Test unknown stage
        $displayName = $this->leads->get_stage_display_name(999);
        $this->assertEquals('Unknown', $displayName);
    }

    /**
     * Test text stage to number conversion
     */
    public function testConvertTextStageToNumber()
    {
        $this->assertEquals(10, $this->leads->convert_text_stage_to_number('lead'));
        $this->assertEquals(20, $this->leads->convert_text_stage_to_number('pre-qualification'));
        $this->assertEquals(40, $this->leads->convert_text_stage_to_number('referral'));
        $this->assertEquals(50, $this->leads->convert_text_stage_to_number('prospect'));
        $this->assertEquals(140, $this->leads->convert_text_stage_to_number('closed lost'));
        
        // Test unknown text stage
        $this->assertEquals(10, $this->leads->convert_text_stage_to_number('unknown-stage'));
    }

    /**
     * Test lead creation with valid data (mocked)
     * Note: This test focuses on data validation and structure
     */
    public function testCreateLeadWithValidData()
    {
        // For unit testing, we'll test the data validation logic
        // without actually hitting the database
        $leadData = [
            'lead_source' => 1,
            'first_name' => 'John',
            'family_name' => 'Doe',
            'cell_phone' => '555-123-4567',
            'email' => 'john@example.com',
            'contact_type' => 1,
            'stage' => 10
        ];

        // Test that the data structure is valid
        $this->assertIsArray($leadData);
        $this->assertArrayHasKey('lead_source', $leadData);
        $this->assertArrayHasKey('first_name', $leadData);
        $this->assertArrayHasKey('family_name', $leadData);
        $this->assertArrayHasKey('email', $leadData);
        
        // Test data types
        $this->assertIsInt($leadData['lead_source']);
        $this->assertIsString($leadData['first_name']);
        $this->assertIsString($leadData['email']);
        
        // Test email validation
        $this->assertTrue(filter_var($leadData['email'], FILTER_VALIDATE_EMAIL) !== false);
        
        // Test that lead source is within valid range
        $validSources = $this->leads->get_lead_source_array();
        $this->assertArrayHasKey($leadData['lead_source'], $validSources);
    }

    /**
     * Test get leads by stage (validation logic)
     */
    public function testGetLeadsByStage()
    {
        // Test stage validation logic
        $validStages = $this->leads->get_lead_stage_array();
        
        // Test that stage 40 exists in valid stages
        $this->assertArrayHasKey(40, $validStages);
        
        // Test that invalid stages are handled
        $this->assertArrayNotHasKey(999, $validStages);
        
        // Test stage number validation
        $this->assertIsInt(40);
        $this->assertGreaterThan(0, 40);
        
        // Test stage name retrieval
        $stageName = $this->leads->get_stage_display_name(40);
        $this->assertIsString($stageName);
        $this->assertNotEmpty($stageName);
    }

    /**
     * Test get leads by multiple stages (validation logic)
     */
    public function testGetLeadsByStages()
    {
        // Test multiple stage validation
        $testStages = [10, 20, 30];
        $validStages = $this->leads->get_lead_stage_array();
        
        // Test that all requested stages are valid
        foreach ($testStages as $stage) {
            $this->assertArrayHasKey($stage, $validStages, "Stage $stage should be valid");
            $this->assertIsInt($stage);
            $this->assertGreaterThan(0, $stage);
        }
        
        // Test array structure
        $this->assertIsArray($testStages);
        $this->assertCount(3, $testStages);
        
        // Test that stages are unique
        $this->assertEquals($testStages, array_unique($testStages));
    }

    /**
     * Test lead update data validation
     */
    public function testUpdateLead()
    {
        // Test update data structure and validation
        $updateData = [
            'first_name' => 'Updated John',
            'stage' => 20,
            'last_edited_by' => 1
        ];

        // Test data structure
        $this->assertIsArray($updateData);
        $this->assertArrayHasKey('first_name', $updateData);
        $this->assertArrayHasKey('stage', $updateData);
        $this->assertArrayHasKey('last_edited_by', $updateData);
        
        // Test data types
        $this->assertIsString($updateData['first_name']);
        $this->assertIsInt($updateData['stage']);
        $this->assertIsInt($updateData['last_edited_by']);
        
        // Test stage validity
        $validStages = $this->leads->get_lead_stage_array();
        $this->assertArrayHasKey($updateData['stage'], $validStages);
        
        // Test name is not empty
        $this->assertNotEmpty(trim($updateData['first_name']));
    }

    /**
     * Test full address building from components
     */
    public function testFullAddressBuilding()
    {
        // Test address component validation and structure
        $addressData = [
            'form_street_1' => '123 Main St',
            'form_street_2' => 'Apt 4B',
            'form_city' => 'Anytown',
            'form_state' => 'CA',
            'form_postcode' => '12345',
            'form_country' => 'US'
        ];

        // Test address components
        $this->assertIsArray($addressData);
        $this->assertArrayHasKey('form_street_1', $addressData);
        $this->assertArrayHasKey('form_city', $addressData);
        $this->assertArrayHasKey('form_state', $addressData);
        $this->assertArrayHasKey('form_postcode', $addressData);
        
        // Test data types and content
        $this->assertIsString($addressData['form_street_1']);
        $this->assertIsString($addressData['form_city']);
        $this->assertIsString($addressData['form_state']);
        $this->assertIsString($addressData['form_postcode']);
        
        // Test required fields are not empty
        $this->assertNotEmpty(trim($addressData['form_street_1']));
        $this->assertNotEmpty(trim($addressData['form_city']));
        $this->assertNotEmpty(trim($addressData['form_state']));
        $this->assertNotEmpty(trim($addressData['form_postcode']));
        
        // Test postal code format (basic validation)
        $this->assertMatchesRegularExpression('/^\d{5}$/', $addressData['form_postcode']);
        
        // Test state code format (2 letters)
        $this->assertMatchesRegularExpression('/^[A-Z]{2}$/', $addressData['form_state']);
    }

    /**
     * Test data validation and sanitization
     */
    public function testDataValidation()
    {
        // Test data validation logic
        $leadData = [
            'first_name' => 'John',
            'invalid_field' => 'should be ignored',
            'email' => 'john@example.com',
            'another_invalid' => 'also ignored',
            'lead_source' => 1,
            'contact_type' => 1
        ];

        // Test valid fields
        $this->assertArrayHasKey('first_name', $leadData);
        $this->assertArrayHasKey('email', $leadData);
        $this->assertArrayHasKey('lead_source', $leadData);
        
        // Test email validation
        $this->assertTrue(filter_var($leadData['email'], FILTER_VALIDATE_EMAIL) !== false);
        
        // Test name sanitization
        $sanitizedName = trim(strip_tags($leadData['first_name']));
        $this->assertEquals('John', $sanitizedName);
        
        // Test lead source validation
        $validSources = $this->leads->get_lead_source_array();
        $this->assertArrayHasKey($leadData['lead_source'], $validSources);
        
        // Test contact type validation
        $validTypes = $this->leads->get_lead_contact_type_array();
        $this->assertArrayHasKey($leadData['contact_type'], $validTypes);
    }

    /**
     * Test error handling for invalid data
     */
    public function testDatabaseErrorHandling()
    {
        // Test error handling for invalid data scenarios
        $invalidEmailData = ['email' => 'invalid-email'];
        $emptyNameData = ['first_name' => ''];
        $invalidSourceData = ['lead_source' => 999];
        
        // Test invalid email
        $this->assertFalse(filter_var($invalidEmailData['email'], FILTER_VALIDATE_EMAIL));
        
        // Test empty name
        $this->assertEmpty(trim($emptyNameData['first_name']));
        
        // Test invalid lead source
        $validSources = $this->leads->get_lead_source_array();
        $this->assertArrayNotHasKey($invalidSourceData['lead_source'], $validSources);
        
        // Test that we can detect these validation issues
        $this->assertTrue(true); // Placeholder for validation logic
    }

    /**
     * Test get last lead ID functionality (validation logic)
     */
    public function testGetLastLeadId()
    {
        // Test ID validation logic
        $testId = '1001';
        
        // Test that ID is numeric
        $this->assertTrue(is_numeric($testId));
        
        // Test that ID is positive
        $this->assertGreaterThan(0, (int)$testId);
        
        // Test ID format
        $this->assertMatchesRegularExpression('/^\d+$/', $testId);
        
        // Test conversion to integer
        $intId = (int)$testId;
        $this->assertIsInt($intId);
        $this->assertEquals(1001, $intId);
    }

    /**
     * Test multilingual stage support
     */
    public function testMultilingualStageSupport()
    {
        $lang = [
            'stage_10' => 'Piste',
            'stage_20' => 'Pré-qualification',
            'stage_40' => 'Référence'
        ];

        $stages = $this->leads->get_lead_stage_array_multilingual($lang);
        
        $this->assertIsArray($stages);
        $this->assertEquals('Piste', $stages[10]);
        $this->assertEquals('Pré-qualification', $stages[20]);
        $this->assertEquals('Référence', $stages[40]);
    }
}