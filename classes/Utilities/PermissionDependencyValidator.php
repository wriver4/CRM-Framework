<?php

class PermissionDependencyValidator
{
  private $hierarchy;
  private $audit;

  public function __construct()
  {
    $this->hierarchy = new RoleHierarchy();
    $this->audit = new PermissionAuditLog();
  }

  public function validate_hierarchy_assignment($parent_id, $child_id)
  {
    if ($parent_id === $child_id) {
      return [
        'valid' => false,
        'error' => 'Cannot assign a role as its own parent',
        'code' => 'SELF_REFERENCE'
      ];
    }

    if ($this->hierarchy->check_circular_hierarchy($parent_id, $child_id)) {
      return [
        'valid' => false,
        'error' => 'This assignment would create a circular hierarchy',
        'code' => 'CIRCULAR_HIERARCHY'
      ];
    }

    return [
      'valid' => true,
      'message' => 'Hierarchy assignment is valid'
    ];
  }

  public function validate_permission_delegation($user_id, $permission_id, $delegating_user_id)
  {
    if ($user_id === $delegating_user_id) {
      return [
        'valid' => false,
        'error' => 'Cannot delegate permissions to yourself',
        'code' => 'SELF_DELEGATION'
      ];
    }

    $users = new Users();
    $delegating_user = $users->getColoumn($delegating_user_id, 'role_id');
    $receiving_user = $users->getColoumn($user_id, 'role_id');

    if (!$delegating_user || !$receiving_user) {
      return [
        'valid' => false,
        'error' => 'Invalid user roles',
        'code' => 'INVALID_USER'
      ];
    }

    return [
      'valid' => true,
      'message' => 'Permission delegation is valid'
    ];
  }

  public function validate_permission_inheritance($role_id, $permission_id)
  {
    $hierarchy = new RoleHierarchy();
    $existing_perms = $hierarchy->get_all_effective_permissions($role_id);

    foreach ($existing_perms as $perm) {
      if ($perm['permission_id'] === $permission_id) {
        return [
          'valid' => false,
          'error' => 'Role already has this permission through inheritance or direct assignment',
          'code' => 'DUPLICATE_PERMISSION',
          'source' => $perm['inheritance_method']
        ];
      }
    }

    return [
      'valid' => true,
      'message' => 'Permission inheritance is valid'
    ];
  }

  public function validate_approval_chain($approval_request_id)
  {
    $approvals = new PermissionApprovals();
    $request = $approvals->get_by_id($approval_request_id);

    if (!$request) {
      return [
        'valid' => false,
        'error' => 'Approval request not found',
        'code' => 'NOT_FOUND'
      ];
    }

    if ($request['approval_status'] !== 'pending') {
      return [
        'valid' => false,
        'error' => 'Request is no longer pending',
        'code' => 'INVALID_STATUS',
        'current_status' => $request['approval_status']
      ];
    }

    if ($request['request_expiration'] && strtotime($request['request_expiration']) < time()) {
      return [
        'valid' => false,
        'error' => 'Approval request has expired',
        'code' => 'REQUEST_EXPIRED',
        'expired_at' => $request['request_expiration']
      ];
    }

    if (!$request['current_approver_user_id']) {
      return [
        'valid' => false,
        'error' => 'No approver assigned to this request',
        'code' => 'NO_APPROVER'
      ];
    }

    return [
      'valid' => true,
      'message' => 'Approval chain is valid'
    ];
  }

  public function detect_orphaned_permissions()
  {
    $db = new Database();
    $pdo = $db->dbcrm();

    $orphaned = [];

    $sql = "SELECT rpi.id, rpi.role_id, rpi.permission_id 
            FROM role_permission_inheritance rpi
            LEFT JOIN roles r ON rpi.role_id = r.id
            WHERE r.id IS NULL";
    $stmt = $pdo->query($sql);
    $orphaned['roles'] = $stmt->fetchAll();

    $sql = "SELECT rpi.id, rpi.role_id, rpi.permission_id 
            FROM role_permission_inheritance rpi
            LEFT JOIN permissions p ON rpi.permission_id = p.id
            WHERE p.id IS NULL";
    $stmt = $pdo->query($sql);
    $orphaned['permissions'] = $stmt->fetchAll();

    return $orphaned;
  }

