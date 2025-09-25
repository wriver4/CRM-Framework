<?php

/**
 * LeadBridgeManager
 * Coordinates all bridge table operations for leads
 * Provides a unified interface for managing lead-related data across bridge tables
 */
class LeadBridgeManager extends Database
{
    private $structureInfo;
    private $documents;
    private $referrals;
    private $prospects;
    private $contracting;

    public function __construct()
    {
        parent::__construct();
        $this->structureInfo = new LeadStructureInfo();
        $this->documents = new LeadDocuments();
        $this->referrals = new LeadReferrals();
        $this->prospects = new Prospects();
        $this->contracting = new LeadContracting();
    }

    /**
     * Get complete lead data with all bridge table information
     */
    public function getCompleteLeadData($lead_id)
    {
        // Get base lead data
        $leads = new Leads();
        $lead_data = $leads->get_lead_by_id($lead_id);
        
        if (empty($lead_data)) {
            return null;
        }

        $lead = $lead_data[0]; // get_lead_by_id returns array

        // Get bridge table data
        $lead['structure_info'] = $this->structureInfo->getByLeadId($lead_id);
        $lead['documents'] = [
            'pictures' => $this->documents->getPictures($lead_id),
            'plans' => $this->documents->getPlans($lead_id),
            'all' => $this->documents->getByLeadId($lead_id)
        ];
        $lead['referral'] = $this->referrals->getByLeadId($lead_id);
        $lead['prospect'] = $this->prospects->get_by_lead_id($lead_id);
        $lead['contracting'] = $this->contracting->getByLeadId($lead_id);

        return $lead;
    }

    /**
     * Update lead data across all relevant bridge tables
     */
    public function updateLeadData($lead_id, $data)
    {
        $results = [];

        // Update structure info if provided
        if (isset($data['structure_info'])) {
            $results['structure_info'] = $this->structureInfo->createOrUpdate($lead_id, $data['structure_info']);
        }

        // Handle document uploads if provided
        if (isset($data['documents'])) {
            $results['documents'] = [];
            foreach ($data['documents'] as $doc) {
                $results['documents'][] = $this->documents->create($lead_id, $doc);
            }
        }

        // Update referral info if provided
        if (isset($data['referral'])) {
            $results['referral'] = $this->referrals->createOrUpdate($lead_id, $data['referral']);
        }

        // Update prospect info if provided
        if (isset($data['prospect'])) {
            $existing_prospect = $this->prospects->get_by_lead_id($lead_id);
            if ($existing_prospect) {
                $results['prospect'] = $this->prospects->update_prospect($lead_id, $data['prospect']);
            } else {
                $results['prospect'] = $this->prospects->create_prospect($lead_id, $data['prospect']);
            }
        }

        // Update contracting info if provided
        if (isset($data['contracting'])) {
            $results['contracting'] = $this->contracting->createOrUpdate($lead_id, $data['contracting']);
        }

        return $results;
    }

    /**
     * Migrate old lead data to bridge tables
     */
    public function migrateLeadToBridgeTables($lead_id, $lead_data = null)
    {
        if (!$lead_data) {
            $leads = new Leads();
            $lead_result = $leads->get_lead_by_id($lead_id);
            if (empty($lead_result)) {
                return false;
            }
            $lead_data = $lead_result[0];
        }

        $migration_results = [];

        // Migrate structure info
        $structure_fields = ['structure_type', 'structure_description', 'structure_other', 'structure_additional'];
        $structure_data = [];
        foreach ($structure_fields as $field) {
            if (!empty($lead_data[$field])) {
                $structure_data[$field] = $lead_data[$field];
            }
        }
        if (!empty($structure_data)) {
            $migration_results['structure_info'] = $this->structureInfo->createOrUpdate($lead_id, $structure_data);
        }

        // Migrate documents
        $migration_results['documents'] = $this->documents->migrateFromLead($lead_id, $lead_data);

        // Migrate referral data (from hear_about fields)
        if (!empty($lead_data['hear_about'])) {
            $migration_results['referral'] = $this->referrals->migrateFromLead($lead_id, $lead_data);
        }

        // Migrate prospect data (based on stage and cost estimates)
        $stage = $lead_data['stage'] ?? 10; // Default to new Lead stage (10)
        // Check if stage is in prospect range (new numbering: 50-120)
        if (in_array($stage, [50, 60, 70, 80, 90, 100, 110, 120]) || 
            in_array($stage, ['50', '60', '70', '80', '90', '100', '110', '120'])) {
            $prospect_data = [
                'estimated_cost_low' => $lead_data['sales_system_cost_low'] ?? $lead_data['eng_system_cost_low'] ?? null,
                'estimated_cost_high' => $lead_data['sales_system_cost_high'] ?? $lead_data['eng_system_cost_high'] ?? null,
                'prospect_notes' => 'Migrated from leads table',
                'prospect_temperature' => (!empty($lead_data['sales_system_cost_low']) || !empty($lead_data['eng_system_cost_low'])) ? 'warm' : 'cold',
                'proposal_status' => 'draft'
            ];
            $migration_results['prospect'] = $this->prospects->create_prospect($lead_id, $prospect_data);
        }

        // Migrate contracting data (for stages 130, 140, 150 - Won, Lost, Contracting)
        if (in_array($stage, [130, 140, 150]) || in_array($stage, ['130', '140', '150'])) {
            $contract_data = [
                'contract_type' => (in_array($lead_data['structure_type'] ?? 1, [2, 3])) ? 'commercial' : 'standard',
                'contract_value' => $lead_data['sales_system_cost_high'] ?? $lead_data['eng_system_cost_high'] ?? null,
                'project_status' => ($stage == 130 || $stage == '130') ? 'completed' : 'pending', // 130 = Closed Won
                'project_notes' => 'Migrated from leads table',
                'deliverables' => json_encode(['System Installation', 'Documentation', 'Training'])
            ];
            $migration_results['contracting'] = $this->contracting->createOrUpdate($lead_id, $contract_data);
        }

        return $migration_results;
    }

