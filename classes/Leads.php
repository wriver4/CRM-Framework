<?php

class Leads extends Database {
    public function __construct() {
        parent::__construct();
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

    // Helper method to get lead stage options (1-8)
    public function get_lead_stage_array() {
        return [
            1 => 'Lead',
            2 => 'Prospect', 
            3 => 'Qualified',
            4 => 'Proposal',
            5 => 'Closing Conference',
            6 => 'Completed Estimate',
            7 => 'Closed Won',
            8 => 'Closed Lost',
            9 => 'Referral'
        ];
    }

    // Helper method to get stage badge class
    public function get_stage_badge_class($stage_number) {
        switch ($stage_number) {
            case 1: // Lead
                return 'badge bg-primary';
            case 2: // Prospect
            case 9: // Referral
                return 'badge bg-info';
            case 3: // Qualified
            case 4: // Proposal
                return 'badge bg-warning';
            case 5: // Closing Conference
            case 6: // Completed Estimate
            case 7: // Closed Won
                return 'badge bg-success';
            case 8: // Closed Lost
                return 'badge bg-danger';
            default:
                return 'badge bg-secondary';
        }
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
        $stages = [];
        for ($i = 1; $i <= 9; $i++) {
            $stages[$i] = $this->get_stage_display_name($i, $lang);
        }
        return $stages;
    }

    // Helper method to convert old text stages to numbers (for migration)
    public function convert_text_stage_to_number($text_stage) {
        $mapping = [
            'lead' => 1,
            'prospect' => 2,
            'qualified' => 3,
            'proposal' => 4,
            'closing conference' => 5,
            'completed estimate' => 6,
            'closed won' => 7,
            'closed lost' => 8,
            'referral' => 9
        ];
        
        return $mapping[strtolower(trim($text_stage))] ?? 1; // Default to Lead
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
        // SQL to insert a new lead with updated structure
        $sql = "INSERT INTO leads (
            lead_source, first_name, family_name, cell_phone, email, ctype, notes, 
            lead_id, business_name, form_street_1, form_street_2, form_city, form_state, form_postcode, form_country, timezone, full_address,
            services_interested_in, structure_type, structure_description, structure_other, structure_additional,
            picture_submitted_1, picture_submitted_2, picture_submitted_3,
            plans_submitted_1, plans_submitted_2, plans_submitted_3,
            picture_upload_link, plans_upload_link, plans_and_pics, get_updates, hear_about, hear_about_other, stage, last_edited_by,
            -- Keep existing business fields
            full_name, full_address
        ) VALUES (
            :lead_source, :first_name, :family_name, :cell_phone, :email, :ctype, :notes,
            :lead_id, :business_name, :form_street_1, :form_street_2, :form_city, :form_state, :form_postcode, :form_country, :timezone, :full_address,
            :services_interested_in, :structure_type, :structure_description, :structure_other, :structure_additional,
            :picture_submitted_1, :picture_submitted_2, :picture_submitted_3,
            :plans_submitted_1, :plans_submitted_2, :plans_submitted_3,
            :picture_upload_link, :plans_upload_link, :plans_and_pics, :get_updates, :hear_about, :hear_about_other, :stage, :last_edited_by,
            -- Keep existing business fields
            :full_name, :full_address
        )";
        // Define valid parameters that exist in the SQL query
        $validParams = [
            'lead_source', 'first_name', 'family_name', 'cell_phone', 'email', 'ctype', 'notes',
            'lead_id', 'business_name', 'form_street_1', 'form_street_2', 'form_city', 
            'form_state', 'form_postcode', 'form_country', 'timezone', 'full_address',
            'services_interested_in', 'structure_type', 'structure_description', 'structure_other',
            'structure_additional', 'picture_submitted_1', 'picture_submitted_2', 'picture_submitted_3',
            'plans_submitted_1', 'plans_submitted_2', 'plans_submitted_3', 'picture_upload_link',
            'plans_upload_link', 'plans_and_pics', 'get_updates', 'hear_about', 'hear_about_other',
            'stage', 'last_edited_by', 'full_name'
        ];
        
        $stmt = $this->dbcrm()->prepare($sql);
        foreach ($data as $key => $value) {
            if (in_array($key, $validParams)) {
                $stmt->bindValue(':' . $key, $value);
            }
        }
        return $stmt->execute();
    }

