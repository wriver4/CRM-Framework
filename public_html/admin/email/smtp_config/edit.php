<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

// Security check
$security = new Security();
$security->check_user_permissions('admin', 'read');

// Direct routing variables - these determine page navigation and template inclusion
$dir = 'admin';
$subdir = 'email';
$sub_subdir = 'smtp_config';
$page = 'edit';

$table_page = false;

require LANG . '/en.php';
$title = 'Edit SMTP Configuration';

$title_icon = '<i class="fa fa-paper-plane" aria-hidden="true"></i>';

require 'get.php';
require HEADER;
require BODY;
require NAV;
require SECTIONOPEN;
?>

<form action="post.php" method="POST" autocomplete="off">
    <input type="hidden" name="nonce" value="<?php echo $nonce_token; ?>">
    <input type="hidden" name="action" value="edit">
    <input type="hidden" name="id" value="<?php echo $config_data['id']; ?>">
    
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="config_name" class="form-label required">Configuration Name</label>
                <input type="text" 
                       name="config_name" 
                       id="config_name" 
                       class="form-control" 
                       value="<?php echo htmlspecialchars($config_data['config_name']); ?>"
                       placeholder="e.g., Main SMTP Server" 
                       required 
                       autofocus>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="user_id" class="form-label">User (Optional)</label>
                <select name="user_id" id="user_id" class="form-select">
                    <option value="">System Default (All Users)</option>
                    <?php foreach ($users as $user): ?>
                    <option value="<?php echo $user['id']; ?>"
                            <?php echo ($config_data['user_id'] ?? '') == $user['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($user['full_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">Leave empty for system-wide default</small>
            </div>
        </div>
    </div>

    <hr class="my-4">
    <h5 class="mb-3"><i class="fa fa-server me-2"></i>SMTP Server Settings</h5>

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="smtp_host" class="form-label required">SMTP Host</label>
                <input type="text" 
                       name="smtp_host" 
                       id="smtp_host" 
                       class="form-control" 
                       value="<?php echo htmlspecialchars($config_data['smtp_host']); ?>"
                       placeholder="smtp.example.com" 
                       required>
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="smtp_port" class="form-label required">SMTP Port</label>
                <input type="number" 
                       name="smtp_port" 
                       id="smtp_port" 
                       class="form-control" 
                       value="<?php echo htmlspecialchars($config_data['smtp_port']); ?>"
                       min="1" 
                       max="65535" 
                       required>
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label for="smtp_encryption" class="form-label required">Encryption</label>
                <select name="smtp_encryption" id="smtp_encryption" class="form-select" required>
                    <option value="tls" <?php echo $config_data['smtp_encryption'] === 'tls' ? 'selected' : ''; ?>>TLS (Port 587)</option>
                    <option value="ssl" <?php echo $config_data['smtp_encryption'] === 'ssl' ? 'selected' : ''; ?>>SSL (Port 465)</option>
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="smtp_username" class="form-label required">SMTP Username</label>
                <input type="text" 
                       name="smtp_username" 
                       id="smtp_username" 
                       class="form-control" 
                       value="<?php echo htmlspecialchars($config_data['smtp_username']); ?>"
                       autocomplete="off" 
                       required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="smtp_password" class="form-label">SMTP Password (leave blank to keep current)</label>
                <input type="password" 
                       name="smtp_password" 
                       id="smtp_password" 
                       class="form-control" 
                       autocomplete="new-password">
                <small class="form-text text-muted">Only enter a password if you want to change it</small>
            </div>
        </div>
    </div>

    <hr class="my-4">
    <h5 class="mb-3"><i class="fa fa-envelope me-2"></i>Email Settings</h5>

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="from_email" class="form-label required">From Email</label>
                <input type="email" 
                       name="from_email" 
                       id="from_email" 
                       class="form-control" 
                       value="<?php echo htmlspecialchars($config_data['from_email']); ?>"
                       placeholder="noreply@example.com" 
                       required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="from_name" class="form-label required">From Name</label>
                <input type="text" 
                       name="from_name" 
                       id="from_name" 
                       class="form-control" 
                       value="<?php echo htmlspecialchars($config_data['from_name']); ?>"
                       placeholder="Your Company Name" 
                       required>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="reply_to_email" class="form-label">Reply-To Email (Optional)</label>
                <input type="email" 
                       name="reply_to_email" 
                       id="reply_to_email" 
                       class="form-control" 
                       value="<?php echo htmlspecialchars($config_data['reply_to_email'] ?? ''); ?>"
                       placeholder="support@example.com">
            </div>
        </div>
    </div>

    <hr class="my-4">

    <div class="row">
        <div class="col-md-6">
            <div class="form-check mb-3">
                <input type="checkbox" 
                       name="is_default" 
                       id="is_default" 
                       class="form-check-input" 
                       value="1"
                       <?php echo $config_data['is_default'] ? 'checked' : ''; ?>>
                <label for="is_default" class="form-check-label">
                    Set as default configuration
                </label>
                <small class="form-text text-muted d-block">This will be used when no specific configuration is selected</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-check mb-3">
                <input type="checkbox" 
                       name="is_active" 
                       id="is_active" 
                       class="form-check-input" 
                       value="1"
                       <?php echo $config_data['is_active'] ? 'checked' : ''; ?>>
                <label for="is_active" class="form-check-label">
                    Active
                </label>
                <small class="form-text text-muted d-block">Only active configurations can be used</small>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="list.php" class="btn btn-secondary">
            <i class="fa fa-times me-1"></i>Cancel
        </a>
        <button type="submit" class="btn btn-success">
            <i class="fa fa-save me-1"></i>Update Configuration
        </button>
    </div>
</form>

<?php
require SECTIONCLOSE;
require FOOTER;