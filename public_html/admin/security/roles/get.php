<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$roles = new Roles();
if ($dir == 'admin' && $subdir == 'security' && $sub_subdir == 'roles' && $page == 'list'){
  $results = $roles->get_all();
  $roles_list = new RolesList($results, $lang);
  $roles_list->create_table();
}
