<?php

class ComplianceReportGenerator extends Database
{

  public function __construct()
  {
    parent::__construct();
  }

  public function generate_permission_compliance_report($start_date, $end_date)
  {
    return [
      'period' => ['start' => $start_date, 'end' => $end_date],
      'access_control_summary' => $this->get_access_control_summary($start_date, $end_date),
      'permission_changes' => $this->get_permission_changes($start_date, $end_date),
      'high_risk_actions' => $this->get_high_risk_actions($start_date, $end_date),
      'unauthorized_attempts' => $this->get_unauthorized_attempts($start_date, $end_date),
      'compliance_metrics' => $this->calculate_compliance_metrics($start_date, $end_date),
      'audit_findings' => $this->identify_compliance_issues($start_date, $end_date)
    ];
  }

  public function get_access_control_summary($start_date, $end_date)
  {
    $sql = "SELECT 
              COUNT(DISTINCT pal.user_id) as active_users,
              COUNT(DISTINCT pal.target_user_id) as affected_users,
              COUNT(DISTINCT pal.permission_id) as permissions_affected,
              COUNT(*) as total_actions,
              COUNT(DISTINCT DATE(pal.created_at)) as days_active
            FROM permission_audit_log pal
            WHERE pal.created_at BETWEEN ? AND ?";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$start_date, $end_date]);
    return $stmt->fetch();
  }

  public function get_permission_changes($start_date, $end_date)
  {
    $sql = "SELECT 
              COUNT(CASE WHEN action_type = 'grant' THEN 1 END) as granted,
              COUNT(CASE WHEN action_type = 'revoke' THEN 1 END) as revoked,
              COUNT(CASE WHEN action_type = 'modify' THEN 1 END) as modified,
              COUNT(CASE WHEN action_type = 'delegate' THEN 1 END) as delegated,
              COUNT(CASE WHEN action_type = 'inherit' THEN 1 END) as inherited
            FROM permission_audit_log
            WHERE created_at BETWEEN ? AND ?";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$start_date, $end_date]);
    return $stmt->fetch();
  }

  public function get_high_risk_actions($start_date, $end_date)
  {
    $sql = "SELECT 
              pal.id,
              u.username as acting_user,
              pal.action_type,
              p.pdescription as permission_name,
              tu.username as target_user,
              pal.created_at,
              pal.reason
            FROM permission_audit_log pal
            JOIN users u ON pal.user_id = u.id
            LEFT JOIN permissions p ON pal.permission_id = p.id
            LEFT JOIN users tu ON pal.target_user_id = tu.id
            WHERE pal.created_at BETWEEN ? AND ?
            AND pal.action_type IN ('grant', 'revoke', 'delegate', 'modify')
            ORDER BY pal.created_at DESC
            LIMIT 100";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$start_date, $end_date]);
    return $stmt->fetchAll();
  }

  public function get_unauthorized_attempts($start_date, $end_date)
  {
    $sql = "SELECT 
              pal.id,
              u.username as user,
              pal.action_type,
              COUNT(*) as attempt_count,
              MAX(pal.created_at) as last_attempt
            FROM permission_audit_log pal
            JOIN users u ON pal.user_id = u.id
            WHERE pal.created_at BETWEEN ? AND ?
            AND pal.reason LIKE '%denied%' OR pal.reason LIKE '%unauthorized%'
            GROUP BY pal.user_id, u.username, pal.action_type
            ORDER BY attempt_count DESC";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$start_date, $end_date]);
    return $stmt->fetchAll();
  }

  public function calculate_compliance_metrics($start_date, $end_date)
  {
    $sql = "SELECT 
              ROUND(
                COUNT(CASE WHEN action_type IN ('grant', 'delegate') THEN 1 END) /
                NULLIF(COUNT(*), 0) * 100, 2
              ) as permission_grant_ratio,
              ROUND(
                COUNT(CASE WHEN approval_status = 'approved' THEN 1 END) /
                NULLIF((SELECT COUNT(*) FROM permission_delegations WHERE created_at BETWEEN ? AND ?), 0) * 100, 2
              ) as approval_rate,
              ROUND(
                COUNT(CASE WHEN DATEDIFF(updated_at, created_at) <= 1 THEN 1 END) /
                NULLIF(COUNT(*), 0) * 100, 2
              ) as timely_resolution_rate
            FROM permission_audit_log
            WHERE created_at BETWEEN ? AND ?";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$start_date, $end_date, $start_date, $end_date]);
    return $stmt->fetch();
  }

  public function identify_compliance_issues($start_date, $end_date)
  {
    $issues = [];
    
    $unused_perms = $this->get_unused_permissions($start_date, $end_date);
    if (count($unused_perms) > 0) {
      $issues[] = [
        'type' => 'unused_permissions',
        'severity' => 'low',
        'count' => count($unused_perms),
        'details' => 'Permissions not used in period'
      ];
    }
    
    $orphaned = $this->get_orphaned_records($start_date, $end_date);
    if (count($orphaned) > 0) {
      $issues[] = [
        'type' => 'orphaned_records',
        'severity' => 'high',
        'count' => count($orphaned),
        'details' => 'References to deleted records'
      ];
    }
    
    $expired_delegs = $this->get_expired_delegations($start_date, $end_date);
    if (count($expired_delegs) > 0) {
      $issues[] = [
        'type' => 'expired_delegations',
        'severity' => 'medium',
        'count' => count($expired_delegs),
        'details' => 'Delegations past expiration date'
      ];
    }
    
    $excessive_access = $this->get_excessive_access_users($start_date, $end_date);
    if (count($excessive_access) > 0) {
      $issues[] = [
        'type' => 'excessive_access',
        'severity' => 'medium',
        'count' => count($excessive_access),
        'details' => 'Users with unusually high permission count'
      ];
    }
    
    return $issues;
  }

