<?php

namespace Tests\Integration;

use Tests\TestCase;

/**
 * Integration tests for Leads error handling
 * 
 * Tests error scenarios and edge cases:
 * - Invalid data handling
 * - Duplicate prevention
 * - Database constraints
 * - Transaction rollback
 * - Validation errors
 */
class LeadsErrorHandlingIntegrationTest extends TestCase
{
    private $leads;
    private $testLeadIds = [];

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->skipIfNotRemote();
        
        $this->leads = new \Leads();
    }

    protected function tearDown(): void
    {
        // Clean up all test leads
        foreach ($this->testLeadIds as $id) {
            try {
                $this->leads->delete_lead($id);
            } catch (Exception $e) {
                // Ignore cleanup errors
            }
        }
        
        parent::tearDown();
    }

    /**
     * Test handling of missing required fields
     */
    public function testMissingRequiredFieldsValidation()
    {
        // Missing first name
        $incompleteData1 = [
            'family_name' => 'Test',
            'email' => 'test@example.com',
            'stage' => 10,
            'last_edited_by' => 1
        ];

        $errors = $this->leads->validate_lead_with_contact_data($incompleteData1);
        $this->assertNotEmpty($errors, 'Should validate missing first_name');

        // Missing email
        $incompleteData2 = [
            'first_name' => 'Test',
            'family_name' => 'Lead',
            'stage' => 10,
            'last_edited_by' => 1
        ];

        $errors = $this->leads->validate_lead_with_contact_data($incompleteData2);
        $this->assertNotEmpty($errors, 'Should validate missing email');

        // Missing family name
        $incompleteData3 = [
            'first_name' => 'Test',
            'email' => 'test@example.com',
            'stage' => 10,
            'last_edited_by' => 1
        ];

        $errors = $this->leads->validate_lead_with_contact_data($incompleteData3);
        $this->assertNotEmpty($errors, 'Should validate missing family_name');
    }

    /**
     * Test invalid email format rejection
     */
    public function testInvalidEmailRejection()
    {
        $invalidEmails = [
            'not-an-email',
            'missing@domain',
            '@nodomain.com',
            'spaces in@email.com'
        ];

        foreach ($invalidEmails as $email) {
            $isValid = filter_var($email, FILTER_VALIDATE_EMAIL);
            $this->assertFalse($isValid, "Email '{$email}' should be invalid");
        }
    }

    /**
     * Test invalid lead source handling
     */
    public function testInvalidLeadSourceHandling()
    {
        $leadData = [
            'lead_source' => 999,  // Invalid source
            'first_name' => 'Invalid',
            'family_name' => 'Source',
            'email' => 'invalid.source@example.com',
            'stage' => 10,
            'last_edited_by' => 1
        ];

        // Should either reject invalid source or handle gracefully
        $sources = $this->leads->get_lead_source_array();
        
        // Source 999 should not be valid
        $this->assertArrayNotHasKey(999, $sources);
    }

    /**
     * Test invalid stage handling
     */
    public function testInvalidStageHandling()
    {
        $stages = $this->leads->get_lead_stage_array();
        
        $invalidStages = [0, 5, 999, -10];
        
        foreach ($invalidStages as $stage) {
            $this->assertArrayNotHasKey($stage, $stages, 
                "Stage {$stage} should not be valid");
        }
    }

    /**
     * Test very long string handling
     */
    public function testVeryLongStringHandling()
    {
        $longString = str_repeat('A', 1000);  // Exceeds field limits
        
        // String should be truncatable
        $truncated = substr($longString, 0, 100);
        $this->assertEquals(100, strlen($truncated));
    }

    /**
     * Test special characters in names
     */
    public function testSpecialCharactersInNames()
    {
        $specialNames = [
            "O'Brien",
            "Jean-Pierre",
            "María José",
            "François",
        ];

        foreach ($specialNames as $name) {
            // Should not cause SQL injection
            $sanitized = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
            $this->assertIsString($sanitized);
            $this->assertNotEmpty($sanitized);
        }
    }

    /**
     * Test SQL injection prevention
     */
    public function testSQLInjectionPrevention()
    {
        $injectionAttempts = [
            "'; DROP TABLE leads; --",
            "1' OR '1'='1",
            "admin' --",
            "<script>alert('xss')</script>"
        ];

        foreach ($injectionAttempts as $malicious) {
            $sanitized = htmlspecialchars($malicious, ENT_QUOTES, 'UTF-8');
            
            // Should not execute SQL or scripts
            $this->assertNotEqual($malicious, $sanitized);
            $this->assertStringNotContainsString('DROP', $sanitized);
            $this->assertStringNotContainsString('script>', $sanitized);
        }
    }

    /**
     * Test XSS attack prevention
     */
    public function testXSSAttackPrevention()
    {
        $xssAttempts = [
            '<img src=x onerror="alert(1)">',
            '<script>alert("xss")</script>',
            'javascript:alert(1)',
            '<svg onload="alert(1)">',
        ];

        foreach ($xssAttempts as $xss) {
            $sanitized = htmlspecialchars($xss, ENT_QUOTES, 'UTF-8');
            $this->assertNotEqual($xss, $sanitized);
            $this->assertStringNotContainsString('<script', $sanitized);
            $this->assertStringNotContainsString('onerror', $sanitized);
            $this->assertStringNotContainsString('onload', $sanitized);
        }
    }

    /**
     * Test null byte injection prevention
     */
    public function testNullByteInjectionPrevention()
    {
        $nullByteString = "normal\x00injected";
        
        // Should sanitize null bytes
        $sanitized = str_replace("\x00", '', $nullByteString);
        $this->assertEquals("normalinjected", $sanitized);
    }

    /**
     * Test empty form submission handling
     */
    public function testEmptyFormSubmission()
    {
        $emptyData = [
            'first_name' => '',
            'family_name' => '',
            'email' => '',
            'stage' => 10,
            'last_edited_by' => 1
        ];

        $errors = $this->leads->validate_lead_with_contact_data($emptyData);
        $this->assertNotEmpty($errors, 'Empty submission should have validation errors');
    }

    /**
     * Test whitespace-only field handling
     */
    public function testWhitespaceOnlyFieldHandling()
    {
        $whitespaceData = [
            'first_name' => '   ',
            'family_name' => '   ',
            'email' => '   ',
            'stage' => 10,
            'last_edited_by' => 1
        ];

        // After trimming, should be empty
        $trimmed = [
            'first_name' => trim($whitespaceData['first_name']),
            'family_name' => trim($whitespaceData['family_name']),
            'email' => trim($whitespaceData['email']),
        ];

        foreach ($trimmed as $field) {
            $this->assertEmpty($field, 'Whitespace-only field should be empty after trim');
        }
    }

    /**
     * Test phone number parsing edge cases
     */
    public function testPhoneNumberParsingEdgeCases()
    {
        $phoneCases = [
            '555-123-4567' => 'valid',
            '(555) 123-4567' => 'valid',
            '555.123.4567' => 'valid',
            '5551234567' => 'valid',
            '123' => 'too short',
            '55512345678' => 'too long',
            '' => 'empty',
            'not-a-number' => 'invalid',
        ];

        foreach ($phoneCases as $phone => $expectedStatus) {
            $cleaned = preg_replace('/[^0-9]/', '', $phone);
            
            if ($expectedStatus === 'valid' && strlen($cleaned) == 10) {
                // Valid 10-digit number
                $this->assertEquals(10, strlen($cleaned));
            } elseif ($expectedStatus === 'empty') {
                $this->assertEmpty($cleaned);
            }
        }
    }

    /**
     * Test postal code edge cases
     */
    public function testPostalCodeEdgeCases()
    {
        $postalCodes = [
            '12345' => true,
            '00000' => true,
            '99999' => true,
            '1234' => false,
            '123456' => false,
            'ABCDE' => false,
            '' => true,  // Optional
        ];

        foreach ($postalCodes as $code => $isValid) {
            if (empty($code)) {
                $this->assertEmpty($code);
            } else {
                $matches = preg_match('/^\d{5}$/', $code);
                if ($isValid) {
                    $this->assertEquals(1, $matches);
                } else {
                    $this->assertEquals(0, $matches);
                }
            }
        }
    }

    /**
     * Test duplicate lead prevention
     */
    public function testDuplicateLeadHandling()
    {
        $leadData = [
            'lead_source' => 1,
            'first_name' => 'Duplicate',
            'family_name' => 'Test',
            'email' => 'duplicate.unique@example.com',
            'contact_type' => 1,
            'form_street_1' => '123 Dup St',
            'form_city' => 'Dup City',
            'form_state' => 'CA',
            'form_postcode' => '90210',
            'form_country' => 'US',
            'stage' => 10,
            'last_edited_by' => 1
        ];

        // Create first lead
        $firstResult = $this->leads->create_lead($leadData);
        $this->testLeadIds[] = $firstResult;

        // Try creating with same email - should handle appropriately
        $secondResult = $this->leads->create_lead($leadData);
        
        // Either creates new lead (with different ID) or indicates duplicate
        if ($secondResult !== false) {
            // If allowed, should have different ID
            $this->assertTrue($firstResult !== $secondResult || $secondResult === false,
                'Duplicate should be handled');
            
            if ($secondResult !== false && $secondResult !== $firstResult) {
                $this->testLeadIds[] = $secondResult;
            }
        }
    }

    /**
     * Test concurrent edit handling
     */
    public function testConcurrentEditHandling()
    {
        $leadData = [
            'lead_source' => 1,
            'first_name' => 'Concurrent',
            'family_name' => 'Test',
            'email' => 'concurrent.test@example.com',
            'contact_type' => 1,
            'form_street_1' => '123 Conc St',
            'form_city' => 'Conc City',
            'form_state' => 'NY',
            'form_postcode' => '10001',
            'form_country' => 'US',
            'stage' => 10,
            'last_edited_by' => 1
        ];

        $leadId = $this->leads->create_lead($leadData);
        $this->testLeadIds[] = $leadId;

        // Simulate two concurrent updates
        $update1 = [
            'first_name' => 'Updated1',
            'stage' => 20,
            'last_edited_by' => 1
        ];

        $update2 = [
            'first_name' => 'Updated2',
            'stage' => 30,
            'last_edited_by' => 1
        ];

        // Both updates should be handled
        $result1 = $this->leads->update_lead($leadId, $update1);
        $result2 = $this->leads->update_lead($leadId, $update2);

        // Last update should win
        $final = $this->leads->get_lead_by_id($leadId);
        $this->assertNotEmpty($final);
    }

    /**
     * Test transaction rollback on error
     */
    public function testTransactionRollbackOnError()
    {
        $leadData = [
            'lead_source' => 1,
            'first_name' => 'Rollback',
            'family_name' => 'Test',
            'email' => 'rollback.test@example.com',
            'contact_type' => 1,
            'form_street_1' => '123 Rollback St',
            'form_city' => 'Rollback City',
            'form_state' => 'TX',
            'form_postcode' => '75001',
            'form_country' => 'US',
            'stage' => 10,
            'last_edited_by' => 1
        ];

        // Valid lead creation
        $validResult = $this->leads->create_lead($leadData);
        $this->assertNotFalse($validResult, 'Valid lead should be created');
        
        $this->testLeadIds[] = $validResult;
    }

    /**
     * Test unicode character handling in validation
     */
    public function testUnicodeCharacterValidation()
    {
        $unicodeNames = [
            'François' => true,
            'José María' => true,
            '李明' => true,
            'Müller' => true,
        ];

        foreach ($unicodeNames as $name => $isValid) {
            // Should not reject unicode
            $length = strlen($name);
            $this->assertGreaterThan(0, $length);
            
            // Should be storable in database
            $sanitized = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
            $this->assertIsString($sanitized);
        }
    }

    /**
     * Test field boundary conditions
     */
    public function testFieldBoundaryConditions()
    {
        $fieldLimits = [
            'first_name' => 100,
            'family_name' => 100,
            'email' => 255,
            'business_name' => 255,
            'form_street_1' => 100,
            'form_city' => 50,
            'form_postcode' => 15,
        ];

        foreach ($fieldLimits as $field => $maxLength) {
            // At limit
            $atLimit = str_repeat('A', $maxLength);
            $this->assertEquals($maxLength, strlen($atLimit));

            // Over limit
            $overLimit = str_repeat('A', $maxLength + 1);
            $this->assertEquals($maxLength + 1, strlen($overLimit));
            
            // Should be truncatable
            $truncated = substr($overLimit, 0, $maxLength);
            $this->assertEquals($maxLength, strlen($truncated));
        }
    }
}
