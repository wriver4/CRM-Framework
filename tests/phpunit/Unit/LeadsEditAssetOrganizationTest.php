<?php

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Leads Edit Asset Organization
 * 
 * Tests the refactored asset organization system ensuring proper:
 * - Footer template conditional loading
 * - Data injection functionality  
 * - Asset loading order
 * - Framework compliance
 */
class LeadsEditAssetOrganizationTest extends TestCase
{
    private $originalGlobals;
    private $footerPath;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Save original globals
        $this->originalGlobals = [
            'dir' => $_GET['dir'] ?? null,
            'page' => $_GET['page'] ?? null
        ];
        
        // Set the footer template path
        $this->footerPath = dirname(__DIR__, 3) . '/public_html/templates/footer.php';
        
        // Ensure footer.php exists
        $this->assertFileExists($this->footerPath, 'Footer template must exist for testing');
    }

    protected function tearDown(): void
    {
        // Restore original globals
        foreach ($this->originalGlobals as $key => $value) {
            if ($value === null) {
                unset($_GET[$key]);
            } else {
                $_GET[$key] = $value;
            }
        }
        
        parent::tearDown();
    }

    /**
     * Test footer template conditional loading for leads edit
     */
    public function testFooterTemplateConditionalLoading()
    {
        // Set the routing variables that trigger the conditional loading
        $_GET['dir'] = 'leads';
        $_GET['page'] = 'edit';
        
        // Mock the global variables that would be set in the edit page
        global $dir, $page, $internal_id, $selected_stage, $lead_id, $form_state, $form_country;
        global $leads, $helpers, $lang;
        
        $dir = 'leads';
        $page = 'edit';
        $internal_id = 1;
        $selected_stage = 20;
        $lead_id = 'LEAD-001';
        $form_state = 'CA';
        $form_country = 'US';
        
        // Mock the required objects
        $leads = $this->createMockLeads();
        $helpers = $this->createMockHelpers();
        $lang = $this->createMockLanguage();
        
        // Capture output from footer template
        ob_start();
        include $this->footerPath;
        $output = ob_get_clean();
        
        // Test that required scripts are loaded
        $this->assertStringContainsString('hide-empty-structure.js', $output, 
            'Footer should load hide-empty-structure.js for leads edit');
        
        $this->assertStringContainsString('contact-selector.js', $output, 
            'Footer should load contact-selector.js for leads edit');
        
        $this->assertStringContainsString('edit-leads.js', $output, 
            'Footer should load edit-leads.js for leads edit');
        
        // Test that data injection is present
        $this->assertStringContainsString('window.leadsEditData', $output, 
            'Footer should inject leadsEditData for leads edit');
        
        // Test correct loading order (data injection before main script)
        $dataInjectionPos = strpos($output, 'window.leadsEditData');
        $mainScriptPos = strpos($output, 'edit-leads.js');
        
        $this->assertLessThan($mainScriptPos, $dataInjectionPos, 
            'Data injection should come before main script');
    }

    /**
     * Test that other pages don't get leads edit assets
     */
    public function testNoConditionalLoadingForOtherPages()
    {
        // Test different dir/page combinations
        $testCases = [
            ['contacts', 'list'],
            ['leads', 'list'],
            ['leads', 'new'],
            ['calendar', 'index'],
            ['dashboard', 'index']
        ];
        
        foreach ($testCases as [$testDir, $testPage]) {
            $_GET['dir'] = $testDir;
            $_GET['page'] = $testPage;
            
            global $dir, $page;
            $dir = $testDir;
            $page = $testPage;
            
            ob_start();
            include $this->footerPath;
            $output = ob_get_clean();
            
            $this->assertStringNotContainsString('contact-selector.js', $output, 
                "contact-selector.js should not load for {$testDir}/{$testPage}");
            
            $this->assertStringNotContainsString('window.leadsEditData', $output, 
                "leadsEditData should not be injected for {$testDir}/{$testPage}");
        }
    }

    /**
     * Test data injection structure and content
     */
    public function testDataInjectionStructure()
    {
        $_GET['dir'] = 'leads';
        $_GET['page'] = 'edit';
        
        global $dir, $page, $internal_id, $selected_stage, $lead_id, $form_state, $form_country;
        global $leads, $helpers, $lang;
        
        $dir = 'leads';
        $page = 'edit';
        $internal_id = 123;
        $selected_stage = 30;
        $lead_id = 'LEAD-TEST';
        $form_state = 'NY';
        $form_country = 'US';
        
        $leads = $this->createMockLeads();
        $helpers = $this->createMockHelpers();
        $lang = $this->createMockLanguage();
        
        ob_start();
        include $this->footerPath;
        $output = ob_get_clean();
        
        // Test that all required data properties are injected
        $requiredProperties = [
            'leadId: 123',
            'selectedStage: 30',
            'leadIdText: \'LEAD-TEST\'',
            'clientState: \'NY\'',
            'clientCountry: \'US\'',
            'stageNames:',
            'usTimezones:',
            'countryTimezones:',
            'errorUnableDetectTimezone:',
            'textUnknown:',
            'errorUnableConvertTime:',
            'errorFailedLoadNotes:',
            'textFrom:'
        ];
        
        foreach ($requiredProperties as $property) {
            $this->assertStringContainsString($property, $output, 
                "Data injection should include property: {$property}");
        }
    }

    /**
     * Test asset loading order compliance
     */
    public function testAssetLoadingOrder()
    {
        $_GET['dir'] = 'leads';
        $_GET['page'] = 'edit';
        
        global $dir, $page;
        $dir = 'leads';
        $page = 'edit';
        
        ob_start();
        include $this->footerPath;
        $output = ob_get_clean();
        
        // Find positions of each asset
        $hideEmptyPos = strpos($output, 'hide-empty-structure.js');
        $contactSelectorPos = strpos($output, 'contact-selector.js');
        $dataInjectionPos = strpos($output, 'window.leadsEditData');
        $editLeadsPos = strpos($output, 'edit-leads.js');
        
        // Test correct loading order
        $this->assertLessThan($contactSelectorPos, $hideEmptyPos, 
            'hide-empty-structure.js should load before contact-selector.js');
        
        $this->assertLessThan($dataInjectionPos, $contactSelectorPos, 
            'contact-selector.js should load before data injection');
        
        $this->assertLessThan($editLeadsPos, $dataInjectionPos, 
            'Data injection should come before edit-leads.js');
    }

    /**
     * Test framework compliance - no inline JavaScript in page content
     */
    public function testFrameworkCompliance()
    {
        // This would normally require checking the actual edit.php file
        $editPagePath = dirname(__DIR__, 3) . '/public_html/leads/edit.php';
        
        if (file_exists($editPagePath)) {
            $pageContent = file_get_contents($editPagePath);
            
            // Test that there's no inline JavaScript in the page
            $this->assertStringNotContainsString('<script>', $pageContent, 
                'leads/edit.php should not contain inline script tags');
            
            // Test that the page ends properly with PHP tags
            $this->assertStringContainsString('require FOOTER;', $pageContent, 
                'leads/edit.php should require FOOTER template');
            
            // Test that there are no large blocks of JavaScript
            $this->assertLessThan(50, substr_count($pageContent, 'function'), 
                'leads/edit.php should not contain JavaScript functions');
        }
    }

    /**
     * Create mock Leads object
     */
    private function createMockLeads()
    {
        $mock = $this->createMock(stdClass::class);
        $mock->method('get_lead_stage_array')
              ->willReturn([
                  10 => 'New',
                  20 => 'Pre-Qualification',
                  30 => 'Qualified'
              ]);
        return $mock;
    }

    /**
     * Create mock Helpers object
     */
    private function createMockHelpers()
    {
        $mock = $this->createMock(stdClass::class);
        $mock->method('get_us_timezone_array')
              ->willReturn(['CA' => 'America/Los_Angeles', 'NY' => 'America/New_York']);
        $mock->method('get_country_timezone_array')
              ->willReturn(['US' => 'America/New_York']);
        return $mock;
    }

    /**
     * Create mock language array
     */
    private function createMockLanguage()
    {
        return [
            'error_unable_detect_timezone' => 'Unable to detect timezone',
            'text_unknown' => 'Unknown',
            'error_unable_convert_time' => 'Unable to convert time',
            'error_failed_load_notes' => 'Failed to load notes',
            'error_unknown_error' => 'Unknown error',
            'error_network_loading_notes' => 'Network error loading notes',
            'text_from' => 'from'
        ];
    }
}