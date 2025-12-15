<?php

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

$notifier = new DelegationExpirationNotifier();
$validator = new PermissionDependencyValidator();

$days_threshold = $_GET['days'] ?? 7;
$action = $_GET['action'] ?? 'notify';

$start_time = microtime(true);
$results = [];

if ($action === 'notify') {
  $results['notifications_sent'] = $notifier->send_expiration_notifications($days_threshold);
  $results['action'] = 'Expiration notifications sent';
} elseif ($action === 'revoke') {
  $results['delegations_revoked'] = $notifier->revoke_expired_delegations();
  $results['action'] = 'Expired delegations revoked';
} elseif ($action === 'validate') {
  $results['validation_report'] = $validator->get_validation_report();
  $results['action'] = 'Validation report generated';
} elseif ($action === 'cleanup') {
  $dry_run = $_GET['dry_run'] ?? true;
  $results['cleanup_report'] = $validator->cleanup_orphaned_records($dry_run);
  $results['action'] = 'Orphaned records cleanup ' . ($dry_run ? '(dry run)' : '(executed)');
}

$end_time = microtime(true);
$results['execution_time'] = round($end_time - $start_time, 4) . ' seconds';
$results['timestamp'] = date('Y-m-d H:i:s');

echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
