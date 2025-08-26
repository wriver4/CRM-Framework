<?php

/**
 * Enhanced Leads class with Contact integration support
 * Extends the original Leads class functionality
 */

require_once 'Leads.php';
require_once 'ContactsEnhanced.php';

class LeadsEnhanced extends Leads
{
    private $contactsEnhanced;

    public function __construct()
    {
        parent::__construct();
        $this->contactsEnhanced = new ContactsEnhanced();
    }

    /**
     * Create a lead with automatic contact creation/linking
     * @param array $data Lead data
     * @return array Result with lead_id and contact_id
     */
    public function create_lead_with_contact($data)
    {
        try {
            // Start transaction
            $this->dbcrm()->beginTransaction();

            // 1. Create or find existing contact
            $contactId = $this->contactsEnhanced->create_contact_from_lead($data);
            
            if (!$contactId) {
                throw new Exception('Failed to create contact');
            }

            // 2. Add contact_id to lead data
            $data['contact_id'] = $contactId;

            // 3. Create the lead
            $leadCreated = $this->create_lead($data);
            
            if (!$leadCreated) {
                throw new Exception('Failed to create lead');
            }

            // 4. Get the lead ID
            $leadId = $this->dbcrm()->lastInsertId();

            // 5. If using many-to-many relationship, link contact to lead
            if ($this->table_exists('lead_contacts')) {
                $this->contactsEnhanced->link_contact_to_lead($leadId, $contactId, true, 'primary');
            }

            // Commit transaction
            $this->dbcrm()->commit();

            return [
                'success' => true,
                'lead_id' => $leadId,
                'contact_id' => $contactId,
                'message' => 'Lead and contact created successfully'
            ];

        } catch (Exception $e) {
            // Rollback transaction
            $this->dbcrm()->rollback();
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to create lead and contact'
            ];
        }
    }

    /**
     * Update lead with contact synchronization
     * @param int $id Lead ID
     * @param array $data Lead data
     * @return array Result
     */
    public function update_lead_with_contact($id, $data)
    {
        try {
            $this->dbcrm()->beginTransaction();

            // 1. Get current lead data
            $currentLead = $this->get_lead_by_id($id);
            if (empty($currentLead)) {
                throw new Exception('Lead not found');
            }
            $currentLead = $currentLead[0]; // get_lead_by_id returns array

            // 2. Update the lead
            $leadUpdated = $this->update_lead($id, $data);
            
            if (!$leadUpdated) {
                throw new Exception('Failed to update lead');
            }

            // 3. Update associated contact if contact data has changed
            if ($currentLead['contact_id']) {
                $contactData = $this->contactsEnhanced->extract_contact_data_from_lead($data);
                $this->contactsEnhanced->update_contact($currentLead['contact_id'], $contactData);
            } else {
                // If no contact is linked, create one
                $contactId = $this->contactsEnhanced->create_contact_from_lead($data);
                if ($contactId) {
                    // Update lead with contact_id
                    $this->update_lead($id, ['contact_id' => $contactId]);
                }
            }

            $this->dbcrm()->commit();

            return [
                'success' => true,
                'message' => 'Lead and contact updated successfully'
            ];

        } catch (Exception $e) {
            $this->dbcrm()->rollback();
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to update lead and contact'
            ];
        }
    }

    /**
     * Get lead with associated contact information
     * @param int $id Lead ID
     * @return array|false
     */
    public function get_lead_with_contact($id)
    {
        $sql = "SELECT 
                    l.*,
                    c.id as contact_id,
                    c.fullname as contact_fullname,
                    c.phones as contact_phones,
                    c.emails as contact_emails,
                    c.p_street_1, c.p_street_2, c.p_city, c.p_state, c.p_postcode, c.p_country,
                    c.business_name as contact_business_name
                FROM leads l
                LEFT JOIN contacts c ON l.contact_id = c.id
                WHERE l.id = :id";
        
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Get all leads with contact information
     * @param array $filters Optional filters
     * @return array
     */
    public function get_leads_with_contacts($filters = [])
    {
        $whereClause = '';
        $params = [];

        // Build WHERE clause based on filters
        if (!empty($filters)) {
            $conditions = [];
            
            if (isset($filters['stage'])) {
                $conditions[] = "l.stage = :stage";
                $params[':stage'] = $filters['stage'];
            }
            
            if (isset($filters['lead_source'])) {
                $conditions[] = "l.lead_source = :lead_source";
                $params[':lead_source'] = $filters['lead_source'];
            }
            
            if (isset($filters['search'])) {
                $conditions[] = "(l.first_name LIKE :search OR l.last_name LIKE :search OR l.email LIKE :search OR c.fullname LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }
            
            if (!empty($conditions)) {
                $whereClause = 'WHERE ' . implode(' AND ', $conditions);
            }
        }

        $sql = "SELECT 
                    l.*,
                    c.id as contact_id,
                    c.fullname as contact_fullname,
                    c.phones as contact_phones,
                    c.emails as contact_emails,
                    c.business_name as contact_business_name
                FROM leads l
                LEFT JOIN contacts c ON l.contact_id = c.id
                {$whereClause}
                ORDER BY l.created_at DESC";
        
        $stmt = $this->dbcrm()->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Migrate existing leads to create contacts
     * This method should be run once to migrate existing data
     * @param int $limit Number of leads to process at once
     * @return array Migration results
     */
    public function migrate_leads_to_contacts($limit = 100)
    {
        $results = [
            'processed' => 0,
            'created_contacts' => 0,
            'linked_contacts' => 0,
            'errors' => []
        ];

        try {
            // Get leads without contact_id
            $sql = "SELECT * FROM leads WHERE contact_id IS NULL LIMIT :limit";
            $stmt = $this->dbcrm()->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $leads = $stmt->fetchAll();

            foreach ($leads as $lead) {
                try {
                    $this->dbcrm()->beginTransaction();

                    // Create contact from lead data
                    $contactId = $this->contactsEnhanced->create_contact_from_lead($lead);
                    
                    if ($contactId) {
                        // Update lead with contact_id
                        $updateSql = "UPDATE leads SET contact_id = :contact_id WHERE id = :id";
                        $updateStmt = $this->dbcrm()->prepare($updateSql);
                        $updateStmt->bindParam(':contact_id', $contactId, PDO::PARAM_INT);
                        $updateStmt->bindParam(':id', $lead['id'], PDO::PARAM_INT);
                        $updateStmt->execute();

                        $results['created_contacts']++;
                        $results['linked_contacts']++;
                    }

                    $this->dbcrm()->commit();
                    $results['processed']++;

                } catch (Exception $e) {
                    $this->dbcrm()->rollback();
                    $results['errors'][] = "Lead ID {$lead['id']}: " . $e->getMessage();
                }
            }

        } catch (Exception $e) {
            $results['errors'][] = "Migration error: " . $e->getMessage();
        }

        return $results;
    }

    /**
     * Check if a table exists
     * @param string $tableName
     * @return bool
     */
    private function table_exists($tableName)
    {
        try {
            $sql = "SELECT 1 FROM {$tableName} LIMIT 1";
            $stmt = $this->dbcrm()->prepare($sql);
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Validate lead data with contact requirements
     * @param array $data
     * @return array Validation errors
     */
    public function validate_lead_with_contact_data($data)
    {
        // Start with parent validation
        $errors = $this->validate_lead_data($data);

        // Additional contact-specific validations
        if (empty($data['first_name']) && empty($data['last_name'])) {
            $errors[] = 'Either first name or last name is required for contact creation';
        }

        // Validate email format for contact
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email address is required for contact creation';
        }

        return $errors;
    }

    /**
     * Get contact information for a lead
     * @param int $leadId
     * @return array|false
     */
    public function get_lead_contact($leadId)
    {
        return $this->contactsEnhanced->get_primary_contact_by_lead_id($leadId);
    }

    /**
     * Search leads by contact information
     * @param string $searchTerm
     * @return array
     */
    public function search_leads_by_contact($searchTerm)
    {
        $sql = "SELECT DISTINCT l.*, c.fullname as contact_name
                FROM leads l
                LEFT JOIN contacts c ON l.contact_id = c.id
                WHERE c.fullname LIKE :search 
                   OR c.personal_email LIKE :search
                   OR c.business_email LIKE :search
                   OR c.cell_phone LIKE :search
                   OR c.business_phone LIKE :search
                ORDER BY l.created_at DESC";
        
        $stmt = $this->dbcrm()->prepare($sql);
        $searchParam = '%' . $searchTerm . '%';
        $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}