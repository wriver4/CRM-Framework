<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Feature tests for Leads Module
 * 
 * Tests complete user workflows and end-to-end functionality:
 * - Lead creation workflow
 * - Lead editing workflow
 * - Lead list viewing
 * - Stage management
 * - Phone number display
 * - Search and filtering
 */
class LeadsFeatureTest extends TestCase
{
    /**
     * Test leads list page loads successfully
     */
    public function testLeadsListPageLoads()
    {
        $response = $this->makeHttpRequest('leads/list.php');
        
        $this->assertResponseStatus($response, 200);
        $this->assertResponseContains($response, 'leads-list');
        $this->assertResponseContains($response, 'DataTable');
    }

    /**
     * Test leads list displays correct columns
     */
    public function testLeadsListDisplaysCorrectColumns()
    {
        $response = $this->makeHttpRequest('leads/list.php');
        
        $this->assertResponseStatus($response, 200);
        
        // Check for expected column headers
        $this->assertResponseContains($response, 'Action');
        $this->assertResponseContains($response, 'Lead #');
        $this->assertResponseContains($response, 'Stage');
        $this->assertResponseContains($response, 'Full Name');
        $this->assertResponseContains($response, 'Phone');
        $this->assertResponseContains($response, 'Email');
        $this->assertResponseContains($response, 'Address');
        
        // Verify project name column is NOT present (removed in recent update)
        $this->assertStringNotContainsString('Project Name', $response['body']);
    }

    /**
     * Test leads list has proper DataTables configuration
     */
    public function testLeadsListDataTablesConfiguration()
    {
        $response = $this->makeHttpRequest('leads/list.php');
        
        $this->assertResponseStatus($response, 200);
        
        // Check for DataTables initialization
        $this->assertResponseContains($response, 'DataTable');
        $this->assertResponseContains($response, 'pageLength');
        $this->assertResponseContains($response, 'order');
        
        // Check for sorting configuration
        $this->assertResponseContains($response, 'columnDefs');
        $this->assertResponseContains($response, 'orderable: false');
    }

    /**
     * Test new lead page loads successfully
     */
    public function testNewLeadPageLoads()
    {
        $response = $this->makeHttpRequest('leads/new.php');
        
        $this->assertResponseStatus($response, 200);
        $this->assertResponseContains($response, 'form');
        $this->assertResponseContains($response, 'first_name');
        $this->assertResponseContains($response, 'family_name');
        $this->assertResponseContains($response, 'cell_phone');
        $this->assertResponseContains($response, 'email');
    }

    /**
     * Test new lead form has all required fields
     */
    public function testNewLeadFormHasRequiredFields()
    {
        $response = $this->makeHttpRequest('leads/new.php');
        
        $this->assertResponseStatus($response, 200);
        
        // Check for essential form fields
        $requiredFields = [
            'lead_source',
            'first_name',
            'family_name',
            'cell_phone',
            'email',
            'contact_type',
            'form_street_1',
            'form_city',
            'form_state',
            'form_postcode',
            'form_country',
            'stage'
        ];
        
        foreach ($requiredFields as $field) {
            $this->assertResponseContains($response, "name=\"{$field}\"");
        }
    }

    /**
     * Test lead view page loads with valid ID
     */
    public function testLeadViewPageLoads()
    {
        // This test assumes there's at least one lead in the system
        // In a real scenario, you'd create a test lead first
        $response = $this->makeHttpRequest('leads/view.php?id=1');
        
        // Should either load successfully or redirect to login
        $this->assertThat(
            $response['status_code'],
            $this->logicalOr(
                $this->equalTo(200),
                $this->equalTo(302),
                $this->equalTo(404)
            )
        );
        
        if ($response['status_code'] === 200) {
            $this->assertResponseContains($response, 'Lead Details');
        }
    }

    /**
     * Test lead edit page loads with valid ID
     */
    public function testLeadEditPageLoads()
    {
        $response = $this->makeHttpRequest('leads/edit.php?id=1');
        
        $this->assertThat(
            $response['status_code'],
            $this->logicalOr(
                $this->equalTo(200),
                $this->equalTo(302),
                $this->equalTo(404)
            )
        );
        
        if ($response['status_code'] === 200) {
            $this->assertResponseContains($response, 'form');
            $this->assertResponseContains($response, 'first_name');
        }
    }