    /**
     * Get leads by stage with bridge table data
     */
    public function getLeadsByStageWithBridgeData($stage)
    {
        $leads = new Leads();
        $stage_leads = $leads->get_leads_by_stage($stage);
        
        foreach ($stage_leads as &$lead) {
            $lead_id = $lead['id'];
            
            // Add relevant bridge table data based on stage
            switch ($stage) {
                case '4': // Referral stage
                    $lead['referral'] = $this->referrals->getByLeadId($lead_id);
                    break;
                    
                case '5':
                case '6':
                case '7':
                case '8':
                case '9':
                case '10':
                case '11':
                case '12': // Prospect stages
                    $lead['prospect'] = $this->prospects->get_by_lead_id($lead_id);
                    $lead['structure_info'] = $this->structureInfo->getByLeadId($lead_id);
                    break;
                    
                case '13':
                case '14': // Contracting stages
                    $lead['contracting'] = $this->contracting->getByLeadId($lead_id);
                    $lead['prospect'] = $this->prospects->get_by_lead_id($lead_id);
                    break;
            }
            
            // Always include documents for all stages
            $lead['documents'] = $this->documents->getByLeadId($lead_id);
        }
        
        return $stage_leads;
    }

    /**
     * Delete all bridge table data for a lead
     */
    public function deleteAllBridgeData($lead_id)
    {
        $results = [];
        
        $results['structure_info'] = $this->structureInfo->delete($lead_id);
        $results['referrals'] = $this->referrals->delete($lead_id);
        $results['contracting'] = $this->contracting->delete($lead_id);
        
        // For documents and prospects, we need to get IDs first
        $documents = $this->documents->getByLeadId($lead_id);
        $results['documents'] = [];
        foreach ($documents as $doc) {
            $results['documents'][] = $this->documents->delete($doc['id']);
        }
        
        // Prospects table might use different method
        $prospect = $this->prospects->get_by_lead_id($lead_id);
        if ($prospect) {
            // Assuming there's a delete method in Prospects class
            $sql = "DELETE FROM lead_prospects WHERE lead_id = :lead_id";
            $stmt = $this->dbcrm()->prepare($sql);
            $results['prospects'] = $stmt->execute([':lead_id' => $lead_id]);
        }
        
        return $results;
    }

    /**
     * Get summary statistics for bridge tables
     */
    public function getBridgeTableStats()
    {
        $stats = [];
        
        $tables = [
            'lead_structure_info' => 'Structure Info Records',
            'lead_documents' => 'Document Records',
            'lead_referrals' => 'Referral Records',
            'lead_prospects' => 'Prospect Records',
            'lead_contracting' => 'Contracting Records'
        ];
        
        foreach ($tables as $table => $label) {
            $sql = "SELECT COUNT(*) as count FROM $table";
            $stmt = $this->dbcrm()->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats[$table] = [
                'label' => $label,
                'count' => $result['count']
            ];
        }
        
        return $stats;
    }

    /**
     * Validate bridge table data integrity
     */
    public function validateDataIntegrity()
    {
        $issues = [];
        
        // Check for orphaned bridge table records
        $bridge_tables = [
            'lead_structure_info',
            'lead_documents', 
            'lead_referrals',
            'lead_prospects',
            'lead_contracting'
        ];
        
        foreach ($bridge_tables as $table) {
            $sql = "SELECT COUNT(*) as count FROM $table bt 
                    LEFT JOIN leads l ON bt.lead_id = l.id 
                    WHERE l.id IS NULL";
            $stmt = $this->dbcrm()->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                $issues[] = "Found {$result['count']} orphaned records in $table";
            }
        }
        
        return $issues;
    }
}