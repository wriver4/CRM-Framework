<?php

class Leads {
    private $db;

    public function __construct() {
        $this->db = new Database();
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
        // SQL to insert a new lead with updated structure
        $sql = "INSERT INTO leads (
            lead_source, first_name, last_name, cell_phone, email, ctype, notes, 
            estimate_number, p_street_1, p_street_2, p_city, p_state, p_postcode, p_country,
            services_interested_in, structure_type, structure_description, structure_other, structure_additional,
            picture_submitted_1, picture_submitted_2, picture_submitted_3,
            plans_submitted_1, plans_submitted_2, plans_submitted_3,
            picture_upload_link, plans_upload_link, plans_and_pics, get_updates, hear_about, hear_about_other, stage, edited_by,
            -- Keep existing business fields
            family_name, fullname, existing_client, address, proposal_sent_date, scheduled_date,
            lead_lost_notes, site_visit_by, referred_to, lead_notes, prospect_notes, lead_lost,
            site_visit_completed, closer, referred_services, assigned_to, referred, site_visit_date,
            date_qualified, contacted_date, referral_done, jd_referral_notes, closing_notes,
            prospect_lost, to_contracting
        ) VALUES (
            :lead_source, :first_name, :last_name, :cell_phone, :email, :ctype, :notes,
            :estimate_number, :p_street_1, :p_street_2, :p_city, :p_state, :p_postcode, :p_country,
            :services_interested_in, :structure_type, :structure_description, :structure_other, :structure_additional,
            :picture_submitted_1, :picture_submitted_2, :picture_submitted_3,
            :plans_submitted_1, :plans_submitted_2, :plans_submitted_3,
            :picture_upload_link, :plans_upload_link, :plans_and_pics, :get_updates, :hear_about, :hear_about_other, :stage, :edited_by,
            -- Keep existing business fields
            :family_name, :fullname, :existing_client, :address, :proposal_sent_date, :scheduled_date,
            :lead_lost_notes, :site_visit_by, :referred_to, :lead_notes, :prospect_notes, :lead_lost,
            :site_visit_completed, :closer, :referred_services, :assigned_to, :referred, :site_visit_date,
            :date_qualified, :contacted_date, :referral_done, :jd_referral_notes, :closing_notes,
            :prospect_lost, :to_contracting
        )";
        return $this->db->execute($sql, $data);
    }

    public function get_leads() {
        // SQL to fetch all leads
        $sql = "SELECT * FROM leads";
        return $this->db->query($sql);
    }

    public function get_lead_by_id($id) {
        // SQL to fetch a lead by ID
        $sql = "SELECT * FROM leads WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }

    public function update_lead($id, $data) {
        // SQL to update a lead with new structure
        $sql = "UPDATE leads SET 
            lead_source = :lead_source, first_name = :first_name, last_name = :last_name, 
            cell_phone = :cell_phone, email = :email, ctype = :ctype, notes = :notes,
            estimate_number = :estimate_number, p_street_1 = :p_street_1, p_street_2 = :p_street_2,
            p_city = :p_city, p_state = :p_state, p_postcode = :p_postcode, p_country = :p_country,
            services_interested_in = :services_interested_in, structure_type = :structure_type,
            structure_description = :structure_description, structure_other = :structure_other,
            structure_additional = :structure_additional, picture_submitted_1 = :picture_submitted_1,
            picture_submitted_2 = :picture_submitted_2, picture_submitted_3 = :picture_submitted_3,
            plans_submitted_1 = :plans_submitted_1, plans_submitted_2 = :plans_submitted_2,
            plans_submitted_3 = :plans_submitted_3, picture_upload_link = :picture_upload_link,
            plans_upload_link = :plans_upload_link, plans_and_pics = :plans_and_pics,
            get_updates = :get_updates, hear_about = :hear_about, hear_about_other = :hear_about_other,
            stage = :stage, edited_by = :edited_by, updated_at = CURRENT_TIMESTAMP,
            -- Keep existing business fields
            family_name = :family_name, fullname = :fullname, existing_client = :existing_client,
            address = :address, proposal_sent_date = :proposal_sent_date, scheduled_date = :scheduled_date,
            lead_lost_notes = :lead_lost_notes, site_visit_by = :site_visit_by, referred_to = :referred_to,
            lead_notes = :lead_notes, prospect_notes = :prospect_notes, lead_lost = :lead_lost,
            site_visit_completed = :site_visit_completed, closer = :closer, referred_services = :referred_services,
            assigned_to = :assigned_to, referred = :referred, site_visit_date = :site_visit_date,
            date_qualified = :date_qualified, contacted_date = :contacted_date, referral_done = :referral_done,
            jd_referral_notes = :jd_referral_notes, closing_notes = :closing_notes, prospect_lost = :prospect_lost,
            to_contracting = :to_contracting
        WHERE id = :id";
        $data['id'] = $id;
        return $this->db->execute($sql, $data);
    }

    public function delete_lead($id) {
        // SQL to delete a lead
        $sql = "DELETE FROM leads WHERE id = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }

    public function get_last_estimate_number() {
        // SQL to get the highest estimate number
        $sql = "SELECT MAX(CAST(estimate_number AS UNSIGNED)) as max_estimate FROM leads WHERE estimate_number IS NOT NULL AND estimate_number != ''";
        $stmt = $this->db->dbcrm()->query($sql);
        $result = $stmt->fetch();
        
        if ($result && !empty($result['max_estimate'])) {
            return $result['max_estimate'];
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
        if (empty($data['last_name'])) {
            $errors[] = 'Last name is required';
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
        
        // Validate field length constraints
        if (!empty($data['first_name']) && strlen($data['first_name']) > 100) {
            $errors[] = 'First name too long (max 100 characters)';
        }
        if (!empty($data['last_name']) && strlen($data['last_name']) > 100) {
            $errors[] = 'Last name too long (max 100 characters)';
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
        
        if (!empty($filters['p_state'])) {
            $sql .= " AND p_state = :p_state";
            $params['p_state'] = $filters['p_state'];
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        return $this->db->query($sql, $params);
    }
}