  public function detect_dangling_delegations()
  {
    $db = new Database();
    $pdo = $db->dbcrm();

    $sql = "SELECT pd.id, pd.delegating_user_id, pd.receiving_user_id, pd.permission_id
            FROM permission_delegations pd
            LEFT JOIN users du ON pd.delegating_user_id = du.id
            LEFT JOIN users ru ON pd.receiving_user_id = ru.id
            LEFT JOIN permissions p ON pd.permission_id = p.id
            WHERE du.id IS NULL OR ru.id IS NULL OR p.id IS NULL";
    
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
  }

  public function cleanup_orphaned_records($dry_run = true)
  {
    $db = new Database();
    $pdo = $db->dbcrm();
    
    $cleanup_count = 0;

    $orphaned = $this->detect_orphaned_permissions();

    if (!$dry_run && count($orphaned['roles']) > 0) {
      $ids = array_column($orphaned['roles'], 'id');
      $placeholders = implode(',', array_fill(0, count($ids), '?'));
      
      $sql = "DELETE FROM role_permission_inheritance WHERE id IN ({$placeholders})";
      $stmt = $pdo->prepare($sql);
      $stmt->execute($ids);
      $cleanup_count += $stmt->rowCount();
    }

    if (!$dry_run && count($orphaned['permissions']) > 0) {
      $ids = array_column($orphaned['permissions'], 'id');
      $placeholders = implode(',', array_fill(0, count($ids), '?'));
      
      $sql = "DELETE FROM role_permission_inheritance WHERE id IN ({$placeholders})";
      $stmt = $pdo->prepare($sql);
      $stmt->execute($ids);
      $cleanup_count += $stmt->rowCount();
    }

    return [
      'dry_run' => $dry_run,
      'records_affected' => $cleanup_count,
      'orphaned_roles' => count($orphaned['roles']),
      'orphaned_permissions' => count($orphaned['permissions'])
    ];
  }

  public function get_validation_report()
  {
    $db = new Database();
    $pdo = $db->dbcrm();

    $report = [
      'total_roles' => 0,
      'total_permissions' => 0,
      'total_relationships' => 0,
      'issues_found' => []
    ];

    $sql = "SELECT COUNT(*) as count FROM roles";
    $stmt = $pdo->query($sql);
    $result = $stmt->fetch();
    $report['total_roles'] = $result['count'];

    $sql = "SELECT COUNT(*) as count FROM permissions";
    $stmt = $pdo->query($sql);
    $result = $stmt->fetch();
    $report['total_permissions'] = $result['count'];

    $sql = "SELECT COUNT(*) as count FROM role_inheritance WHERE is_active = 1";
    $stmt = $pdo->query($sql);
    $result = $stmt->fetch();
    $report['total_relationships'] = $result['count'];

    $orphaned = $this->detect_orphaned_permissions();
    if (count($orphaned['roles']) > 0) {
      $report['issues_found'][] = [
        'type' => 'orphaned_roles',
        'count' => count($orphaned['roles']),
        'severity' => 'high'
      ];
    }

    if (count($orphaned['permissions']) > 0) {
      $report['issues_found'][] = [
        'type' => 'orphaned_permissions',
        'count' => count($orphaned['permissions']),
        'severity' => 'high'
      ];
    }

    $dangling = $this->detect_dangling_delegations();
    if (count($dangling) > 0) {
      $report['issues_found'][] = [
        'type' => 'dangling_delegations',
        'count' => count($dangling),
        'severity' => 'medium'
      ];
    }

    return $report;
  }
}
