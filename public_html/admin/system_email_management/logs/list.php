<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

// Authentication check
$not->loggedin();

// Security check
$security = new Security();
$security->check_user_permissions('admin', 'read');

// Direct routing variables
$dir = 'admin';
$subdir = 'system_email_management';
$sub_subdir = 'logs';
$page = 'list';

$table_page = true;
$table_header = true;

$search = true;
$button_showall = true;
$button_new = false;
$button_refresh = true;
$button_back = true;
$paginate = true;

require LANG . '/en.php';
$title = $lang['email_logs_title'];
$title_icon = '<i class="fa fa-list" aria-hidden="true"></i>';

require HEADER;
require BODY;
require NAV;
require LISTOPEN;
require 'get.php';
?>

<!-- Filter Options -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="template_id" class="form-label"><?php echo $lang['template']; ?></label>
                <select class="form-select form-select-sm" id="template_id" name="template_id">
                    <option value=""><?php echo $lang['email_logs_all_templates']; ?></option>
                    <?php foreach ($templates as $t): ?>
                    <option value="<?php echo $t['id']; ?>" <?php echo (isset($_GET['template_id']) && $_GET['template_id'] == $t['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($t['template_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="status" class="form-label"><?php echo $lang['status']; ?></label>
                <select class="form-select form-select-sm" id="status" name="status">
                    <option value=""><?php echo $lang['email_logs_all_statuses']; ?></option>
                    <option value="success" <?php echo (isset($_GET['status']) && $_GET['status'] == 'success') ? 'selected' : ''; ?>><?php echo $lang['email_logs_success']; ?></option>
                    <option value="failed" <?php echo (isset($_GET['status']) && $_GET['status'] == 'failed') ? 'selected' : ''; ?>><?php echo $lang['email_logs_failed']; ?></option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="date_from" class="form-label"><?php echo $lang['email_logs_from_date']; ?></label>
                <input type="date" class="form-control form-control-sm" id="date_from" name="date_from" value="<?php echo $_GET['date_from'] ?? ''; ?>">
            </div>
            
            <div class="col-md-2">
                <label for="date_to" class="form-label"><?php echo $lang['email_logs_to_date']; ?></label>
                <input type="date" class="form-control form-control-sm" id="date_to" name="date_to" value="<?php echo $_GET['date_to'] ?? ''; ?>">
            </div>
            
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm me-2">
                    <i class="fa fa-filter"></i> <?php echo $lang['filter']; ?>
                </button>
                <a href="list.php" class="btn btn-secondary btn-sm">
                    <i class="fa fa-times"></i> <?php echo $lang['clear']; ?>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Statistics -->
<div class="row mb-3">
    <div class="col-md-4">
        <div class="card border-info">
            <div class="card-body text-center">
                <h3 class="mb-0"><?php echo $stats['total']; ?></h3>
                <small class="text-muted"><?php echo $lang['email_logs_total']; ?></small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-success">
            <div class="card-body text-center">
                <h3 class="mb-0"><?php echo $stats['success']; ?></h3>
                <small class="text-muted"><?php echo $lang['email_logs_success']; ?></small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-danger">
            <div class="card-body text-center">
                <h3 class="mb-0"><?php echo $stats['failed']; ?></h3>
                <small class="text-muted"><?php echo $lang['email_logs_failed']; ?></small>
            </div>
        </div>
    </div>
</div>

<?php if (empty($logs)): ?>
<div class="text-center py-5">
    <i class="fa fa-inbox fa-4x text-muted mb-3"></i>
    <p class="text-muted fs-5"><?php echo $lang['email_logs_empty']; ?></p>
</div>
<?php else: ?>
<div class="table-responsive">
    <table class="table table-striped table-hover table-sm">
        <thead>
            <tr>
                <th><?php echo $lang['id']; ?></th>
                <th><?php echo $lang['template']; ?></th>
                <th><?php echo $lang['email_logs_recipient']; ?></th>
                <th><?php echo $lang['email_logs_subject']; ?></th>
                <th><?php echo $lang['status']; ?></th>
                <th><?php echo $lang['email_logs_sent_at']; ?></th>
                <th><?php echo $lang['email_logs_delivered_at']; ?></th>
                <th><?php echo $lang['actions']; ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
            <tr>
                <td><?php echo $log['id']; ?></td>
                <td>
                    <?php if ($log['template_name']): ?>
                        <small><?php echo htmlspecialchars($log['template_name']); ?></small>
                    <?php else: ?>
                        <span class="text-muted">-</span>
                    <?php endif; ?>
                </td>
                <td>
                    <small><?php echo htmlspecialchars($log['recipient_email']); ?></small>
                    <?php if ($log['recipient_name']): ?>
                        <br><small class="text-muted"><?php echo htmlspecialchars($log['recipient_name']); ?></small>
                    <?php endif; ?>
                </td>
                <td>
                    <small><?php echo htmlspecialchars(substr($log['subject'], 0, 40)); ?><?php echo strlen($log['subject']) > 40 ? '...' : ''; ?></small>
                </td>
                <td>
                    <?php
                    $statusText = $log['success'] ? $lang['email_logs_success'] : $lang['email_logs_failed'];
                    $badgeClass = $log['success'] ? 'success' : 'danger';
                    ?>
                    <span class="badge bg-<?php echo $badgeClass; ?>"><?php echo $statusText; ?></span>
                </td>
                <td>
                    <small><?php echo date('M d, g:i A', strtotime($log['sent_at'])); ?></small>
                </td>
                <td>
                    <?php if ($log['delivered_at']): ?>
                        <small><?php echo date('M d, g:i A', strtotime($log['delivered_at'])); ?></small>
                    <?php else: ?>
                        <span class="text-muted">-</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="view.php?id=<?php echo $log['id']; ?>" class="btn btn-sm btn-outline-info">
                        <i class="fa fa-eye"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php
require LISTCLOSE;
require FOOTER;
?>