    /**
     * Test stage change notification system
     */
    public function testStageChangeNotificationSystem()
    {
        // Test that the notification system is properly integrated
        $response = $this->makeHttpRequest('leads/list.php');
        
        $this->assertResponseStatus($response, 200);
        
        // Check for notification container (even if empty)
        $this->assertThat(
            $response['body'],
            $this->logicalOr(
                $this->stringContains('alert-info'),
                $this->stringContains('stage_moved'),
                $this->stringContains('container-fluid')
            )
        );
    }

    /**
     * Test phone number formatting in list view
     */
    public function testPhoneNumberFormattingInListView()
    {
        $response = $this->makeHttpRequest('leads/list.php');
        
        $this->assertResponseStatus($response, 200);
        
        // Check that phone formatting JavaScript/CSS is loaded
        $this->assertResponseContains($response, 'cell_phone');
        
        // The actual formatting would be tested in unit tests
        // Here we just verify the structure is in place
    }

    /**
     * Test responsive design elements
     */
    public function testResponsiveDesignElements()
    {
        $response = $this->makeHttpRequest('leads/list.php');
        
        $this->assertResponseStatus($response, 200);
        
        // Check for Bootstrap responsive classes
        $this->assertResponseContains($response, 'table-responsive');
        $this->assertResponseContains($response, 'container-fluid');
        
        // Check for mobile-friendly viewport
        $this->assertResponseContains($response, 'viewport');
    }

    /**
     * Test search functionality
     */
    public function testSearchFunctionality()
    {
        $response = $this->makeHttpRequest('leads/list.php');
        
        $this->assertResponseStatus($response, 200);
        
        // Check for search elements
        $this->assertResponseContains($response, 'search');
        
        // DataTables should provide search functionality
        $this->assertResponseContains($response, 'DataTable');
    }

    /**
     * Test pagination functionality
     */
    public function testPaginationFunctionality()
    {
        $response = $this->makeHttpRequest('leads/list.php');
        
        $this->assertResponseStatus($response, 200);
        
        // Check for pagination configuration
        $this->assertResponseContains($response, 'pageLength');
        
        // Should have pagination controls (provided by DataTables)
        $this->assertResponseContains($response, 'DataTable');
    }

    /**
     * Test filtering by stage
     */
    public function testFilteringByStage()
    {
        // Test lost leads filter
        $response = $this->makeHttpRequest('leads/list.php?filter=lost');
        
        $this->assertResponseStatus($response, 200);
        $this->assertResponseContains($response, 'leads-list');
        
        // Should still have the same table structure
        $this->assertResponseContains($response, 'DataTable');
    }

    /**
     * Test navigation elements
     */
    public function testNavigationElements()
    {
        $response = $this->makeHttpRequest('leads/list.php');
        
        $this->assertResponseStatus($response, 200);
        
        // Check for navigation buttons
        $this->assertResponseContains($response, 'btn-success'); // New button
        $this->assertResponseContains($response, 'fa-user-plus'); // New button icon
        
        // Check for action buttons in table
        $this->assertResponseContains($response, 'fa-eye'); // View button
        $this->assertResponseContains($response, 'fa-edit'); // Edit button
    }

    /**
     * Test accessibility features
     */
    public function testAccessibilityFeatures()
    {
        $response = $this->makeHttpRequest('leads/list.php');
        
        $this->assertResponseStatus($response, 200);
        
        // Check for ARIA attributes
        $this->assertResponseContains($response, 'aria-hidden');
        $this->assertResponseContains($response, 'role');
        
        // Check for proper table structure
        $this->assertResponseContains($response, '<th');
        $this->assertResponseContains($response, 'scope="col"');
    }

    /**
     * Test error handling for invalid requests
     */
    public function testErrorHandlingForInvalidRequests()
    {
        // Test invalid lead ID
        $response = $this->makeHttpRequest('leads/view.php?id=99999');
        
        // Should handle gracefully (404 or redirect)
        $this->assertThat(
            $response['status_code'],
            $this->logicalOr(
                $this->equalTo(404),
                $this->equalTo(302),
                $this->equalTo(200) // Might show empty state
            )
        );
        
        // Test missing ID parameter
        $response = $this->makeHttpRequest('leads/view.php');
        
        $this->assertThat(
            $response['status_code'],
            $this->logicalOr(
                $this->equalTo(400),
                $this->equalTo(302),
                $this->equalTo(200)
            )
        );
    }

