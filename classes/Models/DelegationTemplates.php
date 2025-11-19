<?php

class DelegationTemplates extends Database
{

  private $table = 'delegation_templates';

  public function __construct()
  {
    parent::__construct();
  }

  public function create_template($name, $description, $permissions, $role_id = null, $duration_days = null)
  {
    $sql = "INSERT INTO delegation_templates 
              (name, description, role_id, permissions_json, duration_days, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
    $stmt = $this->dbcrm()->prepare($sql);
    
    $permissions_json = json_encode($permissions);
    $result = $stmt->execute([$name, $description, $role_id, $permissions_json, $duration_days]);
    
    if ($result) {
      $this->log_template_action('create', $name, 'Template created');
      return ['success' => true, 'template_id' => $this->dbcrm()->lastInsertId()];
    }
    
    return ['success' => false, 'error' => 'Failed to create template'];
  }

  public function get_all_templates($include_inactive = false)
  {
    $sql = "SELECT * FROM delegation_templates";
    
    if (!$include_inactive) {
      $sql .= " WHERE is_active = 1";
    }
    
    $sql .= " ORDER BY created_at DESC";
    $stmt = $this->dbcrm()->query($sql);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  public function get_template_by_id($template_id)
  {
    $sql = "SELECT * FROM delegation_templates WHERE id = ?";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$template_id]);
    $template = $stmt->fetch();
    
    if ($template) {
      $template['permissions'] = json_decode($template['permissions_json'], true);
    }
    
    return $template;
  }

  public function get_templates_by_role($role_id)
  {
    $sql = "SELECT * FROM delegation_templates 
            WHERE role_id = ? AND is_active = 1
            ORDER BY name ASC";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$role_id]);
    return $stmt->fetchAll();
  }

  public function update_template($template_id, $updates)
  {
    $allowed_fields = ['name', 'description', 'duration_days', 'is_active'];
    $set_clauses = [];
    $params = [];
    
    foreach ($updates as $field => $value) {
      if (in_array($field, $allowed_fields)) {
        $set_clauses[] = "$field = ?";
        $params[] = $value;
      }
    }
    
    if (empty($set_clauses)) {
      return ['success' => false, 'error' => 'No valid fields to update'];
    }
    
    $sql = "UPDATE delegation_templates SET " . implode(', ', $set_clauses) . ", updated_at = NOW() WHERE id = ?";
    $params[] = $template_id;
    
    $stmt = $this->dbcrm()->prepare($sql);
    $result = $stmt->execute($params);
    
    if ($result) {
      $this->log_template_action('update', $template_id, 'Template updated');
      return ['success' => true];
    }
    
    return ['success' => false, 'error' => 'Update failed'];
  }

  public function delete_template($template_id)
  {
    $sql = "DELETE FROM delegation_templates WHERE id = ?";
    $stmt = $this->dbcrm()->prepare($sql);
    $result = $stmt->execute([$template_id]);
    
    if ($result) {
      $this->log_template_action('delete', $template_id, 'Template deleted');
      return ['success' => true];
    }
    
    return ['success' => false, 'error' => 'Delete failed'];
  }

  public function apply_template_to_user($template_id, $target_user_id)
  {
    $template = $this->get_template_by_id($template_id);
    
    if (!$template) {
      return ['success' => false, 'error' => 'Template not found'];
    }
    
    $permissions = $template['permissions'];
    $applied_count = 0;
    $failed_count = 0;
    $errors = [];
    
    foreach ($permissions as $permission_id) {
      try {
        $sql = "INSERT INTO permission_delegations 
                  (delegating_user_id, receiving_user_id, permission_id, delegation_type, approval_status, start_date, end_date, template_id, created_at, updated_at)
                VALUES (?, ?, ?, 'temporary', 'approved', NOW(), " . 
                ($template['duration_days'] ? "DATE_ADD(NOW(), INTERVAL ? DAY)" : "NULL") . 
                ", ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE 
                  updated_at = NOW()";
        
        $stmt = $this->dbcrm()->prepare($sql);
        $params = [
          isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1,
          $target_user_id,
          $permission_id,
        ];
        
        if ($template['duration_days']) {
          $params[] = $template['duration_days'];
        }
        
        $params[] = $template_id;
        
        $stmt->execute($params);
        $applied_count++;
      } catch (Exception $e) {
        $failed_count++;
        $errors[] = "Permission $permission_id: " . $e->getMessage();
      }
    }
    
    $this->log_template_action('apply', $template_id, "Applied to user $target_user_id");
    
    return [
      'success' => true,
      'applied_count' => $applied_count,
      'failed_count' => $failed_count,
      'errors' => $errors
    ];
  }

  public function apply_template_to_role($template_id, $target_role_id)
  {
    $sql = "SELECT id FROM users WHERE role_id = ? AND is_active = 1";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$target_role_id]);
    $users = $stmt->fetchAll();
    
    $results = [
      'total_users' => count($users),
      'successful' => 0,
      'failed' => 0,
      'details' => []
    ];
    
