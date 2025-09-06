<?php

/**
 * Email Processing System Status
 * Monitor system health and configuration
 */

// Load system configuration
require_once '../../config/system.php';

// Security check
$security = new Security();
$security->check_user_permissions('admin', 'read');

// Set page variables for navigation
$dir = 'admin/email';
$page = 'system_status';

// Load language file
$lang = include LANG . '/en.php';

// Initialize database
$database = new Database();
$pdo = $database->dbcrm();

// System status checks
$status_checks = [];

// 1. Database connectivity
try {
    $stmt = $pdo->query("SELECT 1");
    $status_checks['database'] = [
        'name' => 'Database Connection',
        'status' => 'ok',
        'message' => 'Connected successfully'
    ];
} catch (Exception $e) {
    $status_checks['database'] = [
        'name' => 'Database Connection',
        'status' => 'error',
        'message' => 'Connection failed: ' . $e->getMessage()
    ];
}

// 2. Required tables exist
$required_tables = ['email_form_processing', 'crm_sync_queue', 'email_accounts_config'];
$missing_tables = [];

foreach ($required_tables as $table) {
    try {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        if (!$stmt->fetch()) {
            $missing_tables[] = $table;
        }
    } catch (Exception $e) {
        $missing_tables[] = $table . ' (error checking)';
    }
}

if (empty($missing_tables)) {
    $status_checks['tables'] = [
        'name' => 'Required Tables',
        'status' => 'ok',
        'message' => 'All required tables exist'
    ];
} else {
    $status_checks['tables'] = [
        'name' => 'Required Tables',
        'status' => 'error',
        'message' => 'Missing tables: ' . implode(', ', $missing_tables)
    ];
}

// 3. Email accounts configuration
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count, SUM(is_active) as active FROM email_accounts_config");
    $account_stats = $stmt->fetch();
    
    if ($account_stats['count'] == 0) {
        $status_checks['accounts'] = [
            'name' => 'Email Accounts',
            'status' => 'warning',
            'message' => 'No email accounts configured'
        ];
    } elseif ($account_stats['active'] == 0) {
        $status_checks['accounts'] = [
            'name' => 'Email Accounts',
            'status' => 'warning',
            'message' => $account_stats['count'] . ' accounts configured, but none are active'
        ];
    } else {
        $status_checks['accounts'] = [
            'name' => 'Email Accounts',
            'status' => 'ok',
            'message' => $account_stats['active'] . ' active accounts out of ' . $account_stats['count'] . ' total'
        ];
    }
} catch (Exception $e) {
    $status_checks['accounts'] = [
        'name' => 'Email Accounts',
        'status' => 'error',
        'message' => 'Error checking accounts: ' . $e->getMessage()
    ];
}

// 4. Recent processing activity
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM email_form_processing WHERE processed_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    $recent_activity = $stmt->fetchColumn();
    
    $status_checks['activity'] = [
        'name' => 'Recent Activity',
        'status' => $recent_activity > 0 ? 'ok' : 'info',
        'message' => $recent_activity . ' emails processed in the last 24 hours'
    ];
} catch (Exception $e) {
    $status_checks['activity'] = [
        'name' => 'Recent Activity',
        'status' => 'error',
        'message' => 'Error checking activity: ' . $e->getMessage()
    ];
}

// 5. Failed processing entries
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM email_form_processing WHERE processing_status = 'failed' AND processed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $failed_count = $stmt->fetchColumn();
    
    if ($failed_count == 0) {
        $status_checks['failures'] = [
            'name' => 'Processing Failures',
            'status' => 'ok',
            'message' => 'No failed processing in the last 7 days'
        ];
    } elseif ($failed_count < 5) {
        $status_checks['failures'] = [
            'name' => 'Processing Failures',
            'status' => 'warning',
            'message' => $failed_count . ' failed processing attempts in the last 7 days'
        ];
    } else {
        $status_checks['failures'] = [
            'name' => 'Processing Failures',
            'status' => 'error',
            'message' => $failed_count . ' failed processing attempts in the last 7 days - investigate!'
        ];
    }
} catch (Exception $e) {
    $status_checks['failures'] = [
        'name' => 'Processing Failures',
        'status' => 'error',
        'message' => 'Error checking failures: ' . $e->getMessage()
    ];
}

