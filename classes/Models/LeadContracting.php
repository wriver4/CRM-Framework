<?php

/**
 * LeadContracting Model
 * Manages contracting information for leads using the bridge table approach
 */
class LeadContracting extends Database
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create contracting record for a lead
     */
    public function create($lead_id, $data = [])
    {
        $sql = "INSERT INTO lead_contracting (
            lead_id, contract_number, contract_type, contract_value, contract_signed_date,
            contract_start_date, contract_completion_date, payment_terms, payment_schedule,
            deposit_amount, deposit_received, deposit_date, project_manager_id, lead_technician_id,
            project_start_date, estimated_completion_date, actual_completion_date, project_status,
            completion_percentage, deliverables, milestones, current_milestone, permits_required,
            permits_obtained, insurance_verified, warranty_terms, warranty_start_date, warranty_end_date,
            quality_check_completed, quality_check_date, client_satisfaction_score, project_notes,
            change_orders, created_at, updated_at
        ) VALUES (
            :lead_id, :contract_number, :contract_type, :contract_value, :contract_signed_date,
            :contract_start_date, :contract_completion_date, :payment_terms, :payment_schedule,
            :deposit_amount, :deposit_received, :deposit_date, :project_manager_id, :lead_technician_id,
            :project_start_date, :estimated_completion_date, :actual_completion_date, :project_status,
            :completion_percentage, :deliverables, :milestones, :current_milestone, :permits_required,
            :permits_obtained, :insurance_verified, :warranty_terms, :warranty_start_date, :warranty_end_date,
            :quality_check_completed, :quality_check_date, :client_satisfaction_score, :project_notes,
            :change_orders, NOW(), NOW()
        )";
        
        $stmt = $this->dbcrm()->prepare($sql);
        return $stmt->execute([
            ':lead_id' => $lead_id,
            ':contract_number' => $data['contract_number'] ?? null,
            ':contract_type' => $data['contract_type'] ?? 'standard',
            ':contract_value' => $data['contract_value'] ?? null,
            ':contract_signed_date' => $data['contract_signed_date'] ?? null,
            ':contract_start_date' => $data['contract_start_date'] ?? null,
            ':contract_completion_date' => $data['contract_completion_date'] ?? null,
            ':payment_terms' => $data['payment_terms'] ?? null,
            ':payment_schedule' => $data['payment_schedule'] ?? null,
            ':deposit_amount' => $data['deposit_amount'] ?? null,
            ':deposit_received' => $data['deposit_received'] ?? 0,
            ':deposit_date' => $data['deposit_date'] ?? null,
            ':project_manager_id' => $data['project_manager_id'] ?? null,
            ':lead_technician_id' => $data['lead_technician_id'] ?? null,
            ':project_start_date' => $data['project_start_date'] ?? null,
            ':estimated_completion_date' => $data['estimated_completion_date'] ?? null,
            ':actual_completion_date' => $data['actual_completion_date'] ?? null,
            ':project_status' => $data['project_status'] ?? 'pending',
            ':completion_percentage' => $data['completion_percentage'] ?? 0,
            ':deliverables' => $data['deliverables'] ?? null,
            ':milestones' => $data['milestones'] ?? null,
            ':current_milestone' => $data['current_milestone'] ?? null,
            ':permits_required' => $data['permits_required'] ?? 0,
            ':permits_obtained' => $data['permits_obtained'] ?? 0,
            ':insurance_verified' => $data['insurance_verified'] ?? 0,
            ':warranty_terms' => $data['warranty_terms'] ?? null,
            ':warranty_start_date' => $data['warranty_start_date'] ?? null,
            ':warranty_end_date' => $data['warranty_end_date'] ?? null,
            ':quality_check_completed' => $data['quality_check_completed'] ?? 0,
            ':quality_check_date' => $data['quality_check_date'] ?? null,
            ':client_satisfaction_score' => $data['client_satisfaction_score'] ?? null,
            ':project_notes' => $data['project_notes'] ?? null,
            ':change_orders' => $data['change_orders'] ?? null
        ]);
    }

    /**
     * Get contracting by lead ID
     */
    public function getByLeadId($lead_id)
    {
        $sql = "SELECT * FROM lead_contracting WHERE lead_id = :lead_id";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute([':lead_id' => $lead_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all contracting records with lead data
     */
    public function getAllWithLeads()
    {
        $sql = "SELECT * FROM v_contracting_complete ORDER BY updated_at DESC";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update contracting data
     */
    public function update($lead_id, $data)
    {
        $fields = [];
        $params = [':lead_id' => $lead_id];
        
        $allowed_fields = [
            'contract_number', 'contract_type', 'contract_value', 'contract_signed_date',
            'contract_start_date', 'contract_completion_date', 'payment_terms', 'payment_schedule',
            'deposit_amount', 'deposit_received', 'deposit_date', 'project_manager_id', 'lead_technician_id',
            'project_start_date', 'estimated_completion_date', 'actual_completion_date', 'project_status',
            'completion_percentage', 'deliverables', 'milestones', 'current_milestone', 'permits_required',
            'permits_obtained', 'insurance_verified', 'warranty_terms', 'warranty_start_date', 'warranty_end_date',
            'quality_check_completed', 'quality_check_date', 'client_satisfaction_score', 'project_notes',
            'change_orders'
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
        
        $sql = "UPDATE lead_contracting SET " . implode(', ', $fields) . " WHERE lead_id = :lead_id";
        $stmt = $this->dbcrm()->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Create or update contracting (upsert)
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
     * Delete contracting for a lead
     */
    public function delete($lead_id)
    {
        $sql = "DELETE FROM lead_contracting WHERE lead_id = :lead_id";
        $stmt = $this->dbcrm()->prepare($sql);
        return $stmt->execute([':lead_id' => $lead_id]);
    }

    /**
     * Get contracts by status
     */
    public function getByStatus($status = 'pending')
    {
        $sql = "SELECT * FROM v_contracting_complete WHERE project_status = :status ORDER BY updated_at DESC";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute([':status' => $status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get contracts requiring attention (overdue, permits needed, etc.)
     */
    public function getRequiringAttention()
    {
        $sql = "SELECT * FROM v_contracting_complete 
                WHERE (estimated_completion_date < CURDATE() AND project_status != 'completed')
                OR (permits_required = 1 AND permits_obtained = 0)
                OR (deposit_amount > 0 AND deposit_received = 0)
                ORDER BY estimated_completion_date ASC";
        
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get contract type options
     */
    public function getContractTypeOptions()
    {
        return [
            'standard' => 'Standard Contract',
            'commercial' => 'Commercial Contract',
            'residential' => 'Residential Contract',
            'maintenance' => 'Maintenance Contract',
            'warranty' => 'Warranty Work',
            'custom' => 'Custom Contract'
        ];
    }

    /**
     * Get project status options
     */
    public function getProjectStatusOptions()
    {
        return [
            'pending' => 'Pending',
            'planning' => 'Planning',
            'in_progress' => 'In Progress',
            'on_hold' => 'On Hold',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled'
        ];
    }

    /**
     * Get payment terms options
     */
    public function getPaymentTermsOptions()
    {
        return [
            'net_30' => 'Net 30',
            'net_15' => 'Net 15',
            'due_on_receipt' => 'Due on Receipt',
            '50_50' => '50% Deposit, 50% on Completion',
            '30_70' => '30% Deposit, 70% on Completion',
            'milestone' => 'Milestone-based',
            'custom' => 'Custom Terms'
        ];
    }
}