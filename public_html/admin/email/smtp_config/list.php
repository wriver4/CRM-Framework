<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

// Security check
$security = new Security();
$security->check_user_permissions('admin', 'read');

// Direct routing variables - these determine page navigation and template inclusion
$dir = 'admin';
$subdir = 'email';
$sub_subdir = 'smtp_config';
$page = 'list';

$table_page = true;
$table_header = true;

$search = false;
$button_showall = false;
$button_new = true;
$button_refresh = true;
$button_back = false;
$paginate = false;

require LANG . '/en.php';
$title = 'SMTP Configuration';
$new_button = 'Add Configuration';

$title_icon = '<i class="fa fa-paper-plane" aria-hidden="true"></i>';
$new_icon = '<i class="fa fa-plus" aria-hidden="true"></i>';

require HEADER;
require BODY;
require NAV;
require LISTOPEN;
require 'get.php';
?>

<?php if (isset($_SESSION['smtp_message'])): ?>
<div class="alert alert-<?php echo $_SESSION['smtp_message_type']; ?> alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($_SESSION['smtp_message']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php 
    unset($_SESSION['smtp_message']);
    unset($_SESSION['smtp_message_type']);
endif; 
?>

<?php if (empty($configs)): ?>
<div class="text-center py-5">
    <i class="fa fa-paper-plane fa-4x text-muted mb-3"></i>
    <p class="text-muted fs-5">No SMTP configurations found.</p>
    <a href="new.php" class="btn btn-primary">
        <i class="fa fa-plus me-1"></i>Add First Configuration
    </a>
</div>
<?php else: ?>
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Configuration Name</th>
                <th>User</th>
                <th>SMTP Server</th>
                <th>From Email</th>
                <th>Default</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($configs as $config): ?>
            <tr>
                <td>
                    <strong><?php echo htmlspecialchars($config['config_name']); ?></strong>
                </td>
                <td>
                    <?php if ($config['user_id']): ?>
                        <span class="badge bg-info"><?php echo htmlspecialchars($config['user_name']); ?></span>
                    <?php else: ?>
                        <span class="badge bg-secondary">System Default</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php echo htmlspecialchars($config['smtp_host']); ?>:<?php echo $config['smtp_port']; ?>
                    <small class="text-muted">(<?php echo strtoupper($config['smtp_encryption']); ?>)</small>
                </td>
                <td><?php echo htmlspecialchars($config['from_email']); ?></td>
                <td>
                    <?php if ($config['is_default']): ?>
                        <span class="badge bg-primary">Default</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($config['is_active']): ?>
                        <span class="badge bg-success">Active</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark">Inactive</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <a href="edit.php?id=<?php echo $config['id']; ?>" 
                           class="btn btn-outline-primary"
                           title="Edit">
                            <i class="fa fa-edit"></i>
                        </a>
                        <a href="delete.php?id=<?php echo $config['id']; ?>" 
                           class="btn btn-outline-danger"
                           onclick="return confirm('Delete this SMTP configuration?')"
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