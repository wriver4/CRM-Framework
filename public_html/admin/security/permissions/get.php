<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$perms = new Permissions();
if ($dir == 'admin' && $subdir == 'security' && $sub_subdir == 'permissions' && $page == 'list') {
  $results = $perms->get_all();
  $perms_list = new PermissionsList($results, $lang);
  $perms_list->create_table();
}
