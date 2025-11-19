<?php

class PermissionAuditLog extends Database
{

  public function __construct()
  {
    parent::__construct();
  }

  public function get_all()
  {
    $sql = "SELECT pal.*, 
            u.username as user_name,
            u.email as user_email
            FROM permission_audit_log pal
            LEFT JOIN users u ON pal.user_id = u.id
            ORDER BY pal.created_at DESC";
    $stmt = $this->dbcrm()->query($sql);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  public function get_by_id($id)
  {
    $sql = "SELECT pal.*, 
            u.username as user_name,
            u.email as user_email
            FROM permission_audit_log pal
            LEFT JOIN users u ON pal.user_id = u.id
            WHERE pal.id = ?";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
  }

  public function get_by_user($user_id, $limit = 100, $offset = 0)
  {
    $sql = "SELECT pal.*, 
            u.username as user_name
            FROM permission_audit_log pal
            LEFT JOIN users u ON pal.user_id = u.id
            WHERE pal.user_id = ?
            ORDER BY pal.created_at DESC
            LIMIT ? OFFSET ?";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$user_id, $limit, $offset]);
    return $stmt->fetchAll();
  }

  public function get_by_target_type($target_type, $limit = 100, $offset = 0)
  {
    $sql = "SELECT pal.*, 
            u.username as user_name
            FROM permission_audit_log pal
            LEFT JOIN users u ON pal.user_id = u.id
            WHERE pal.target_type = ?
            ORDER BY pal.created_at DESC
            LIMIT ? OFFSET ?";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$target_type, $limit, $offset]);
    return $stmt->fetchAll();
  }

  public function get_by_action($action, $limit = 100, $offset = 0)
  {
    $sql = "SELECT pal.*, 
            u.username as user_name
            FROM permission_audit_log pal
            LEFT JOIN users u ON pal.user_id = u.id
            WHERE pal.action = ?
            ORDER BY pal.created_at DESC
            LIMIT ? OFFSET ?";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$action, $limit, $offset]);
    return $stmt->fetchAll();
  }

  public function get_by_target_id($target_type, $target_id)
  {
    $sql = "SELECT pal.*, 
            u.username as user_name
            FROM permission_audit_log pal
            LEFT JOIN users u ON pal.user_id = u.id
            WHERE pal.target_type = ? AND pal.target_id = ?
            ORDER BY pal.created_at DESC";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$target_type, $target_id]);
    return $stmt->fetchAll();
  }

  public function get_date_range($start_date, $end_date, $limit = 1000, $offset = 0)
  {
    $sql = "SELECT pal.*, 
            u.username as user_name
            FROM permission_audit_log pal
            LEFT JOIN users u ON pal.user_id = u.id
            WHERE pal.created_at BETWEEN ? AND ?
            ORDER BY pal.created_at DESC
            LIMIT ? OFFSET ?";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$start_date, $end_date, $limit, $offset]);
    return $stmt->fetchAll();
  }

  public function get_high_risk_activities($limit = 100, $offset = 0)
  {
    $sql = "SELECT pal.*, 
            u.username as user_name
            FROM permission_audit_log pal
            LEFT JOIN users u ON pal.user_id = u.id
            WHERE pal.action IN ('delete', 'revoke', 'deny')
            ORDER BY pal.created_at DESC
            LIMIT ? OFFSET ?";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$limit, $offset]);
    return $stmt->fetchAll();
  }

  public function log_action($data)
  {
    $sql = "INSERT INTO permission_audit_log 
            (user_id, action, target_type, target_id, old_value, new_value, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $this->dbcrm()->prepare($sql);
    return $stmt->execute([
      $data['user_id'],
      $data['action'],
      $data['target_type'],
      $data['target_id'],
      $data['old_value'] ?? null,
      $data['new_value'] ?? null,
      $data['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? null,
      $data['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
  }

  public function get_summary_by_user($start_date = null, $end_date = null)
  {
    $where = "1=1";
    $params = [];
    
    if ($start_date && $end_date) {
      $where .= " AND pal.created_at BETWEEN ? AND ?";
      $params = [$start_date, $end_date];
    }
    
    $sql = "SELECT u.id, u.username, COUNT(*) as total_actions,
            SUM(CASE WHEN pal.action = 'grant' THEN 1 ELSE 0 END) as grants,
            SUM(CASE WHEN pal.action = 'revoke' THEN 1 ELSE 0 END) as revokes,
            SUM(CASE WHEN pal.action = 'delete' THEN 1 ELSE 0 END) as deletes
            FROM permission_audit_log pal
            LEFT JOIN users u ON pal.user_id = u.id
            WHERE {$where}
            GROUP BY u.id, u.username
            ORDER BY total_actions DESC";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
  }

  public function get_summary_by_action($start_date = null, $end_date = null)
  {
    $where = "1=1";
    $params = [];
    
    if ($start_date && $end_date) {
      $where .= " AND pal.created_at BETWEEN ? AND ?";
      $params = [$start_date, $end_date];
    }
    
    $sql = "SELECT pal.action, COUNT(*) as count, MAX(pal.created_at) as last_action
            FROM permission_audit_log pal
            WHERE {$where}
            GROUP BY pal.action
            ORDER BY count DESC";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
  }

  public function get_summary_by_target($start_date = null, $end_date = null)
  {
    $where = "1=1";
    $params = [];
    
    if ($start_date && $end_date) {
      $where .= " AND pal.created_at BETWEEN ? AND ?";
      $params = [$start_date, $end_date];
    }
    
    $sql = "SELECT pal.target_type, COUNT(*) as count, MAX(pal.created_at) as last_action
            FROM permission_audit_log pal
            WHERE {$where}
            GROUP BY pal.target_type
            ORDER BY count DESC";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
  }

  public function export_audit_log($start_date = null, $end_date = null)
  {
    $where = "1=1";
    $params = [];
    
    if ($start_date && $end_date) {
      $where .= " AND pal.created_at BETWEEN ? AND ?";
      $params = [$start_date, $end_date];
    }
    
    $sql = "SELECT pal.id, pal.user_id, u.username, pal.action, pal.target_type, 
            pal.target_id, pal.old_value, pal.new_value, pal.ip_address, pal.created_at
            FROM permission_audit_log pal
            LEFT JOIN users u ON pal.user_id = u.id
            WHERE {$where}
            ORDER BY pal.created_at DESC";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
  }

  public function count_logs($start_date = null, $end_date = null)
  {
    $where = "1=1";
    $params = [];
    
    if ($start_date && $end_date) {
      $where .= " AND pal.created_at BETWEEN ? AND ?";
      $params = [$start_date, $end_date];
    }
    
    $sql = "SELECT COUNT(*) as count FROM permission_audit_log pal WHERE {$where}";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch();
    return $result['count'] ?? 0;
  }
}
