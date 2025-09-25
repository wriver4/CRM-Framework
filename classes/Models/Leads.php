<?php

/**
 * Leads class with Contact integration support
 * Consolidated from original Leads and LeadsEnhanced classes
 */

class Leads extends Database
{
    private $contacts;
    private $bridgeManager;

    public function __construct()
    {
        parent::__construct();
        $this->contacts = new Contacts();
        $this->bridgeManager = new LeadBridgeManager();
    }

    // Helper method to get lead source options (1-6)
    public function get_lead_source_array() {
        return [
            1 => 'Web',
            2 => 'Referral', 
            3 => 'Phone',
            4 => 'Email',
            5 => 'Trade Show',
            6 => 'Other'
        ];
    }

    // Helper method to get contact type options (1-5)
    public function get_lead_contact_type_array() {
        return [
            1 => 'Homeowner',
            2 => 'Property Manager',
            3 => 'Contractor',
            4 => 'Architect',
            5 => 'Other'
        ];
    }

    // Helper method to get lead stage options (new numbering system)
    public function get_lead_stage_array() {
        require_once dirname(__DIR__, 2) . '/scripts/stage_remapping.php';
        
        $stages = [];
        foreach (StageRemapping::getNewStageMapping() as $stageNumber => $data) {
            $stages[$stageNumber] = $data['name'];
        }
        return $stages;
    }

    // Helper method to get stage badge class
    public function get_stage_badge_class($stage_number) {
        require_once dirname(__DIR__, 2) . '/scripts/stage_remapping.php';
        
        $badgeClasses = StageRemapping::getStageBadgeClasses();
        return $badgeClasses[$stage_number] ?? 'badge bg-secondary';
    }

    // Helper method to get stage display name with multilingual support
    public function get_stage_display_name($stage_number, $lang = null) {
        // If $lang array is provided, use it for multilingual support
        if ($lang && isset($lang['stage_' . $stage_number])) {
            return $lang['stage_' . $stage_number];
        }
        
        // Otherwise fall back to English defaults
        $stages = $this->get_lead_stage_array();
        return $stages[$stage_number] ?? 'Unknown';
    }

    // Helper method to get all stages with multilingual support
    public function get_lead_stage_array_multilingual($lang = null) {
        require_once dirname(__DIR__, 2) . '/scripts/stage_remapping.php';
        
        $stages = [];
        foreach (StageRemapping::getNewStageMapping() as $stageNumber => $data) {
            $stages[$stageNumber] = $this->get_stage_display_name($stageNumber, $lang);
        }
        return $stages;
    }

    // Helper method to convert old text stages to numbers (for migration)
    public function convert_text_stage_to_number($text_stage) {
        require_once dirname(__DIR__, 2) . '/scripts/stage_remapping.php';
        
        $mapping = [
            'lead' => 10,
            'pre-qualification' => 20,
            'qualified' => 30,
            'referral' => 40,
            'prospect' => 50,
            'prelim design' => 60,
            'manufacturing estimate' => 70,
            'contractor estimate' => 80,
            'completed estimate' => 90,
            'prospect response' => 100,
            'closing conference' => 110,
            'potential client response' => 120,
            'contracting' => 150,
            'closed won' => 130,
            'closed lost' => 140
        ];
        
        return $mapping[strtolower(trim($text_stage))] ?? 10; // Default to Lead
    }

    // Helper method to get valid next stages for a given stage
    public function get_valid_next_stages($current_stage) {
        require_once dirname(__DIR__, 2) . '/scripts/stage_remapping.php';
        
        return StageRemapping::getNewStageProgressions()[$current_stage] ?? [];
    }

    // Helper method to check if a stage can be marked as lost
    public function can_be_marked_lost($stage_number) {
        // All stages except final states can be marked as lost
        return !in_array($stage_number, [130, 140]); // Can't mark Won or Lost as Lost again
    }

    // Helper method to check if a stage is a trigger stage (for future action implementation)
    public function is_trigger_stage($stage_number) {
        require_once dirname(__DIR__, 2) . '/scripts/stage_remapping.php';
        
        return in_array($stage_number, StageRemapping::getTriggerStages());
    }

