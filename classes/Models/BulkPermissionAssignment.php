<?php

class BulkPermissionAssignment extends Database
{

  public function __construct()
  {
    parent::__construct();
  }

  public function import_permissions_from_csv($file_path)
  {
    $results = [
      'total_rows' => 0,
      'successful' => 0,
      'failed' => 0,
      'errors' => [],
      'warnings' => []
    ];
    
    if (!file_exists($file_path)) {
      $results['errors'][] = 'File not found: ' . $file_path;
      return $results;
    }
    
    $file = fopen($file_path, 'r');
    $row_num = 0;
    
    while (($row = fgetcsv($file)) !== false) {
      $row_num++;
      if ($row_num == 1) continue;
      
      $results['total_rows']++;
      
      if (count($row) < 3) {
        $results['failed']++;
        $results['errors'][] = "Row $row_num: Invalid format, expected at least 3 columns";
        continue;
      }
      
      $result = $this->assign_permission_to_user(
        intval($row[0]),
        intval($row[1]),
        $row[2] ?? 'direct',
        $row[3] ?? null
      );
      
      if ($result['success']) {
        $results['successful']++;
      } else {
        $results['failed']++;
        $results['errors'][] = "Row $row_num: " . $result['error'];
      }
    }
    
    fclose($file);
    return $results;
  }

