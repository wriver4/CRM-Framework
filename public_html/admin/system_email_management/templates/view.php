<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

// Security check
$security = new Security();
$security->check_user_permissions('admin', 'read');

// Direct routing variables
$dir = 'admin';
$subdir = 'system_email_management';
$sub_subdir = 'templates';
$page = 'view';

$table_page = false;
$table_header = true;

require LANG . '/en.php';
$title = 'View Template';
$title_icon = '<i class="fa fa-eye" aria-hidden="true"></i>';

require HEADER;
require BODY;
require NAV;
require 'get.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="list.php">Templates</a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($template_data['template_name']); ?></li>
                </ol>
            </nav>
        </div>
    </div>

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

    <!-- Template Details -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><?php echo htmlspecialchars($template_data['template_name']); ?></h5>
            <div class="btn-group">
                <a href="edit.php?id=<?php echo $template_data['id']; ?>" class="btn btn-sm btn-primary">
                    <i class="fa fa-edit"></i> Edit
                </a>
                <a href="content.php?id=<?php echo $template_data['id']; ?>" class="btn btn-sm btn-success">
                    <i class="fa fa-language"></i> Manage Content
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Template Key:</th>
                            <td><code><?php echo htmlspecialchars($template_data['template_key']); ?></code></td>
                        </tr>
                        <tr>
                            <th>Module:</th>
                            <td><span class="badge bg-primary"><?php echo htmlspecialchars($template_data['module']); ?></span></td>
                        </tr>
                        <tr>
                            <th>Category:</th>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($template_data['category']); ?></span></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <?php if ($template_data['active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Requires Approval:</th>
                            <td>
                                <?php if ($template_data['requires_approval']): ?>
                                    <span class="badge bg-warning text-dark">Yes</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">No</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Trigger Event:</th>
                            <td><?php echo $template_data['trigger_event'] ? htmlspecialchars($template_data['trigger_event']) : '<span class="text-muted">None</span>'; ?></td>
                        </tr>
                        <tr>
                            <th>Created By:</th>
                            <td><?php echo htmlspecialchars($template_data['created_by_name'] ?? 'Unknown'); ?></td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td><?php echo date('M d, Y g:i A', strtotime($template_data['created_at'])); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <?php if ($template_data['description']): ?>
            <div class="mt-3">
                <strong>Description:</strong>
                <p class="mb-0"><?php echo nl2br(htmlspecialchars($template_data['description'])); ?></p>
            </div>
            <?php endif; ?>
            
            <?php if ($template_data['trigger_conditions']): ?>
            <div class="mt-3">
                <strong>Trigger Conditions:</strong>
                <pre class="bg-light p-2 rounded"><code><?php echo htmlspecialchars(json_encode(json_decode($template_data['trigger_conditions']), JSON_PRETTY_PRINT)); ?></code></pre>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <!-- Content -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-language"></i> Content (<?php echo count($template_contents); ?> languages)</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($template_contents)): ?>
                        <p class="text-muted">No content added yet.</p>
                        <a href="content.php?id=<?php echo $template_data['id']; ?>" class="btn btn-sm btn-primary">
                            <i class="fa fa-plus"></i> Add Content
                        </a>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($template_contents as $content): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo strtoupper($content['language_code']); ?></strong>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($content['subject']); ?></small>
                                    </div>
                                    <span class="badge bg-success">✓</span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <a href="content.php?id=<?php echo $template_data['id']; ?>" class="btn btn-sm btn-outline-primary mt-2">
                            <i class="fa fa-edit"></i> Edit Content
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Variables -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-code"></i> Variables (<?php echo count($template_variables); ?>)</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($template_variables)): ?>
                        <p class="text-muted">No variables defined yet.</p>
                        <a href="content.php?id=<?php echo $template_data['id']; ?>#variables" class="btn btn-sm btn-primary">
                            <i class="fa fa-plus"></i> Add Variables
                        </a>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($template_variables as $var): ?>
                            <div class="list-group-item">
                                <code>{{<?php echo htmlspecialchars($var['variable_key']); ?>}}</code>
                                <br><small class="text-muted"><?php echo htmlspecialchars($var['variable_label']); ?></small>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <a href="content.php?id=<?php echo $template_data['id']; ?>#variables" class="btn btn-sm btn-outline-primary mt-2">
                            <i class="fa fa-edit"></i> Manage Variables
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Triggers -->
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-bolt"></i> Trigger Rules (<?php echo count($template_triggers); ?>)</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($template_triggers)): ?>
                        <p class="text-muted">No trigger rules configured.</p>
                        <a href="../triggers/new.php?template_id=<?php echo $template_data['id']; ?>" class="btn btn-sm btn-primary">
                            <i class="fa fa-plus"></i> Add Trigger Rule
                        </a>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Trigger Type</th>
                                        <th>Recipient Type</th>
                                        <th>Delay</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($template_triggers as $trigger): ?>
                                    <tr>
                                        <td><span class="badge bg-info"><?php echo htmlspecialchars($trigger['trigger_type']); ?></span></td>
                                        <td><?php echo htmlspecialchars($trigger['recipient_type']); ?></td>
                                        <td><?php echo $trigger['delay_minutes'] ? $trigger['delay_minutes'] . ' min' : 'Immediate'; ?></td>
                                        <td>
                                            <?php if ($trigger['active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="../triggers/edit.php?id=<?php echo $trigger['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between">
        <a href="list.php" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Back to List
        </a>
        <a href="delete.php?id=<?php echo $template_data['id']; ?>" 
           class="btn btn-danger"
           onclick="return confirm('Delete this template? This will also delete all content, variables, and triggers.')">
            <i class="fa fa-trash"></i> Delete Template
        </a>
    </div>
</div>

<?php require FOOTER; ?>