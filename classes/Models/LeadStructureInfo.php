<?php

/**
 * LeadStructureInfo Model
 * Manages structure information for leads using the bridge table approach
 */
class LeadStructureInfo extends Database
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create structure info record for a lead
     */
    public function create($lead_id, $data = [])
    {
        $sql = "INSERT INTO lead_structure_info (
            lead_id, structure_type, structure_description, structure_other, 
            structure_additional, building_age, roof_type, created_at, updated_at
        ) VALUES (
            :lead_id, :structure_type, :structure_description, :structure_other,
            :structure_additional, :building_age, :roof_type, NOW(), NOW()
        )";
        
        $stmt = $this->dbcrm()->prepare($sql);
        return $stmt->execute([
            ':lead_id' => $lead_id,
            ':structure_type' => $data['structure_type'] ?? null,
            ':structure_description' => $data['structure_description'] ?? null,
            ':structure_other' => $data['structure_other'] ?? null,
            ':structure_additional' => $data['structure_additional'] ?? null,
            ':building_age' => $data['building_age'] ?? null,
            ':roof_type' => $data['roof_type'] ?? null
        ]);
    }

    /**
     * Get structure info by lead ID
     */
    public function getByLeadId($lead_id)
    {
        $sql = "SELECT * FROM lead_structure_info WHERE lead_id = :lead_id";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute([':lead_id' => $lead_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update structure info for a lead
     */
    public function update($lead_id, $data)
    {
        $fields = [];
        $params = [':lead_id' => $lead_id];
        
        $allowed_fields = [
            'structure_type', 'structure_description', 'structure_other',
            'structure_additional', 'building_age', 'roof_type'
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
        
        $sql = "UPDATE lead_structure_info SET " . implode(', ', $fields) . " WHERE lead_id = :lead_id";
        $stmt = $this->dbcrm()->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Create or update structure info (upsert)
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
     * Delete structure info for a lead
     */
    public function delete($lead_id)
    {
        $sql = "DELETE FROM lead_structure_info WHERE lead_id = :lead_id";
        $stmt = $this->dbcrm()->prepare($sql);
        return $stmt->execute([':lead_id' => $lead_id]);
    }

    /**
     * Get structure type options
     */
    public function getStructureTypeOptions()
    {
        return [
            1 => 'Residential - Existing',
            2 => 'Residential - New Construction',
            3 => 'Commercial - Existing', 
            4 => 'Commercial - New Construction',
            5 => 'Industrial',
            6 => 'Other'
        ];
    }
}