  public function assign_permission_to_user($user_id, $permission_id, $assignment_type = 'direct', $duration_days = null)
  {
    if (!$this->validate_user_exists($user_id)) {
      return ['success' => false, 'error' => 'User not found'];
    }
    
    if (!$this->validate_permission_exists($permission_id)) {
      return ['success' => false, 'error' => 'Permission not found'];
    }
    
    try {
      $sql = "INSERT INTO permission_delegations 
                (delegating_user_id, receiving_user_id, permission_id, delegation_type, approval_status, start_date, end_date, created_at, updated_at)
              VALUES (?, ?, ?, ?, 'approved', NOW(), " . 
              ($duration_days ? "DATE_ADD(NOW(), INTERVAL ? DAY)" : "NULL") . 
              ", NOW(), NOW())
              ON DUPLICATE KEY UPDATE 
                approval_status = 'approved',
                updated_at = NOW()";
      
      $stmt = $this->dbcrm()->prepare($sql);
      $params = [
        isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1,
        $user_id,
        $permission_id,
        $assignment_type
      ];
      
      if ($duration_days) {
        $params[] = $duration_days;
      }
      
      $stmt->execute($params);
      
      $this->log_audit_action('grant', $user_id, $permission_id, 'Bulk permission assignment');
      
      return ['success' => true, 'assignment_id' => $this->dbcrm()->lastInsertId()];
    } catch (Exception $e) {
      return ['success' => false, 'error' => $e->getMessage()];
    }
  }

  public function bulk_assign_role_to_users($role_id, $user_ids)
  {
    $results = [
      'total' => count($user_ids),
      'successful' => 0,
      'failed' => 0,
      'errors' => []
    ];
    
    if (!$this->validate_role_exists($role_id)) {
      $results['errors'][] = 'Role not found';
      return $results;
    }
    
    foreach ($user_ids as $user_id) {
      $result = $this->assign_role_to_user($user_id, $role_id);
      
      if ($result['success']) {
        $results['successful']++;
      } else {
        $results['failed']++;
        $results['errors'][] = "User $user_id: " . $result['error'];
      }
    }
    
    return $results;
  }

  public function assign_role_to_user($user_id, $role_id)
  {
    if (!$this->validate_user_exists($user_id)) {
      return ['success' => false, 'error' => 'User not found'];
    }
    
    if (!$this->validate_role_exists($role_id)) {
      return ['success' => false, 'error' => 'Role not found'];
    }
    
    try {
      $sql = "UPDATE users SET role_id = ? WHERE id = ?";
      $stmt = $this->dbcrm()->prepare($sql);
      $stmt->execute([$role_id, $user_id]);
      
      $this->log_audit_action('grant', $user_id, null, "Assigned role $role_id");
      
      return ['success' => true, 'user_id' => $user_id, 'role_id' => $role_id];
    } catch (Exception $e) {
      return ['success' => false, 'error' => $e->getMessage()];
    }
  }

  public function bulk_revoke_permissions($user_id, $permission_ids)
  {
    $results = [
      'total' => count($permission_ids),
      'successful' => 0,
      'failed' => 0
    ];
    
    foreach ($permission_ids as $permission_id) {
      try {
        $sql = "DELETE FROM permission_delegations 
                WHERE receiving_user_id = ? AND permission_id = ?";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute([$user_id, $permission_id]);
        
        $this->log_audit_action('revoke', $user_id, $permission_id, 'Bulk revocation');
        $results['successful']++;
      } catch (Exception $e) {
        $results['failed']++;
      }
    }
    
    return $results;
  }

  public function export_permissions_to_csv($user_id = null, $output_path = null)
  {
    $sql = "SELECT 
              u.id as user_id,
              u.username,
              p.id as permission_id,
              p.pdescription as permission_name,
              p.module,
              p.action,
              pd.delegation_type,
              pd.approval_status,
              pd.start_date,
              pd.end_date
            FROM permission_delegations pd
            JOIN users u ON pd.receiving_user_id = u.id
            JOIN permissions p ON pd.permission_id = p.id";
    
    if ($user_id) {
      $sql .= " WHERE pd.receiving_user_id = ?";
    }
    
    $sql .= " ORDER BY u.username, p.module, p.action";
    
    $stmt = $this->dbcrm()->prepare($sql);
    
    if ($user_id) {
      $stmt->execute([$user_id]);
    } else {
      $stmt->execute();
    }
    
    $results = $stmt->fetchAll();
    
    $csv = "User ID,Username,Permission ID,Permission Name,Module,Action,Delegation Type,Approval Status,Start Date,End Date\n";
    
    foreach ($results as $row) {
      $csv .= implode(',', [
        $row['user_id'],
        '"' . $row['username'] . '"',
        $row['permission_id'],
        '"' . $row['permission_name'] . '"',
        $row['module'],
        $row['action'],
        $row['delegation_type'],
        $row['approval_status'],
        $row['start_date'],
        $row['end_date']
      ]) . "\n";
    }
    
    if ($output_path) {
      file_put_contents($output_path, $csv);
      return ['success' => true, 'file_path' => $output_path, 'row_count' => count($results)];
    }
    
    return ['csv' => $csv, 'row_count' => count($results)];
  }

  public function validate_bulk_assignment($user_ids, $permission_ids)
  {
    $validation = [
      'valid' => true,
      'errors' => [],
      'warnings' => []
    ];
    
    $invalid_users = [];
    foreach ($user_ids as $user_id) {
      if (!$this->validate_user_exists($user_id)) {
        $invalid_users[] = $user_id;
      }
    }
    
    if (count($invalid_users) > 0) {
      $validation['valid'] = false;
      $validation['errors'][] = 'Invalid user IDs: ' . implode(', ', $invalid_users);
    }
    
    $invalid_perms = [];
    foreach ($permission_ids as $perm_id) {
      if (!$this->validate_permission_exists($perm_id)) {
        $invalid_perms[] = $perm_id;
      }
    }
    
    if (count($invalid_perms) > 0) {
      $validation['valid'] = false;
      $validation['errors'][] = 'Invalid permission IDs: ' . implode(', ', $invalid_perms);
    }
    
    if (count($user_ids) > 100) {
      $validation['warnings'][] = 'Large bulk assignment: ' . count($user_ids) . ' users';
    }
    
    return $validation;
  }

  public function perform_dry_run($user_ids, $permission_ids)
  {
    $validation = $this->validate_bulk_assignment($user_ids, $permission_ids);
    
    if (!$validation['valid']) {
      return ['success' => false, 'errors' => $validation['errors']];
    }
    
    $results = [
      'success' => true,
      'simulation' => [
        'users_affected' => count(array_unique($user_ids)),
        'permissions_affected' => count(array_unique($permission_ids)),
        'total_assignments' => count($user_ids) * count($permission_ids),
        'warnings' => $validation['warnings']
      ]
    ];
    
    return $results;
  }

  private function validate_user_exists($user_id)
  {
    $sql = "SELECT 1 FROM users WHERE id = ? LIMIT 1";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$user_id]);
    return $stmt->fetch() !== false;
  }

  private function validate_permission_exists($permission_id)
  {
    $sql = "SELECT 1 FROM permissions WHERE id = ? OR pid = ? LIMIT 1";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$permission_id, $permission_id]);
    return $stmt->fetch() !== false;
  }

  private function validate_role_exists($role_id)
  {
    $sql = "SELECT 1 FROM roles WHERE id = ? LIMIT 1";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$role_id]);
    return $stmt->fetch() !== false;
  }

  private function log_audit_action($action, $target_user_id, $permission_id, $reason)
  {
    $sql = "INSERT INTO permission_audit_log 
              (user_id, action_type, target_user_id, permission_id, change_reason, ip_address, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $this->dbcrm()->prepare($sql);
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $stmt->execute([$user_id, $action, $target_user_id, $permission_id, $reason, $ip]);
  }

  public function get_bulk_assignment_history($limit = 50)
  {
    $sql = "SELECT 
              pal.user_id,
              u.username as acting_user,
              COUNT(*) as action_count,
              MAX(pal.created_at) as last_action,
              GROUP_CONCAT(DISTINCT pal.action_type) as action_types
            FROM permission_audit_log pal
            JOIN users u ON pal.user_id = u.id
            WHERE pal.change_reason LIKE '%bulk%'
            GROUP BY pal.user_id, u.username
            ORDER BY last_action DESC
            LIMIT ?";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
  }
}