    foreach ($users as $user) {
      $result = $this->apply_template_to_user($template_id, $user['id']);
      if ($result['success']) {
        $results['successful']++;
        $results['details'][] = [
          'user_id' => $user['id'],
          'status' => 'applied',
          'permissions_count' => $result['applied_count']
        ];
      } else {
        $results['failed']++;
        $results['details'][] = [
          'user_id' => $user['id'],
          'status' => 'failed',
          'error' => $result['error']
        ];
      }
    }
    
    return $results;
  }

  public function duplicate_template($template_id, $new_name)
  {
    $original = $this->get_template_by_id($template_id);
    
    if (!$original) {
      return ['success' => false, 'error' => 'Template not found'];
    }
    
    $permissions = $original['permissions'];
    
    return $this->create_template(
      $new_name,
      $original['description'] . ' (Copy)',
      $permissions,
      $original['role_id'],
      $original['duration_days']
    );
  }

  public function get_template_usage_stats($template_id)
  {
    $sql = "SELECT 
              COUNT(*) as total_applications,
              COUNT(DISTINCT receiving_user_id) as unique_users,
              COUNT(CASE WHEN approval_status = 'approved' THEN 1 END) as approved_delegations,
              COUNT(CASE WHEN approval_status = 'pending' THEN 1 END) as pending_delegations,
              MAX(created_at) as last_applied
            FROM permission_delegations
            WHERE template_id = ?";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$template_id]);
    return $stmt->fetch();
  }

  public function get_popular_templates($limit = 10)
  {
    $sql = "SELECT 
              dt.id,
              dt.name,
              dt.description,
              COUNT(DISTINCT pd.receiving_user_id) as users_applied,
              COUNT(DISTINCT pd.id) as total_delegations,
              dt.created_at,
              dt.usage_count
            FROM delegation_templates dt
            LEFT JOIN permission_delegations pd ON dt.id = pd.template_id
            WHERE dt.is_active = 1
            GROUP BY dt.id, dt.name, dt.description, dt.created_at, dt.usage_count
            ORDER BY users_applied DESC, usage_count DESC
            LIMIT ?";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
  }

  public function export_template_to_json($template_id)
  {
    $template = $this->get_template_by_id($template_id);
    
    if (!$template) {
      return ['success' => false, 'error' => 'Template not found'];
    }
    
    $export = [
      'name' => $template['name'],
      'description' => $template['description'],
      'duration_days' => $template['duration_days'],
      'permissions' => $template['permissions'],
      'exported_at' => date('Y-m-d H:i:s'),
      'version' => '1.0'
    ];
    
    return [
      'success' => true,
      'data' => json_encode($export, JSON_PRETTY_PRINT)
    ];
  }

  public function import_template_from_json($json_data, $name = null)
  {
    try {
      $data = json_decode($json_data, true);
      
      if (!isset($data['permissions']) || !is_array($data['permissions'])) {
        return ['success' => false, 'error' => 'Invalid JSON format: missing permissions array'];
      }
      
      $name = $name ?? $data['name'] ?? 'Imported Template ' . date('Y-m-d H:i');
      $description = $data['description'] ?? 'Imported from JSON';
      $duration = $data['duration_days'] ?? null;
      
      return $this->create_template($name, $description, $data['permissions'], null, $duration);
    } catch (Exception $e) {
      return ['success' => false, 'error' => 'JSON parsing error: ' . $e->getMessage()];
    }
  }

  public function validate_template($template_data)
  {
    $validation = [
      'valid' => true,
      'errors' => []
    ];
    
    if (empty($template_data['name'])) {
      $validation['valid'] = false;
      $validation['errors'][] = 'Template name is required';
    }
    
    if (!isset($template_data['permissions']) || !is_array($template_data['permissions']) || empty($template_data['permissions'])) {
      $validation['valid'] = false;
      $validation['errors'][] = 'At least one permission is required';
    }
    
    if ($template_data['duration_days'] && (!is_numeric($template_data['duration_days']) || $template_data['duration_days'] < 1)) {
      $validation['valid'] = false;
      $validation['errors'][] = 'Duration must be a positive number';
    }
    
    return $validation;
  }

  public function get_template_statistics()
  {
    $sql = "SELECT 
              COUNT(*) as total_templates,
              SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_templates,
              SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_templates,
              COUNT(DISTINCT role_id) as roles_with_templates,
              AVG(usage_count) as avg_usage_count
            FROM delegation_templates";
    $stmt = $this->dbcrm()->query($sql);
    $stmt->execute();
    return $stmt->fetch();
  }

  private function log_template_action($action, $template_id, $reason)
  {
    $sql = "INSERT INTO permission_audit_log 
              (user_id, action_type, change_reason, ip_address, created_at)
            VALUES (?, ?, ?, ?, NOW())";
    $stmt = $this->dbcrm()->prepare($sql);
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $reason_full = "Template $action: Template #$template_id - $reason";
    $stmt->execute([$user_id, 'modify', $reason_full, $ip]);
  }
}
