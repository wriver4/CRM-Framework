<?php

class RoleRecommender extends Database
{

  public function __construct()
  {
    parent::__construct();
  }

  public function get_permission_usage_by_user($user_id, $days = 30)
  {
    $sql = "SELECT 
              p.id,
              p.pdescription as permission_name,
              p.module,
              p.action,
              COUNT(DISTINCT DATE(pal.created_at)) as days_used,
              COUNT(*) as total_actions,
              MAX(pal.created_at) as last_used
            FROM permission_audit_log pal
            JOIN permissions p ON pal.permission_id = p.id
            WHERE pal.target_user_id = ?
            AND pal.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY p.id, p.pdescription, p.module, p.action
            ORDER BY total_actions DESC";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$user_id, $days]);
    return $stmt->fetchAll();
  }

  public function get_frequently_used_permissions($limit = 20)
  {
    $sql = "SELECT 
              p.id,
              p.pdescription as permission_name,
              p.module,
              p.action,
              COUNT(DISTINCT pal.user_id) as users_using,
              COUNT(DISTINCT pal.target_user_id) as affected_users,
              COUNT(*) as total_actions,
              COUNT(DISTINCT DATE(pal.created_at)) as days_active
            FROM permission_audit_log pal
            JOIN permissions p ON pal.permission_id = p.id
            WHERE pal.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
            GROUP BY p.id, p.pdescription, p.module, p.action
            ORDER BY total_actions DESC
            LIMIT ?";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
  }

  public function calculate_role_recommendation_score($user_id, $candidate_role_id)
  {
    $user_permissions = $this->get_permission_usage_by_user($user_id, 90);
    $role_permissions = $this->get_role_permissions($candidate_role_id);
    
    if (empty($user_permissions) || empty($role_permissions)) {
      return 0;
    }
    
    $user_perm_ids = array_column($user_permissions, 'id');
    $role_perm_ids = array_column($role_permissions, 'pid');
    
    $matching = count(array_intersect($user_perm_ids, $role_perm_ids));
    $coverage = $matching / count($role_perm_ids);
    $usage_density = count($user_permissions) / 100;
    
    $score = ($coverage * 0.6) + (min($usage_density, 1.0) * 0.4);
    return round($score * 100, 2);
  }

  public function get_role_permissions($role_id)
  {
    $sql = "SELECT p.* FROM permissions p
            JOIN roles_permissions rp ON p.pid = rp.pid
            WHERE rp.role_id = ? AND p.is_active = 1 AND rp.is_active = 1";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$role_id]);
    return $stmt->fetchAll();
  }

  public function recommend_roles_for_user($user_id, $limit = 5)
  {
    $all_roles = $this->get_all_roles();
    $recommendations = [];
    
    foreach ($all_roles as $role) {
      $score = $this->calculate_role_recommendation_score($user_id, $role['id']);
      if ($score > 0) {
        $recommendations[] = [
          'role_id' => $role['id'],
          'role_name' => $role['role'],
          'score' => $score,
          'permission_coverage' => $score
        ];
      }
    }
    
    usort($recommendations, function ($a, $b) {
      return $b['score'] <=> $a['score'];
    });
    
    return array_slice($recommendations, 0, $limit);
  }

  public function get_all_roles()
  {
    $sql = "SELECT id, role FROM roles WHERE is_active = 1 AND is_system = 0 ORDER BY role ASC";
    $stmt = $this->dbcrm()->query($sql);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  public function get_role_permission_gap($user_id, $role_id)
  {
    $user_perms = $this->get_permission_usage_by_user($user_id, 90);
    $role_perms = $this->get_role_permissions($role_id);
    
    $user_perm_ids = array_column($user_perms, 'id');
    $role_perm_ids = array_column($role_perms, 'pid');
    
    $missing = array_diff($role_perm_ids, $user_perm_ids);
    $extra = array_diff($user_perm_ids, $role_perm_ids);
    
    return [
      'missing_permissions' => count($missing),
      'extra_permissions' => count($extra),
      'coverage_percentage' => empty($role_perm_ids) ? 100 : round((count($user_perm_ids) / count($role_perm_ids)) * 100, 2)
    ];
  }

  public function find_similar_users($user_id, $limit = 10)
  {
    $user_perms = $this->get_permission_usage_by_user($user_id, 90);
    $user_perm_ids = array_column($user_perms, 'id');
    
    if (empty($user_perm_ids)) {
      return [];
    }
    
    $placeholders = implode(',', array_fill(0, count($user_perm_ids), '?'));
    
    $sql = "SELECT 
              pal.target_user_id,
              u.username,
              COUNT(DISTINCT pal.permission_id) as shared_permissions,
              COUNT(*) as total_actions
            FROM permission_audit_log pal
            JOIN users u ON pal.target_user_id = u.id
            WHERE pal.permission_id IN ($placeholders)
            AND pal.target_user_id != ?
            AND pal.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
            GROUP BY pal.target_user_id, u.username
            ORDER BY shared_permissions DESC, total_actions DESC
            LIMIT ?";
    
    $stmt = $this->dbcrm()->prepare($sql);
    $params = array_merge($user_perm_ids, [$user_id, $limit]);
    $stmt->execute($params);
    return $stmt->fetchAll();
  }

  public function get_department_role_recommendations($department_id, $limit = 5)
  {
    $sql = "SELECT 
              r.id,
              r.role as role_name,
              COUNT(DISTINCT tm.user_id) as users_in_dept,
              COUNT(DISTINCT rp.pid) as permission_count,
              AVG(
                (SELECT COUNT(*) FROM permission_audit_log pal 
                 WHERE pal.target_user_id = tm.user_id 
                 AND pal.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY))
              ) as avg_permission_usage
            FROM roles r
            LEFT JOIN roles_permissions rp ON r.id = rp.role_id
            LEFT JOIN team_members tm ON tm.user_id IS NOT NULL
            WHERE r.is_active = 1
            GROUP BY r.id, r.role
            HAVING users_in_dept > 0
            ORDER BY avg_permission_usage DESC
            LIMIT ?";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
  }

  public function get_role_adoption_potential($role_id)
  {
    $role_perms = $this->get_role_permissions($role_id);
    $role_perm_ids = array_column($role_perms, 'pid');
    
    if (empty($role_perm_ids)) {
      return ['adoption_score' => 0, 'potential_users' => 0];
    }
    
    $placeholders = implode(',', array_fill(0, count($role_perm_ids), '?'));
    
    $sql = "SELECT 
              COUNT(DISTINCT pal.target_user_id) as potential_users,
              AVG(pal_count.action_count) as avg_actions_per_user
            FROM permission_audit_log pal
            WHERE pal.permission_id IN ($placeholders)
            AND pal.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
            LEFT JOIN (
              SELECT target_user_id, COUNT(*) as action_count
              FROM permission_audit_log
              WHERE created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
              GROUP BY target_user_id
            ) pal_count ON pal.target_user_id = pal_count.target_user_id";
    
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute($role_perm_ids);
    $result = $stmt->fetch();
    
    $adoption_score = $result ? round(($result['potential_users'] / 100) * 100, 2) : 0;
    
    return [
      'adoption_score' => $adoption_score,
      'potential_users' => $result['potential_users'] ?? 0,
      'avg_actions_per_user' => $result['avg_actions_per_user'] ?? 0
    ];
  }

  public function export_recommendations($user_id)
  {
    return [
      'recommended_roles' => $this->recommend_roles_for_user($user_id, 10),
      'usage_pattern' => $this->get_permission_usage_by_user($user_id, 90),
      'similar_users' => $this->find_similar_users($user_id, 5)
    ];
  }
}
