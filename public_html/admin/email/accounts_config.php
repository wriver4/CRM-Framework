<?php

/**
 * Email Accounts Configuration Management
 * Manage email accounts for form processing
 */

// Load system configuration
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

// Security check
$security = new Security();
$security->check_user_permissions('admin', 'read');

// Direct routing variables - these determine page navigation and template inclusion
$dir = 'admin';
$subdir = 'email';
$sub_subdir = '';
$sub_sub_subdir = '';
$page = 'accounts_config';
$table_page = true;

// Set display variables
$title = 'Email Accounts Configuration';
$title_icon = '<i class="fa fa-cog"></i>';

// Load language file
$lang = include LANG . '/en.php';

// Initialize database
$database = new Database();
$pdo = $database->dbcrm();

// Handle actions
$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nonce = new Nonce();
    
    if (!$nonce->validate($_POST['nonce'] ?? '')) {
        $message = "Invalid security token. Please try again.";
        $messageType = "danger";
    } else {
        $email_address = trim($_POST['email_address'] ?? '');
        $form_type = $_POST['form_type'] ?? '';
        $imap_host = trim($_POST['imap_host'] ?? '');
        $imap_port = (int)($_POST['imap_port'] ?? 993);
        $imap_encryption = $_POST['imap_encryption'] ?? 'ssl';
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        try {
            if ($action === 'add') {
                // Encrypt password
                $encrypted_password = base64_encode($password);
                
                $stmt = $pdo->prepare("INSERT INTO email_accounts_config 
                    (email_address, form_type, imap_host, imap_port, imap_encryption, username, password, is_active) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                
                $stmt->bindValue(1, $email_address, PDO::PARAM_STR);
                $stmt->bindValue(2, $form_type, PDO::PARAM_STR);
                $stmt->bindValue(3, $imap_host, PDO::PARAM_STR);
                $stmt->bindValue(4, $imap_port, PDO::PARAM_INT);
                $stmt->bindValue(5, $imap_encryption, PDO::PARAM_STR);
                $stmt->bindValue(6, $username, PDO::PARAM_STR);
                $stmt->bindValue(7, $encrypted_password, PDO::PARAM_STR);
                $stmt->bindValue(8, $is_active, PDO::PARAM_INT);
                
                $stmt->execute();
                $stmt = null;
                
                $message = "Email account added successfully.";
                $messageType = "success";
                $action = 'list';
                
            } elseif ($action === 'edit' && $id > 0) {
                // Update account
                if ($password) {
                    // Update with new password
                    $encrypted_password = base64_encode($password);
                    $stmt = $pdo->prepare("UPDATE email_accounts_config SET 
                        email_address = ?, form_type = ?, imap_host = ?, imap_port = ?, 
                        imap_encryption = ?, username = ?, password = ?, is_active = ? 
                        WHERE id = ?");
                    
                    $stmt->bindValue(1, $email_address, PDO::PARAM_STR);
                    $stmt->bindValue(2, $form_type, PDO::PARAM_STR);
                    $stmt->bindValue(3, $imap_host, PDO::PARAM_STR);
                    $stmt->bindValue(4, $imap_port, PDO::PARAM_INT);
                    $stmt->bindValue(5, $imap_encryption, PDO::PARAM_STR);
                    $stmt->bindValue(6, $username, PDO::PARAM_STR);
                    $stmt->bindValue(7, $encrypted_password, PDO::PARAM_STR);
                    $stmt->bindValue(8, $is_active, PDO::PARAM_INT);
                    $stmt->bindValue(9, $id, PDO::PARAM_INT);
                } else {
                    // Update without changing password
                    $stmt = $pdo->prepare("UPDATE email_accounts_config SET 
                        email_address = ?, form_type = ?, imap_host = ?, imap_port = ?, 
                        imap_encryption = ?, username = ?, is_active = ? 
                        WHERE id = ?");
                    
                    $stmt->bindValue(1, $email_address, PDO::PARAM_STR);
                    $stmt->bindValue(2, $form_type, PDO::PARAM_STR);
                    $stmt->bindValue(3, $imap_host, PDO::PARAM_STR);
                    $stmt->bindValue(4, $imap_port, PDO::PARAM_INT);
                    $stmt->bindValue(5, $imap_encryption, PDO::PARAM_STR);
                    $stmt->bindValue(6, $username, PDO::PARAM_STR);
                    $stmt->bindValue(7, $is_active, PDO::PARAM_INT);
                    $stmt->bindValue(8, $id, PDO::PARAM_INT);
                }
                
                $stmt->execute();
                $stmt = null;
                
                $message = "Email account updated successfully.";
                $messageType = "success";
                $action = 'list';
            }
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = "danger";
        }
    }
}

// Handle delete action
if ($action === 'delete' && $id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM email_accounts_config WHERE id = ?");
        $stmt->bindValue(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        $stmt = null;
        
        $message = "Email account deleted successfully.";
        $messageType = "success";
        $action = 'list';
    } catch (Exception $e) {
        $message = "Error deleting account: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Get account data for editing
$account_data = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM email_accounts_config WHERE id = ?");
    $stmt->bindValue(1, $id, PDO::PARAM_INT);
    $stmt->execute();
    $account_data = $stmt->fetch();
    $stmt = null;
    
    if (!$account_data) {
        $message = "Account not found.";
        $messageType = "danger";
        $action = 'list';
    }
}

// Get all accounts for listing
if ($action === 'list') {
    $stmt = $pdo->prepare("SELECT * FROM email_accounts_config ORDER BY email_address");
    $stmt->execute();
    $accounts = $stmt->fetchAll();
    $stmt = null;
}

// Generate nonce for forms
$nonce = new Nonce();
$nonce_token = $nonce->create('email_accounts_config');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Accounts Configuration - <?php echo TABTITLEPREFIX; ?></title>
    <?php include HEADER; ?>
</head>
<body>
    <?php include NAV; ?>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fa fa-cog me-2"></i>Email Accounts Configuration</h2>
                    <?php if ($action === 'list'): ?>
                    <a href="?action=add" class="btn btn-primary">
                        <i class="fa fa-plus me-1"></i>Add Account
                    </a>
                    <?php else: ?>
                    <a href="?" class="btn btn-secondary">
                        <i class="fa fa-arrow-left me-1"></i>Back to List
                    </a>
                    <?php endif; ?>
                </div>

                <?php if (isset($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if ($action === 'list'): ?>
                <!-- Accounts List -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Email Accounts</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($accounts)): ?>
                        <div class="text-center py-4">
                            <i class="fa fa-envelope fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No email accounts configured.</p>
                            <a href="?action=add" class="btn btn-primary">Add First Account</a>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Email Address</th>
                                        <th>Form Type</th>
                                        <th>IMAP Server</th>
                                        <th>Status</th>
                                        <th>Last Check</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($accounts as $account): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($account['email_address']); ?></td>
                                        <td>
                                            <span class="badge bg-info"><?php echo ucfirst($account['form_type']); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($account['imap_host']); ?>:<?php echo $account['imap_port']; ?></td>
                                        <td>
                                            <?php if ($account['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($account['last_check']): ?>
                                                <?php echo date('M j, Y H:i', strtotime($account['last_check'])); ?>
                                            <?php else: ?>
                                                <span class="text-muted">Never</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="?action=edit&id=<?php echo $account['id']; ?>" class="btn btn-outline-primary">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <a href="?action=delete&id=<?php echo $account['id']; ?>" 
                                                   class="btn btn-outline-danger"
                                                   onclick="return confirm('Delete this email account?')">
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
                    </div>
                </div>

                <?php elseif ($action === 'add' || $action === 'edit'): ?>
                <!-- Add/Edit Form -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><?php echo $action === 'add' ? 'Add' : 'Edit'; ?> Email Account</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="nonce" value="<?php echo $nonce_token; ?>">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email_address" class="form-label">Email Address *</label>
                                        <input type="email" name="email_address" id="email_address" class="form-control" 
                                               value="<?php echo htmlspecialchars($account_data['email_address'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="form_type" class="form-label">Form Type *</label>
                                        <select name="form_type" id="form_type" class="form-select" required>
                                            <option value="">Select Form Type</option>
                                            <option value="estimate" <?php echo ($account_data['form_type'] ?? '') === 'estimate' ? 'selected' : ''; ?>>Estimate</option>
                                            <option value="ltr" <?php echo ($account_data['form_type'] ?? '') === 'ltr' ? 'selected' : ''; ?>>LTR</option>
                                            <option value="contact" <?php echo ($account_data['form_type'] ?? '') === 'contact' ? 'selected' : ''; ?>>Contact</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="imap_host" class="form-label">IMAP Host *</label>
                                        <input type="text" name="imap_host" id="imap_host" class="form-control" 
                                               value="<?php echo htmlspecialchars($account_data['imap_host'] ?? 'mail.waveguardco.com'); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="imap_port" class="form-label">IMAP Port *</label>
                                        <input type="number" name="imap_port" id="imap_port" class="form-control" 
                                               value="<?php echo $account_data['imap_port'] ?? 993; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="imap_encryption" class="form-label">Encryption *</label>
                                        <select name="imap_encryption" id="imap_encryption" class="form-select" required>
                                            <option value="ssl" <?php echo ($account_data['imap_encryption'] ?? 'ssl') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                            <option value="tls" <?php echo ($account_data['imap_encryption'] ?? '') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                            <option value="none" <?php echo ($account_data['imap_encryption'] ?? '') === 'none' ? 'selected' : ''; ?>>None</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username *</label>
                                        <input type="text" name="username" id="username" class="form-control" 
                                               value="<?php echo htmlspecialchars($account_data['username'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label">
                                            Password <?php echo $action === 'edit' ? '(leave blank to keep current)' : '*'; ?>
                                        </label>
                                        <input type="password" name="password" id="password" class="form-control" 
                                               <?php echo $action === 'add' ? 'required' : ''; ?>>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="is_active" id="is_active" class="form-check-input" 
                                           <?php echo ($account_data['is_active'] ?? 1) ? 'checked' : ''; ?>>
                                    <label for="is_active" class="form-check-label">Active</label>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save me-1"></i><?php echo $action === 'add' ? 'Add' : 'Update'; ?> Account
                                </button>
                                <a href="?" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include FOOTER; ?>
</body>
</html>