// 6. Sync queue status
try {
    $stmt = $pdo->query("SELECT sync_status, COUNT(*) as count FROM crm_sync_queue GROUP BY sync_status");
    $sync_stats = [];
    while ($row = $stmt->fetch()) {
        $sync_stats[$row['sync_status']] = $row['count'];
    }
    
    $pending = $sync_stats['pending'] ?? 0;
    $failed = $sync_stats['failed'] ?? 0;
    
    if ($failed > 10) {
        $status_checks['sync'] = [
            'name' => 'CRM Sync Queue',
            'status' => 'error',
            'message' => "High number of failed syncs: {$failed}. Pending: {$pending}"
        ];
    } elseif ($pending > 50) {
        $status_checks['sync'] = [
            'name' => 'CRM Sync Queue',
            'status' => 'warning',
            'message' => "High number of pending syncs: {$pending}. Failed: {$failed}"
        ];
    } else {
        $status_checks['sync'] = [
            'name' => 'CRM Sync Queue',
            'status' => 'ok',
            'message' => "Pending: {$pending}, Failed: {$failed}"
        ];
    }
} catch (Exception $e) {
    $status_checks['sync'] = [
        'name' => 'CRM Sync Queue',
        'status' => 'error',
        'message' => 'Error checking sync queue: ' . $e->getMessage()
    ];
}

// 7. File permissions
$log_dir = DOCROOT . '/logs';
$cron_script = DOCROOT . '/scripts/email_cron.php';

$permission_issues = [];

if (!is_writable($log_dir)) {
    $permission_issues[] = 'Logs directory not writable';
}

if (!file_exists($cron_script)) {
    $permission_issues[] = 'Cron script missing';
} elseif (!is_readable($cron_script)) {
    $permission_issues[] = 'Cron script not readable';
}

if (empty($permission_issues)) {
    $status_checks['permissions'] = [
        'name' => 'File Permissions',
        'status' => 'ok',
        'message' => 'All required files and directories accessible'
    ];
} else {
    $status_checks['permissions'] = [
        'name' => 'File Permissions',
        'status' => 'error',
        'message' => implode(', ', $permission_issues)
    ];
}

// Overall system status
$overall_status = 'ok';
foreach ($status_checks as $check) {
    if ($check['status'] === 'error') {
        $overall_status = 'error';
        break;
    } elseif ($check['status'] === 'warning' && $overall_status !== 'error') {
        $overall_status = 'warning';
    }
}

