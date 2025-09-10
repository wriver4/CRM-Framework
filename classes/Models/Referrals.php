<?php

class Referrals extends Database {
    
    public function __construct() {
        parent::__construct();
    }

    /**
     * Create a new referral record
     */
    public function create_referral($lead_id, $data = []) {
        $sql = "INSERT INTO lead_referrals (
            lead_id, referral_source_type, referral_source_name, 
            referral_source_contact, referral_source_email, referral_source_phone,
            commission_rate, commission_amount, commission_type,
            agreement_type, referral_code, referral_notes,
            follow_up_required, follow_up_date, referral_status
        ) VALUES (
            :lead_id, :referral_source_type, :referral_source_name,
            :referral_source_contact, :referral_source_email, :referral_source_phone,
            :commission_rate, :commission_amount, :commission_type,
            :agreement_type, :referral_code, :referral_notes,
            :follow_up_required, :follow_up_date, :referral_status
        )";
        
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute([
            ':lead_id' => $lead_id,
            ':referral_source_type' => $data['referral_source_type'] ?? 'partner',
            ':referral_source_name' => $data['referral_source_name'] ?? null,
            ':referral_source_contact' => $data['referral_source_contact'] ?? null,
            ':referral_source_email' => $data['referral_source_email'] ?? null,
            ':referral_source_phone' => $data['referral_source_phone'] ?? null,
            ':commission_rate' => $data['commission_rate'] ?? null,
            ':commission_amount' => $data['commission_amount'] ?? null,
            ':commission_type' => $data['commission_type'] ?? 'percentage',
            ':agreement_type' => $data['agreement_type'] ?? null,
            ':referral_code' => $data['referral_code'] ?? null,
            ':referral_notes' => $data['referral_notes'] ?? null,
            ':follow_up_required' => $data['follow_up_required'] ?? 0,
            ':follow_up_date' => $data['follow_up_date'] ?? null,
            ':referral_status' => $data['referral_status'] ?? 'pending'
        ]);
        
        return $this->dbcrm()->lastInsertId();
    }

    /**
     * Get referral by lead ID
     */
    public function get_by_lead_id($lead_id) {
        $sql = "SELECT * FROM lead_referrals WHERE lead_id = :lead_id";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute([':lead_id' => $lead_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all referrals with lead data
     */
    public function get_all_with_leads() {
        $sql = "SELECT * FROM v_referrals_complete ORDER BY updated_at DESC";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update referral data
     */
    public function update_referral($lead_id, $data) {
        $fields = [];
        $params = [':lead_id' => $lead_id];
        
        $allowed_fields = [
            'referral_source_type', 'referral_source_name', 'referral_source_contact',
            'referral_source_email', 'referral_source_phone', 'commission_rate',
            'commission_amount', 'commission_type', 'agreement_type', 'referral_code',
            'referral_notes', 'follow_up_required', 'follow_up_date', 'referral_status'
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
        
        $sql = "UPDATE lead_referrals SET " . implode(', ', $fields) . " WHERE lead_id = :lead_id";
        $stmt = $this->dbcrm()->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Get referrals requiring follow-up
     */
    public function get_follow_up_required() {
        $sql = "SELECT * FROM v_referrals 
                WHERE follow_up_required = 1 
                AND (follow_up_date IS NULL OR follow_up_date <= CURDATE())
                ORDER BY follow_up_date ASC";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calculate commission for referral
     */
    public function calculate_commission($lead_id, $sale_amount) {
        $referral = $this->get_by_lead_id($lead_id);
        if (!$referral) return 0;
        
        if ($referral['commission_type'] === 'percentage' && $referral['commission_rate']) {
            return ($sale_amount * $referral['commission_rate']) / 100;
        } elseif ($referral['commission_type'] === 'fixed' && $referral['commission_amount']) {
            return $referral['commission_amount'];
        }
        
        return 0;
    }
}