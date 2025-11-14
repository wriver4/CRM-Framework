<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();

// Security check
$security = new Security();
$security->check_user_permissions('admin', 'read');

// Direct routing variables
$dir = 'admin';
$subdir = 'system_email_management';
$page = 'index';

$table_page = false;
$table_header = false;

require LANG . '/en.php';
$title = $lang['email_template_system'];
$title_icon = '<i class="fa fa-envelope" aria-hidden="true"></i>';

require HEADER;
require BODY;
require NAV;
require SECTIONOPEN;

// Get statistics
$database = new Database();
$pdo = $database->dbcrm();

// Count templates
$stmt = $pdo->query("SELECT COUNT(*) FROM email_templates WHERE active = 1");
$active_templates = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM email_templates");
$total_templates = $stmt->fetchColumn();

// Count queue items
$stmt = $pdo->query("SELECT COUNT(*) FROM email_queue WHERE status = 'pending'");
$pending_emails = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM email_queue WHERE status = 'pending_approval'");
$pending_approval = $stmt->fetchColumn();

// Count triggers
$stmt = $pdo->query("SELECT COUNT(*) FROM email_trigger_rules WHERE active = 1");
$active_triggers = $stmt->fetchColumn();

// Recent sent emails
$stmt = $pdo->query("SELECT COUNT(*) FROM email_send_log WHERE DATE(sent_at) = CURDATE()");
$sent_today = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM email_send_log WHERE sent_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$sent_week = $stmt->fetchColumn();
?>

<div class="row mb-4">
    <div class="col-12">
        <h2><?php echo $title_icon; ?> <?php echo $lang['email_template_dashboard']; ?></h2>
        <p class="text-muted"><?php echo $lang['email_template_manage']; ?></p>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2"><?php echo $lang['active_templates']; ?></h6>
                        <h3 class="mb-0"><?php echo $active_templates; ?></h3>
                        <small class="text-muted"><?php echo $lang['of']; ?> <?php echo $total_templates; ?> <?php echo $lang['total_templates']; ?></small>
                    </div>
                    <div class="text-primary">
                        <i class="fa fa-file-text fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2"><?php echo $lang['pending_queue']; ?></h6>
                        <h3 class="mb-0"><?php echo $pending_emails; ?></h3>
                        <?php if ($pending_approval > 0): ?>
                        <small class="text-warning"><?php echo $pending_approval; ?> <?php echo $lang['need_approval']; ?></small>
                        <?php else: ?>
                        <small class="text-muted"><?php echo $lang['ready_to_send']; ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="text-warning">
                        <i class="fa fa-clock-o fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2"><?php echo $lang['active_triggers']; ?></h6>
                        <h3 class="mb-0"><?php echo $active_triggers; ?></h3>
                        <small class="text-muted"><?php echo $lang['automated_rules']; ?></small>
                    </div>
                    <div class="text-success">
                        <i class="fa fa-bolt fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2"><?php echo $lang['sent_today']; ?></h6>
                        <h3 class="mb-0"><?php echo $sent_today; ?></h3>
                        <small class="text-muted"><?php echo $sent_week; ?> <?php echo $lang['sent_this_week']; ?></small>
                    </div>
                    <div class="text-info">
                        <i class="fa fa-paper-plane fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Management Sections -->
