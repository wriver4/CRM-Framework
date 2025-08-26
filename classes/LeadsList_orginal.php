<?php

namespace WGCRM\Classes;



class LeadsList extends ActionTable
{
    protected string $modelClass = Lead::class;
    protected string $tableName = 'leads';
    protected array $searchableFields = ['first_name', 'family_name', 'email', 'company', 'phone'];
    protected array $filterableFields = ['status', 'source'];
    protected string $defaultOrderBy = 'created_at';
    protected string $defaultOrderDirection = 'DESC';

    public function __construct()
    {
        parent::__construct();
        $this->setItemsPerPage(10);
    }

    /**
     * Get available status options for leads
     */
    public function getStatusOptions(): array
    {
        return [
            'new' => 'New',
            'contacted' => 'Contacted',
            'qualified' => 'Qualified',
            'proposal' => 'Proposal',
            'negotiation' => 'Negotiation',
            'closed_won' => 'Closed Won',
            'closed_lost' => 'Closed Lost',
            'converted' => 'Converted'
        ];
    }

    /**
     * Get available source options for leads
     */
    public function getSourceOptions(): array
    {
        return [
            'website' => 'Website',
            'referral' => 'Referral',
            'social_media' => 'Social Media',
            'email_campaign' => 'Email Campaign',
            'cold_call' => 'Cold Call',
            'trade_show' => 'Trade Show',
            'advertisement' => 'Advertisement',
            'partner' => 'Partner',
            'other' => 'Other'
        ];
    }

    /**
     * Get lead statistics by status
     */
    public function getLeadsByStatus(): array
    {
        $model = new $this->modelClass();
        $statuses = array_keys($this->getStatusOptions());
        $counts = [];
        
        foreach ($statuses as $status) {
            $counts[$status] = $model->count(['status' => $status]);
        }
        
        return $counts;
    }

    /**
     * Get lead statistics by source
     */
    public function getLeadsBySource(): array
    {
        $model = new $this->modelClass();
        $sources = array_keys($this->getSourceOptions());
        $counts = [];
        
        foreach ($sources as $source) {
            $counts[$source] = $model->count(['source' => $source]);
        }
        
        return $counts;
    }

    /**
     * Get total estimated value of all leads
     */
    public function getTotalEstimatedValue(array $filters = []): float
    {
        $model = new $this->modelClass();
        return $model->getTotalEstimatedValue($filters ?: $this->getActiveFilters());
    }

    /**
     * Get average estimated value of leads
     */
    public function getAverageEstimatedValue(array $filters = []): float
    {
        $totalLeads = $this->getTotalCount($filters);
        if ($totalLeads === 0) {
            return 0;
        }
        
        return $this->getTotalEstimatedValue($filters) / $totalLeads;
    }

    /**
     * Calculate conversion rate (closed won / total leads)
     */
    public function getConversionRate(): float
    {
        $totalLeads = $this->getTotalCount();
        if ($totalLeads === 0) {
            return 0;
        }
        
        $model = new $this->modelClass();
        $convertedLeads = $model->count(['status' => 'closed_won']);
        return ($convertedLeads / $totalLeads) * 100;
    }

    /**
     * Get recent leads
     */
    public function getRecentLeads(int $limit = 5): array
    {
        $model = new $this->modelClass();
        return $model->getRecent($limit);
    }

    /**
     * Get top leads by estimated value
     */
    public function getTopLeadsByValue(int $limit = 10): array
    {
        $model = new $this->modelClass();
        return $model->getTopByValue($limit);
    }

