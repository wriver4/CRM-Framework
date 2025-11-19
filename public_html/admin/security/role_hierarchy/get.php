<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$hierarchy = new RoleHierarchy();
if ($dir == 'admin' && $subdir == 'security' && $sub_subdir == 'role_hierarchy' && $page == 'list'){
  $results = $hierarchy->get_hierarchy_tree();
  $hierarchy_list = new RoleHierarchyList($results, $lang);
  $hierarchy_list->create_table();
}
