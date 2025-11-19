<?php

class PermissionConflictDetector extends Database
{

  public function __construct()
  {
    parent::__construct();
  }

  public function detect_all_conflicts()
  {
    return [
      'mutual_exclusions' => $this->detect_mutually_exclusive_permissions(),
      'role_conflicts' => $this->detect_role_conflicts(),
      'user_conflicts' => $this->detect_user_conflicts(),
      'delegation_conflicts' => $this->detect_delegation_conflicts(),
      'circular_hierarchies' => $this->detect_circular_hierarchies(),
      'permission_gaps' => $this->detect_permission_gaps()
    ];
  }

  public function detect_mutually_exclusive_permissions()
  {
    $conflicts = [];
    
    $exclusive_pairs = [
      ['admin', 'restricted'],
      ['view', 'hide'],
      ['can_create', 'cannot_create'],
      ['can_delete', 'can_not_delete']
    ];
    
    foreach ($exclusive_pairs as $pair) {
      $sql = "SELECT 
                u.id as user_id,
                u.username,
                GROUP_CONCAT(p.pdescription) as permissions,
                COUNT(*) as conflict_count
              FROM users u
              JOIN roles r ON u.role_id = r.id
              JOIN roles_permissions rp ON r.id = rp.role_id
              JOIN permissions p ON rp.pid = p.pid
              WHERE (p.pdescription LIKE ? OR p.pdescription LIKE ?)
              AND p.is_active = 1
              AND rp.is_active = 1
              GROUP BY u.id, u.username
              HAVING conflict_count > 1";
      
      $stmt = $this->dbcrm()->prepare($sql);
      $stmt->execute(['%' . $pair[0] . '%', '%' . $pair[1] . '%']);
      $results = $stmt->fetchAll();
      
      if (count($results) > 0) {
        $conflicts[] = [
          'type' => 'mutually_exclusive',
          'pair' => $pair,
          'affected_users' => $results
        ];
      }
    }
    
    return $conflicts;
  }

  public function detect_role_conflicts()
  {
    $sql = "SELECT 
              r.id,
              r.role,
              GROUP_CONCAT(DISTINCT p.action) as actions,
              COUNT(DISTINCT p.action) as action_count,
              COUNT(DISTINCT rp.pid) as permission_count
            FROM roles r
            JOIN roles_permissions rp ON r.id = rp.role_id
            JOIN permissions p ON rp.pid = p.pid
            WHERE p.is_active = 1
            AND rp.is_active = 1
            GROUP BY r.id, r.role
            HAVING action_count > 5
            ORDER BY permission_count DESC";
    
    $stmt = $this->dbcrm()->query($sql);
    $stmt->execute();
    $roles = $stmt->fetchAll();
    
    $conflicts = [];
    foreach ($roles as $role) {
      $inconsistencies = $this->analyze_role_inconsistencies($role['id']);
      if (count($inconsistencies) > 0) {
        $conflicts[] = [
          'role_id' => $role['id'],
          'role_name' => $role['role'],
          'inconsistencies' => $inconsistencies
        ];
      }
    }
    
    return $conflicts;
  }

