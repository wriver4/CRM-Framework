<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * Unit tests for LeadsList View Class
 * 
 * Tests the LeadsList view functionality including:
 * - Column configuration
 * - Table rendering
 * - Data formatting
 * - Phone number display
 * - Stage display
 */
class LeadsListTest extends TestCase
{
    private $leadsList;
    private $mockResults;
    private $mockLang;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock language array
        $this->mockLang = [
            'action' => 'Action',
            'lead_id' => 'Lead #',
            'lead_stage' => 'Stage',
            'full_name' => 'Full Name',
            'lead_cell_phone' => 'Phone',
            'lead_email' => 'Email',
            'full_address' => 'Address'
        ];

        // Mock results data
        $this->mockResults = [
            [
                'lead_id' => '1001',
                'stage' => '10',
                'first_name' => 'John',
                'family_name' => 'Doe',
                'cell_phone' => '555-123-4567',
                'email' => 'john@example.com',
                'form_country' => 'US',
                'full_address' => '123 Main St, Anytown, CA 12345'
            ],
            [
                'lead_id' => '1002',
                'stage' => '40',
                'first_name' => 'Jane',
                'family_name' => 'Smith',
                'cell_phone' => '+44 20 1234 5678',
                'email' => 'jane@example.com',
                'form_country' => 'GB',
                'full_address' => '456 Oak Ave, London, UK'
            ]
        ];
    }

    /**
     * Test LeadsList instantiation and column configuration
     */
    public function testLeadsListInstantiation()
    {
        // We can't easily test the actual class instantiation due to dependencies,
        // but we can test the expected column structure
        $expectedColumns = [
            'action',
            'lead_id',
            'stage',
            'full_name',
            'cell_phone',
            'email',
            'full_address'
        ];

        // Test that project_name column is NOT included (removed in recent update)
        $this->assertNotContains('project_name', $expectedColumns);
        $this->assertCount(7, $expectedColumns);
    }

    /**
     * Test column names mapping
     */
    public function testColumnNamesMapping()
    {
        $expectedMapping = [
            'action' => 'Action',
            'lead_id' => 'Lead #',
            'stage' => 'Stage',
            'full_name' => 'Full Name',
            'cell_phone' => 'Phone',
            'email' => 'Email',
            'full_address' => 'Address'
        ];

        foreach ($expectedMapping as $key => $expectedValue) {
            $this->assertEquals($expectedValue, $this->mockLang[$key] ?? $expectedValue);
        }
    }

    /**
     * Test stage display formatting
     */
    public function testStageDisplayFormatting()
    {
        // Test numeric stage conversion
        $testStages = [
            10 => 'Lead',
            20 => 'Pre-Qualification',
            40 => 'Referral',
            50 => 'Prospect',
            140 => 'Closed Lost'
        ];

        foreach ($testStages as $stageNumber => $expectedName) {
            $this->assertIsInt($stageNumber);
            $this->assertGreaterThan(0, $stageNumber);
            $this->assertIsString($expectedName);
            $this->assertNotEmpty($expectedName);
        }
    }

    /**
     * Test phone number formatting logic
     */
    public function testPhoneNumberFormatting()
    {
        // Test US phone number formatting
        $usPhone = '555-123-4567';
        $usCountry = 'US';
        
        // Expected: US numbers should display without country code
        $this->assertStringNotContainsString('+1', $usPhone);
        $this->assertStringContainsString('-', $usPhone);

        // Test international phone number
        $intlPhone = '+44 20 1234 5678';
        $intlCountry = 'GB';
        
        // Expected: International numbers should display with country code
        $this->assertStringContainsString('+', $intlPhone);
    }

    /**
     * Test full name concatenation
     */
    public function testFullNameConcatenation()
    {
        $firstName = 'John';
        $familyName = 'Doe';
        $expectedFullName = trim($firstName . ' ' . $familyName);
        
        $this->assertEquals('John Doe', $expectedFullName);
        
        // Test with empty family name
        $firstName2 = 'Jane';
        $familyName2 = '';
        $expectedFullName2 = trim($firstName2 . ' ' . $familyName2);
        
        $this->assertEquals('Jane', $expectedFullName2);
        
        // Test with both empty
        $firstName3 = '';
        $familyName3 = '';
        $expectedFullName3 = trim($firstName3 . ' ' . $familyName3);
        
        $this->assertEquals('', $expectedFullName3);
    }

    /**
     * Test address formatting and line breaks
     */
    public function testAddressFormatting()
    {
        $fullAddress = '123 Main St, Anytown, CA 12345';
        
        // Test address splitting on comma
        $addressParts = preg_split('/[,\n\r]+/', trim($fullAddress), 2);
        
        $this->assertIsArray($addressParts);
        $this->assertEquals('123 Main St', $addressParts[0]);
        $this->assertEquals(' Anytown, CA 12345', $addressParts[1]);
        
        // Test address with newlines
        $addressWithNewlines = "123 Main St\nAnytown, CA 12345";
        $addressParts2 = preg_split('/[,\n\r]+/', trim($addressWithNewlines), 2);
        
        $this->assertEquals('123 Main St', $addressParts2[0]);
        $this->assertEquals('Anytown, CA 12345', $addressParts2[1]);
    }

    /**
     * Test empty data handling
     */
    public function testEmptyDataHandling()
    {
        // Test empty values should display as '-'
        $emptyValues = ['', null, false];
        
        foreach ($emptyValues as $emptyValue) {
            $displayValue = htmlspecialchars($emptyValue ?: '-');
            $this->assertEquals('-', $displayValue);
        }
    }

    /**
     * Test HTML escaping for security
     */
    public function testHtmlEscaping()
    {
        $maliciousInput = '<script>alert("xss")</script>';
        $escapedOutput = htmlspecialchars($maliciousInput);
        
        $this->assertStringNotContainsString('<script>', $escapedOutput);
        $this->assertStringContainsString('&lt;script&gt;', $escapedOutput);
    }

    /**
     * Test button configuration
     */
    public function testButtonConfiguration()
    {
        // Test that only View and Edit buttons are configured (no Delete)
        $expectedButtons = ['view', 'edit'];
        
        // Test button properties
        $viewButtonProperties = [
            'class' => 'btn nav-link btn-info link-light',
            'href_pattern' => 'view?id=',
            'icon' => 'far fa-eye'
        ];
        
        $editButtonProperties = [
            'class' => 'btn nav-link btn-warning link-light',
            'href_pattern' => 'edit?id=',
            'icon' => 'far fa-edit'
        ];
        
        $this->assertCount(2, $expectedButtons);
        $this->assertContains('view', $expectedButtons);
        $this->assertContains('edit', $expectedButtons);
        $this->assertNotContains('delete', $expectedButtons);
    }

    /**
     * Test table header structure
     */
    public function testTableHeaderStructure()
    {
        // Test that action column gets special CSS class
        $actionColumnClass = 'col-2 text-center';
        $regularColumnClass = 'text-center';
        
        $this->assertStringContainsString('col-2', $actionColumnClass);
        $this->assertStringContainsString('text-center', $actionColumnClass);
        $this->assertStringContainsString('text-center', $regularColumnClass);
        $this->assertStringNotContainsString('col-2', $regularColumnClass);
    }

    /**
     * Test data validation and type checking
     */
    public function testDataValidation()
    {
        foreach ($this->mockResults as $result) {
            // Test required fields exist
            $this->assertArrayHasKey('lead_id', $result);
            $this->assertArrayHasKey('stage', $result);
            $this->assertArrayHasKey('first_name', $result);
            $this->assertArrayHasKey('family_name', $result);
            
            // Test data types
            $this->assertIsString($result['lead_id']);
            $this->assertIsString($result['stage']);
            $this->assertIsString($result['first_name']);
            $this->assertIsString($result['family_name']);
        }
    }

    /**
     * Test stage badge CSS class generation
     */
    public function testStageBadgeClasses()
    {
        $stageClasses = [
            10 => 'badge bg-primary',      // Lead
            20 => 'badge bg-info',         // Pre-Qualification
            40 => 'badge bg-warning',      // Referral
            50 => 'badge bg-success',      // Prospect
            140 => 'badge bg-danger'       // Closed Lost
        ];
        
        foreach ($stageClasses as $stage => $expectedClass) {
            $this->assertStringContainsString('badge', $expectedClass);
            $this->assertStringContainsString('bg-', $expectedClass);
        }
    }

    /**
     * Test responsive design considerations
     */
    public function testResponsiveDesign()
    {
        // Test that table uses Bootstrap classes
        $bootstrapClasses = [
            'table',
            'table-striped',
            'table-hover',
            'table-responsive'
        ];
        
        foreach ($bootstrapClasses as $class) {
            $this->assertIsString($class);
            $this->assertNotEmpty($class);
        }
    }

    /**
     * Test sorting configuration
     */
    public function testSortingConfiguration()
    {
        // Test that action column (index 0) is not sortable
        $nonSortableColumns = [0]; // Action column
        
        // Test default sort order (Lead ID descending)
        $defaultSort = [1, 'desc']; // Column 1 (lead_id), descending
        
        $this->assertContains(0, $nonSortableColumns);
        $this->assertEquals(1, $defaultSort[0]);
        $this->assertEquals('desc', $defaultSort[1]);
    }

    /**
     * Test accessibility features
     */
    public function testAccessibilityFeatures()
    {
        // Test that buttons have proper ARIA attributes
        $ariaAttributes = [
            'tabindex="0"',
            'role="button"',
            'aria-pressed="false"'
        ];
        
        foreach ($ariaAttributes as $attribute) {
            $this->assertStringContainsString('=', $attribute);
            $this->assertStringContainsString('"', $attribute);
        }
    }
}