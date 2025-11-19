<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$approvals = new PermissionApprovals();
if ($dir == 'admin' && $subdir == 'security' && $sub_subdir == 'permission_requests' && $page == 'list'){
  $results = $approvals->get_all();
  $requests_list = new PermissionRequestList($results, $lang);
  $requests_list->create_table();
}
