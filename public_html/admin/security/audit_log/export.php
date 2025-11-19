<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();

$audit = new PermissionAuditLog();

$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

$logs = $audit->export_audit_log($start_date . ' 00:00:00', $end_date . ' 23:59:59');

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=audit_log_' . date('Y-m-d_His') . '.csv');

$output = fopen('php://output', 'w');

fputcsv($output, ['ID', 'User ID', 'Username', 'Action', 'Target Type', 'Target ID', 'Old Value', 'New Value', 'IP Address', 'Date']);

foreach ($logs as $log) {
  fputcsv($output, [
    $log['id'],
    $log['user_id'],
    $log['username'],
    $log['action'],
    $log['target_type'],
    $log['target_id'],
    $log['old_value'],
    $log['new_value'],
    $log['ip_address'],
    $log['created_at']
  ]);
}

fclose($output);
exit;
