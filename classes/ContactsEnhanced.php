<?php

/**
 * Enhanced Contacts class with Lead integration support
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

class ContactsEnhanced extends Database
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create a contact from lead data
     * @param array $leadData Lead data array
     * @return int|false Contact ID on success, false on failure
     */
    public function create_contact_from_lead($leadData)
    {
        // Extract contact information from lead data
        $contactData = $this->extract_contact_data_from_lead($leadData);
        
        // Check if contact already exists by email
        $existingContact = $this->find_contact_by_email($contactData['personal_email']);
        if ($existingContact) {
            return $existingContact['id'];
        }
        
        return $this->create_contact($contactData);
    }

    /**
     * Create relationship in leads_contacts bridge table
     * @param int $leadId
     * @param int $contactId
     * @param string $relationshipType
     * @return bool
     */
    public function createLeadContactRelationship($leadId, $contactId, $relationshipType = 'primary')
    {
        try {
            $sql = "INSERT INTO leads_contacts (lead_id, contact_id, relationship_type, status, created_at, updated_at) 
                    VALUES (:lead_id, :contact_id, :relationship_type, 1, NOW(), NOW())";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':lead_id', $leadId, PDO::PARAM_INT);
            $stmt->bindValue(':contact_id', $contactId, PDO::PARAM_INT);
            $stmt->bindValue(':relationship_type', $relationshipType, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Bridge table creation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Extract contact data from lead data
     * @param array $leadData
     * @return array
     */
    private function extract_contact_data_from_lead($leadData)
    {
        // Map lead fields to contact fields
        $first_name = $leadData['first_name'] ?? '';
        $family_name = $leadData['family_name'] ?? '';
        $full_name = trim($first_name . ' ' . $family_name);
        
        // Handle phone numbers - store as JSON
        $phones = json_encode([
            '1' => $leadData['cell_phone'] ?? '', // Primary phone
            '2' => '', // Business phone (empty for leads)
            '3' => ''  // Alt phone (empty for leads)
        ]);
        
        // Handle emails - store as JSON
        $emails = json_encode([
            '1' => $leadData['email'] ?? '', // Personal email
            '2' => '', // Business email (empty for leads)
            '3' => ''  // Alt email (empty for leads)
        ]);
        
        return [
            'prop_id' => null, // Will be set when property is created
            'ctype' => $leadData['ctype'] ?? 1, // Use lead contact type
            'call_order' => 1, // Default primary contact
            'first_name' => $first_name,
            'family_name' => $family_name,
            'full_name' => $full_name,
            'cell_phone' => $leadData['cell_phone'] ?? '',
            'business_phone' => '',
            'alt_phone' => '',
            'phones' => $phones,
            'personal_email' => $leadData['email'] ?? '',
            'business_email' => '',
            'alt_email' => '',
            'emails' => $emails,
            // Personal address from lead form data
            'p_street_1' => $leadData['form_street_1'] ?? '',
            'p_street_2' => $leadData['form_street_2'] ?? '',
            'p_city' => $leadData['form_city'] ?? '',
            'p_state' => $leadData['form_state'] ?? '',
            'p_postcode' => $leadData['form_postcode'] ?? '',
            'p_country' => $leadData['form_country'] ?? 'US',
            // Business information
            'business_name' => $leadData['business_name'] ?? '',
            'b_street_1' => '',
            'b_street_2' => '',
            'b_city' => '',
            'b_state' => '',
            'b_postcode' => '',
            'b_country' => '',
            // Mailing address (same as personal initially)
            'm_street_1' => $leadData['form_street_1'] ?? '',
            'm_street_2' => $leadData['form_street_2'] ?? '',
            'm_city' => $leadData['form_city'] ?? '',
            'm_state' => $leadData['form_state'] ?? '',
            'm_postcode' => $leadData['form_postcode'] ?? '',
            'm_country' => $leadData['form_country'] ?? 'US',
            'status' => 1, // Active
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Find contact by email address
     * @param string $email
     * @return array|false
     */
    public function find_contact_by_email($email)
    {
        if (empty($email)) {
            return false;
        }
        
        $sql = "SELECT * FROM contacts WHERE 
                personal_email = :email OR 
                business_email = :email OR 
                alt_email = :email OR
                JSON_EXTRACT(emails, '$.\"1\"') = :email OR
                JSON_EXTRACT(emails, '$.\"2\"') = :email OR
                JSON_EXTRACT(emails, '$.\"3\"') = :email
                LIMIT 1";
        
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Find contact by phone number
     * @param string $phone
     * @return array|false
     */
    public function find_contact_by_phone($phone)
    {
        if (empty($phone)) {
            return false;
        }
        
        // Clean phone number for comparison
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        $sql = "SELECT * FROM contacts WHERE 
                REPLACE(REPLACE(REPLACE(cell_phone, '-', ''), '(', ''), ')', '') LIKE :phone OR
                REPLACE(REPLACE(REPLACE(business_phone, '-', ''), '(', ''), ')', '') LIKE :phone OR
                REPLACE(REPLACE(REPLACE(alt_phone, '-', ''), '(', ''), ')', '') LIKE :phone
                LIMIT 1";
        
        $stmt = $this->dbcrm()->prepare($sql);
        $searchPhone = '%' . $cleanPhone . '%';
        $stmt->bindValue(':phone', $searchPhone, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Create a new contact
     * @param array $data
     * @return int|false Contact ID on success, false on failure
     */
    public function create_contact($data)
    {
        $sql = "INSERT INTO contacts (
            prop_id, ctype, call_order, first_name, family_name, full_name, 
            cell_phone, business_phone, alt_phone, phones, 
            personal_email, business_email, alt_email, emails, 
            p_street_1, p_street_2, p_city, p_state, p_postcode, p_country, 
            business_name, b_street_1, b_street_2, b_city, b_state, b_postcode, b_country, 
            m_street_1, m_street_2, m_city, m_state, m_postcode, m_country,
            status, created_at, updated_at
        ) VALUES (
            :prop_id, :ctype, :call_order, :first_name, :family_name, :full_name,
            :cell_phone, :business_phone, :alt_phone, :phones,
            :personal_email, :business_email, :alt_email, :emails,
            :p_street_1, :p_street_2, :p_city, :p_state, :p_postcode, :p_country,
            :business_name, :b_street_1, :b_street_2, :b_city, :b_state, :b_postcode, :b_country,
            :m_street_1, :m_street_2, :m_city, :m_state, :m_postcode, :m_country,
            :status, :created_at, :updated_at
        )";
        
        $stmt = $this->dbcrm()->prepare($sql);
        
        // Bind parameters
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        if ($stmt->execute()) {
            return $this->dbcrm()->lastInsertId();
        }
        
        return false;
    }

    /**
     * Update contact information
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update_contact($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $sql = "UPDATE contacts SET 
            prop_id = :prop_id, ctype = :ctype, call_order = :call_order,
            first_name = :first_name, family_name = :family_name, full_name = :full_name,
            cell_phone = :cell_phone, business_phone = :business_phone, alt_phone = :alt_phone, phones = :phones,
            personal_email = :personal_email, business_email = :business_email, alt_email = :alt_email, emails = :emails,
            p_street_1 = :p_street_1, p_street_2 = :p_street_2, p_city = :p_city, p_state = :p_state, p_postcode = :p_postcode, p_country = :p_country,
            business_name = :business_name, b_street_1 = :b_street_1, b_street_2 = :b_street_2, b_city = :b_city, b_state = :b_state, b_postcode = :b_postcode, b_country = :b_country,
            m_street_1 = :m_street_1, m_street_2 = :m_street_2, m_city = :m_city, m_state = :m_state, m_postcode = :m_postcode, m_country = :m_country,
            status = :status, updated_at = :updated_at
        WHERE id = :id";
        
        $data['id'] = $id;
        $stmt = $this->dbcrm()->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        return $stmt->execute();
    }

    /**
     * Get contacts associated with a lead
     * @param int $leadId
     * @return array
     */
    public function get_contacts_by_lead_id($leadId)
    {
        $sql = "SELECT c.*, lc.is_primary, lc.contact_role 
                FROM contacts c
                INNER JOIN lead_contacts lc ON c.id = lc.contact_id
                WHERE lc.lead_id = :lead_id
                ORDER BY lc.is_primary DESC, c.call_order ASC";
        
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->bindValue(':lead_id', $leadId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get primary contact for a lead
     * @param int $leadId
     * @return array|false
     */
    public function get_primary_contact_by_lead_id($leadId)
    {
        $sql = "SELECT c.* 
                FROM contacts c
                INNER JOIN leads l ON c.id = l.contact_id
                WHERE l.id = :lead_id
                LIMIT 1";
        
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->bindValue(':lead_id', $leadId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    }

    /**
     * Link a contact to a lead
     * @param int $leadId
     * @param int $contactId
     * @param bool $isPrimary
     * @param string $role
     * @return bool
     */
    public function link_contact_to_lead($leadId, $contactId, $isPrimary = false, $role = 'primary')
    {
        // If this is primary, unset other primary contacts for this lead
        if ($isPrimary) {
            $this->unset_primary_contacts_for_lead($leadId);
        }
        
        $sql = "INSERT INTO lead_contacts (lead_id, contact_id, is_primary, contact_role)
                VALUES (:lead_id, :contact_id, :is_primary, :contact_role)
                ON DUPLICATE KEY UPDATE 
                is_primary = :is_primary, contact_role = :contact_role";
        
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->bindValue(':lead_id', $leadId, PDO::PARAM_INT);
        $stmt->bindValue(':contact_id', $contactId, PDO::PARAM_INT);
        $stmt->bindValue(':is_primary', $isPrimary, PDO::PARAM_BOOL);
        $stmt->bindValue(':contact_role', $role, PDO::PARAM_STR);
        
        return $stmt->execute();
    }

    /**
     * Unset primary contacts for a lead
     * @param int $leadId
     * @return bool
     */
    private function unset_primary_contacts_for_lead($leadId)
    {
        $sql = "UPDATE lead_contacts SET is_primary = 0 WHERE lead_id = :lead_id";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->bindValue(':lead_id', $leadId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    // Include all existing methods from original Contacts class
    public function get_active_list()
    {
        $sql = 'SELECT id, ctype, full_name, phones, call_order, emails from contacts WHERE status = 1';
        $stmt = $this->dbcrm()->query($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();
        return $results;
    }

    public function get_list()
    {
        $sql = 'SELECT id, ctype, full_name, phones, emails from contacts';
        $stmt = $this->dbcrm()->query($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();
        return $results;
    }

    public function get_by_id($id)
    {
        $sql = 'SELECT * from contacts where id = :id';
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result;
    }
    
    public function get_primary_contact_by_prop_id($prop_id)
    {
        $sql = 'SELECT * from contacts where prop_id = :prop_id';
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->bindValue(':prop_id', $prop_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result;
    }

    public function get_installer_nickname()
    {
        $sql = "SELECT b_nickname from installers";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        return $result;
    }

    public function get_installer_by_id($id)
    {
        $sql = 'SELECT * from installers where id = :id';
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result;
    }
}