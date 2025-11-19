<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $approvals = new PermissionApprovals();
  $audit = new PermissionAuditLog();
  
  $user_id = $_SESSION['user_id'] ?? null;
  $permission_id = $_POST['permission_id'] ?? null;
  $reason = $_POST['reason'] ?? null;
  $duration = $_POST['duration'] ?? null;
  $requested_role_id = $_POST['requested_role_id'] ?? null;
  
  if ($permission_id && $user_id) {
    $data = [
      'requesting_user_id' => $user_id,
      'permission_id' => $permission_id,
      'requested_role_id' => $requested_role_id,
      'reason' => $reason
    ];
    
    $result = $approvals->create_approval_request($data);
    
    if ($result) {
      $audit->log_action([
        'user_id' => $user_id,
        'action' => 'request',
        'target_type' => 'permission_request',
        'target_id' => $permission_id,
        'new_value' => json_encode($data)
      ]);
      
      header('Location: list.php?success=1');
      exit;
    }
  }
}

header('Location: list.php?error=1');
exit;
