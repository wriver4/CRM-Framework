<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();

require LANG . '/en.php';
$title = $lang['reconciliation_results'] ?? 'Reconciliation Results';
$title_icon = '<i class="fa-solid fa-check-circle" aria-hidden="true"></i>';

require HEADER;
require BODY;
require NAV;

$action = $_POST['action'] ?? 'none';
$hierarchy = new RoleHierarchy();
$delegations = new PermissionDelegations();

?>

<div class="container-fluid mt-4">
  <div class="card">
    <div class="card-header bg-primary text-white">
      <h5 class="mb-0"><?php echo $title; ?></h5>
    </div>
    <div class="card-body">

<?php

if ($action === 'verify_orphaned') {
  echo '<h6>' . ($lang['orphaned_records'] ?? 'Orphaned Records Check') . '</h6>';
  
  $stmt = $this->dbcrm()->query("SELECT COUNT(*) as count FROM role_permission_inheritance WHERE role_id NOT IN (SELECT id FROM roles)");
  $orphaned_rpi = $stmt->fetch();
  
  $stmt = $this->dbcrm()->query("SELECT COUNT(*) as count FROM role_permission_inheritance WHERE permission_id NOT IN (SELECT id FROM permissions)");
  $orphaned_perms = $stmt->fetch();
  
  echo '<p>' . $lang['orphaned_role_permissions'] ?? 'Orphaned Role Permission Records' . ': <span class="badge badge-danger">' . $orphaned_rpi['count'] . '</span></p>';
  echo '<p>' . $lang['orphaned_permission_records'] ?? 'Orphaned Permission Records' . ': <span class="badge badge-danger">' . $orphaned_perms['count'] . '</span></p>';
  
  if ($orphaned_rpi['count'] == 0 && $orphaned_perms['count'] == 0) {
    echo '<div class="alert alert-success">' . ($lang['no_orphaned_records'] ?? 'No orphaned records found!') . '</div>';
  } else {
    echo '<div class="alert alert-warning">' . ($lang['orphaned_records_found'] ?? 'Orphaned records were found. Consider cleanup.') . '</div>';
  }
  
} elseif ($action === 'verify_hierarchy') {
  echo '<h6>' . ($lang['hierarchy_consistency'] ?? 'Hierarchy Consistency Check') . '</h6>';
  
  $roles = new Roles();
  $all_roles = $roles->get_all();
  $circular_count = 0;
  
  foreach ($all_roles as $role) {
    if ($hierarchy->check_circular_hierarchy($role['id'], $role['id'])) {
      $circular_count++;
    }
  }
  
  echo '<p>' . $lang['circular_hierarchies'] ?? 'Circular Hierarchies Found' . ': <span class="badge badge-' . ($circular_count > 0 ? 'danger' : 'success') . '">' . $circular_count . '</span></p>';
  
  if ($circular_count == 0) {
    echo '<div class="alert alert-success">' . ($lang['hierarchy_valid'] ?? 'Hierarchy is valid!') . '</div>';
  } else {
    echo '<div class="alert alert-danger">' . ($lang['circular_found'] ?? 'Circular hierarchies were found!') . '</div>';
  }
  
} elseif ($action === 'verify_delegations') {
  echo '<h6>' . ($lang['delegation_consistency'] ?? 'Delegation Consistency Check') . '</h6>';
  
  $all_delegations = $delegations->get_all();
  $expired_count = 0;
  $revoked_count = 0;
  $active_count = 0;
  
  foreach ($all_delegations as $del) {
    if ($del['approval_status'] === 'revoked') $revoked_count++;
    elseif ($del['end_date'] && strtotime($del['end_date']) < time()) $expired_count++;
    elseif ($del['approval_status'] === 'approved') $active_count++;
  }
  
  echo '<p>' . $lang['active_delegations'] ?? 'Active Delegations' . ': <span class="badge badge-success">' . $active_count . '</span></p>';
  echo '<p>' . $lang['expired_delegations'] ?? 'Expired Delegations' . ': <span class="badge badge-warning">' . $expired_count . '</span></p>';
  echo '<p>' . $lang['revoked_delegations'] ?? 'Revoked Delegations' . ': <span class="badge badge-danger">' . $revoked_count . '</span></p>';
  
  echo '<div class="alert alert-info">' . ($lang['delegation_check_complete'] ?? 'Delegation check completed.') . '</div>';
}

?>

    </div>
  </div>

  <div class="mt-3">
    <a href="list.php" class="btn btn-secondary"><?php echo $lang['back'] ?? 'Back'; ?></a>
  </div>
</div>

<?php
require FOOTER;