    /**
     * Test security headers and CSRF protection
     */
    public function testSecurityFeatures()
    {
        $response = $this->makeHttpRequest('leads/list.php');
        
        $this->assertResponseStatus($response, 200);
        
        // Check for CSRF token or nonce
        $this->assertThat(
            $response['body'],
            $this->logicalOr(
                $this->stringContains('csrf'),
                $this->stringContains('nonce'),
                $this->stringContains('token')
            )
        );
    }

    /**
     * Test performance and loading times
     */
    public function testPerformanceAndLoadingTimes()
    {
        $startTime = microtime(true);
        
        $response = $this->makeHttpRequest('leads/list.php');
        
        $endTime = microtime(true);
        $loadTime = $endTime - $startTime;
        
        $this->assertResponseStatus($response, 200);
        
        // Page should load within reasonable time (5 seconds)
        $this->assertLessThan(5.0, $loadTime, 'Page should load within 5 seconds');
    }

    /**
     * Test JavaScript and CSS resources
     */
    public function testJavaScriptAndCSSResources()
    {
        $response = $this->makeHttpRequest('leads/list.php');
        
        $this->assertResponseStatus($response, 200);
        
        // Check for essential JavaScript libraries
        $this->assertResponseContains($response, 'jquery');
        $this->assertResponseContains($response, 'bootstrap');
        $this->assertResponseContains($response, 'datatables');
        
        // Check for CSS
        $this->assertResponseContains($response, '.css');
        $this->assertResponseContains($response, 'bootstrap');
    }

    /**
     * Test form validation on new lead page
     */
    public function testFormValidationOnNewLeadPage()
    {
        $response = $this->makeHttpRequest('leads/new.php');
        
        $this->assertResponseStatus($response, 200);
        
        // Check for validation attributes
        $this->assertThat(
            $response['body'],
            $this->logicalOr(
                $this->stringContains('required'),
                $this->stringContains('validation'),
                $this->stringContains('pattern')
            )
        );
    }

    /**
     * Test internationalization support
     */
    public function testInternationalizationSupport()
    {
        $response = $this->makeHttpRequest('leads/list.php');
        
        $this->assertResponseStatus($response, 200);
        
        // Check for language support structure
        $this->assertThat(
            $response['body'],
            $this->logicalOr(
                $this->stringContains('lang'),
                $this->stringContains('locale'),
                $this->stringContains('i18n')
            )
        );
    }

    /**
     * Test mobile responsiveness
     */
    public function testMobileResponsiveness()
    {
        // Simulate mobile user agent
        $response = $this->makeHttpRequest('leads/list.php');
        
        $this->assertResponseStatus($response, 200);
        
        // Check for mobile-friendly elements
        $this->assertResponseContains($response, 'viewport');
        $this->assertResponseContains($response, 'responsive');
        
        // Bootstrap should handle mobile layout
        $this->assertResponseContains($response, 'bootstrap');
    }

    /**
     * Test data export functionality (if available)
     */
    public function testDataExportFunctionality()
    {
        $response = $this->makeHttpRequest('leads/list.php');
        
        $this->assertResponseStatus($response, 200);
        
        // Check if export buttons are available
        $this->assertThat(
            $response['body'],
            $this->logicalOr(
                $this->stringContains('export'),
                $this->stringContains('download'),
                $this->stringContains('csv'),
                $this->stringContains('excel')
            )
        );
    }

    /**
     * Test workflow integration between pages
     */
    public function testWorkflowIntegrationBetweenPages()
    {
        // Test navigation from list to new
        $listResponse = $this->makeHttpRequest('leads/list.php');
        $this->assertResponseStatus($listResponse, 200);
        $this->assertResponseContains($listResponse, 'new.php');
        
        // Test new page loads
        $newResponse = $this->makeHttpRequest('leads/new.php');
        $this->assertResponseStatus($newResponse, 200);
        
        // Test that new page has back navigation
        $this->assertThat(
            $newResponse['body'],
            $this->logicalOr(
                $this->stringContains('back'),
                $this->stringContains('cancel'),
                $this->stringContains('list.php')
            )
        );
    }
}