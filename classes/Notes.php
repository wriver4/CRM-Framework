<?php

class Notes extends Database {
    public function __construct() {
        parent::__construct();
    }

    // Helper method to get note source options
    public function get_note_source_array() {
        return [
            1 => 'Phone Call',
            2 => 'Email',
            3 => 'Text Message',
            4 => 'Internal Note',
            5 => 'Meeting',
            6 => 'Site Visit',
            7 => 'Follow-up'
        ];
    }

    // Helper method to get source badge class
    public function get_source_badge_class($source_number) {
        switch ($source_number) {
            case 1: // Phone Call
                return 'badge bg-info';
            case 2: // Email
                return 'badge bg-primary';
            case 3: // Text Message
                return 'badge bg-light text-dark';
            case 4: // Internal Note
                return 'badge bg-secondary';
            case 5: // Meeting
                return 'badge bg-success';
            case 6: // Site Visit
                return 'badge bg-warning text-dark';
            case 7: // Follow-up
                return 'badge bg-danger';
            default:
                return 'badge bg-secondary';
        }
    }

    // Create a new note and link it to a lead
    public function create_note_for_lead($lead_id, $data) {
        $pdo = $this->dbcrm();
        
        try {
            $pdo->beginTransaction();
            
            // Insert the note
            $sql = "INSERT INTO notes (source, note_text, user_id, contact_id, form_source, date_created) 
                    VALUES (:source, :note_text, :user_id, :contact_id, :form_source, NOW())";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':source' => $data['source'],
                ':note_text' => $data['note_text'],
                ':user_id' => $data['user_id'],
                ':contact_id' => $data['contact_id'] ?? null,
                ':form_source' => $data['form_source'] ?? 'leads'
            ]);
            
            $note_id = $pdo->lastInsertId();
            
            // Link the note to the lead
            $link_sql = "INSERT INTO leads_notes (lead_id, note_id) VALUES (:lead_id, :note_id)";
            $link_stmt = $pdo->prepare($link_sql);
            $link_stmt->execute([
                ':lead_id' => $lead_id,
                ':note_id' => $note_id
            ]);
            
            $pdo->commit();
            return $note_id;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    // Get all notes for a specific lead with optional search and ordering
    public function get_notes_by_lead($lead_id, $search = '', $order = 'DESC') {
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        
        $sql = "SELECT n.*, u.full_name, u.username, ln.date_linked,
                       c.full_name as contact_name, c.first_name as contact_first_name, 
                       c.family_name as contact_family_name
                FROM notes n
                INNER JOIN leads_notes ln ON n.id = ln.note_id
                LEFT JOIN users u ON n.user_id = u.id 
                LEFT JOIN contacts c ON n.contact_id = c.id
                WHERE ln.lead_id = :lead_id";
        
        $params = [':lead_id' => $lead_id];
        
        // Add search filter if provided
        if (!empty($search)) {
            $sql .= " AND n.note_text LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }
        
        $sql .= " ORDER BY n.date_created $order";
        
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // Get notes count for a lead (for display purposes)
    public function get_notes_count_by_lead($lead_id, $search = '') {
        $sql = "SELECT COUNT(DISTINCT n.id) as count
                FROM notes n
                INNER JOIN leads_notes ln ON n.id = ln.note_id
                WHERE ln.lead_id = :lead_id";
        
        $params = [':lead_id' => $lead_id];
        
        if (!empty($search)) {
            $sql .= " AND n.note_text LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }
        
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    // Get a specific note by ID with lead context
    public function get_note_by_id($id, $lead_id = null) {
        if ($lead_id) {
            $sql = "SELECT n.*, u.full_name, u.username, ln.date_linked
                    FROM notes n
                    INNER JOIN leads_notes ln ON n.id = ln.note_id
                    LEFT JOIN users u ON n.user_id = u.id 
                    WHERE n.id = :id AND ln.lead_id = :lead_id";
            $params = [':id' => $id, ':lead_id' => $lead_id];
        } else {
            $sql = "SELECT n.*, u.full_name, u.username 
                    FROM notes n
                    LEFT JOIN users u ON n.user_id = u.id 
                    WHERE n.id = :id";
            $params = [':id' => $id];
        }
        
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch();
    }

    // Update a note
    public function update_note($id, $data) {
        $sql = "UPDATE notes 
                SET source = :source, note_text = :note_text 
                WHERE id = :id";
        
        $stmt = $this->dbcrm()->prepare($sql);
        
        return $stmt->execute([
            ':id' => $id,
            ':source' => $data['source'],
            ':note_text' => $data['note_text']
        ]);
    }

    // Delete a note and its lead links
    public function delete_note($id) {
        $pdo = $this->dbcrm();
        
        try {
            $pdo->beginTransaction();
            
            // Delete the lead links first
            $link_sql = "DELETE FROM leads_notes WHERE note_id = :note_id";
            $link_stmt = $pdo->prepare($link_sql);
            $link_stmt->execute([':note_id' => $id]);
            
            // Delete the note
            $note_sql = "DELETE FROM notes WHERE id = :id";
            $note_stmt = $pdo->prepare($note_sql);
            $note_stmt->execute([':id' => $id]);
            
            $pdo->commit();
            return true;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    // Get notes count for a lead
    public function get_notes_count($lead_id) {
        $sql = "SELECT COUNT(*) as count 
                FROM leads_notes 
                WHERE lead_id = :lead_id";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute([':lead_id' => $lead_id]);
        $result = $stmt->fetch();
        
        return $result['count'] ?? 0;
    }

    // Link an existing note to a lead (for cross-referencing)
    public function link_note_to_lead($note_id, $lead_id) {
        $sql = "INSERT IGNORE INTO leads_notes (lead_id, note_id) 
                VALUES (:lead_id, :note_id)";
        $stmt = $this->dbcrm()->prepare($sql);
        
        return $stmt->execute([
            ':lead_id' => $lead_id,
            ':note_id' => $note_id
        ]);
    }

    // Create a note from another form and link to lead
    public function create_system_note($lead_id, $note_text, $form_source, $user_id = null) {
        $data = [
            'source' => 4, // Internal Note (for system-generated notes)
            'note_text' => $note_text,
            'user_id' => $user_id ?? $_SESSION['user_id'] ?? 1,
            'form_source' => $form_source
        ];
        
        return $this->create_note_for_lead($lead_id, $data);
    }

    // Get recent notes across all leads (for dashboard/activity feed)
    public function get_recent_notes($limit = 10) {
        $sql = "SELECT n.*, u.full_name, u.username, l.lead_id
                FROM notes n
                LEFT JOIN users u ON n.user_id = u.id
                INNER JOIN leads_notes ln ON n.id = ln.note_id
                INNER JOIN leads l ON ln.lead_id = l.id
                ORDER BY n.date_created DESC
                LIMIT :limit";
        
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    // Validate note data
    public function validate_note_data($data) {
        $errors = [];
        
        if (empty($data['source']) || !in_array($data['source'], array_keys($this->get_note_source_array()))) {
            $errors[] = 'Valid note source is required';
        }
        
        if (empty(trim($data['note_text']))) {
            $errors[] = 'Note text is required';
        }
        
        if (empty($data['user_id'])) {
            $errors[] = 'User ID is required';
        }
        
        return $errors;
    }
}