  public function get_unused_permissions($start_date, $end_date)
  {
    $sql = "SELECT p.* FROM permissions p
            WHERE p.is_active = 1
            AND NOT EXISTS (
              SELECT 1 FROM permission_audit_log pal
              WHERE pal.permission_id = p.id
              AND pal.created_at BETWEEN ? AND ?
            )
            LIMIT 50";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$start_date, $end_date]);
    return $stmt->fetchAll();
  }

  public function get_orphaned_records($start_date, $end_date)
  {
    $sql = "SELECT pal.* FROM permission_audit_log pal
            LEFT JOIN permissions p ON pal.permission_id = p.id
            LEFT JOIN users u ON pal.user_id = u.id
            WHERE pal.created_at BETWEEN ? AND ?
            AND (p.id IS NULL OR u.id IS NULL)
            LIMIT 100";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$start_date, $end_date]);
    return $stmt->fetchAll();
  }

  public function get_expired_delegations($start_date, $end_date)
  {
    $sql = "SELECT * FROM permission_delegations
            WHERE end_date < NOW()
            AND approval_status = 'approved'
            AND created_at >= ?
            LIMIT 100";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$start_date]);
    return $stmt->fetchAll();
  }

  public function get_excessive_access_users($start_date, $end_date, $threshold = 50)
  {
    $sql = "SELECT 
              u.id,
              u.username,
              COUNT(DISTINCT rp.pid) as permission_count
            FROM users u
            JOIN roles r ON u.role_id = r.id
            JOIN roles_permissions rp ON r.id = rp.role_id
            WHERE rp.is_active = 1
            GROUP BY u.id, u.username
            HAVING permission_count > ?
            ORDER BY permission_count DESC
            LIMIT 20";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$threshold]);
    return $stmt->fetchAll();
  }

  public function generate_user_compliance_report($user_id, $start_date, $end_date)
  {
    $sql = "SELECT 
              u.username,
              COUNT(DISTINCT pal.action_type) as action_types,
              COUNT(DISTINCT pal.permission_id) as permissions_involved,
              COUNT(*) as total_actions,
              MAX(pal.created_at) as last_action
            FROM permission_audit_log pal
            JOIN users u ON pal.user_id = u.id
            WHERE pal.user_id = ?
            AND pal.created_at BETWEEN ? AND ?
            GROUP BY pal.user_id, u.username";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$user_id, $start_date, $end_date]);
    
    $summary = $stmt->fetch();
    $detailed_actions = $this->get_user_detailed_actions($user_id, $start_date, $end_date);
    
    return [
      'user' => $summary,
      'actions' => $detailed_actions
    ];
  }

  public function get_user_detailed_actions($user_id, $start_date, $end_date)
  {
    $sql = "SELECT 
              pal.action_type,
              COUNT(*) as count,
              MAX(pal.created_at) as last_used
            FROM permission_audit_log pal
            WHERE pal.user_id = ?
            AND pal.created_at BETWEEN ? AND ?
            GROUP BY pal.action_type
            ORDER BY count DESC";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$user_id, $start_date, $end_date]);
    return $stmt->fetchAll();
  }

  public function export_report_to_csv($report_data)
  {
    $csv = "Compliance Report\n";
    $csv .= "Period: " . $report_data['period']['start'] . " to " . $report_data['period']['end'] . "\n\n";
    
    $csv .= "Access Control Summary\n";
    $summary = $report_data['access_control_summary'];
    foreach ($summary as $key => $value) {
      $csv .= ucfirst(str_replace('_', ' ', $key)) . "," . $value . "\n";
    }
    
    $csv .= "\n\nCompliante Metrics\n";
    $metrics = $report_data['compliance_metrics'];
    foreach ($metrics as $key => $value) {
      $csv .= ucfirst(str_replace('_', ' ', $key)) . "," . $value . "\n";
    }
    
    return $csv;
  }

  public function schedule_report_generation($frequency, $start_day = 1)
  {
    $config = [
      'frequency' => $frequency,
      'start_day' => $start_day,
      'created_at' => date('Y-m-d H:i:s'),
      'next_run' => $this->calculate_next_run($frequency, $start_day)
    ];
    
    return $config;
  }

  private function calculate_next_run($frequency, $start_day)
  {
    $now = new DateTime();
    
    switch ($frequency) {
      case 'daily':
        return $now->modify('+1 day')->format('Y-m-d H:i:s');
      case 'weekly':
        return $now->modify('+7 days')->format('Y-m-d H:i:s');
      case 'monthly':
        return $now->modify('+1 month')->format('Y-m-d H:i:s');
      default:
        return $now->format('Y-m-d H:i:s');
    }
  }
}
