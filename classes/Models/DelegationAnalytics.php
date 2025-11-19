<?php

class DelegationAnalytics extends Database
{

  public function __construct()
  {
    parent::__construct();
  }

  public function get_delegation_summary()
  {
    $sql = "SELECT 
              COUNT(DISTINCT id) as total_delegations,
              COUNT(DISTINCT receiving_user_id) as unique_receivers,
              COUNT(DISTINCT delegating_user_id) as unique_delegators,
              SUM(CASE WHEN approval_status = 'approved' THEN 1 ELSE 0 END) as approved_count,
              SUM(CASE WHEN approval_status = 'pending' THEN 1 ELSE 0 END) as pending_count,
              SUM(CASE WHEN approval_status = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
              SUM(CASE WHEN end_date IS NOT NULL AND end_date > NOW() THEN 1 ELSE 0 END) as active_delegations,
              SUM(CASE WHEN end_date IS NOT NULL AND end_date <= NOW() THEN 1 ELSE 0 END) as expired_delegations
            FROM permission_delegations";
    $stmt = $this->dbcrm()->query($sql);
    $stmt->execute();
    return $stmt->fetch();
  }

  public function get_delegation_trends($days = 30)
  {
    $sql = "SELECT 
              DATE(created_at) as delegation_date,
              COUNT(*) as count,
              SUM(CASE WHEN approval_status = 'approved' THEN 1 ELSE 0 END) as approved,
              SUM(CASE WHEN approval_status = 'pending' THEN 1 ELSE 0 END) as pending,
              SUM(CASE WHEN approval_status = 'rejected' THEN 1 ELSE 0 END) as rejected
            FROM permission_delegations
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(created_at)
            ORDER BY delegation_date ASC";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$days]);
    return $stmt->fetchAll();
  }

  public function get_top_delegators($limit = 10)
  {
    $sql = "SELECT 
              u.id,
              u.username,
              COUNT(*) as delegation_count,
              COUNT(DISTINCT receiving_user_id) as unique_receivers,
              SUM(CASE WHEN pd.approval_status = 'approved' THEN 1 ELSE 0 END) as approved_count,
              SUM(CASE WHEN pd.approval_status = 'rejected' THEN 1 ELSE 0 END) as rejected_count
            FROM permission_delegations pd
            JOIN users u ON pd.delegating_user_id = u.id
            GROUP BY pd.delegating_user_id, u.id, u.username
            ORDER BY delegation_count DESC
            LIMIT ?";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
  }

  public function get_top_receivers($limit = 10)
  {
    $sql = "SELECT 
              u.id,
              u.username,
              COUNT(*) as received_delegation_count,
              COUNT(DISTINCT delegating_user_id) as unique_delegators,
              SUM(CASE WHEN pd.approval_status = 'approved' THEN 1 ELSE 0 END) as approved_count,
              SUM(CASE WHEN pd.end_date > NOW() OR pd.end_date IS NULL THEN 1 ELSE 0 END) as active_count
            FROM permission_delegations pd
            JOIN users u ON pd.receiving_user_id = u.id
            WHERE pd.approval_status = 'approved'
            GROUP BY pd.receiving_user_id, u.id, u.username
            ORDER BY received_delegation_count DESC
            LIMIT ?";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
  }

  public function get_delegation_by_permission($limit = 15)
  {
    $sql = "SELECT 
              p.id,
              p.pdescription as permission_name,
              p.module,
              p.action,
              COUNT(*) as delegation_count,
              SUM(CASE WHEN pd.approval_status = 'approved' THEN 1 ELSE 0 END) as approved_count,
              COUNT(DISTINCT pd.delegating_user_id) as delegators,
              COUNT(DISTINCT pd.receiving_user_id) as receivers
            FROM permission_delegations pd
            JOIN permissions p ON pd.permission_id = p.id
            GROUP BY pd.permission_id, p.id, p.pdescription, p.module, p.action
            ORDER BY delegation_count DESC
            LIMIT ?";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
  }

  public function get_delegation_patterns($user_id = null)
  {
    $sql = "SELECT 
              u_delegator.username as delegator,
              u_receiver.username as receiver,
              COUNT(*) as delegation_count,
              COUNT(DISTINCT permission_id) as permission_types,
              MAX(created_at) as last_delegation,
              SUM(CASE WHEN approval_status = 'approved' THEN 1 ELSE 0 END) as approved_count
            FROM permission_delegations pd
            JOIN users u_delegator ON pd.delegating_user_id = u_delegator.id
            JOIN users u_receiver ON pd.receiving_user_id = u_receiver.id";
    
    if ($user_id) {
      $sql .= " WHERE pd.delegating_user_id = ? OR pd.receiving_user_id = ?";
    }
    
    $sql .= " GROUP BY pd.delegating_user_id, pd.receiving_user_id, u_delegator.username, u_receiver.username
              ORDER BY delegation_count DESC";
    
    $stmt = $this->dbcrm()->prepare($sql);
    
    if ($user_id) {
      $stmt->execute([$user_id, $user_id]);
    } else {
      $stmt->execute();
    }
    
    return $stmt->fetchAll();
  }

  public function get_delegation_chain_depth()
  {
    $sql = "SELECT 
              COUNT(*) as chain_depth,
              GROUP_CONCAT(DISTINCT delegation_type) as delegation_types,
              SUM(CASE WHEN approval_status = 'approved' THEN 1 ELSE 0 END) as approved
            FROM permission_delegations
            GROUP BY receiving_user_id
            ORDER BY chain_depth DESC
            LIMIT 20";
    $stmt = $this->dbcrm()->query($sql);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  public function get_role_delegation_analysis()
  {
    $sql = "SELECT 
              r.id,
              r.role as role_name,
              COUNT(DISTINCT pd.id) as total_delegations,
              COUNT(DISTINCT pd.receiving_user_id) as users_with_delegation,
              SUM(CASE WHEN pd.approval_status = 'approved' THEN 1 ELSE 0 END) as approved,
              SUM(CASE WHEN pd.approval_status = 'rejected' THEN 1 ELSE 0 END) as rejected,
              AVG(CASE WHEN pd.end_date IS NOT NULL THEN 
                DATEDIFF(pd.end_date, pd.start_date) ELSE NULL END) as avg_duration_days
            FROM roles r
            LEFT JOIN permission_delegations pd ON r.id = pd.granted_role_id
            GROUP BY r.id, r.role
            HAVING total_delegations > 0
            ORDER BY total_delegations DESC";
    $stmt = $this->dbcrm()->query($sql);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  public function get_delegation_expiration_analysis()
  {
    $sql = "SELECT 
              SUM(CASE WHEN end_date IS NULL THEN 1 ELSE 0 END) as indefinite_delegations,
              SUM(CASE WHEN end_date > DATE_ADD(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as expiring_after_30_days,
              SUM(CASE WHEN end_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as expiring_in_30_days,
              SUM(CASE WHEN end_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as expiring_in_7_days,
              SUM(CASE WHEN end_date < NOW() THEN 1 ELSE 0 END) as already_expired,
              SUM(CASE WHEN approval_status = 'approved' THEN 1 ELSE 0 END) as active_approved
            FROM permission_delegations";
    $stmt = $this->dbcrm()->query($sql);
    $stmt->execute();
    return $stmt->fetch();
  }

  public function get_delegation_by_status($status)
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
            WHERE pd.approval_status = ?
            ORDER BY pd.created_at DESC
            LIMIT 100";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$status]);
    return $stmt->fetchAll();
  }

  public function get_approval_performance()
  {
    $sql = "SELECT 
              approver.username as approver_name,
              COUNT(*) as total_reviewed,
              SUM(CASE WHEN pd.approval_status = 'approved' THEN 1 ELSE 0 END) as approved_count,
              SUM(CASE WHEN pd.approval_status = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
              AVG(TIMESTAMPDIFF(HOUR, pd.created_at, pd.updated_at)) as avg_review_hours
            FROM permission_delegations pd
            JOIN users approver ON pd.approved_by_user_id = approver.id
            WHERE pd.approved_by_user_id IS NOT NULL
            GROUP BY pd.approved_by_user_id, approver.username
            ORDER BY total_reviewed DESC";
    $stmt = $this->dbcrm()->query($sql);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  public function get_delegation_comparison($start_date, $end_date)
  {
    $sql = "SELECT 
              DATE(created_at) as report_date,
              COUNT(*) as delegations,
              SUM(CASE WHEN approval_status = 'approved' THEN 1 ELSE 0 END) as approved,
              SUM(CASE WHEN approval_status = 'rejected' THEN 1 ELSE 0 END) as rejected,
              SUM(CASE WHEN approval_status = 'pending' THEN 1 ELSE 0 END) as pending
            FROM permission_delegations
            WHERE DATE(created_at) BETWEEN ? AND ?
            GROUP BY DATE(created_at)
            ORDER BY report_date ASC";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$start_date, $end_date]);
    return $stmt->fetchAll();
  }

  public function export_analytics_to_array()
  {
    $data = [
      'summary' => $this->get_delegation_summary(),
      'trends' => $this->get_delegation_trends(30),
      'top_delegators' => $this->get_top_delegators(10),
      'top_receivers' => $this->get_top_receivers(10),
      'delegation_by_permission' => $this->get_delegation_by_permission(15),
      'role_analysis' => $this->get_role_delegation_analysis(),
      'expiration_analysis' => $this->get_delegation_expiration_analysis(),
      'approval_performance' => $this->get_approval_performance()
    ];
    return $data;
  }
}
