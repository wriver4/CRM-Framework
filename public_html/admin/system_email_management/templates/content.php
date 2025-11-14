<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

// Security check
$security = new Security();
$security->check_user_permissions('admin', 'update');

// Direct routing variables
$dir = 'admin';
$subdir = 'system_email_management';
$sub_subdir = 'templates';
$page = 'content';

$table_page = false;
$table_header = true;

require LANG . '/en.php';
$title = 'Manage Template Content';
$title_icon = '<i class="fa fa-language" aria-hidden="true"></i>';

require HEADER;
require BODY;
require NAV;
require 'get.php';

// Get existing content by language
$contentByLang = [];
foreach ($template_contents as $content) {
    $contentByLang[$content['language_code']] = $content;
}
?>

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="list.php">Templates</a></li>
                    <li class="breadcrumb-item"><a href="view.php?id=<?php echo $template_data['id']; ?>"><?php echo htmlspecialchars($template_data['template_name']); ?></a></li>
                    <li class="breadcrumb-item active">Content</li>
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

    <!-- Template Info -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><?php echo htmlspecialchars($template_data['template_name']); ?></h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Key:</strong> <code><?php echo htmlspecialchars($template_data['template_key']); ?></code></p>
                    <p class="mb-1"><strong>Module:</strong> <span class="badge bg-primary"><?php echo htmlspecialchars($template_data['module']); ?></span></p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Category:</strong> <span class="badge bg-secondary"><?php echo htmlspecialchars($template_data['category']); ?></span></p>
                    <p class="mb-1"><strong>Status:</strong> 
                        <?php if ($template_data['active']): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Inactive</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#content-en">
                <i class="fa fa-language"></i> English Content
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#content-es">
                <i class="fa fa-language"></i> Spanish Content
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#variables">
                <i class="fa fa-code"></i> Variables (<?php echo count($template_variables); ?>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#preview">
                <i class="fa fa-eye"></i> Preview
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <!-- English Content Tab -->
        <div class="tab-pane fade show active" id="content-en">
            <?php 
            $enContent = $contentByLang['en'] ?? null;
            include 'content_form.php'; 
            renderContentForm($template_data['id'], 'en', $enContent, $nonce_token);
            ?>
        </div>

        <!-- Spanish Content Tab -->
        <div class="tab-pane fade" id="content-es">
            <?php 
            $esContent = $contentByLang['es'] ?? null;
            include 'content_form.php'; 
            renderContentForm($template_data['id'], 'es', $esContent, $nonce_token);
            ?>
        </div>

        <!-- Variables Tab -->
        <div class="tab-pane fade" id="variables">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Template Variables</h5>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addVariableModal">
                        <i class="fa fa-plus"></i> Add Variable
                    </button>
                </div>
                <div class="card-body">
                    <?php if (empty($template_variables)): ?>
                        <p class="text-muted text-center py-4">No variables defined yet. Add variables to use in your template content.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Variable Key</th>
                                        <th>Label</th>
                                        <th>Type</th>
                                        <th>Source</th>
                                        <th>Required</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($template_variables as $var): ?>
                                    <tr>
                                        <td><code>{{<?php echo htmlspecialchars($var['variable_key']); ?>}}</code></td>
                                        <td><?php echo htmlspecialchars($var['variable_label']); ?></td>
                                        <td><span class="badge bg-info"><?php echo htmlspecialchars($var['variable_type']); ?></span></td>
                                        <td><small class="text-muted"><?php echo htmlspecialchars($var['variable_source'] ?? 'N/A'); ?></small></td>
                                        <td>
                                            <?php if ($var['is_required']): ?>
                                                <span class="badge bg-danger">Required</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Optional</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form action="post.php" method="POST" style="display:inline;" onsubmit="return confirm('Delete this variable?');">
                                                <input type="hidden" name="nonce" value="<?php echo $nonce_token; ?>">
                                                <input type="hidden" name="action" value="delete_variable">
                                                <input type="hidden" name="variable_id" value="<?php echo $var['id']; ?>">
                                                <input type="hidden" name="template_id" value="<?php echo $template_data['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    
                    <div class="alert alert-info mt-3">
                        <strong><i class="fa fa-info-circle"></i> Usage:</strong> Use variables in your content with double curly braces, e.g., <code>{{variable_key}}</code>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Tab -->
        <div class="tab-pane fade" id="preview">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Email Preview</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Preview functionality coming soon. Use the test page to see rendered output.</p>
                    <a href="/test_email_templates.php" class="btn btn-primary" target="_blank">
                        <i class="fa fa-external-link"></i> Open Test Page
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Variable Modal -->
<div class="modal fade" id="addVariableModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="post.php" method="POST">
                <input type="hidden" name="nonce" value="<?php echo $nonce_token; ?>">
                <input type="hidden" name="action" value="add_variable">
                <input type="hidden" name="template_id" value="<?php echo $template_data['id']; ?>">
                
                <div class="modal-header">
                    <h5 class="modal-title">Add Variable</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="variable_key" class="form-label">Variable Key <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="variable_key" name="variable_key" pattern="[a-z0-9_]+" required>
                        <div class="form-text">Lowercase, underscores only (e.g., lead_name)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="variable_label" class="form-label">Label <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="variable_label" name="variable_label" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="variable_description" class="form-label">Description</label>
                        <textarea class="form-control" id="variable_description" name="variable_description" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="variable_type" class="form-label">Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="variable_type" name="variable_type" required>
                            <option value="text">Text</option>
                            <option value="number">Number</option>
                            <option value="date">Date</option>
                            <option value="datetime">Date/Time</option>
                            <option value="email">Email</option>
                            <option value="url">URL</option>
                            <option value="phone">Phone</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="variable_source" class="form-label">Source</label>
                        <input type="text" class="form-control" id="variable_source" name="variable_source" placeholder="e.g., leads.full_name">
                        <div class="form-text">Database field or data source</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="default_value" class="form-label">Default Value</label>
                        <input type="text" class="form-control" id="default_value" name="default_value">
                    </div>
                    
                    <div class="mb-3">
                        <label for="sort_order" class="form-label">Sort Order</label>
                        <input type="number" class="form-control" id="sort_order" name="sort_order" value="0">
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_required" name="is_required" value="1">
                        <label class="form-check-label" for="is_required">Required</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Variable</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require FOOTER; ?>