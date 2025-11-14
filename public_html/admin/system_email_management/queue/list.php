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
$sub_subdir = 'queue';
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
$title = $lang['email_queue_title'];
$title_icon = '<i class="fa fa-clock-o" aria-hidden="true"></i>';

require HEADER;
require BODY;
require NAV;
require LISTOPEN;
require 'get.php';
?>

<?php if (isset($_SESSION['email_queue_message'])): ?>
<div class="alert alert-<?php echo $_SESSION['email_queue_message_type']; ?> alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($_SESSION['email_queue_message']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php 
    unset($_SESSION['email_queue_message']);
    unset($_SESSION['email_queue_message_type']);
endif; 
?>

<!-- Filter Tabs -->
<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link <?php echo !isset($_GET['status']) ? 'active' : ''; ?>" href="list.php">
            <?php echo $lang['all']; ?> (<?php echo $counts['total']; ?>)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'active' : ''; ?>" href="list.php?status=pending">
            <?php echo $lang['email_queue_pending']; ?> (<?php echo $counts['pending']; ?>)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo (isset($_GET['status']) && $_GET['status'] == 'pending_approval') ? 'active' : ''; ?>" href="list.php?status=pending_approval">
            <?php echo $lang['email_queue_needs_approval']; ?> (<?php echo $counts['pending_approval']; ?>)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo (isset($_GET['status']) && $_GET['status'] == 'sent') ? 'active' : ''; ?>" href="list.php?status=sent">
            <?php echo $lang['email_queue_sent']; ?> (<?php echo $counts['sent']; ?>)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo (isset($_GET['status']) && $_GET['status'] == 'failed') ? 'active' : ''; ?>" href="list.php?status=failed">
            <?php echo $lang['email_queue_failed']; ?> (<?php echo $counts['failed']; ?>)
        </a>
    </li>
</ul>

<!-- Quick Actions -->
<div class="mb-3">
    <a href="process.php" class="btn btn-primary btn-sm">
        <i class="fa fa-play"></i> <?php echo $lang['email_queue_process_now']; ?>
    </a>
    <?php if ($counts['pending_approval'] > 0): ?>
    <a href="bulk_approve.php" class="btn btn-success btn-sm">
        <i class="fa fa-check-circle"></i> <?php echo $lang['email_queue_bulk_approve']; ?> (<?php echo $counts['pending_approval']; ?>)
    </a>
    <?php endif; ?>
</div>

<?php if (empty($queue_items)): ?>
<div class="text-center py-5">
    <i class="fa fa-inbox fa-4x text-muted mb-3"></i>
    <p class="text-muted fs-5"><?php echo $lang['email_queue_empty']; ?></p>
</div>
<?php else: ?>
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th><?php echo $lang['id']; ?></th>
                <th><?php echo $lang['template']; ?></th>
                <th><?php echo $lang['email_queue_recipient']; ?></th>
                <th><?php echo $lang['email_queue_subject']; ?></th>
                <th><?php echo $lang['status']; ?></th>
                <th><?php echo $lang['email_queue_priority']; ?></th>
                <th><?php echo $lang['email_queue_scheduled']; ?></th>
                <th><?php echo $lang['email_queue_attempts']; ?></th>
                <th><?php echo $lang['actions']; ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($queue_items as $item): ?>
            <tr>
                <td><?php echo $item['id']; ?></td>
                <td>
                    <?php if ($item['template_name']): ?>
                        <strong><?php echo htmlspecialchars($item['template_name']); ?></strong>
                        <br><small class="text-muted"><code><?php echo htmlspecialchars($item['template_key']); ?></code></small>
                    <?php else: ?>
                        <span class="text-muted"><?php echo $lang['email_queue_direct_email']; ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php echo htmlspecialchars($item['recipient_email']); ?>
                    <?php if ($item['recipient_name']): ?>
                        <br><small class="text-muted"><?php echo htmlspecialchars($item['recipient_name']); ?></small>
                    <?php endif; ?>
                </td>
                <td>
                    <small><?php echo htmlspecialchars(substr($item['subject'], 0, 50)); ?><?php echo strlen($item['subject']) > 50 ? '...' : ''; ?></small>
                </td>
                <td>
                    <?php
                    $statusBadges = [
                        'pending' => 'warning',
                        'pending_approval' => 'info',
                        'approved' => 'success',
                        'sent' => 'success',
                        'failed' => 'danger',
                        'rejected' => 'danger'
                    ];
                    $badgeClass = $statusBadges[$item['status']] ?? 'secondary';
                    ?>
                    <span class="badge bg-<?php echo $badgeClass; ?>"><?php echo htmlspecialchars($item['status']); ?></span>
                </td>
                <td>
                    <?php
                    $priorityBadges = [
                        'high' => 'danger',
                        'normal' => 'secondary',
                        'low' => 'info'
                    ];
                    $priorityBadge = $priorityBadges[$item['priority']] ?? 'secondary';
                    ?>
                    <span class="badge bg-<?php echo $priorityBadge; ?>"><?php echo htmlspecialchars($item['priority']); ?></span>
                </td>
                <td>
                    <?php if ($item['scheduled_at']): ?>
                        <small><?php echo date('M d, g:i A', strtotime($item['scheduled_at'])); ?></small>
                    <?php else: ?>
                        <span class="text-muted"><?php echo $lang['email_queue_immediate']; ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php echo $item['attempts']; ?> / <?php echo $item['max_attempts']; ?>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <a href="view.php?id=<?php echo $item['id']; ?>" 
                           class="btn btn-outline-info"
                           title="<?php echo $lang['view']; ?>">
                            <i class="fa fa-eye"></i>
                        </a>
                        
                        <?php if ($item['status'] === 'pending_approval'): ?>
                        <a href="approve.php?id=<?php echo $item['id']; ?>" 
                           class="btn btn-outline-success"
                           title="<?php echo $lang['email_queue_approve']; ?>">
                            <i class="fa fa-check"></i>
                        </a>
                        <a href="reject.php?id=<?php echo $item['id']; ?>" 
                           class="btn btn-outline-danger"
                           onclick="return confirm('<?php echo $lang['email_queue_reject_confirm']; ?>')"
                           title="<?php echo $lang['email_queue_reject']; ?>">
                            <i class="fa fa-times"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($item['status'] === 'failed'): ?>
                        <a href="retry.php?id=<?php echo $item['id']; ?>" 
                           class="btn btn-outline-warning"
                           title="<?php echo $lang['email_queue_retry']; ?>">
                            <i class="fa fa-refresh"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php if (in_array($item['status'], ['pending', 'pending_approval', 'failed'])): ?>
                        <a href="delete.php?id=<?php echo $item['id']; ?>" 
                           class="btn btn-outline-danger"
                           onclick="return confirm('<?php echo $lang['email_queue_delete_confirm']; ?>')"
                           title="<?php echo $lang['delete']; ?>">
                            <i class="fa fa-trash"></i>
                        </a>
                        <?php endif; ?>
                    </div>
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