    // Helper method to get stage category for grouping
    public function get_stage_category($stage_number) {
        require_once dirname(__DIR__, 2) . '/scripts/stage_remapping.php';
        
        $categories = StageRemapping::getStageCategories();
        
        foreach ($categories as $category => $stages) {
            if (in_array($stage_number, $stages)) {
                return $category;
            }
        }
        
        return 'unknown';
    }

    // Helper method to get structure type options (1-6)
    public function get_lead_structure_type_array() {
        return [
            1 => 'Residential - Existing',
            2 => 'Residential - New Construction',
            3 => 'Commercial - Existing', 
            4 => 'Commercial - New Construction',
            5 => 'Industrial',
            6 => 'Other'
        ];
    }

    public function create_lead($data) {
        // Build full_address if not provided
        if (empty($data['full_address'])) {
            $street1 = trim($data['form_street_1'] ?? '');
            $street2 = trim($data['form_street_2'] ?? '');
            $city = trim($data['form_city'] ?? '');
            $state = trim($data['form_state'] ?? '');
            $postcode = trim($data['form_postcode'] ?? '');
            $country = trim($data['form_country'] ?? '');

            $line1 = trim(implode(' ', array_filter([$street1, $street2], fn($v) => $v !== '')));

            $cityPart = $city;
            if ($city !== '' && ($state !== '' || $postcode !== '')) {
                $cityPart .= ',';
            }
            $statePost = trim($state . ($postcode !== '' ? ' ' . $postcode : ''));
            $line2 = trim(implode(' ', array_filter([$cityPart, $statePost], fn($v) => $v !== '')));

            $lines = [];
            if ($line1 !== '') { $lines[] = $line1; }
            if ($line2 !== '') { $lines[] = $line2; }
            if ($country !== '') { $lines[] = $country; }

            $data['full_address'] = implode("\n", $lines);
        }

        // Create base lead record (without bridge table fields)
        $lead_id = $this->create_base_lead($data);
        
        if ($lead_id) {
            // Create bridge table data
            $this->bridgeManager->updateLeadData($lead_id, $data);
        }
        
        return $lead_id;
    }

    /**
     * Create base lead record without bridge table data
     */
    private function create_base_lead($data) {
        $sql = "INSERT INTO leads (
            lead_source, first_name, family_name, cell_phone, email, contact_type, notes, 
            lead_id, business_name, form_street_1, form_street_2, form_city, form_state, form_postcode, form_country, timezone, full_address,
            services_interested_in, get_updates, stage, last_edited_by, full_name, contact_id
        ) VALUES (
            :lead_source, :first_name, :family_name, :cell_phone, :email, :contact_type, :notes,
            :lead_id, :business_name, :form_street_1, :form_street_2, :form_city, :form_state, :form_postcode, :form_country, :timezone, :full_address,
            :services_interested_in, :get_updates, :stage, :last_edited_by, :full_name, :contact_id
        )";
        
        // Define valid parameters for base lead table
        $validParams = [
            'lead_source', 'first_name', 'family_name', 'cell_phone', 'email', 'contact_type', 'notes',
            'lead_id', 'business_name', 'form_street_1', 'form_street_2', 'form_city', 
            'form_state', 'form_postcode', 'form_country', 'timezone', 'full_address',
            'services_interested_in', 'get_updates', 'stage', 'last_edited_by', 'full_name', 'contact_id'
        ];
        
        $stmt = $this->dbcrm()->prepare($sql);
        foreach ($data as $key => $value) {
            if (in_array($key, $validParams)) {
                $stmt->bindValue(':' . $key, $value);
            }
        }
        
        if ($stmt->execute()) {
            return $this->dbcrm()->lastInsertId();
        }
        