  public function analyze_role_inconsistencies($role_id)
  {
    $inconsistencies = [];
    
    $sql = "SELECT 
              p.module,
              GROUP_CONCAT(DISTINCT p.action) as actions,
              COUNT(*) as permission_count
            FROM permissions p
            JOIN roles_permissions rp ON p.pid = rp.pid
            WHERE rp.role_id = ? AND p.is_active = 1
            GROUP BY p.module";
    
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$role_id]);
    $results = $stmt->fetchAll();
    
    foreach ($results as $result) {
      $actions = explode(',', $result['actions']);
      
      if (in_array('view', $actions) && in_array('delete', $actions) && !in_array('edit', $actions)) {
        $inconsistencies[] = [
          'module' => $result['module'],
          'issue' => 'Can delete but cannot edit',
          'severity' => 'medium'
        ];
      }
      
      if (in_array('create', $actions) && !in_array('view', $actions)) {
        $inconsistencies[] = [
          'module' => $result['module'],
          'issue' => 'Can create but cannot view',
          'severity' => 'high'
        ];
      }
    }
    
    return $inconsistencies;
  }

  public function detect_user_conflicts()
  {
    $sql = "SELECT 
              u.id,
              u.username,
              COUNT(DISTINCT r.id) as role_count,
              GROUP_CONCAT(DISTINCT r.role) as roles,
              COUNT(DISTINCT rp.pid) as total_permissions
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            LEFT JOIN roles_permissions rp ON r.id = rp.role_id
            WHERE u.is_active = 1
            GROUP BY u.id, u.username
            HAVING role_count > 2 OR total_permissions > 100";
    
    $stmt = $this->dbcrm()->query($sql);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  public function detect_delegation_conflicts()
  {
    $conflicts = [];
    
    $sql = "SELECT 
              pd1.receiving_user_id,
              pd1.permission_id,
              COUNT(*) as concurrent_delegations,
              GROUP_CONCAT(DISTINCT pd1.delegating_user_id) as delegators
            FROM permission_delegations pd1
            WHERE pd1.approval_status = 'approved'
            AND (pd1.end_date IS NULL OR pd1.end_date > NOW())
            GROUP BY pd1.receiving_user_id, pd1.permission_id
            HAVING concurrent_delegations > 1";
    
    $stmt = $this->dbcrm()->query($sql);
    $stmt->execute();
    $results = $stmt->fetchAll();
    
    foreach ($results as $result) {
      $conflicts[] = [
        'type' => 'concurrent_delegation',
        'user_id' => $result['receiving_user_id'],
        'permission_id' => $result['permission_id'],
        'delegator_count' => $result['concurrent_delegations'],
        'delegators' => $result['delegators'],
        'severity' => 'low',
        'recommendation' => 'Review delegation chain for consistency'
      ];
    }
    
    return $conflicts;
  }

  public function detect_circular_hierarchies()
  {
    $conflicts = [];
    
    $sql = "SELECT 
              rh.parent_role_id,
              rh.child_role_id,
              pr.role as parent_role,
              cr.role as child_role
            FROM role_inheritance rh
            JOIN roles pr ON rh.parent_role_id = pr.id
            JOIN roles cr ON rh.child_role_id = cr.id
            WHERE rh.is_active = 1";
    
    $stmt = $this->dbcrm()->query($sql);
    $stmt->execute();
    $hierarchies = $stmt->fetchAll();
    
    foreach ($hierarchies as $hierarchy) {
      if ($this->has_circular_path($hierarchy['parent_role_id'], $hierarchy['child_role_id'])) {
        $conflicts[] = [
          'type' => 'circular_hierarchy',
          'parent_id' => $hierarchy['parent_role_id'],
          'child_id' => $hierarchy['child_role_id'],
          'parent_name' => $hierarchy['parent_role'],
          'child_name' => $hierarchy['child_role'],
          'severity' => 'high',
          'recommendation' => 'Remove the circular inheritance relationship'
        ];
      }
    }
    
    return $conflicts;
  }

  public function has_circular_path($parent_id, $child_id, $visited = [])
  {
    if (in_array($child_id, $visited)) {
      return true;
    }
    
    $visited[] = $child_id;
    
    $sql = "SELECT child_role_id FROM role_inheritance 
            WHERE parent_role_id = ? AND is_active = 1";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$child_id]);
    $descendants = $stmt->fetchAll();
    
    foreach ($descendants as $descendant) {
      if ($descendant['child_role_id'] == $parent_id) {
        return true;
      }
      
      if ($this->has_circular_path($parent_id, $descendant['child_role_id'], $visited)) {
        return true;
      }
    }
    
    return false;
  }

  public function detect_permission_gaps()
  {
    $gaps = [];
    
    $sql = "SELECT 
              u.id,
              u.username,
              COUNT(DISTINCT rp.pid) as assigned_permissions
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            LEFT JOIN roles_permissions rp ON r.id = rp.role_id AND rp.is_active = 1
            WHERE u.is_active = 1
            GROUP BY u.id, u.username
            HAVING assigned_permissions < 5";
    
    $stmt = $this->dbcrm()->query($sql);
    $stmt->execute();
    $low_perm_users = $stmt->fetchAll();
    
    foreach ($low_perm_users as $user) {
      $gaps[] = [
        'type' => 'low_permissions',
        'user_id' => $user['id'],
        'username' => $user['username'],
        'permission_count' => $user['assigned_permissions'],
        'severity' => 'medium',
        'recommendation' => 'Review user role assignment or add missing permissions'
      ];
    }
    
    return $gaps;
  }

  public function resolve_conflict($conflict_id, $resolution_action, $resolution_notes = '')
  {
    $sql = "INSERT INTO permission_audit_log 
              (user_id, action_type, change_reason, created_at)
            VALUES (?, 'modify', ?, NOW())";
    $stmt = $this->dbcrm()->prepare($sql);
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
    $reason = "Conflict resolution: " . $resolution_action . " - " . $resolution_notes;
    $stmt->execute([$user_id, $reason]);
    
    return [
      'success' => true,
      'conflict_id' => $conflict_id,
      'action' => $resolution_action,
      'resolved_at' => date('Y-m-d H:i:s'),
      'notes' => $resolution_notes
    ];
  }

  public function generate_conflict_report()
  {
    $all_conflicts = $this->detect_all_conflicts();
    
    $total_conflicts = 0;
    $severity_count = ['high' => 0, 'medium' => 0, 'low' => 0];
    
    foreach ($all_conflicts as $category => $conflicts) {
      if (is_array($conflicts)) {
        $total_conflicts += count($conflicts);
        
        foreach ($conflicts as $conflict) {
          if (isset($conflict['severity'])) {
            $severity_count[$conflict['severity']]++;
          }
        }
      }
    }
    
    return [
      'total_conflicts' => $total_conflicts,
      'by_severity' => $severity_count,
      'conflicts' => $all_conflicts,
      'generated_at' => date('Y-m-d H:i:s'),
      'recommendations' => $this->get_conflict_resolutions($all_conflicts)
    ];
  }

  public function get_conflict_resolutions($conflicts)
  {
    $resolutions = [];
    
    foreach ($conflicts as $category => $items) {
      if (!is_array($items)) continue;
      
      foreach ($items as $item) {
        if (isset($item['recommendation'])) {
          $resolutions[] = [
            'category' => $category,
            'recommendation' => $item['recommendation'],
            'severity' => $item['severity'] ?? 'medium'
          ];
        }
      }
    }
    
    return $resolutions;
  }
}