// Get recent statistics
try {
    $stats_query = "SELECT 
        DATE(processed_at) as date,
        processing_status,
        COUNT(*) as count
    FROM email_form_processing 
    WHERE processed_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAYS)
    GROUP BY DATE(processed_at), processing_status
    ORDER BY date DESC";
    
    $stmt = $pdo->query($stats_query);
    $daily_stats = $stmt->fetchAll();
} catch (Exception $e) {
    $daily_stats = [];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email System Status - <?php echo TABTITLEPREFIX; ?></title>
    <?php include HEADER; ?>
</head>
<body>
    <?php include NAV; ?>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fa fa-heartbeat me-2"></i>Email Processing System Status</h2>
                    <div>
                        <span class="badge badge-lg bg-<?php echo $overall_status === 'ok' ? 'success' : ($overall_status === 'warning' ? 'warning' : 'danger'); ?>">
                            System <?php echo ucfirst($overall_status); ?>
                        </span>
                    </div>
                </div>

                <!-- Overall Status Card -->
                <div class="card mb-4 border-<?php echo $overall_status === 'ok' ? 'success' : ($overall_status === 'warning' ? 'warning' : 'danger'); ?>">
                    <div class="card-header bg-<?php echo $overall_status === 'ok' ? 'success' : ($overall_status === 'warning' ? 'warning' : 'danger'); ?> text-white">
                        <h5 class="mb-0">
                            <i class="fa fa-<?php echo $overall_status === 'ok' ? 'check-circle' : ($overall_status === 'warning' ? 'exclamation-triangle' : 'times-circle'); ?> me-2"></i>
                            Overall System Status: <?php echo ucfirst($overall_status); ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">
                            <?php if ($overall_status === 'ok'): ?>
                                All systems are operating normally. Email processing is functioning correctly.
                            <?php elseif ($overall_status === 'warning'): ?>
                                System is operational but some issues require attention. Check the details below.
                            <?php else: ?>
                                Critical issues detected that may affect email processing. Immediate attention required.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <!-- Status Checks -->
                <div class="row">
                    <?php foreach ($status_checks as $key => $check): ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card h-100 border-<?php echo $check['status'] === 'ok' ? 'success' : ($check['status'] === 'warning' ? 'warning' : 'danger'); ?>">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fa fa-<?php echo $check['status'] === 'ok' ? 'check-circle text-success' : ($check['status'] === 'warning' ? 'exclamation-triangle text-warning' : 'times-circle text-danger'); ?> me-2"></i>
                                    <?php echo $check['name']; ?>
                                </h6>
                                <p class="card-text small"><?php echo htmlspecialchars($check['message']); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Recent Activity Chart -->
                <?php if (!empty($daily_stats)): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Processing Activity (Last 7 Days)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Success</th>
                                        <th>Failed</th>
                                        <th>Skipped</th>
                                        <th>Duplicate</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Group stats by date
                                    $grouped_stats = [];
                                    foreach ($daily_stats as $stat) {
                                        $grouped_stats[$stat['date']][$stat['processing_status']] = $stat['count'];
                                    }
                                    
                                    foreach ($grouped_stats as $date => $statuses):
                                        $total = array_sum($statuses);
                                    ?>
                                    <tr>
                                        <td><?php echo date('M j, Y', strtotime($date)); ?></td>
                                        <td><span class="badge bg-success"><?php echo $statuses['success'] ?? 0; ?></span></td>
                                        <td><span class="badge bg-danger"><?php echo $statuses['failed'] ?? 0; ?></span></td>
                                        <td><span class="badge bg-warning"><?php echo $statuses['skipped'] ?? 0; ?></span></td>
                                        <td><span class="badge bg-secondary"><?php echo $statuses['duplicate'] ?? 0; ?></span></td>
                                        <td><strong><?php echo $total; ?></strong></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Quick Actions -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <a href="/admin/email/processing_log" class="btn btn-outline-primary w-100 mb-2">
                                    <i class="fa fa-list me-1"></i>View Processing Log
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="/admin/email/accounts_config" class="btn btn-outline-info w-100 mb-2">
                                    <i class="fa fa-cog me-1"></i>Manage Accounts
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="/admin/email/sync_queue" class="btn btn-outline-warning w-100 mb-2">
                                    <i class="fa fa-sync me-1"></i>Check Sync Queue
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="/leads/email_import.php" class="btn btn-outline-success w-100 mb-2">
                                    <i class="fa fa-envelope me-1"></i>Manual Import
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Information -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">System Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <dl class="row">
                                    <dt class="col-sm-4">PHP Version:</dt>
                                    <dd class="col-sm-8"><?php echo PHP_VERSION; ?></dd>
                                    
                                    <dt class="col-sm-4">Database:</dt>
                                    <dd class="col-sm-8">
                                        <?php
                                        try {
                                            $version = $pdo->query('SELECT VERSION()')->fetchColumn();
                                            echo htmlspecialchars($version);
                                        } catch (Exception $e) {
                                            echo 'Unknown';
                                        }
                                        ?>
                                    </dd>
                                    
                                    <dt class="col-sm-4">Server Time:</dt>
                                    <dd class="col-sm-8"><?php echo date('Y-m-d H:i:s T'); ?></dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <dl class="row">
                                    <dt class="col-sm-4">IMAP Extension:</dt>
                                    <dd class="col-sm-8">
                                        <?php echo extension_loaded('imap') ? '<span class="text-success">Available</span>' : '<span class="text-danger">Not Available</span>'; ?>
                                    </dd>
                                    
                                    <dt class="col-sm-4">JSON Extension:</dt>
                                    <dd class="col-sm-8">
                                        <?php echo extension_loaded('json') ? '<span class="text-success">Available</span>' : '<span class="text-danger">Not Available</span>'; ?>
                                    </dd>
                                    
                                    <dt class="col-sm-4">Cron Script:</dt>
                                    <dd class="col-sm-8">
                                        <?php echo file_exists($cron_script) ? '<span class="text-success">Present</span>' : '<span class="text-danger">Missing</span>'; ?>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include FOOTER; ?>
</body>
</html>