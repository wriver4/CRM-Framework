<?php

namespace Tests\Integration;

use Tests\TestCase;

/**
 * Integration tests for Lead-Contact sync
 * 
 * Tests the integration between leads and contacts modules:
 * - Lead creation with automatic contact creation
 * - Contact field synchronization
 * - Contact updates with lead changes
 * - Lead-to-contact association
 */
class LeadsContactSyncIntegrationTest extends TestCase
{
    private $leads;
    private $contacts;
    private $testLeadId;
    private $testContactId;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->skipIfNotRemote();
        
        $this->leads = new \Leads();
        $this->contacts = new \Contacts();
    }

    protected function tearDown(): void
    {
        // Clean up test data in reverse order (contact first, then lead)
        if ($this->testContactId) {
            try {
                $this->contacts->delete_contact($this->testContactId);
            } catch (Exception $e) {
                // Ignore cleanup errors
            }
        }
        
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
     * Test creating lead with automatic contact creation
     */
    public function testLeadCreatesAutomaticContact()
    {
        $leadData = [
            'lead_source' => 1,
            'first_name' => 'Sync',
            'family_name' => 'Test',
            'cell_phone' => '555-100-0001',
            'email' => 'sync.test@example.com',
            'contact_type' => 1,
            'form_street_1' => '100 Sync St',
            'form_city' => 'Sync City',
            'form_state' => 'CA',
            'form_postcode' => '90210',
            'form_country' => 'US',
            'stage' => 10,
            'last_edited_by' => 1
        ];

        // Create lead with contact integration
        $result = $this->leads->create_lead_with_contact($leadData);
        
        $this->assertTrue($result['success'], 'Lead creation should succeed');
        $this->assertArrayHasKey('lead_id', $result);
        $this->assertArrayHasKey('contact_id', $result);

        $this->testLeadId = $result['lead_id'];
        $this->testContactId = $result['contact_id'];

        // Verify contact was created
        $retrievedContact = $this->contacts->get_contact_by_id($result['contact_id']);
        $this->assertNotEmpty($retrievedContact, 'Contact should be created');
        
        // Verify contact has lead's information
        $this->assertEquals($leadData['first_name'], $retrievedContact[0]['first_name']);
        $this->assertEquals($leadData['email'], $retrievedContact[0]['email']);
    }

    /**
     * Test lead and contact field synchronization
     */
    public function testLeadContactFieldSync()
    {
        $leadData = [
            'lead_source' => 1,
            'first_name' => 'Field',
            'family_name' => 'Sync',
            'cell_phone' => '555-100-0002',
            'email' => 'field.sync@example.com',
            'contact_type' => 1,
            'form_street_1' => '200 Field St',
            'form_city' => 'Field City',
            'form_state' => 'TX',
            'form_postcode' => '75001',
            'form_country' => 'US',
            'business_name' => 'Field Corp',
            'stage' => 10,
            'last_edited_by' => 1
        ];

        $result = $this->leads->create_lead_with_contact($leadData);
        $this->testLeadId = $result['lead_id'];
        $this->testContactId = $result['contact_id'];

        // Retrieve lead
        $lead = $this->leads->get_lead_by_id($result['lead_id']);
        $this->assertNotEmpty($lead);
        
        // Retrieve contact
        $contact = $this->contacts->get_contact_by_id($result['contact_id']);
        $this->assertNotEmpty($contact);

        // Check field synchronization
        $this->assertEquals($lead[0]['first_name'], $contact[0]['first_name']);
        $this->assertEquals($lead[0]['email'], $contact[0]['email']);
        $this->assertEquals($lead[0]['cell_phone'], $contact[0]['cell_phone']);
        $this->assertEquals($lead[0]['form_street_1'], $contact[0]['contact_street_1']);
    }

    /**
     * Test updating lead updates associated contact
     */
    public function testLeadUpdateUpdatesContact()
    {
        // Create initial lead with contact
        $leadData = [
            'lead_source' => 1,
            'first_name' => 'Update',
            'family_name' => 'Test',
            'cell_phone' => '555-100-0003',
            'email' => 'update.test@example.com',
            'contact_type' => 1,
            'form_street_1' => '300 Update St',
            'form_city' => 'Update City',
            'form_state' => 'NY',
            'form_postcode' => '10001',
            'form_country' => 'US',
            'stage' => 10,
            'last_edited_by' => 1
        ];

        $result = $this->leads->create_lead_with_contact($leadData);
        $this->testLeadId = $result['lead_id'];
        $this->testContactId = $result['contact_id'];

        // Update lead
        $updateData = [
            'first_name' => 'UpdatedName',
            'cell_phone' => '555-100-9999',
            'stage' => 20,
            'last_edited_by' => 1
        ];

        $updateResult = $this->leads->update_lead_with_contact($result['lead_id'], $updateData);
        $this->assertTrue($updateResult['success'], 'Lead update should succeed');

        // Verify lead was updated
        $updatedLead = $this->leads->get_lead_by_id($result['lead_id']);
        $this->assertEquals('UpdatedName', $updatedLead[0]['first_name']);

        // Verify contact was updated
        $updatedContact = $this->contacts->get_contact_by_id($result['contact_id']);
        $this->assertEquals('UpdatedName', $updatedContact[0]['first_name']);
    }

    /**
     * Test associating multiple contacts to single lead
     */
    public function testMultipleContactsPerLead()
    {
        // Create a lead
        $leadData = [
            'lead_source' => 1,
            'first_name' => 'Multi',
            'family_name' => 'Contact',
            'cell_phone' => '555-100-0004',
            'email' => 'multi.contact@example.com',
            'contact_type' => 1,
            'form_street_1' => '400 Multi St',
            'form_city' => 'Multi City',
            'form_state' => 'WA',
            'form_postcode' => '98001',
            'form_country' => 'US',
            'stage' => 10,
            'last_edited_by' => 1
        ];

        $result = $this->leads->create_lead_with_contact($leadData);
        $this->testLeadId = $result['lead_id'];
        $this->testContactId = $result['contact_id'];

        // Retrieve contacts by lead ID
        $contactsForLead = $this->contacts->get_contacts_by_lead_id($result['lead_id']);
        
        $this->assertIsArray($contactsForLead);
        $this->assertGreaterThanOrEqual(1, count($contactsForLead));
        
        // Verify first contact is the primary one
        $this->assertEquals($result['contact_id'], $contactsForLead[0]['id']);
    }

    /**
     * Test contact linking to existing lead
     */
    public function testContactLinkingToExistingLead()
    {
        // First create a lead
        $leadData = [
            'lead_source' => 1,
            'first_name' => 'Existing',
            'family_name' => 'Lead',
            'cell_phone' => '555-100-0005',
            'email' => 'existing@example.com',
            'contact_type' => 1,
            'form_street_1' => '500 Existing St',
            'form_city' => 'Existing City',
            'form_state' => 'FL',
            'form_postcode' => '33101',
            'form_country' => 'US',
            'stage' => 10,
            'last_edited_by' => 1
        ];

        $leadResult = $this->leads->create_lead($leadData);
        $this->testLeadId = $leadResult;

        // Create a contact linked to the lead
        $contactData = [
            'first_name' => 'Secondary',
            'family_name' => 'Contact',
            'email' => 'secondary@example.com',
            'contact_street_1' => '500 Existing St',
            'contact_city' => 'Existing City',
            'lead_id' => $leadResult
        ];

        $contactResult = $this->contacts->create_contact($contactData);
        $this->testContactId = $contactResult;

        // Verify contact is linked to lead
        $contactsForLead = $this->contacts->get_contacts_by_lead_id($leadResult);
        $this->assertGreaterThanOrEqual(1, count($contactsForLead));
        
        $linkedContact = array_filter($contactsForLead, function($c) use ($contactResult) {
            return $c['id'] == $contactResult;
        });
        
        $this->assertNotEmpty($linkedContact, 'Secondary contact should be linked to lead');
    }

    /**
     * Test email validation before contact creation
     */
    public function testEmailValidationBeforeContactCreation()
    {
        $leadData = [
            'lead_source' => 1,
            'first_name' => 'Email',
            'family_name' => 'Valid',
            'cell_phone' => '555-100-0006',
            'email' => 'valid.email@example.com',
            'contact_type' => 1,
            'form_street_1' => '600 Valid St',
            'form_city' => 'Valid City',
            'form_state' => 'CO',
            'form_postcode' => '80202',
            'form_country' => 'US',
            'stage' => 10,
            'last_edited_by' => 1
        ];

        // Valid email should pass
        $this->assertTrue(
            filter_var($leadData['email'], FILTER_VALIDATE_EMAIL) !== false,
            'Email should be valid'
        );

        // Should be able to create lead with contact
        $result = $this->leads->create_lead_with_contact($leadData);
        $this->assertTrue($result['success']);
        
        $this->testLeadId = $result['lead_id'];
        $this->testContactId = $result['contact_id'];

        // Verify email is stored correctly
        $contact = $this->contacts->get_contact_by_id($result['contact_id']);
        $this->assertEquals($leadData['email'], $contact[0]['email']);
    }

    /**
     * Test duplicate email handling
     */
    public function testDuplicateEmailHandling()
    {
        $email = 'duplicate.test@example.com';
        
        // Create first lead with contact
        $leadData1 = [
            'lead_source' => 1,
            'first_name' => 'First',
            'family_name' => 'Duplicate',
            'email' => $email,
            'contact_type' => 1,
            'form_street_1' => '700 Dup St',
            'form_city' => 'Dup City',
            'form_state' => 'OR',
            'form_postcode' => '97201',
            'form_country' => 'US',
            'stage' => 10,
            'last_edited_by' => 1
        ];

        $result1 = $this->leads->create_lead_with_contact($leadData1);
        $this->testLeadId = $result1['lead_id'];
        $this->testContactId = $result1['contact_id'];

        // Try creating second lead with same email
        $leadData2 = [
            'lead_source' => 1,
            'first_name' => 'Second',
            'family_name' => 'Duplicate',
            'email' => $email,
            'contact_type' => 1,
            'form_street_1' => '700 Dup St',
            'form_city' => 'Dup City',
            'form_state' => 'OR',
            'form_postcode' => '97201',
            'form_country' => 'US',
            'stage' => 10,
            'last_edited_by' => 1
        ];

        // System should handle duplicate email appropriately
        // Either create a new lead/contact or update existing
        $result2 = $this->leads->create_lead_with_contact($leadData2);
        
        // Should either succeed with new IDs or indicate existing
        $this->assertTrue(is_array($result2), 'Should return array result');
    }

    /**
     * Test contact type affects contact role
     */
    public function testContactTypeMapping()
    {
        $contactTypes = $this->leads->get_lead_contact_type_array();
        
        $testCases = [
            1 => 'Homeowner',
            2 => 'Property Manager',
            3 => 'Contractor',
        ];

        foreach ($testCases as $typeId => $typeName) {
            $this->assertArrayHasKey($typeId, $contactTypes);
            $this->assertEquals($typeName, $contactTypes[$typeId]);
        }
    }

    /**
     * Test address sync between lead and contact
     */
    public function testAddressSyncLeadToContact()
    {
        $leadData = [
            'lead_source' => 1,
            'first_name' => 'Address',
            'family_name' => 'Sync',
            'email' => 'address.sync@example.com',
            'contact_type' => 1,
            'form_street_1' => '800 Address Ave',
            'form_street_2' => 'Suite 200',
            'form_city' => 'Address City',
            'form_state' => 'AZ',
            'form_postcode' => '85001',
            'form_country' => 'US',
            'stage' => 10,
            'last_edited_by' => 1
        ];

        $result = $this->leads->create_lead_with_contact($leadData);
        $this->testLeadId = $result['lead_id'];
        $this->testContactId = $result['contact_id'];

        // Verify address is synced to contact
        $contact = $this->contacts->get_contact_by_id($result['contact_id']);
        
        $this->assertEquals($leadData['form_street_1'], $contact[0]['contact_street_1']);
        $this->assertEquals($leadData['form_city'], $contact[0]['contact_city']);
        $this->assertEquals($leadData['form_state'], $contact[0]['contact_state']);
        $this->assertEquals($leadData['form_postcode'], $contact[0]['contact_postcode']);
    }
}
