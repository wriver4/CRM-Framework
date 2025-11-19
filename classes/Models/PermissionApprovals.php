<?php

class PermissionApprovals extends Database
{

  public function __construct()
  {
    parent::__construct();
  }

  public function get_all()
  {
    $sql = "SELECT par.*, 
            ru.username as requesting_user,
            au.username as current_approver,
            p.pdescription as permission_name
            FROM permission_approval_requests par
            LEFT JOIN users ru ON par.requesting_user_id = ru.id
            LEFT JOIN users au ON par.current_approver_user_id = au.id
            LEFT JOIN permissions p ON par.permission_id = p.id
            ORDER BY par.created_at DESC";
    $stmt = $this->dbcrm()->query($sql);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  public function get_by_id($id)
  {
    $sql = "SELECT par.*, 
            ru.username as requesting_user,
            au.username as current_approver,
            p.pdescription as permission_name
            FROM permission_approval_requests par
            LEFT JOIN users ru ON par.requesting_user_id = ru.id
            LEFT JOIN users au ON par.current_approver_user_id = au.id
            LEFT JOIN permissions p ON par.permission_id = p.id
            WHERE par.id = ?";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
  }

  public function get_pending_approvals()
  {
    $sql = "SELECT par.*, 
            ru.username as requesting_user,
            au.username as current_approver,
            p.pdescription as permission_name
            FROM permission_approval_requests par
            LEFT JOIN users ru ON par.requesting_user_id = ru.id
            LEFT JOIN users au ON par.current_approver_user_id = au.id
            LEFT JOIN permissions p ON par.permission_id = p.id
            WHERE par.approval_status = 'pending'
            ORDER BY par.created_at ASC";
    $stmt = $this->dbcrm()->query($sql);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  public function get_approvals_for_user($user_id)
  {
    $sql = "SELECT par.*, 
            ru.username as requesting_user,
            au.username as current_approver,
            p.pdescription as permission_name
            FROM permission_approval_requests par
            LEFT JOIN users ru ON par.requesting_user_id = ru.id
            LEFT JOIN users au ON par.current_approver_user_id = au.id
            LEFT JOIN permissions p ON par.permission_id = p.id
            WHERE par.current_approver_user_id = ? AND par.approval_status = 'pending'
            ORDER BY par.created_at ASC";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
  }

  public function get_user_requests($user_id)
  {
    $sql = "SELECT par.*, 
            ru.username as requesting_user,
            au.username as current_approver,
            p.pdescription as permission_name
            FROM permission_approval_requests par
            LEFT JOIN users ru ON par.requesting_user_id = ru.id
            LEFT JOIN users au ON par.current_approver_user_id = au.id
            LEFT JOIN permissions p ON par.permission_id = p.id
            WHERE par.requesting_user_id = ?
            ORDER BY par.created_at DESC";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
  }

  public function create_approval_request($data)
  {
    $sql = "INSERT INTO permission_approval_requests 
            (requesting_user_id, permission_id, requested_role_id, 
             approval_status, current_approver_user_id, reason, request_expiration)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $this->dbcrm()->prepare($sql);
    return $stmt->execute([
      $data['requesting_user_id'],
      $data['permission_id'],
      $data['requested_role_id'] ?? null,
      'pending',
      $data['current_approver_user_id'] ?? null,
      $data['reason'] ?? null,
      $data['request_expiration'] ?? date('Y-m-d H:i:s', strtotime('+30 days'))
    ]);
  }

  public function approve_request($id, $approved_by_user_id, $notes = null)
  {
    $sql = "UPDATE permission_approval_requests 
            SET approval_status = 'approved', approved_by_user_id = ?, approval_date = NOW(), approval_notes = ?
            WHERE id = ?";
    $stmt = $this->dbcrm()->prepare($sql);
    return $stmt->execute([$approved_by_user_id, $notes, $id]);
  }

  public function reject_request($id, $rejected_by_user_id, $reason = null)
  {
    $sql = "UPDATE permission_approval_requests 
            SET approval_status = 'rejected', rejected_by_user_id = ?, rejection_date = NOW(), rejection_reason = ?
            WHERE id = ?";
    $stmt = $this->dbcrm()->prepare($sql);
    return $stmt->execute([$rejected_by_user_id, $reason, $id]);
  }

  public function escalate_request($id, $next_approver_user_id)
  {
    $sql = "UPDATE permission_approval_requests 
            SET current_approver_user_id = ?, escalation_level = escalation_level + 1
            WHERE id = ?";
    $stmt = $this->dbcrm()->prepare($sql);
    return $stmt->execute([$next_approver_user_id, $id]);
  }

  public function get_expired_requests()
  {
    $sql = "SELECT par.*, 
            ru.username as requesting_user,
            p.pdescription as permission_name
            FROM permission_approval_requests par
            LEFT JOIN users ru ON par.requesting_user_id = ru.id
            LEFT JOIN permissions p ON par.permission_id = p.id
            WHERE par.approval_status = 'pending' AND par.request_expiration < NOW()";
    $stmt = $this->dbcrm()->query($sql);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  public function expire_request($id)
  {
    $sql = "UPDATE permission_approval_requests 
            SET approval_status = 'expired'
            WHERE id = ? AND approval_status = 'pending'";
    $stmt = $this->dbcrm()->prepare($sql);
    return $stmt->execute([$id]);
  }

  public function delete_request($id)
  {
    $sql = "DELETE FROM permission_approval_requests WHERE id = ?";
    $stmt = $this->dbcrm()->prepare($sql);
    return $stmt->execute([$id]);
  }
}