    /**
     * Convert lead to contact
     */
    public function convertLeadToContact(int $leadId): bool
    {
        $model = new $this->modelClass();
        $lead = $model->getById($leadId);
        
        if (!$lead) {
            return false;
        }

        // Prepare contact data from lead
        $contactData = [
            'first_name' => $lead['first_name'],
            'family_name' => $lead['family_name'],
            'email' => $lead['email'],
            'phone' => $lead['phone'] ?? '',
            'company' => $lead['company'] ?? '',
            'position' => $lead['position'] ?? '',
            'full_address' => $lead['full_address'] ?? '',
            'city' => $lead['city'] ?? '',
            'state' => $lead['state'] ?? '',
            'zip_code' => $lead['zip_code'] ?? '',
            'country' => $lead['country'] ?? '',
            'website' => $lead['website'] ?? '',
            'notes' => 'Converted from lead on ' . date('Y-m-d H:i:s') . 
                      (!empty($lead['notes']) ? "\n\nOriginal notes: " . $lead['notes'] : ''),
            'source' => $lead['source'],
            'status' => 'active',
            'lead_id' => $leadId,
            'created_at' => date('Y-m-d H:i:s')
        ];

        try {
            // Create contact
            $contactModel = new Contact();
            $contactId = $contactModel->create($contactData);

            if ($contactId) {
                // Update lead status to converted
                $updated = $model->update($leadId, [
                    'status' => 'converted',
                    'converted_at' => date('Y-m-d H:i:s'),
                    'contact_id' => $contactId
                ]);

                return $updated;
            }
        } catch (Exception $e) {
            error_log("Error converting lead to contact: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Bulk convert multiple leads to contacts
     */
    public function bulkConvertToContacts(array $leadIds): array
    {
        $results = [
            'success' => [],
            'failed' => []
        ];

        foreach ($leadIds as $leadId) {
            if ($this->convertLeadToContact($leadId)) {
                $results['success'][] = $leadId;
            } else {
                $results['failed'][] = $leadId;
            }
        }

        return $results;
    }

    /**
     * Update lead status
     */
    public function updateLeadStatus(int $leadId, string $status): bool
    {
        if (!array_key_exists($status, $this->getStatusOptions())) {
            return false;
        }

        $model = new $this->modelClass();
        $updateData = ['status' => $status];

        // Add timestamp for specific status changes
        switch ($status) {
            case 'contacted':
                $updateData['contacted_at'] = date('Y-m-d H:i:s');
                break;
            case 'qualified':
                $updateData['qualified_at'] = date('Y-m-d H:i:s');
                break;
            case 'closed_won':
            case 'closed_lost':
                $updateData['closed_at'] = date('Y-m-d H:i:s');
                break;
        }

        return $model->update($leadId, $updateData);
    }

    /**
     * Bulk update lead status
     */
    public function bulkUpdateStatus(array $leadIds, string $status): bool
    {
        if (!array_key_exists($status, $this->getStatusOptions())) {
            return false;
        }

        $model = new $this->modelClass();
        return $model->bulkUpdateStatus($leadIds, $status);
    }

    /**
     * Get comprehensive lead statistics
     */
    public function getLeadStatistics(): array
    {
        return [
            'total_leads' => $this->getTotalCount(),
            'leads_by_status' => $this->getLeadsByStatus(),
            'leads_by_source' => $this->getLeadsBySource(),
            'total_estimated_value' => $this->getTotalEstimatedValue(),
            'average_estimated_value' => $this->getAverageEstimatedValue(),
            'conversion_rate' => $this->getConversionRate(),
            'recent_leads' => $this->getRecentLeads(),
            'top_leads_by_value' => $this->getTopLeadsByValue()
        ];
    }

    /**
     * Get leads that need follow-up
     */
    public function getLeadsNeedingFollowUp(): array
    {
        $model = new $this->modelClass();
        return $model->getLeadsNeedingFollowUp();
    }

    /**
     * Get leads by date range
     */
    public function getLeadsByDateRange(string $startDate, string $endDate): array
    {
        $filters = array_merge($this->getActiveFilters(), [
            'date_range' => [
                'start' => $startDate,
                'end' => $endDate
            ]
        ]);

        $model = new $this->modelClass();
        return $model->getAll($filters);
    }

    /**
     * Get lead activity summary
     */
    public function getLeadActivitySummary(int $days = 30): array
    {
        $model = new $this->modelClass();
        return $model->getActivitySummary($days);
    }

    /**
     * Search leads with advanced criteria
     */
    public function advancedSearch(array $criteria): array
    {
        $model = new $this->modelClass();
        return $model->advancedSearch($criteria);
    }

    /**
     * Get lead pipeline data for reporting
     */
    public function getPipelineData(): array
    {
        $statusCounts = $this->getLeadsByStatus();
        $pipeline = [];

        foreach ($this->getStatusOptions() as $status => $label) {
            $pipeline[] = [
                'status' => $status,
                'label' => $label,
                'count' => $statusCounts[$status] ?? 0,
                'percentage' => $this->getTotalCount() > 0 
                    ? round(($statusCounts[$status] ?? 0) / $this->getTotalCount() * 100, 2) 
                    : 0
            ];
        }

        return $pipeline;
    }

    /**
     * Export leads data
     */
    public function exportLeads(string $format = 'csv', array $filters = []): string
    {
        $model = new $this->modelClass();
        $leads = $model->getAll($filters ?: $this->getActiveFilters());
        
        return $this->exportData($leads, $format);
    }

    /**
     * Import leads from file
     */
    public function importLeads(string $filePath, array $mapping = []): array
    {
        $model = new $this->modelClass();
        return $model->import($filePath, $mapping);
    }

    /**
     * Validate lead data
     */
    public function validateLeadData(array $data): array
    {
        $errors = [];

        // Required fields
        if (empty($data['first_name'])) {
            $errors['first_name'] = 'First name is required';
        }

        if (empty($data['family_name'])) {
            $errors['family_name'] = 'Family name is required';
        }

        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        // Validate status
        if (!empty($data['status']) && !array_key_exists($data['status'], $this->getStatusOptions())) {
            $errors['status'] = 'Invalid status';
        }

        // Validate source
        if (!empty($data['source']) && !array_key_exists($data['source'], $this->getSourceOptions())) {
            $errors['source'] = 'Invalid source';
        }

        // Validate estimated value
        if (!empty($data['estimated_value']) && !is_numeric($data['estimated_value'])) {
            $errors['estimated_value'] = 'Estimated value must be a number';
        }

        return $errors;
    }

    /**
     * Get filter options for the UI
     */
    public function getFilterOptions(): array
    {
        return [
            'status' => $this->getStatusOptions(),
            'source' => $this->getSourceOptions()
        ];
    }

    /**
     * Get default filters for new instances
     */
    protected function getDefaultFilters(): array
    {
        return [
            'status' => '',
            'source' => ''
        ];
    }
}