    public function get_leads() {
        // SQL to fetch all leads
        $sql = "SELECT * FROM leads";
        $stmt = $this->dbcrm()->query($sql);
        return $stmt->fetchAll();
    }

    public function get_lead_by_id($id) {
        // SQL to fetch a lead by ID
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
        // SQL to update a lead with new structure
        $sql = "UPDATE leads SET 
            lead_source = :lead_source, first_name = :first_name, family_name = :family_name, 
            cell_phone = :cell_phone, email = :email, ctype = :ctype,
            lead_id = :lead_id, business_name = :business_name, form_street_1 = :form_street_1, form_street_2 = :form_street_2,
            form_city = :form_city, form_state = :form_state, form_postcode = :form_postcode, form_country = :form_country, timezone = :timezone, full_address = :full_address,
            services_interested_in = :services_interested_in, structure_type = :structure_type,
            structure_description = :structure_description, structure_other = :structure_other,
            structure_additional = :structure_additional, picture_submitted_1 = :picture_submitted_1,
            picture_submitted_2 = :picture_submitted_2, picture_submitted_3 = :picture_submitted_3,
            plans_submitted_1 = :plans_submitted_1, plans_submitted_2 = :plans_submitted_2,
            plans_submitted_3 = :plans_submitted_3, picture_upload_link = :picture_upload_link,
            plans_upload_link = :plans_upload_link, plans_and_pics = :plans_and_pics,
            get_updates = :get_updates, hear_about = :hear_about, hear_about_other = :hear_about_other,
            stage = :stage, last_edited_by = :last_edited_by, updated_at = CURRENT_TIMESTAMP,
            -- Keep existing business fields
            full_name = :full_name
        WHERE id = :id";
        $data['id'] = $id;
        
        // Define valid parameters that exist in the SQL query
        $validParams = [
            'lead_source', 'first_name', 'family_name', 'cell_phone', 'email', 'ctype',
            'lead_id', 'business_name', 'form_street_1', 'form_street_2', 'form_city', 
            'form_state', 'form_postcode', 'form_country', 'timezone', 'full_address',
            'services_interested_in', 'structure_type', 'structure_description', 'structure_other',
            'structure_additional', 'picture_submitted_1', 'picture_submitted_2', 'picture_submitted_3',
            'plans_submitted_1', 'plans_submitted_2', 'plans_submitted_3', 'picture_upload_link',
            'plans_upload_link', 'plans_and_pics', 'get_updates', 'hear_about', 'hear_about_other',
            'stage', 'last_edited_by', 'full_name', 'id'
        ];
        
        $stmt = $this->dbcrm()->prepare($sql);
        foreach ($data as $key => $value) {
            if (in_array($key, $validParams)) {
                $stmt->bindValue(':' . $key, $value);
            }
        }
        return $stmt->execute();
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
        
        // Validate ctype is within range (1-5)
        if (!empty($data['ctype']) && (!is_numeric($data['ctype']) || $data['ctype'] < 1 || $data['ctype'] > 5)) {
            $errors[] = 'Invalid contact type';
        }
        
        // Validate structure_type is within range (1-6)
        if (!empty($data['structure_type']) && (!is_numeric($data['structure_type']) || $data['structure_type'] < 1 || $data['structure_type'] > 6)) {
            $errors[] = 'Invalid structure type';
        }
        
        // Validate stage is within range (1-9)
        if (!empty($data['stage']) && (!is_numeric($data['stage']) || $data['stage'] < 1 || $data['stage'] > 9)) {
            $errors[] = 'Invalid stage';
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

    // Get all leads for list display
    public function get_all_active($filters = []) {
        $sql = "SELECT 
            id, lead_source, first_name, family_name, business_name, email, cell_phone, 
            stage, structure_type, ctype, created_at, updated_at, last_edited_by,
            lead_id, form_street_1, form_city, form_state, form_postcode, full_address
        FROM leads 
        WHERE stage NOT IN (7, 8, 9)";
        
        $params = [];
        
        // Add filters if needed
        if (!empty($filters['stage'])) {
            $sql .= " AND stage = :stage";
            $params['stage'] = $filters['stage'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (first_name LIKE :search OR family_name LIKE :search OR email LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        $sql .= " ORDER BY updated_at DESC";
        
        $stmt = $this->dbcrm()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();
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
            $sql .= " AND (first_name LIKE :search OR family_name LIKE :search OR email LIKE :search)";
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
}