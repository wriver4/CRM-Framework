<?php

/**
 * LeadDocuments Model
 * Manages document uploads for leads using the bridge table approach
 */
class LeadDocuments extends Database
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create document record for a lead
     */
    public function create($lead_id, $data = [])
    {
        $sql = "INSERT INTO lead_documents (
            lead_id, document_type, document_category, file_name, file_path,
            description, upload_date, is_active, sort_order, created_at, updated_at
        ) VALUES (
            :lead_id, :document_type, :document_category, :file_name, :file_path,
            :description, :upload_date, :is_active, :sort_order, NOW(), NOW()
        )";
        
        $stmt = $this->dbcrm()->prepare($sql);
        return $stmt->execute([
            ':lead_id' => $lead_id,
            ':document_type' => $data['document_type'] ?? 'picture',
            ':document_category' => $data['document_category'] ?? 'submitted_files',
            ':file_name' => $data['file_name'] ?? null,
            ':file_path' => $data['file_path'] ?? null,
            ':description' => $data['description'] ?? null,
            ':upload_date' => $data['upload_date'] ?? date('Y-m-d H:i:s'),
            ':is_active' => $data['is_active'] ?? 1,
            ':sort_order' => $data['sort_order'] ?? 1
        ]);
    }

    /**
     * Get documents by lead ID
     */
    public function getByLeadId($lead_id, $document_type = null)
    {
        $sql = "SELECT * FROM lead_documents WHERE lead_id = :lead_id AND is_active = 1";
        $params = [':lead_id' => $lead_id];
        
        if ($document_type) {
            $sql .= " AND document_type = :document_type";
            $params[':document_type'] = $document_type;
        }
        
        $sql .= " ORDER BY sort_order ASC, created_at ASC";
        
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get pictures for a lead
     */
    public function getPictures($lead_id)
    {
        return $this->getByLeadId($lead_id, 'picture');
    }

    /**
     * Get plans for a lead
     */
    public function getPlans($lead_id)
    {
        return $this->getByLeadId($lead_id, 'plan');
    }

    /**
     * Update document
     */
    public function update($id, $data)
    {
        $fields = [];
        $params = [':id' => $id];
        
        $allowed_fields = [
            'document_type', 'document_category', 'file_name', 'file_path',
            'description', 'is_active', 'sort_order'
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
        
        $sql = "UPDATE lead_documents SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->dbcrm()->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Soft delete document (mark as inactive)
     */
    public function softDelete($id)
    {
        return $this->update($id, ['is_active' => 0]);
    }

    /**
     * Hard delete document
     */
    public function delete($id)
    {
        $sql = "DELETE FROM lead_documents WHERE id = :id";
        $stmt = $this->dbcrm()->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Migrate old picture/plan fields to bridge table
     */
    public function migrateFromLead($lead_id, $lead_data)
    {
        $migrated = [];
        
        // Migrate picture_upload_link
        if (!empty($lead_data['picture_upload_link'])) {
            $this->create($lead_id, [
                'document_type' => 'picture',
                'document_category' => 'initial_submission',
                'file_name' => 'picture_upload_' . $lead_id,
                'file_path' => $lead_data['picture_upload_link'],
                'description' => 'Migrated from picture_upload_link'
            ]);
            $migrated[] = 'picture_upload_link';
        }
        
        // Migrate plans_upload_link
        if (!empty($lead_data['plans_upload_link'])) {
            $this->create($lead_id, [
                'document_type' => 'plan',
                'document_category' => 'initial_submission',
                'file_name' => 'plans_upload_' . $lead_id,
                'file_path' => $lead_data['plans_upload_link'],
                'description' => 'Migrated from plans_upload_link'
            ]);
            $migrated[] = 'plans_upload_link';
        }
        
        // Migrate individual picture files
        for ($i = 1; $i <= 3; $i++) {
            $field = 'picture_submitted_' . $i;
            if (!empty($lead_data[$field])) {
                $this->create($lead_id, [
                    'document_type' => 'picture',
                    'document_category' => 'submitted_files',
                    'file_name' => $lead_data[$field],
                    'file_path' => $lead_data[$field],
                    'description' => 'Migrated from ' . $field,
                    'sort_order' => $i
                ]);
                $migrated[] = $field;
            }
        }
        
        // Migrate individual plan files
        for ($i = 1; $i <= 3; $i++) {
            $field = 'plans_submitted_' . $i;
            if (!empty($lead_data[$field])) {
                $this->create($lead_id, [
                    'document_type' => 'plan',
                    'document_category' => 'submitted_files',
                    'file_name' => $lead_data[$field],
                    'file_path' => $lead_data[$field],
                    'description' => 'Migrated from ' . $field,
                    'sort_order' => $i
                ]);
                $migrated[] = $field;
            }
        }
        
        return $migrated;
    }

    /**
     * Get document type options
     */
    public function getDocumentTypeOptions()
    {
        return [
            'picture' => 'Picture',
            'plan' => 'Plan',
            'document' => 'Document',
            'contract' => 'Contract',
            'permit' => 'Permit',
            'other' => 'Other'
        ];
    }

    /**
     * Get document category options
     */
    public function getDocumentCategoryOptions()
    {
        return [
            'initial_submission' => 'Initial Submission',
            'submitted_files' => 'Submitted Files',
            'site_survey' => 'Site Survey',
            'engineering' => 'Engineering',
            'permits' => 'Permits',
            'contracts' => 'Contracts',
            'other' => 'Other'
        ];
    }
}