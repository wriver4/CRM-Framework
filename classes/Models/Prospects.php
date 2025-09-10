<?php

class Prospects extends Database {
    
    public function __construct() {
        parent::__construct();
    }

    /**
     * Create a new prospect record
     */
    public function create_prospect($lead_id, $data = []) {
        $sql = "INSERT INTO lead_prospects (
            lead_id, site_survey_completed, site_survey_date, site_survey_notes,
            building_type, building_age, roof_type, roof_condition,
            electrical_capacity, special_requirements, engineering_review_required,
            proposal_version, estimated_system_size, estimated_cost_low,
            estimated_cost_high, final_quoted_price, pricing_notes,
            prospect_temperature, proposal_status
        ) VALUES (
            :lead_id, :site_survey_completed, :site_survey_date, :site_survey_notes,
            :building_type, :building_age, :roof_type, :roof_condition,
            :electrical_capacity, :special_requirements, :engineering_review_required,
            :proposal_version, :estimated_system_size, :estimated_cost_low,
            :estimated_cost_high, :final_quoted_price, :pricing_notes,
            :prospect_temperature, :proposal_status
        )";
        
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute([
            ':lead_id' => $lead_id,
            ':site_survey_completed' => $data['site_survey_completed'] ?? 0,
            ':site_survey_date' => $data['site_survey_date'] ?? null,
            ':site_survey_notes' => $data['site_survey_notes'] ?? null,
            ':building_type' => $data['building_type'] ?? null,
            ':building_age' => $data['building_age'] ?? null,
            ':roof_type' => $data['roof_type'] ?? null,
            ':roof_condition' => $data['roof_condition'] ?? null,
            ':electrical_capacity' => $data['electrical_capacity'] ?? null,
            ':special_requirements' => $data['special_requirements'] ?? null,
            ':engineering_review_required' => $data['engineering_review_required'] ?? 0,
            ':proposal_version' => $data['proposal_version'] ?? 1,
            ':estimated_system_size' => $data['estimated_system_size'] ?? null,
            ':estimated_cost_low' => $data['estimated_cost_low'] ?? null,
            ':estimated_cost_high' => $data['estimated_cost_high'] ?? null,
            ':final_quoted_price' => $data['final_quoted_price'] ?? null,
            ':pricing_notes' => $data['pricing_notes'] ?? null,
            ':prospect_temperature' => $data['prospect_temperature'] ?? 'warm',
            ':proposal_status' => $data['proposal_status'] ?? 'draft'
        ]);
        
        return $this->dbcrm()->lastInsertId();
    }

    /**
     * Get prospect by lead ID
     */
    public function get_by_lead_id($lead_id) {
        $sql = "SELECT * FROM lead_prospects WHERE lead_id = :lead_id";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute([':lead_id' => $lead_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all prospects with lead data
     */
    public function get_all_with_leads() {
        $sql = "SELECT * FROM v_prospects_complete ORDER BY updated_at DESC";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get prospects by temperature
     */
    public function get_by_temperature($temperature = 'hot') {
        $sql = "SELECT * FROM v_prospects_complete WHERE prospect_temperature = :temperature ORDER BY updated_at DESC";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute([':temperature' => $temperature]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get prospects requiring follow-up
     */
    public function get_follow_up_required() {
        $sql = "SELECT * FROM v_prospects_complete 
                WHERE next_follow_up_date IS NOT NULL 
                AND next_follow_up_date <= CURDATE()
                ORDER BY next_follow_up_date ASC";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update prospect data
     */
    public function update_prospect($lead_id, $data) {
        $fields = [];
        $params = [':lead_id' => $lead_id];
        
        $allowed_fields = [
            'site_survey_completed', 'site_survey_date', 'site_survey_notes',
            'building_type', 'building_age', 'roof_type', 'roof_condition',
            'electrical_capacity', 'special_requirements', 'engineering_review_required',
            'engineering_review_completed', 'engineering_review_date', 'engineering_notes',
            'proposal_version', 'proposal_sent_date', 'proposal_valid_until',
            'proposal_status', 'estimated_system_size', 'estimated_cost_low',
            'estimated_cost_high', 'final_quoted_price', 'pricing_notes',
            'last_contact_date', 'next_follow_up_date', 'follow_up_method',
            'prospect_temperature'
        ];
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE prospects SET " . implode(', ', $fields) . " WHERE lead_id = :lead_id";
        $stmt = $this->dbcrm()->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Generate next proposal version
     */
    public function increment_proposal_version($lead_id) {
        $sql = "UPDATE prospects SET proposal_version = proposal_version + 1 WHERE lead_id = :lead_id";
        $stmt = $this->dbcrm()->prepare($sql);
        return $stmt->execute([':lead_id' => $lead_id]);
    }

    /**
     * Get proposal history
     */
    public function get_proposal_history($lead_id) {
        // This would require a separate proposals table for full history
        // For now, just return current proposal info
        return $this->get_by_lead_id($lead_id);
    }

    /**
     * Mark proposal as sent
     */
    public function mark_proposal_sent($lead_id, $sent_date = null) {
        $sent_date = $sent_date ?: date('Y-m-d');
        return $this->update_prospect($lead_id, [
            'proposal_status' => 'sent',
            'proposal_sent_date' => $sent_date,
            'proposal_valid_until' => date('Y-m-d', strtotime('+30 days'))
        ]);
    }
}