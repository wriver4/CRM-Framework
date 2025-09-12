<?php

/**
 * Email Import Management Controller
 * Provides interface for managing email form processing
 * Follows existing CRM framework patterns
 */

// Load system configuration
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

// Set routing variables for template system
// Direct routing variables - these determine page navigation and template inclusion
$dir = 'admin';
$subdir = 'email';
$sub_subdir = '';
$sub_sub_subdir = '';
$page = 'email_import';
$table_page = true;
$title = 'Email Form Import Management';
$title_icon = '<i class="fa fa-envelope"></i>';

// Load language file
$lang = include '../languages/en.php';

// Initialize security and check permissions
$security = new Security();
$security->check_user_login();
$security->check_user_permissions('admin', 'read');

// Initialize classes
$emailProcessor = new EmailFormProcessor();
$helpers = new Helpers();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nonce = new Nonce();
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'process_emails':
                if ($nonce->verify($_POST['nonce'])) {
                    try {
                        $results = $emailProcessor->processAllEmails();
                        $success_message = "Email processing completed. Results: " . json_encode($results);
                    } catch (Exception $e) {
                        $error_message = "Email processing failed: " . $e->getMessage();
                    }
                } else {
                    $error_message = "Invalid security token.";
                }
                break;
                
            case 'test_connection':
                if ($nonce->verify($_POST['nonce']) && isset($_POST['account_id'])) {
                    try {
                        $accountId = (int)$_POST['account_id'];
                        $result = $emailProcessor->testEmailConnection($accountId);
                        $success_message = "Connection test successful: " . $result;
                    } catch (Exception $e) {
                        $error_message = "Connection test failed: " . $e->getMessage();
                    }
                }
                break;
        }
    }
}

// Get email processing statistics
$stats = $emailProcessor->getProcessingStats();
$recentProcessing = $emailProcessor->getRecentProcessing(20);
$emailAccounts = $emailProcessor->getEmailAccounts();

// Include header template
include '../../templates/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo $title_icon . ' ' . $title; ?></h3>
                </div>
                <div class="card-body">
                    
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($success_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Processing Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Processed</h5>
                                    <h2><?php echo number_format($stats['total_processed'] ?? 0); ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Successful</h5>
                                    <h2><?php echo number_format($stats['successful'] ?? 0); ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Failed</h5>
                                    <h2><?php echo number_format($stats['failed'] ?? 0); ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Today</h5>
                                    <h2><?php echo number_format($stats['today'] ?? 0); ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <form method="post" class="d-inline">
                                <?php $nonce = new Nonce(); echo $nonce->field(); ?>
                                <input type="hidden" name="action" value="process_emails">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-play"></i> Process Emails Now
                                </button>
                            </form>
                            
                            <a href="list.php" class="btn btn-secondary">
                                <i class="fa fa-list"></i> View All Leads
                            </a>
                            
                            <a href="#emailAccounts" class="btn btn-info" data-bs-toggle="collapse">
                                <i class="fa fa-cog"></i> Email Accounts
                            </a>
                        </div>
                    </div>
                    
                    <!-- Email Accounts Configuration -->
                    <div class="collapse mb-4" id="emailAccounts">
                        <div class="card">
                            <div class="card-header">
                                <h5>Email Account Configuration</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Email Address</th>
                                                <th>Form Type</th>
                                                <th>Status</th>
                                                <th>Last Check</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($emailAccounts as $account): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($account['email_address']); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $account['form_type'] === 'estimate' ? 'primary' : ($account['form_type'] === 'ltr' ? 'success' : 'info'); ?>">
                                                            <?php echo strtoupper($account['form_type']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $account['is_active'] ? 'success' : 'secondary'; ?>">
                                                            <?php echo $account['is_active'] ? 'Active' : 'Inactive'; ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo $account['last_check'] ? date('M j, Y g:i A', strtotime($account['last_check'])) : 'Never'; ?></td>
                                                    <td>
                                                        <form method="post" class="d-inline">
                                                            <?php $nonce = new Nonce(); echo $nonce->field(); ?>
                                                            <input type="hidden" name="action" value="test_connection">
                                                            <input type="hidden" name="account_id" value="<?php echo $account['id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                                                <i class="fa fa-plug"></i> Test
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Processing Log -->
                    <div class="card">
                        <div class="card-header">
                            <h5>Recent Email Processing</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="processingTable">
                                    <thead>
                                        <tr>
                                            <th>Date/Time</th>
                                            <th>Email Account</th>
                                            <th>Form Type</th>
                                            <th>Sender</th>
                                            <th>Subject</th>
                                            <th>Status</th>
                                            <th>Lead</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentProcessing as $record): ?>
                                            <tr>
                                                <td><?php echo date('M j, Y g:i A', strtotime($record['processed_at'])); ?></td>
                                                <td><?php echo htmlspecialchars($record['email_account']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $record['form_type'] === 'estimate' ? 'primary' : ($record['form_type'] === 'ltr' ? 'success' : 'info'); ?>">
                                                        <?php echo strtoupper($record['form_type']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($record['sender_email']); ?></td>
                                                <td><?php echo htmlspecialchars(substr($record['subject'], 0, 50)) . (strlen($record['subject']) > 50 ? '...' : ''); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $record['processing_status'] === 'success' ? 'success' : 
                                                            ($record['processing_status'] === 'failed' ? 'danger' : 
                                                            ($record['processing_status'] === 'duplicate' ? 'warning' : 'secondary')); 
                                                    ?>">
                                                        <?php echo ucfirst($record['processing_status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($record['lead_id']): ?>
                                                        <a href="view.php?id=<?php echo $record['lead_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                            Lead #<?php echo $record['lead_id']; ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">None</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#detailModal<?php echo $record['id']; ?>">
                                                        <i class="fa fa-eye"></i> Details
                                                    </button>
                                                </td>
                                            </tr>
                                            
                                            <!-- Detail Modal -->
                                            <div class="modal fade" id="detailModal<?php echo $record['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Email Processing Details</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <h6>Email Information</h6>
                                                            <p><strong>Subject:</strong> <?php echo htmlspecialchars($record['subject']); ?></p>
                                                            <p><strong>Sender:</strong> <?php echo htmlspecialchars($record['sender_email']); ?></p>
                                                            <p><strong>Received:</strong> <?php echo $record['received_at'] ? date('M j, Y g:i A', strtotime($record['received_at'])) : 'Unknown'; ?></p>
                                                            
                                                            <?php if ($record['parsed_form_data']): ?>
                                                                <h6 class="mt-3">Parsed Form Data</h6>
                                                                <pre class="bg-light p-2"><?php echo htmlspecialchars(json_encode(json_decode($record['parsed_form_data']), JSON_PRETTY_PRINT)); ?></pre>
                                                            <?php endif; ?>
                                                            
                                                            <?php if ($record['error_message']): ?>
                                                                <h6 class="mt-3">Error Message</h6>
                                                                <div class="alert alert-danger">
                                                                    <?php echo htmlspecialchars($record['error_message']); ?>
                                                                </div>
                                                            <?php endif; ?>
                                                            
                                                            <h6 class="mt-3">Raw Email Content</h6>
                                                            <textarea class="form-control" rows="10" readonly><?php echo htmlspecialchars($record['raw_email_content']); ?></textarea>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#processingTable').DataTable({
        "order": [[ 0, "desc" ]],
        "pageLength": 25,
        "responsive": true
    });
});
</script>

<?php
// Include footer template
include '../templates/footer.php';
?>