<div class="row">
    <!-- Templates Management -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fa fa-file-text"></i> <?php echo $lang['email_templates_list']; ?></h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li><i class="fa fa-check text-success"></i> <?php echo $lang['multilingual_content']; ?></li>
                    <li><i class="fa fa-check text-success"></i> <?php echo $lang['variable_substitution']; ?></li>
                    <li><i class="fa fa-check text-success"></i> <?php echo $lang['html_plain_text']; ?></li>
                    <li><i class="fa fa-check text-success"></i> <?php echo $lang['global_headers_footers']; ?></li>
                </ul>
            </div>
            <div class="card-footer">
                <a href="templates/list.php" class="btn btn-primary">
                    <i class="fa fa-list"></i> <?php echo $lang['manage_templates']; ?>
                </a>
                <a href="templates/new.php" class="btn btn-outline-primary">
                    <i class="fa fa-plus"></i> <?php echo $lang['email_template_new']; ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Triggers Management -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fa fa-bolt"></i> <?php echo $lang['email_triggers']; ?></h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li><i class="fa fa-check text-success"></i> <?php echo $lang['lead_triggers']; ?></li>
                    <li><i class="fa fa-check text-success"></i> <?php echo $lang['conditional_logic']; ?></li>
                    <li><i class="fa fa-check text-success"></i> <?php echo $lang['multiple_recipients']; ?></li>
                    <li><i class="fa fa-check text-success"></i> <?php echo $lang['delay_scheduling']; ?></li>
                </ul>
            </div>
            <div class="card-footer">
                <a href="triggers/list.php" class="btn btn-success">
                    <i class="fa fa-list"></i> <?php echo $lang['manage_triggers']; ?>
                </a>
                <a href="triggers/new.php" class="btn btn-outline-success">
                    <i class="fa fa-plus"></i> <?php echo $lang['email_trigger_new']; ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Queue Management -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fa fa-clock-o"></i> <?php echo $lang['email_queue']; ?></h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li><i class="fa fa-check text-success"></i> <?php echo $lang['view_pending_emails']; ?></li>
                    <li><i class="fa fa-check text-success"></i> <?php echo $lang['approve_reject_emails']; ?></li>
                    <li><i class="fa fa-check text-success"></i> <?php echo $lang['retry_failed_sends']; ?></li>
                    <li><i class="fa fa-check text-success"></i> <?php echo $lang['priority_management']; ?></li>
                </ul>
            </div>
            <div class="card-footer">
                <a href="queue/list.php" class="btn btn-warning text-dark">
                    <i class="fa fa-list"></i> <?php echo $lang['view_queue']; ?>
                </a>
                <?php if ($pending_approval > 0): ?>
                <a href="queue/list.php?status=pending_approval" class="btn btn-outline-warning">
                    <i class="fa fa-check-circle"></i> <?php echo $lang['email_queue_approvals']; ?> (<?php echo $pending_approval; ?>)
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Logs & Reports -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fa fa-bar-chart"></i> <?php echo $lang['email_logs']; ?></h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li><i class="fa fa-check text-success"></i> <?php echo $lang['sent_email_history']; ?></li>
                    <li><i class="fa fa-check text-success"></i> <?php echo $lang['delivery_tracking']; ?></li>
                    <li><i class="fa fa-check text-success"></i> <?php echo $lang['error_logs']; ?></li>
                    <li><i class="fa fa-check text-success"></i> <?php echo $lang['usage_statistics']; ?></li>
                </ul>
            </div>
            <div class="card-footer">
                <a href="logs/list.php" class="btn btn-info">
                    <i class="fa fa-list"></i> <?php echo $lang['view_logs']; ?>
                </a>
                <a href="logs/stats.php" class="btn btn-outline-info">
                    <i class="fa fa-bar-chart"></i> <?php echo $lang['email_logs_stats']; ?>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fa fa-wrench"></i> <?php echo $lang['quick_actions']; ?></h5>
            </div>
            <div class="card-body">
                <div class="btn-group" role="group">
                    <a href="/test_email_templates.php" class="btn btn-outline-secondary" target="_blank">
                        <i class="fa fa-flask"></i> <?php echo $lang['test_system']; ?>
                    </a>
                    <a href="queue/process.php" class="btn btn-outline-primary">
                        <i class="fa fa-play"></i> <?php echo $lang['process_queue']; ?>
                    </a>
                    <a href="templates/variables.php" class="btn btn-outline-info">
                        <i class="fa fa-code"></i> <?php echo $lang['view_variables']; ?>
                    </a>
                    <a href="templates/global.php" class="btn btn-outline-success">
                        <i class="fa fa-globe"></i> <?php echo $lang['global_templates']; ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
require SECTIONCLOSE;
require FOOTER; 
?>