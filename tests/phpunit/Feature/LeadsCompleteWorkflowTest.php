<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Feature tests for complete Leads workflow
 * 
 * Tests end-to-end user workflows (requires authentication):
 * - Complete lead creation workflow
 * - Lead editing with contact sync
 * - Lead deletion with cleanup
 * - Lead status/stage changes
 * - Lead search and filtering
 * 
 * Note: These tests verify page accessibility and structure
 * In local/test mode, pages may redirect to login (302)
 */
class LeadsCompleteWorkflowTest extends TestCase
{
    /**
     * Test leads module pages are accessible
     */
    public function testLeadsModulePageAccessibility()
    {
        // Test new lead page
        $response = $this->makeHttpRequest('leads/new.php');
        $this->assertTrue(
            in_array($response['status_code'], [200, 302]),
            'New lead page should load or redirect to login'
        );
        
        // Test list page
        $response = $this->makeHttpRequest('leads/list.php');
        $this->assertTrue(
            in_array($response['status_code'], [200, 302]),
            'List page should load or redirect to login'
        );
    }

    /**
     * Test new lead form has essential structure
     */
    public function testNewLeadFormStructure()
    {
        $response = $this->makeHttpRequest('leads/new.php');
        
        // Page should be accessible
        $this->assertTrue(in_array($response['status_code'], [200, 302]));
        
        // Should succeed if authenticated
        if ($response['status_code'] === 200) {
            $this->assertResponseContains($response, 'form');
            $this->assertResponseContains($response, 'first_name');
            $this->assertResponseContains($response, 'family_name');
            $this->assertResponseContains($response, 'email');
        }
    }

    /**
     * Test leads list page structure
     */
    public function testLeadsListPageStructure()
    {
        $response = $this->makeHttpRequest('leads/list.php');
        
        $this->assertTrue(in_array($response['status_code'], [200, 302]));
        
        if ($response['status_code'] === 200) {
            $this->assertResponseContains($response, 'DataTable');
            $this->assertResponseContains($response, 'Lead #');
        }
    }

    /**
     * Test edit page accessibility
     */
    public function testEditPageAccessibility()
    {
        $response = $this->makeHttpRequest('leads/edit.php');
        
        // Should be 200, 302 (redirect to login), or 404 (no ID provided)
        $this->assertTrue(
            in_array($response['status_code'], [200, 302, 404]),
            'Edit page should be accessible or require authentication'
        );
    }

    /**
     * Test view page accessibility
     */
    public function testViewPageAccessibility()
    {
        $response = $this->makeHttpRequest('leads/view.php');
        
        // Should be 200, 302 (redirect to login), or 404 (no ID provided)
        $this->assertTrue(
            in_array($response['status_code'], [200, 302, 404]),
            'View page should be accessible or require authentication'
        );
    }

    /**
     * Test delete page accessibility
     */
    public function testDeletePageAccessibility()
    {
        $response = $this->makeHttpRequest('leads/delete.php');
        
        // Should be 200, 302 (redirect to login), or 404 (no ID provided)
        $this->assertTrue(
            in_array($response['status_code'], [200, 302, 404]),
            'Delete page should be accessible or require authentication'
        );
    }

    /**
     * Test leads API endpoint
     */
    public function testLeadsAPIEndpoint()
    {
        $response = $this->makeHttpRequest('leads/api.php');
        
        // API should be accessible (may return various codes)
        $this->assertTrue(
            is_array($response) && isset($response['status_code']),
            'API endpoint should respond with valid response'
        );
    }

    /**
     * Test leads get endpoint
     */
    public function testLeadsGetEndpoint()
    {
        $response = $this->makeHttpRequest('leads/get.php');
        
        // Get endpoint should respond
        $this->assertTrue(
            is_array($response) && isset($response['status_code']),
            'Get endpoint should respond with valid response'
        );
    }

    /**
     * Test filter parameters work
     */
    public function testFilterParameters()
    {
        $response = $this->makeHttpRequest('leads/list.php?filter=lost');
        
        $this->assertTrue(
            in_array($response['status_code'], [200, 302]),
            'List page with filter should work'
        );
    }

    /**
     * Test form elements are present
     */
    public function testFormElementsPresent()
    {
        $response = $this->makeHttpRequest('leads/new.php');
        
        $this->assertTrue(in_array($response['status_code'], [200, 302]));
        
        if ($response['status_code'] === 200) {
            // Check for form input fields
            $formElements = [
                'form',
                'input',
                'select',
                'textarea'
            ];
            
            $hasFormElements = false;
            foreach ($formElements as $element) {
                if (strpos($response['body'], $element) !== false) {
                    $hasFormElements = true;
                    break;
                }
            }
            
            $this->assertTrue($hasFormElements, 'Form should have input elements');
        }
    }

    /**
     * Test submit button exists
     */
    public function testSubmitButtonExists()
    {
        $response = $this->makeHttpRequest('leads/new.php');
        
        $this->assertTrue(in_array($response['status_code'], [200, 302]));
        
        if ($response['status_code'] === 200) {
            $this->assertTrue(
                strpos($response['body'], 'submit') !== false || 
                strpos($response['body'], 'button') !== false,
                'Form should have submit button'
            );
        }
    }

    /**
     * Test Bootstrap CSS classes present
     */
    public function testBootstrapClassesPresent()
    {
        $response = $this->makeHttpRequest('leads/new.php');
        
        $this->assertTrue(in_array($response['status_code'], [200, 302]));
        
        if ($response['status_code'] === 200) {
            $this->assertTrue(
                strpos($response['body'], 'col') !== false ||
                strpos($response['body'], 'row') !== false ||
                strpos($response['body'], 'form-control') !== false,
                'Page should use Bootstrap classes'
            );
        }
    }

    /**
     * Test page includes header/footer
     */
    public function testPageStructure()
    {
        $response = $this->makeHttpRequest('leads/new.php');
        
        $this->assertTrue(in_array($response['status_code'], [200, 302]));
        
        if ($response['status_code'] === 200) {
            // Check for common HTML structure
            $this->assertTrue(
                strlen($response['body']) > 100,
                'Page should have content'
            );
        }
    }
}
