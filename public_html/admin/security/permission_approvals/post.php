<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $approvals = new PermissionApprovals();
  $audit = new PermissionAuditLog();
  $user_id = $_SESSION['user_id'] ?? null;
  
  $request_id = $_POST['request_id'] ?? null;
  $action = $_POST['action'] ?? null;
  $notes = $_POST['notes'] ?? null;
  
  if ($request_id && $user_id && $action) {
    if ($action === 'approve') {
      $result = $approvals->approve_request($request_id, $user_id, $notes);
      $audit_action = 'approve';
    } elseif ($action === 'reject') {
      $result = $approvals->reject_request($request_id, $user_id, $notes);
      $audit_action = 'reject';
    } else {
      $result = false;
    }
    
    if ($result) {
      $audit->log_action([
        'user_id' => $user_id,
        'action' => $audit_action,
        'target_type' => 'permission_approval',
        'target_id' => $request_id,
        'new_value' => $notes
      ]);
      
      header('Location: list.php?success=1');
      exit;
    }
  }
}

header('Location: list.php?error=1');
exit;