        return false;
    }

    public function get_leads() {
        // SQL to fetch all leads
        $sql = "SELECT * FROM leads";
        $stmt = $this->dbcrm()->query($sql);
        return $stmt->fetchAll();
    }

    public function get_leads_by_stage($stage) {
        // SQL to fetch leads by specific stage
        $sql = "SELECT * FROM leads WHERE stage = :stage ORDER BY updated_at DESC";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->bindValue(':stage', $stage, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function get_leads_by_stages($stages) {
        // SQL to fetch leads by multiple stages
        $placeholders = str_repeat('?,', count($stages) - 1) . '?';
        $sql = "SELECT * FROM leads WHERE stage IN ($placeholders) ORDER BY updated_at DESC";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute($stages);
        return $stmt->fetchAll();
    }

    public function get_lead_by_id($id) {
        // Get complete lead data with bridge tables
        $complete_data = $this->bridgeManager->getCompleteLeadData($id);
        
        // Return in array format for backward compatibility
        return $complete_data ? [$complete_data] : [];
    }

    /**
     * Get basic lead data without bridge tables (for performance)
     */
    public function get_lead_basic($id) {
        $sql = "SELECT * FROM leads WHERE id = :id";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function get_lead_by_lead_id($lead_id) {
        // SQL to fetch a lead by external lead_id (lead number)
        $sql = "SELECT * FROM leads WHERE lead_id = :lead_id";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->bindValue(':lead_id', $lead_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function update_lead($id, $data) {
        try {
            // Build full_address if not provided
            if (empty($data['full_address'])) {
                $street1 = trim($data['form_street_1'] ?? '');
                $street2 = trim($data['form_street_2'] ?? '');
                $city = trim($data['form_city'] ?? '');
                $state = trim($data['form_state'] ?? '');
                $postcode = trim($data['form_postcode'] ?? '');
                $country = trim($data['form_country'] ?? '');

                $line1 = trim(implode(' ', array_filter([$street1, $street2], fn($v) => $v !== '')));

                $cityPart = $city;
                if ($city !== '' && ($state !== '' || $postcode !== '')) {
                    $cityPart .= ',';
                }
                $statePost = trim($state . ($postcode !== '' ? ' ' . $postcode : ''));
                $line2 = trim(implode(' ', array_filter([$cityPart, $statePost], fn($v) => $v !== '')));

                $lines = [];
                if ($line1 !== '') { $lines[] = $line1; }
                if ($line2 !== '') { $lines[] = $line2; }
                if ($country !== '') { $lines[] = $country; }

                $data['full_address'] = implode("\n", $lines);
            }

            // Update base lead record
            $base_result = $this->update_base_lead($id, $data);
            
            if ($base_result) {
                // Update bridge table data
                $this->bridgeManager->updateLeadData($id, $data);
            }
            
            return $base_result;
            
        } catch (Exception $e) {
            // Log the error with form context
            $this->logFormError('admin_leads_edit', $e->getMessage(), $data);
            throw $e;
        }
    }

    /**
     * Update base lead record without bridge table data
     */
    private function update_base_lead($id, $data) {
        $sql = "UPDATE leads SET 
            lead_source = :lead_source, first_name = :first_name, family_name = :family_name, 
            cell_phone = :cell_phone, email = :email, contact_type = :contact_type,
            lead_id = :lead_id, business_name = :business_name, project_name = :project_name,
            form_street_1 = :form_street_1, form_street_2 = :form_street_2,
            form_city = :form_city, form_state = :form_state, form_postcode = :form_postcode, 
            form_country = :form_country, timezone = :timezone, full_address = :full_address,
            services_interested_in = :services_interested_in, get_updates = :get_updates,
            stage = :stage, last_edited_by = :last_edited_by, updated_at = CURRENT_TIMESTAMP, 
            full_name = :full_name
        WHERE id = :id";
        
        $data['id'] = $id;
        
        // Define valid parameters for base lead table
        $validParams = [
            'lead_source', 'first_name', 'family_name', 'cell_phone', 'email', 'contact_type',
            'lead_id', 'business_name', 'project_name', 'form_street_1', 'form_street_2', 'form_city', 
            'form_state', 'form_postcode', 'form_country', 'timezone', 'full_address',
            'services_interested_in', 'get_updates', 'stage', 'last_edited_by', 'full_name', 'id'
        ];
        
        // Prepare parameters for logging
        $logParameters = [];
        foreach ($validParams as $param) {
            if (isset($data[$param])) {
                $logParameters[$param] = $data[$param];
            } else {
                // Provide default values for missing parameters
                switch ($param) {
                    case 'lead_source':
                    case 'contact_type':
                        $logParameters[$param] = 1;
                        break;
                    case 'stage':
                        $logParameters[$param] = '1';
                        break;
                    case 'get_updates':
                        $logParameters[$param] = 0;
                        break;
                    case 'form_country':
                        $logParameters[$param] = 'US';
                        break;
                    default:
                        $logParameters[$param] = null;
                        break;
                }
            }
        }
        
        // Use the new logging system
        $context = [
            'operation' => 'update_lead',
            'lead_id' => $id,
            'form_source' => 'admin_leads_edit'
        ];
        
        return $this->prepareAndExecute($sql, $logParameters, $context);
    }

    public function delete_lead($id) {
        // SQL to delete a lead
        $sql = "DELETE FROM leads WHERE id = :id";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function get_last_lead_id() {
        // SQL to get the highest lead ID
        $sql = "SELECT MAX(CAST(lead_id AS UNSIGNED)) as max_lead FROM leads WHERE lead_id IS NOT NULL AND lead_id != ''";
        $stmt = $this->dbcrm()->query($sql);
        $result = $stmt->fetch();
        
        if ($result && !empty($result['max_lead'])) {
            return $result['max_lead'];
        }
        
        return 0;
    }

    // Validation method for lead data
    public function validate_lead_data($data) {
        $errors = [];
        
        // Required fields validation
        if (empty($data['first_name'])) {
            $errors[] = 'First name is required';
        }
        if (empty($data['family_name'])) {
            $errors[] = 'Family name is required';
        }
        if (empty($data['email'])) {
            $errors[] = 'Email is required';
        }
        
        // Email format validation
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
        
        // Validate lead_source is within range (1-6)
        if (!empty($data['lead_source']) && (!is_numeric($data['lead_source']) || $data['lead_source'] < 1 || $data['lead_source'] > 6)) {
            $errors[] = 'Invalid lead source';
        }
        
        // Validate contact_type is within range (1-5)
        if (!empty($data['contact_type']) && (!is_numeric($data['contact_type']) || $data['contact_type'] < 1 || $data['contact_type'] > 5)) {
            $errors[] = 'Invalid contact type';
        }
        
        // Validate structure_type is within range (1-6)
        if (!empty($data['structure_type']) && (!is_numeric($data['structure_type']) || $data['structure_type'] < 1 || $data['structure_type'] > 6)) {
            $errors[] = 'Invalid structure type';
        }
        
        // Validate stage is within valid range (new numbering system)
        if (!empty($data['stage']) && is_numeric($data['stage'])) {
            require_once dirname(__DIR__, 2) . '/scripts/stage_remapping.php';
            $validStages = array_keys(StageRemapping::getNewStageMapping());
            if (!in_array((int)$data['stage'], $validStages)) {
                $errors[] = 'Invalid stage';
            }
        } elseif (!empty($data['stage'])) {
            $errors[] = 'Stage must be numeric';
        }
        
        // Validate field length constraints
        if (!empty($data['first_name']) && strlen($data['first_name']) > 100) {
            $errors[] = 'First name too long (max 100 characters)';
        }
        if (!empty($data['family_name']) && strlen($data['family_name']) > 100) {
            $errors[] = 'Family name too long (max 100 characters)';
        }
        if (!empty($data['cell_phone']) && strlen($data['cell_phone']) > 15) {
            $errors[] = 'Phone number too long (max 15 characters)';
        }
        if (!empty($data['services_interested_in']) && strlen($data['services_interested_in']) > 20) {
            $errors[] = 'Services interested in too long (max 20 characters)';
        }
        if (!empty($data['hear_about']) && strlen($data['hear_about']) > 20) {
            $errors[] = 'Hear about field too long (max 20 characters)';
        }
        
        return $errors;
    }

    // Helper method to get leads with filters
    public function get_leads_filtered($filters = []) {
        $sql = "SELECT * FROM leads WHERE 1=1";
        $params = [];
        
        if (!empty($filters['stage'])) {
            $sql .= " AND stage = :stage";
            $params['stage'] = $filters['stage'];
        }
        
        if (!empty($filters['lead_source'])) {
            $sql .= " AND lead_source = :lead_source";
            $params['lead_source'] = $filters['lead_source'];
        }
        
        if (!empty($filters['structure_type'])) {
            $sql .= " AND structure_type = :structure_type";
            $params['structure_type'] = $filters['structure_type'];
        }
        
        if (!empty($filters['form_state'])) {
            $sql .= " AND form_state = :form_state";
            $params['form_state'] = $filters['form_state'];
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->dbcrm()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get all leads for list display (using new stage filtering)
    public function get_all_active($filters = []) {
        require_once dirname(__DIR__, 2) . '/scripts/stage_remapping.php';
        
        // Get leads module stages (10, 20, 30, 40, 50, 140)
        $moduleFilters = StageRemapping::getModuleStageFilters();
        $leadStages = $moduleFilters['leads'];
        $placeholders = str_repeat('?,', count($leadStages) - 1) . '?';
        
        $sql = "SELECT 
            id, lead_source, first_name, family_name, business_name, project_name, email, cell_phone, 
            stage, structure_type, contact_type, created_at, updated_at, last_edited_by,
            lead_id, form_street_1, form_city, form_state, form_postcode, full_address, contact_id
        FROM leads 
        WHERE stage IN ($placeholders)";
        
        $params = $leadStages;
        
        // Add filters if needed
        if (!empty($filters['stage'])) {
            $sql .= " AND stage = ?";
            $params[] = $filters['stage'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (first_name LIKE ? OR family_name LIKE ? OR email LIKE ? OR project_name LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY updated_at DESC";
        
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Get leads count for pagination
    public function get_count($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM leads WHERE 1=1";
        $params = [];
        
        if (!empty($filters['stage'])) {
            $sql .= " AND stage = :stage";
            $params['stage'] = $filters['stage'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (first_name LIKE :search OR family_name LIKE :search OR email LIKE :search OR project_name LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        $stmt = $this->dbcrm()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    // Get notes for a lead through the leads_notes bridge table
    public function get_lead_notes($lead_id) {
        $sql = "SELECT n.*, ln.date_linked 
                FROM notes n 
                INNER JOIN leads_notes ln ON n.id = ln.note_id 
                WHERE ln.lead_id = :lead_id 
                ORDER BY n.date_created DESC";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->bindValue(':lead_id', $lead_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get previous lead ID for navigation
    public function get_previous_lead_id($current_id) {
        $sql = "SELECT id FROM leads WHERE id < :current_id ORDER BY id DESC LIMIT 1";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->bindValue(':current_id', $current_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ? $result['id'] : null;
    }

    // Get next lead ID for navigation
    public function get_next_lead_id($current_id) {
        $sql = "SELECT id FROM leads WHERE id > :current_id ORDER BY id ASC LIMIT 1";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->bindValue(':current_id', $current_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ? $result['id'] : null;
    }

    // Get lead navigation info (previous, current, next)
    public function get_lead_navigation($current_id) {
        return [
            'previous' => $this->get_previous_lead_id($current_id),
            'current' => $current_id,
            'next' => $this->get_next_lead_id($current_id)
        ];
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
            $contactId = $this->contacts->create_contact_from_lead($data);
            
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
            if ($this->table_exists('leads_contacts')) {
                $this->contacts->link_contact_to_lead($leadId, $contactId, true, 'primary');
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
                $contactData = $this->contacts->extract_contact_data_from_lead($data);
                $this->contacts->update_contact($currentLead['contact_id'], $contactData);
            } else {
                // If no contact is linked, create one
                $contactId = $this->contacts->create_contact_from_lead($data);
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
                    c.full_name as contact_fullname,
                    c.phones as contact_phones,
                    c.emails as contact_emails,
                    c.p_street_1, c.p_street_2, c.p_city, c.p_state, c.p_postcode, c.p_country,
                    c.business_name as contact_business_name
                FROM leads l
                LEFT JOIN contacts c ON l.contact_id = c.id
                WHERE l.id = :id";
        
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
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
                $conditions[] = "(l.first_name LIKE :search OR l.family_name LIKE :search OR l.email LIKE :search OR c.full_name LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }
            
            if (!empty($conditions)) {
                $whereClause = 'WHERE ' . implode(' AND ', $conditions);
            }
        }

        $sql = "SELECT 
                    l.*,
                    c.id as contact_id,
                    c.full_name as contact_fullname,
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
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $leads = $stmt->fetchAll();

            foreach ($leads as $lead) {
                try {
                    $this->dbcrm()->beginTransaction();

                    // Create contact from lead data
                    $contactId = $this->contacts->create_contact_from_lead($lead);
                    
                    if ($contactId) {
                        // Update lead with contact_id
                        $updateSql = "UPDATE leads SET contact_id = :contact_id WHERE id = :id";
                        $updateStmt = $this->dbcrm()->prepare($updateSql);
                        $updateStmt->bindValue(':contact_id', $contactId, PDO::PARAM_INT);
                        $updateStmt->bindValue(':id', $lead['id'], PDO::PARAM_INT);
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
        if (empty($data['first_name']) && empty($data['family_name'])) {
            $errors[] = 'Either first name or family name is required for contact creation';
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
        return $this->contacts->get_primary_contact_by_lead_id($leadId);
    }

    /**
     * Search leads by contact information
     * @param string $searchTerm
     * @return array
     */
    public function search_leads_by_contact($searchTerm)
    {
        $sql = "SELECT DISTINCT l.*, c.full_name as contact_name
                FROM leads l
                LEFT JOIN contacts c ON l.contact_id = c.id
                WHERE c.full_name LIKE :search 
                   OR c.personal_email LIKE :search
                   OR c.business_email LIKE :search
                   OR c.cell_phone LIKE :search
                   OR c.business_phone LIKE :search
                ORDER BY l.created_at DESC";
        
        $stmt = $this->dbcrm()->prepare($sql);
        $searchParam = '%' . $searchTerm . '%';
        $stmt->bindValue(':search', $searchParam, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Update lead with contact_id
     * @param int $leadId
     * @param int $contactId
     * @return bool
     */
    public function updateLeadContactId($leadId, $contactId)
    {
        try {
            $sql = "UPDATE leads SET contact_id = :contact_id, updated_at = NOW() WHERE id = :lead_id";
            $stmt = $this->dbcrm()->prepare($sql);
            $stmt->bindValue(':contact_id', $contactId, PDO::PARAM_INT);
            $stmt->bindValue(':lead_id', $leadId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Lead contact ID update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a lead with enhanced data handling
     * @param array $leadData
     * @return int|false Lead ID on success, false on failure
     */
    public function createLead($leadData)
    {
        try {
            // Use existing create method from parent class
            $leadId = $this->create($leadData);
            return $leadId;
        } catch (Exception $e) {
            error_log("Lead creation error: " . $e->getMessage());
            return false;
        }
    }

    // ========================================
    // DASHBOARD ANALYTICS METHODS
    // ========================================

    /**
     * Get lead counts by stage for dashboard analytics
     * @return array Array of stage counts with stage names and numbers
     */
    public function getLeadCountsByStage()
    {
        try {
            $sql = "SELECT 
                        stage,
                        COUNT(*) as count
                    FROM leads 
                    GROUP BY stage 
                    ORDER BY 
                        CASE stage
                            WHEN '1' THEN 1
                            WHEN 'Lead' THEN 1
                            WHEN '2' THEN 2
                            WHEN 'Prospect' THEN 2
                            WHEN '3' THEN 3
                            WHEN 'Qualified' THEN 3
                            WHEN '4' THEN 4
                            WHEN 'Proposal' THEN 4
                            WHEN '5' THEN 5
                            WHEN 'Closing Conference' THEN 5
                            WHEN '6' THEN 6
                            WHEN 'Completed Estimate' THEN 6
                            WHEN '7' THEN 7
                            WHEN 'Closed Won' THEN 7
                            WHEN '8' THEN 8
                            WHEN 'Closed Lost' THEN 8
                            WHEN '9' THEN 9
                            WHEN 'Referral' THEN 9
                            ELSE 99
                        END";
            
            $stmt = $this->dbcrm()->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            // Convert stage names to numbers and consolidate counts
            $stageCountsMap = [];
            foreach ($results as $row) {
                $stageNumber = $this->normalizeStageToNumber($row['stage']);
                
                if (!isset($stageCountsMap[$stageNumber])) {
                    $stageCountsMap[$stageNumber] = 0;
                }
                $stageCountsMap[$stageNumber] += (int)$row['count'];
            }
            
            // Convert to final format with proper display names
            $stageCounts = [];
            foreach ($stageCountsMap as $stageNumber => $count) {
                $stageName = $this->get_stage_display_name($stageNumber);
                $stageCounts[] = [
                    'stage_number' => $stageNumber,
                    'stage_name' => $stageName,
                    'count' => $count,
                    'badge_class' => $this->get_stage_badge_class($stageNumber)
                ];
            }
            
            // Sort by stage number
            usort($stageCounts, function($a, $b) {
                return $a['stage_number'] <=> $b['stage_number'];
            });
            
            return $stageCounts;
        } catch (Exception $e) {
            error_log("Error getting lead counts by stage: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total lead count
     * @return int Total number of leads
     */
    public function getTotalLeadCount()
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM leads";
            $stmt = $this->dbcrm()->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            return (int)$result['total'];
        } catch (Exception $e) {
            error_log("Error getting total lead count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get lead counts by source for dashboard analytics
     * @return array Array of source counts
     */
    public function getLeadCountsBySource()
    {
        try {
            $sql = "SELECT 
                        lead_source,
                        COUNT(*) as count
                    FROM leads 
                    GROUP BY lead_source 
                    ORDER BY lead_source";
            
            $stmt = $this->dbcrm()->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            $sourceCounts = [];
            $sourceArray = $this->get_lead_source_array();
            
            foreach ($results as $row) {
                $sourceId = (int)$row['lead_source'];
                $sourceName = $sourceArray[$sourceId] ?? 'Unknown';
                
                $sourceCounts[] = [
                    'source_id' => $sourceId,
                    'source_name' => $sourceName,
                    'count' => (int)$row['count']
                ];
            }
            
            return $sourceCounts;
        } catch (Exception $e) {
            error_log("Error getting lead counts by source: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent leads activity for dashboard
     * @param int $limit Number of recent leads to return
     * @return array Array of recent leads
     */
    public function getRecentLeads($limit = 10)
    {
        try {
            $sql = "SELECT 
                        id,
                        lead_id,
                        full_name,
                        email,
                        stage,
                        created_at,
                        updated_at
                    FROM leads 
                    ORDER BY updated_at DESC 
                    LIMIT :limit";
            
            $stmt = $this->dbcrm()->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            // Enhance results with stage information
            foreach ($results as &$lead) {
                $stageNumber = $this->normalizeStageToNumber($lead['stage']);
                $lead['stage_number'] = $stageNumber;
                $lead['stage_name'] = $this->get_stage_display_name($stageNumber);
                $lead['badge_class'] = $this->get_stage_badge_class($stageNumber);
            }
            
            return $results;
        } catch (Exception $e) {
            error_log("Error getting recent leads: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get leads created in the last 30 days by day
     * @return array Array of daily lead counts
     */
    public function getLeadsLast30Days()
    {
        try {
            $sql = "SELECT 
                        DATE(created_at) as date,
                        COUNT(*) as count
                    FROM leads 
                    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                    GROUP BY DATE(created_at)
                    ORDER BY date";
            
            $stmt = $this->dbcrm()->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            // Fill in missing dates with 0 counts
            $dailyCounts = [];
            $startDate = new DateTime('-30 days');
            $endDate = new DateTime();
            
            // Create array with all dates initialized to 0
            $period = new DatePeriod($startDate, new DateInterval('P1D'), $endDate);
            foreach ($period as $date) {
                $dailyCounts[$date->format('Y-m-d')] = 0;
            }
            
            // Fill in actual counts
            foreach ($results as $row) {
                $dailyCounts[$row['date']] = (int)$row['count'];
            }
            
            // Convert to array format for charts
            $chartData = [];
            foreach ($dailyCounts as $date => $count) {
                $chartData[] = [
                    'date' => $date,
                    'count' => $count
                ];
            }
            
            return $chartData;
        } catch (Exception $e) {
            error_log("Error getting leads for last 30 days: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Normalize stage value to stage number (handles both text and numeric stages)
     * @param mixed $stage Stage value from database
     * @return int Stage number (1-9)
     */
    private function normalizeStageToNumber($stage)
    {
        // If it's already a number, return it
        if (is_numeric($stage)) {
            $num = (int)$stage;
            return ($num >= 1 && $num <= 9) ? $num : 1;
        }
        
        // If it's text, convert using existing method
        return $this->convert_text_stage_to_number($stage);
    }

    /**
     * Get dashboard summary statistics
     * @return array Summary statistics for dashboard
     */
    public function getDashboardSummary()
    {
        try {
            $summary = [
                'total_leads' => $this->getTotalLeadCount(),
                'stage_counts' => $this->getLeadCountsByStage(),
                'source_counts' => $this->getLeadCountsBySource(),
                'recent_leads' => $this->getRecentLeads(5),
                'daily_counts' => $this->getLeadsLast30Days()
            ];
            
            // Calculate conversion metrics
            $stageCounts = $summary['stage_counts'];
            $totalLeads = $summary['total_leads'];
            
            if ($totalLeads > 0) {
                // Find closed won and closed lost counts
                $closedWon = 0;
                $closedLost = 0;
                
                foreach ($stageCounts as $stage) {
                    if ($stage['stage_number'] == 130) { // Closed Won (new numbering)
                        $closedWon = $stage['count'];
                    } elseif ($stage['stage_number'] == 140) { // Closed Lost (new numbering)
                        $closedLost = $stage['count'];
                    }
                }
                
                $totalClosed = $closedWon + $closedLost;
                $summary['conversion_rate'] = $totalClosed > 0 ? round(($closedWon / $totalClosed) * 100, 1) : 0;
                $summary['closed_won'] = $closedWon;
                $summary['closed_lost'] = $closedLost;
                $summary['active_leads'] = $totalLeads - $totalClosed;
            } else {
                $summary['conversion_rate'] = 0;
                $summary['closed_won'] = 0;
                $summary['closed_lost'] = 0;
                $summary['active_leads'] = 0;
            }
            
            return $summary;
        } catch (Exception $e) {
            error_log("Error getting dashboard summary: " . $e->getMessage());
            return [
                'total_leads' => 0,
                'stage_counts' => [],
                'source_counts' => [],
                'recent_leads' => [],
                'daily_counts' => [],
                'conversion_rate' => 0,
                'closed_won' => 0,
                'closed_lost' => 0,
                'active_leads' => 0
            ];
        }
    }

    // =====================================================
    // BRIDGE TABLE INTEGRATION METHODS
    // =====================================================

    /**
     * Get leads by stage with bridge data
     */
    public function get_leads_by_stage_with_bridge_data($stage) {
        return $this->bridgeManager->getLeadsByStageWithBridgeData($stage);
    }

    /**
     * Migrate a lead to bridge tables
     */
    public function migrateLeadToBridgeTables($lead_id) {
        return $this->bridgeManager->migrateLeadToBridgeTables($lead_id);
    }
}