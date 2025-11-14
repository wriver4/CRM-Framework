<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

// Security check
$security = new Security();
$security->check_user_permissions('admin', 'read');

// Direct routing variables
$dir = 'admin';
$subdir = 'system_email_management';
$sub_subdir = 'triggers';
$page = 'list';

$table_page = true;
$table_header = true;

$search = true;
$button_showall = true;
$button_new = true;
$button_refresh = true;
$button_back = true;
$paginate = false;

require LANG . '/en.php';
$title = 'Email Triggers';
$new_button = 'New Trigger';

$title_icon = '<i class="fa fa-bolt" aria-hidden="true"></i>';
$new_icon = '<i class="fa fa-plus" aria-hidden="true"></i>';

require HEADER;
require BODY;
require NAV;
require LISTOPEN;
require 'get.php';
?>

<?php if (isset($_SESSION['email_trigger_message'])): ?>
<div class="alert alert-<?php echo $_SESSION['email_trigger_message_type']; ?> alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($_SESSION['email_trigger_message']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php 
    unset($_SESSION['email_trigger_message']);
    unset($_SESSION['email_trigger_message_type']);
endif; 
?>

<!-- Filter Tabs -->
<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link <?php echo !isset($_GET['module']) ? 'active' : ''; ?>" href="list.php">
            All Triggers
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo (isset($_GET['module']) && $_GET['module'] == 'leads') ? 'active' : ''; ?>" href="list.php?module=leads">
            Leads
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo (isset($_GET['module']) && $_GET['module'] == 'referrals') ? 'active' : ''; ?>" href="list.php?module=referrals">
            Referrals
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo (isset($_GET['module']) && $_GET['module'] == 'prospects') ? 'active' : ''; ?>" href="list.php?module=prospects">
            Prospects
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo (isset($_GET['status']) && $_GET['status'] == 'inactive') ? 'active' : ''; ?>" href="list.php?status=inactive">
            Inactive
        </a>
    </li>
</ul>

<?php if (empty($triggers)): ?>
<div class="text-center py-5">
    <i class="fa fa-bolt fa-4x text-muted mb-3"></i>
    <p class="text-muted fs-5">No email triggers found.</p>
    <a href="new.php" class="btn btn-primary">
        <i class="fa fa-plus me-1"></i>Create First Trigger
    </a>
</div>
<?php else: ?>
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Template</th>
                <th>Module</th>
                <th>Trigger Type</th>
                <th>Recipient Type</th>
                <th>Delay</th>
                <th>Conditions</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($triggers as $trigger): ?>
            <tr>
                <td>
                    <strong><?php echo htmlspecialchars($trigger['template_name']); ?></strong>
                    <br><small class="text-muted"><code><?php echo htmlspecialchars($trigger['template_key']); ?></code></small>
                </td>
                <td>
                    <span class="badge bg-primary"><?php echo htmlspecialchars($trigger['module']); ?></span>
                </td>
                <td>
                    <span class="badge bg-info"><?php echo htmlspecialchars($trigger['trigger_type']); ?></span>
                </td>
                <td>
                    <?php
                    $recipientBadges = [
                        'lead' => 'success',
                        'assigned_user' => 'warning',
                        'custom' => 'secondary'
                    ];
                    $recipientBadge = $recipientBadges[$trigger['recipient_type']] ?? 'secondary';
                    ?>
                    <span class="badge bg-<?php echo $recipientBadge; ?>"><?php echo htmlspecialchars($trigger['recipient_type']); ?></span>
                    <?php if ($trigger['recipient_type'] === 'custom' && $trigger['custom_recipient_email']): ?>
                        <br><small class="text-muted"><?php echo htmlspecialchars($trigger['custom_recipient_email']); ?></small>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($trigger['delay_minutes'] > 0): ?>
                        <?php echo $trigger['delay_minutes']; ?> min
                    <?php else: ?>
                        <span class="text-muted">Immediate</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($trigger['trigger_condition']): ?>
                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                data-bs-toggle="modal" 
                                data-bs-target="#conditionModal<?php echo $trigger['id']; ?>">
                            <i class="fa fa-code"></i> View
                        </button>
                        
                        <!-- Condition Modal -->
                        <div class="modal fade" id="conditionModal<?php echo $trigger['id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Trigger Conditions</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <pre class="bg-light p-2 rounded"><code><?php echo htmlspecialchars(json_encode(json_decode($trigger['trigger_condition']), JSON_PRETTY_PRINT)); ?></code></pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <span class="text-muted">None</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($trigger['active']): ?>
                        <span class="badge bg-success">Active</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Inactive</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <a href="edit.php?id=<?php echo $trigger['id']; ?>" 
                           class="btn btn-outline-primary"
                           title="Edit">
                            <i class="fa fa-edit"></i>
                        </a>
                        <a href="delete.php?id=<?php echo $trigger['id']; ?>" 
                           class="btn btn-outline-danger"
                           onclick="return confirm('Delete this trigger rule?')"
                           title="Delete">
                            <i class="fa fa-trash"></i>
                        </a>
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