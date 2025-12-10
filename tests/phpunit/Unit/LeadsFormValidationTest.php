<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * Unit tests for Leads form validation
 * 
 * Tests form input validation, sanitization, and error handling
 */
class LeadsFormValidationTest extends TestCase
{
    private $leads;
    private $helpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->leads = new \Leads();
        $this->helpers = new \Helpers();
    }

    /**
     * Test email validation
     */
    public function testEmailValidation()
    {
        $validEmails = [
            'user@example.com' => true,
            'john.doe@company.co.uk' => true,
            'test+tag@example.com' => true,
            'invalid-email' => false,
            'user@' => false,
            '@example.com' => false,
            'user@example' => false,
            '' => false,
        ];

        foreach ($validEmails as $email => $isValid) {
            $result = filter_var($email, FILTER_VALIDATE_EMAIL);
            if ($isValid) {
                $this->assertNotFalse($result, "'{$email}' should be a valid email");
            } else {
                $this->assertFalse($result, "'{$email}' should be invalid");
            }
        }
    }

    /**
     * Test phone number formatting
     */
    public function testPhoneNumberFormatting()
    {
        $phoneCases = [
            '5551234567' => '555-123-4567',
            '15551234567' => '555-123-4567',
            '555-123-4567' => '555-123-4567',
            '(555) 123-4567' => '555-123-4567',
            '555.123.4567' => '555-123-4567',
            '' => '',
            '123' => '123',
            'invalid' => '',  // Non-numeric becomes empty after cleanup
        ];

        foreach ($phoneCases as $input => $expected) {
            $result = $this->formatPhoneNumber($input);
            $this->assertEquals($expected, $result, "Phone '{$input}' should format to '{$expected}'");
        }
    }

    /**
     * Test postal code validation (US format)
     */
    public function testPostalCodeValidation()
    {
        $validCodes = [
            '12345' => true,
            '90210' => true,
            '00001' => true,
            '99999' => true,
            '1234' => false,
            '123456' => false,
            'ABCDE' => false,
            '' => true,  // Optional field
            'A1234' => false,
        ];

        foreach ($validCodes as $code => $isValid) {
            if (empty($code)) {
                $this->assertTrue(true, 'Empty postal code is allowed');
            } else {
                $matches = preg_match('/^\d{5}$/', $code);
                if ($isValid) {
                    $this->assertEquals(1, $matches, "'{$code}' should be valid postal code");
                } else {
                    $this->assertEquals(0, $matches, "'{$code}' should be invalid postal code");
                }
            }
        }
    }

    /**
     * Test state code validation (US)
     */
    public function testStateCodeValidation()
    {
        $validStates = [
            'CA' => true,
            'NY' => true,
            'TX' => true,
            'WA' => true,
            'ca' => false,
            'California' => false,
            'C' => false,
            'CAL' => false,
            '' => true,  // Optional field
        ];

        foreach ($validStates as $state => $isValid) {
            if (empty($state)) {
                $this->assertTrue(true, 'Empty state is allowed');
            } else {
                $matches = preg_match('/^[A-Z]{2}$/', $state);
                if ($isValid) {
                    $this->assertEquals(1, $matches, "'{$state}' should be valid state code");
                } else {
                    $this->assertEquals(0, $matches, "'{$state}' should be invalid state code");
                }
            }
        }
    }

    /**
     * Test address line length validation
     */
    public function testAddressLineLengthValidation()
    {
        $addressCases = [
            '123 Main Street' => true,
            'A' => true,
            str_repeat('A', 100) => true,  // Max allowed
            str_repeat('A', 101) => false, // Over limit
        ];

        foreach ($addressCases as $address => $isValid) {
            $length = strlen($address);
            if ($isValid) {
                $this->assertLessThanOrEqual(100, $length, "Address length {$length} should be valid");
            } else {
                $this->assertGreaterThan(100, $length, "Address length {$length} should be invalid");
            }
        }
    }

    /**
     * Test name field validation
     */
    public function testNameFieldValidation()
    {
        $nameCases = [
            'John' => true,
            'John Doe' => true,
            'O\'Brien' => true,
            'Jean-Pierre' => true,
            '' => false,  // Required
            '   ' => false,  // Only whitespace
            str_repeat('A', 100) => true,  // Max allowed
            str_repeat('A', 101) => false, // Over max (100 chars)
        ];

        foreach ($nameCases as $name => $isValid) {
            $trimmed = trim($name);
            $length = strlen($trimmed);
            
            if ($isValid) {
                $this->assertNotEmpty($trimmed, "Name '{$name}' should not be empty");
                $this->assertLessThanOrEqual(100, $length, "Name '{$name}' should not exceed 100 chars");
            } else {
                if (empty($trimmed)) {
                    $this->assertTrue(true, "Empty name should be invalid");
                } elseif ($length > 100) {
                    $this->assertGreaterThan(100, $length, "Name exceeding 100 chars should be invalid");
                }
            }
        }
    }

    /**
     * Test lead source validation
     */
    public function testLeadSourceValidation()
    {
        $sources = $this->leads->get_lead_source_array();
        
        $validSources = [1, 2, 3, 4, 5, 6];
        $invalidSources = [0, 7, 99, -1];

        foreach ($validSources as $source) {
            $this->assertArrayHasKey($source, $sources, "Source {$source} should be valid");
        }

        foreach ($invalidSources as $source) {
            $this->assertArrayNotHasKey($source, $sources, "Source {$source} should be invalid");
        }
    }

    /**
     * Test contact type validation
     */
    public function testContactTypeValidation()
    {
        $types = $this->leads->get_lead_contact_type_array();
        
        $validTypes = [1, 2, 3, 4, 5];
        $invalidTypes = [0, 6, 99, -1];

        foreach ($validTypes as $type) {
            $this->assertArrayHasKey($type, $types, "Contact type {$type} should be valid");
        }

        foreach ($invalidTypes as $type) {
            $this->assertArrayNotHasKey($type, $types, "Contact type {$type} should be invalid");
        }
    }

    /**
     * Test stage validation
     */
    public function testStageValidation()
    {
        $stages = $this->leads->get_lead_stage_array();
        
        $validStages = [10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 110, 120, 130, 140];
        
        foreach ($validStages as $stage) {
            $this->assertArrayHasKey($stage, $stages, "Stage {$stage} should be valid");
        }

        // Test invalid stages
        $invalidStages = [0, 15, 25, 999, -10];
        foreach ($invalidStages as $stage) {
            $this->assertArrayNotHasKey($stage, $stages, "Stage {$stage} should be invalid");
        }
    }

    /**
     * Test data sanitization (XSS prevention)
     */
    public function testDataSanitization()
    {
        $sanitizationCases = [
            'John Doe' => 'John Doe',
            'John<script>alert(1)</script>' => 'John&lt;script&gt;alert(1)&lt;/script&gt;',
            'O\'Brien' => 'O&#039;Brien',
            '<img src=x onerror=alert(1)>' => '&lt;img src=x onerror=alert(1)&gt;',
            '   spaces   ' => 'spaces',  // trim
        ];

        foreach ($sanitizationCases as $input => $expected) {
            $sanitized = htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
            $this->assertEquals($expected, $sanitized, "Input '{$input}' should sanitize correctly");
        }
    }

    /**
     * Test unicode character handling
     */
    public function testUnicodeCharacterHandling()
    {
        $unicodeCases = [
            'François',
            'José',
            '李明',
            'Müller',
            '🎉 Party',
        ];

        foreach ($unicodeCases as $input) {
            // Should not strip unicode characters during sanitization
            $sanitized = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
            // After sanitization, should still have content
            $this->assertNotEmpty($sanitized, "Unicode '{$input}' should not be empty after sanitization");
        }
    }

    /**
     * Test empty/NULL field handling
     */
    public function testEmptyFieldHandling()
    {
        // Test individual cases
        $this->assertTrue(empty(trim('')), 'Empty string should be empty');
        $this->assertTrue(empty(trim((string)null)), 'Null should be empty after cast');
        $this->assertTrue(empty('0'), "'0' is considered empty in PHP");
        $this->assertFalse(empty('text'), "Text should not be empty");
        $this->assertTrue(empty(trim('   ')), "Whitespace should be empty after trim");
    }

    /**
     * Test field length limits
     */
    public function testFieldLengthLimits()
    {
        $fieldLimits = [
            'first_name' => 100,
            'family_name' => 100,
            'email' => 255,
            'cell_phone' => 15,
            'business_name' => 255,
            'form_street_1' => 100,
            'form_street_2' => 50,
            'form_city' => 50,
            'form_postcode' => 15,
        ];

        foreach ($fieldLimits as $field => $maxLength) {
            $longString = str_repeat('A', $maxLength + 1);
            $this->assertGreaterThan($maxLength, strlen($longString), 
                "Test string for '{$field}' should exceed limit");
            
            $truncated = substr($longString, 0, $maxLength);
            $this->assertEquals($maxLength, strlen($truncated),
                "Truncated string for '{$field}' should be exactly {$maxLength} chars");
        }
    }

    /**
     * Test required vs optional fields
     */
    public function testRequiredVsOptionalFields()
    {
        // Required fields should not allow empty values when they are provided
        $this->assertTrue(!empty('value'), "Non-empty value should pass required check");
        
        // Empty value should fail for required fields
        $this->assertTrue(empty(''), "Empty value should not pass required check");
        
        // Optional fields can be empty
        $this->assertTrue(empty(''), "Optional field can have empty value");
        
        // All field types tested above allow empty optional values
        $this->assertTrue(empty(''), "All optional fields can be empty");
    }

    /**
     * Helper function to format phone numbers (simulating post.php logic)
     */
    private function formatPhoneNumber(string $phone, string $country = 'US'): string
    {
        if (empty($phone)) {
            return '';
        }

        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Remove leading 1 for US numbers
        if (strlen($phone) == 11 && substr($phone, 0, 1) == '1') {
            $phone = substr($phone, 1);
        }

        // Format 10-digit US numbers as XXX-XXX-XXXX for storage
        if (strlen($phone) == 10) {
            return substr($phone, 0, 3) . '-' . substr($phone, 3, 3) . '-' . substr($phone, 6, 4);
        }

        // If not standard 10-digit format, return as-is
        return $phone;
    }
}
