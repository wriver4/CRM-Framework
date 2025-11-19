<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$audit = new PermissionAuditLog();
if ($dir == 'admin' && $subdir == 'security' && $sub_subdir == 'audit_log' && $page == 'list'){
  $results = $audit->get_all();
  $audit_list = new AuditLogList($results, $lang);
  $audit_list->create_table();
}
