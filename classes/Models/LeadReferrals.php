<?php

/**
 * LeadReferrals Model
 * Manages referral information for leads using the bridge table approach
 */
class LeadReferrals extends Database
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create referral record for a lead
     */
    public function create($lead_id, $data = [])
    {
        $sql = "INSERT INTO lead_referrals (
            lead_id, referral_source_type, referral_source_name, referral_contact_id,
            referral_code, commission_rate, commission_amount, commission_type,
            commission_paid, commission_paid_date, agreement_type, agreement_signed_date,
            referral_notes, follow_up_required, follow_up_date, referral_status,
            created_at, updated_at
        ) VALUES (
            :lead_id, :referral_source_type, :referral_source_name, :referral_contact_id,
            :referral_code, :commission_rate, :commission_amount, :commission_type,
            :commission_paid, :commission_paid_date, :agreement_type, :agreement_signed_date,
            :referral_notes, :follow_up_required, :follow_up_date, :referral_status,
            NOW(), NOW()
        )";
        
        $stmt = $this->dbcrm()->prepare($sql);
        return $stmt->execute([
            ':lead_id' => $lead_id,
            ':referral_source_type' => $data['referral_source_type'] ?? 'other',
            ':referral_source_name' => $data['referral_source_name'] ?? null,
            ':referral_contact_id' => $data['referral_contact_id'] ?? null,
            ':referral_code' => $data['referral_code'] ?? null,
            ':commission_rate' => $data['commission_rate'] ?? null,
            ':commission_amount' => $data['commission_amount'] ?? null,
            ':commission_type' => $data['commission_type'] ?? 'percentage',
            ':commission_paid' => $data['commission_paid'] ?? 0,
            ':commission_paid_date' => $data['commission_paid_date'] ?? null,
            ':agreement_type' => $data['agreement_type'] ?? null,
            ':agreement_signed_date' => $data['agreement_signed_date'] ?? null,
            ':referral_notes' => $data['referral_notes'] ?? null,
            ':follow_up_required' => $data['follow_up_required'] ?? 0,
            ':follow_up_date' => $data['follow_up_date'] ?? null,
            ':referral_status' => $data['referral_status'] ?? 'pending'
        ]);
    }

    /**
     * Get referral by lead ID
     */
    public function getByLeadId($lead_id)
    {
        $sql = "SELECT * FROM lead_referrals WHERE lead_id = :lead_id";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute([':lead_id' => $lead_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all referrals with lead data
     */
    public function getAllWithLeads()
    {
        $sql = "SELECT 
            lr.*,
            l.first_name, l.family_name, l.email, l.cell_phone, l.stage,
            c.first_name as contact_first_name, c.family_name as contact_family_name,
            rc.first_name as referral_contact_first_name, rc.family_name as referral_contact_family_name,
            rc.personal_email as referral_contact_email
        FROM lead_referrals lr
        LEFT JOIN leads l ON lr.lead_id = l.id
        LEFT JOIN contacts c ON l.id = c.lead_id AND c.call_order = 1
        LEFT JOIN contacts rc ON lr.referral_contact_id = rc.id
        ORDER BY lr.updated_at DESC";
        
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update referral data
     */
    public function update($lead_id, $data)
    {
        $fields = [];
        $params = [':lead_id' => $lead_id];
        
        $allowed_fields = [
            'referral_source_type', 'referral_source_name', 'referral_contact_id',
            'referral_code', 'commission_rate', 'commission_amount', 'commission_type',
            'commission_paid', 'commission_paid_date', 'agreement_type', 'agreement_signed_date',
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
        
        $fields[] = "updated_at = NOW()";
        
        $sql = "UPDATE lead_referrals SET " . implode(', ', $fields) . " WHERE lead_id = :lead_id";
        $stmt = $this->dbcrm()->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Create or update referral (upsert)
     */
    public function createOrUpdate($lead_id, $data)
    {
        $existing = $this->getByLeadId($lead_id);
        
        if ($existing) {
            return $this->update($lead_id, $data);
        } else {
            return $this->create($lead_id, $data);
        }
    }

    /**
     * Delete referral for a lead
     */
    public function delete($lead_id)
    {
        $sql = "DELETE FROM lead_referrals WHERE lead_id = :lead_id";
        $stmt = $this->dbcrm()->prepare($sql);
        return $stmt->execute([':lead_id' => $lead_id]);
    }

    /**
     * Get referrals requiring follow-up
     */
    public function getFollowUpRequired()
    {
        $sql = "SELECT 
            lr.*,
            l.first_name, l.family_name, l.email, l.cell_phone, l.stage
        FROM lead_referrals lr
        LEFT JOIN leads l ON lr.lead_id = l.id
        WHERE lr.follow_up_required = 1 
        AND (lr.follow_up_date IS NULL OR lr.follow_up_date <= CURDATE())
        ORDER BY lr.follow_up_date ASC";
        
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get referrals by status
     */
    public function getByStatus($status = 'pending')
    {
        $sql = "SELECT 
            lr.*,
            l.first_name, l.family_name, l.email, l.cell_phone, l.stage
        FROM lead_referrals lr
        LEFT JOIN leads l ON lr.lead_id = l.id
        WHERE lr.referral_status = :status
        ORDER BY lr.updated_at DESC";
        
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute([':status' => $status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Migrate from old hear_about fields
     */
    public function migrateFromLead($lead_id, $lead_data)
    {
        if (empty($lead_data['hear_about'])) {
            return false;
        }

        $referral_source_type = 'other';
        switch (strtolower($lead_data['hear_about'])) {
            case 'referral':
                $referral_source_type = 'customer';
                break;
            case 'partner':
                $referral_source_type = 'partner';
                break;
            case 'online':
                $referral_source_type = 'online';
                break;
        }

        return $this->create($lead_id, [
            'referral_source_type' => $referral_source_type,
            'referral_source_name' => $lead_data['hear_about_other'] ?? $lead_data['hear_about'],
            'referral_notes' => 'Migrated from leads table. Original hear_about: ' . $lead_data['hear_about'],
            'referral_status' => 'pending'
        ]);
    }

    /**
     * Get referral source type options
     */
    public function getReferralSourceTypeOptions()
    {
        return [
            'customer' => 'Customer Referral',
            'partner' => 'Partner Referral',
            'online' => 'Online Source',
            'advertising' => 'Advertising',
            'trade_show' => 'Trade Show',
            'cold_call' => 'Cold Call',
            'other' => 'Other'
        ];
    }

    /**
     * Get referral status options
     */
    public function getReferralStatusOptions()
    {
        return [
            'pending' => 'Pending',
            'active' => 'Active',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled'
        ];
    }

    /**
     * Get commission type options
     */
    public function getCommissionTypeOptions()
    {
        return [
            'percentage' => 'Percentage',
            'fixed' => 'Fixed Amount',
            'tiered' => 'Tiered'
        ];
    }
}