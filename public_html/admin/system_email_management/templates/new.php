<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

// Security check
$security = new Security();
$security->check_user_permissions('admin', 'create');

// Direct routing variables
$dir = 'admin';
$subdir = 'system_email_management';
$sub_subdir = 'templates';
$page = 'new';

$table_page = false;
$table_header = true;

require LANG . '/en.php';
$title = 'New Email Template';
$title_icon = '<i class="fa fa-plus" aria-hidden="true"></i>';

require HEADER;
require BODY;
require NAV;
require 'get.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo $title_icon; ?> Create New Email Template</h5>
                </div>
                <div class="card-body">
                    <form action="post.php" method="POST">
                        <input type="hidden" name="nonce" value="<?php echo $nonce_token; ?>">
                        <input type="hidden" name="action" value="create">
                        
                        <!-- Template Key -->
                        <div class="mb-3">
                            <label for="template_key" class="form-label">
                                Template Key <span class="text-danger">*</span>
                                <small class="text-muted">(Unique identifier, lowercase, underscores only)</small>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="template_key" 
                                   name="template_key" 
                                   pattern="[a-z0-9_]+" 
                                   placeholder="e.g., lead_welcome_email"
                                   required>
                            <div class="form-text">Used in code to reference this template. Cannot be changed later.</div>
                        </div>
                        
                        <!-- Template Name -->
                        <div class="mb-3">
                            <label for="template_name" class="form-label">
                                Template Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="template_name" 
                                   name="template_name" 
                                   placeholder="e.g., Lead Welcome Email"
                                   required>
                            <div class="form-text">Display name for this template.</div>
                        </div>
                        
                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="2"
                                      placeholder="Brief description of when this template is used"></textarea>
                        </div>
                        
                        <div class="row">
                            <!-- Module -->
                            <div class="col-md-6 mb-3">
                                <label for="module" class="form-label">
                                    Module <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="module" name="module" required>
                                    <option value="">Select Module...</option>
                                    <option value="leads">Leads</option>
                                    <option value="referrals">Referrals</option>
                                    <option value="prospects">Prospects</option>
                                    <option value="contracting">Contracting</option>
                                    <option value="customers">Customers</option>
                                    <option value="users">Users</option>
                                    <option value="system">System</option>
                                </select>
                            </div>
                            
                            <!-- Category -->
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">
                                    Category <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Select Category...</option>
                                    <option value="notification">Notification</option>
                                    <option value="welcome">Welcome</option>
                                    <option value="reminder">Reminder</option>
                                    <option value="confirmation">Confirmation</option>
                                    <option value="alert">Alert</option>
                                    <option value="report">Report</option>
                                    <option value="marketing">Marketing</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Trigger Event -->
                        <div class="mb-3">
                            <label for="trigger_event" class="form-label">
                                Trigger Event <small class="text-muted">(Optional)</small>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="trigger_event" 
                                   name="trigger_event" 
                                   placeholder="e.g., lead_created, lead_status_changed">
                            <div class="form-text">System event that can trigger this template.</div>
                        </div>
                        
                        <!-- Trigger Conditions -->
                        <div class="mb-3">
                            <label for="trigger_conditions" class="form-label">
                                Trigger Conditions <small class="text-muted">(Optional, JSON format)</small>
                            </label>
                            <textarea class="form-control font-monospace" 
                                      id="trigger_conditions" 
                                      name="trigger_conditions" 
                                      rows="3"
                                      placeholder='{"status": "new", "source": "website"}'></textarea>
                            <div class="form-text">JSON object defining when this template should be triggered.</div>
                        </div>
                        
                        <div class="row">
                            <!-- Requires Approval -->
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="requires_approval" 
                                           name="requires_approval" 
                                           value="1">
                                    <label class="form-check-label" for="requires_approval">
                                        Requires Approval
                                    </label>
                                    <div class="form-text">Emails using this template must be approved before sending.</div>
                                </div>
                            </div>
                            
                            <!-- Active -->
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="active" 
                                           name="active" 
                                           value="1" 
                                           checked>
                                    <label class="form-check-label" for="active">
                                        Active
                                    </label>
                                    <div class="form-text">Template is available for use.</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="list.php" class="btn btn-secondary">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Create Template
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fa fa-info-circle"></i> Next Steps</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">After creating the template, you'll need to:</p>
                    <ol class="mb-0">
                        <li>Add content for at least one language (English recommended)</li>
                        <li>Define variables that will be used in the template</li>
                        <li>Optionally create trigger rules for automatic sending</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require FOOTER; ?>