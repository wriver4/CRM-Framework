<?php

class PermissionDelegations extends Database
{

  public function __construct()
  {
    parent::__construct();
  }

  public function get_all()
  {
    $sql = "SELECT pd.*, 
            du.username as delegating_user, 
            ru.username as receiving_user,
            p.pdescription as permission_name,
            r.role as role_name
            FROM permission_delegations pd
            LEFT JOIN users du ON pd.delegating_user_id = du.id
            LEFT JOIN users ru ON pd.receiving_user_id = ru.id
            LEFT JOIN permissions p ON pd.permission_id = p.id
            LEFT JOIN roles r ON pd.granted_role_id = r.id
            ORDER BY pd.created_at DESC";
    $stmt = $this->dbcrm()->query($sql);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  public function get_by_id($id)
  {
    $sql = "SELECT pd.*, 
            du.username as delegating_user, 
            ru.username as receiving_user,
            p.pdescription as permission_name,
            r.role as role_name
            FROM permission_delegations pd
            LEFT JOIN users du ON pd.delegating_user_id = du.id
            LEFT JOIN users ru ON pd.receiving_user_id = ru.id
            LEFT JOIN permissions p ON pd.permission_id = p.id
            LEFT JOIN roles r ON pd.granted_role_id = r.id
            WHERE pd.id = ?";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
  }

  public function get_active_delegations()
  {
    $sql = "SELECT pd.*, 
            du.username as delegating_user, 
            ru.username as receiving_user,
            p.pdescription as permission_name
            FROM permission_delegations pd
            LEFT JOIN users du ON pd.delegating_user_id = du.id
            LEFT JOIN users ru ON pd.receiving_user_id = ru.id
            LEFT JOIN permissions p ON pd.permission_id = p.id
            WHERE pd.approval_status = 'approved' 
            AND (pd.end_date IS NULL OR pd.end_date > NOW())
            ORDER BY pd.start_date DESC";
    $stmt = $this->dbcrm()->query($sql);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  public function get_pending_delegations()
  {
    $sql = "SELECT pd.*, 
            du.username as delegating_user, 
            ru.username as receiving_user,
            p.pdescription as permission_name
            FROM permission_delegations pd
            LEFT JOIN users du ON pd.delegating_user_id = du.id
            LEFT JOIN users ru ON pd.receiving_user_id = ru.id
            LEFT JOIN permissions p ON pd.permission_id = p.id
            WHERE pd.approval_status IN ('pending', 'approval_pending')
            ORDER BY pd.created_at DESC";
    $stmt = $this->dbcrm()->query($sql);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  public function get_user_delegations($user_id)
  {
    $sql = "SELECT pd.*, 
            du.username as delegating_user, 
            ru.username as receiving_user,
            p.pdescription as permission_name
            FROM permission_delegations pd
            LEFT JOIN users du ON pd.delegating_user_id = du.id
            LEFT JOIN users ru ON pd.receiving_user_id = ru.id
            LEFT JOIN permissions p ON pd.permission_id = p.id
            WHERE pd.receiving_user_id = ? AND pd.approval_status = 'approved'
            AND (pd.end_date IS NULL OR pd.end_date > NOW())
            ORDER BY pd.start_date DESC";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
  }

  public function create_delegation($data)
  {
    $sql = "INSERT INTO permission_delegations 
            (delegating_user_id, receiving_user_id, permission_id, granted_role_id, 
             delegation_type, approval_status, reason, start_date, end_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $this->dbcrm()->prepare($sql);
    return $stmt->execute([
      $data['delegating_user_id'],
      $data['receiving_user_id'],
      $data['permission_id'],
      $data['granted_role_id'] ?? null,
      $data['delegation_type'] ?? 'temporary',
      $data['approval_status'] ?? 'pending',
      $data['reason'] ?? null,
      $data['start_date'] ?? date('Y-m-d H:i:s'),
      $data['end_date'] ?? null
    ]);
  }

  public function update_delegation($id, $data)
  {
    $updates = [];
    $values = [];
    
    if (isset($data['approval_status'])) {
      $updates[] = 'approval_status = ?';
      $values[] = $data['approval_status'];
    }
    if (isset($data['approved_by_user_id'])) {
      $updates[] = 'approved_by_user_id = ?';
      $values[] = $data['approved_by_user_id'];
    }
    if (isset($data['restrictions'])) {
      $updates[] = 'restrictions = ?';
      $values[] = is_string($data['restrictions']) ? $data['restrictions'] : json_encode($data['restrictions']);
    }
    if (isset($data['end_date'])) {
      $updates[] = 'end_date = ?';
      $values[] = $data['end_date'];
    }
    
    if (empty($updates)) return false;
    
    $values[] = $id;
    $sql = "UPDATE permission_delegations SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $this->dbcrm()->prepare($sql);
    return $stmt->execute($values);
  }

  public function approve_delegation($id, $approved_by_user_id)
  {
    return $this->update_delegation($id, [
      'approval_status' => 'approved',
      'approved_by_user_id' => $approved_by_user_id
    ]);
  }

  public function reject_delegation($id, $approved_by_user_id)
  {
    return $this->update_delegation($id, [
      'approval_status' => 'rejected',
      'approved_by_user_id' => $approved_by_user_id
    ]);
  }

  public function revoke_delegation($id)
  {
    return $this->update_delegation($id, ['approval_status' => 'revoked']);
  }

  public function get_expiring_delegations($days = 7)
  {
    $sql = "SELECT pd.*, 
            du.username as delegating_user, 
            ru.username as receiving_user,
            ru.email as receiving_user_email,
            p.pdescription as permission_name
            FROM permission_delegations pd
            LEFT JOIN users du ON pd.delegating_user_id = du.id
            LEFT JOIN users ru ON pd.receiving_user_id = ru.id
            LEFT JOIN permissions p ON pd.permission_id = p.id
            WHERE pd.approval_status = 'approved' 
            AND pd.end_date IS NOT NULL
            AND DATE(pd.end_date) BETWEEN DATE(NOW()) AND DATE(DATE_ADD(NOW(), INTERVAL ? DAY))
            ORDER BY pd.end_date ASC";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$days]);
    return $stmt->fetchAll();
  }

  public function delete_delegation($id)
  {
    $sql = "DELETE FROM permission_delegations WHERE id = ?";
    $stmt = $this->dbcrm()->prepare($sql);
    return $stmt->execute([$id]);
  }
}
