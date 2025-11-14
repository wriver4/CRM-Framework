<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();

// Security check
$security = new Security();
$security->check_user_permissions('admin', 'read');

// Direct routing variables
$dir = 'admin';
$subdir = 'system_email_management';
$sub_subdir = 'templates';
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
$title = $lang['email_templates_list'];
$new_button = $lang['email_template_new'];

$title_icon = '<i class="fa fa-file-text" aria-hidden="true"></i>';
$new_icon = '<i class="fa fa-plus" aria-hidden="true"></i>';

require HEADER;
require BODY;
require NAV;
require LISTOPEN;
require 'get.php';
?>

<?php if (isset($_SESSION['email_template_message'])): ?>
<div class="alert alert-<?php echo $_SESSION['email_template_message_type']; ?> alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($_SESSION['email_template_message']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php 
    unset($_SESSION['email_template_message']);
    unset($_SESSION['email_template_message_type']);
endif; 
?>

<!-- Filter Tabs -->
<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link <?php echo !isset($_GET['module']) ? 'active' : ''; ?>" href="list.php">
            <?php echo $lang['all_templates']; ?>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo (isset($_GET['module']) && $_GET['module'] == 'leads') ? 'active' : ''; ?>" href="list.php?module=leads">
            <?php echo $lang['leads']; ?>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo (isset($_GET['module']) && $_GET['module'] == 'referrals') ? 'active' : ''; ?>" href="list.php?module=referrals">
            <?php echo $lang['referrals']; ?>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo (isset($_GET['module']) && $_GET['module'] == 'prospects') ? 'active' : ''; ?>" href="list.php?module=prospects">
            <?php echo $lang['prospects']; ?>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo (isset($_GET['status']) && $_GET['status'] == 'inactive') ? 'active' : ''; ?>" href="list.php?status=inactive">
            <?php echo $lang['inactive']; ?>
        </a>
    </li>
</ul>

<?php if (empty($templates)): ?>
<div class="text-center py-5">
    <i class="fa fa-file-text fa-4x text-muted mb-3"></i>
    <p class="text-muted fs-5"><?php echo $lang['no_templates_found']; ?></p>
    <a href="new.php" class="btn btn-primary">
        <i class="fa fa-plus me-1"></i><?php echo $lang['create_first_template']; ?>
    </a>
</div>
<?php else: ?>
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th><?php echo $lang['template_name']; ?></th>
                <th><?php echo $lang['key']; ?></th>
                <th><?php echo $lang['module']; ?></th>
                <th><?php echo $lang['category']; ?></th>
                <th><?php echo $lang['languages']; ?></th>
                <th><?php echo $lang['variables']; ?></th>
                <th><?php echo $lang['triggers']; ?></th>
                <th><?php echo $lang['status']; ?></th>
                <th><?php echo $lang['actions']; ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($templates as $template): ?>
            <tr>
                <td>
                    <strong><?php echo htmlspecialchars($template['template_name']); ?></strong>
                    <?php if ($template['description']): ?>
                    <br><small class="text-muted"><?php echo htmlspecialchars($template['description']); ?></small>
                    <?php endif; ?>
                </td>
                <td>
                    <code><?php echo htmlspecialchars($template['template_key']); ?></code>
                </td>
                <td>
                    <span class="badge bg-primary"><?php echo htmlspecialchars($template['module']); ?></span>
                </td>
                <td>
                    <span class="badge bg-secondary"><?php echo htmlspecialchars($template['category']); ?></span>
                </td>
                <td>
                    <?php if ($template['language_count'] > 0): ?>
                        <span class="badge bg-info"><?php echo $template['language_count']; ?> <?php echo $lang['language_count']; ?></span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark"><?php echo $lang['no_content']; ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($template['variable_count'] > 0): ?>
                        <span class="badge bg-success"><?php echo $template['variable_count']; ?> <?php echo $lang['vars']; ?></span>
                    <?php else: ?>
                        <span class="text-muted"><?php echo $lang['none']; ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($template['trigger_count'] > 0): ?>
                        <span class="badge bg-warning text-dark"><?php echo $template['trigger_count']; ?> <?php echo $lang['trigger_count']; ?></span>
                    <?php else: ?>
                        <span class="text-muted"><?php echo $lang['none']; ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($template['active']): ?>
                        <span class="badge bg-success"><?php echo $lang['active']; ?></span>
                    <?php else: ?>
                        <span class="badge bg-danger"><?php echo $lang['inactive']; ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <a href="view.php?id=<?php echo $template['id']; ?>" 
                           class="btn btn-outline-info"
                           title="<?php echo $lang['view']; ?>">
                            <i class="fa fa-eye"></i>
                        </a>
                        <a href="edit.php?id=<?php echo $template['id']; ?>" 
                           class="btn btn-outline-primary"
                           title="<?php echo $lang['edit']; ?>">
                            <i class="fa fa-edit"></i>
                        </a>
                        <a href="content.php?id=<?php echo $template['id']; ?>" 
                           class="btn btn-outline-success"
                           title="<?php echo $lang['manage_content']; ?>">
                            <i class="fa fa-language"></i>
                        </a>
                        <a href="delete.php?id=<?php echo $template['id']; ?>" 
                           class="btn btn-outline-danger"
                           onclick="return confirm('<?php echo $lang['delete_template_confirm']; ?>')"
                           title="<?php echo $lang['delete']; ?>">
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