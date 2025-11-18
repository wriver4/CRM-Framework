<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$rps = new RolesPermissions();
if ($dir == 'admin' && $subdir == 'security' && $sub_subdir == 'roles_permissions' && $page == 'list'){
  $results = $rps->get_all();
  $rps_list = new RolesPermissionsList($results, $lang);
  $rps_list->create_table();
}
