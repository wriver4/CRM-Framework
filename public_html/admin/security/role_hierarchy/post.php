<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $hierarchy = new RoleHierarchy();
  $audit = new PermissionAuditLog();
  $user_id = $_SESSION['user_id'] ?? null;
  
  $parent_role_id = $_POST['parent_role_id'] ?? null;
  $child_role_id = $_POST['child_role_id'] ?? null;
  $inheritance_type = $_POST['inheritance_type'] ?? 'full';
  
  if ($parent_role_id && $child_role_id && $user_id && $parent_role_id != $child_role_id) {
    if ($hierarchy->check_circular_hierarchy($parent_role_id, $child_role_id)) {
      header('Location: new.php?error=circular');
      exit;
    }
    
    $result = $hierarchy->add_inheritance_relationship($parent_role_id, $child_role_id, $inheritance_type);
    
    if ($result) {
      $audit->log_action([
        'user_id' => $user_id,
        'action' => 'create',
        'target_type' => 'role_hierarchy',
        'target_id' => 'parent_' . $parent_role_id . '_child_' . $child_role_id,
        'new_value' => json_encode(['parent_id' => $parent_role_id, 'child_id' => $child_role_id, 'type' => $inheritance_type])
      ]);
      
      header('Location: list.php?success=1');
      exit;
    }
  }
}

header('Location: new.php?error=1');
exit;
