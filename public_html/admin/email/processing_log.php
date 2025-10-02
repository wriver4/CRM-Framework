<?php

/**
 * Email Processing Log Management
 * View and manage email form processing history
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
$page = 'processing_log';
$table_page = true;

// Set display variables
$title = 'Email Processing Log';
$title_icon = '<i class="fa fa-list"></i>';

// Load language file
$lang = include LANG . '/en.php';

// Initialize database
$database = new Database();
$pdo = $database->dbcrm();

// Handle actions - sanitize inputs
$action = isset($_GET['action']) ? htmlspecialchars($_GET['action'], ENT_QUOTES, 'UTF-8') : 'list';
$id = (int)($_GET['id'] ?? 0);

// Process actions
switch ($action) {
    case 'delete':
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM email_form_processing WHERE id = ?");
                $stmt->bindValue(1, $id, PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
                
                $message = "Processing log entry deleted successfully.";
                $messageType = "success";
            } catch (Exception $e) {
                $message = "Error deleting entry: " . $e->getMessage();
                $messageType = "danger";
            }
        }
        break;
        
    case 'reprocess':
        if ($id > 0) {
            try {
                // Mark for reprocessing by updating status
                $stmt = $pdo->prepare("UPDATE email_form_processing SET processing_status = 'pending', error_message = NULL WHERE id = ?");
                $stmt->bindValue(1, $id, PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
                
                $message = "Entry marked for reprocessing.";
                $messageType = "info";
            } catch (Exception $e) {
                $message = "Error marking for reprocessing: " . $e->getMessage();
                $messageType = "danger";
            }
        }
        break;
}

// Get filter parameters - sanitize inputs
$status_filter = isset($_GET['status']) ? htmlspecialchars($_GET['status'], ENT_QUOTES, 'UTF-8') : '';
$form_type_filter = isset($_GET['form_type']) ? htmlspecialchars($_GET['form_type'], ENT_QUOTES, 'UTF-8') : '';
$date_from = isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from'], ENT_QUOTES, 'UTF-8') : '';
$date_to = isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to'], ENT_QUOTES, 'UTF-8') : '';

// Build query with filters
$where_conditions = [];
$params = [];

if ($status_filter) {
    $where_conditions[] = "processing_status = ?";
    $params[] = $status_filter;
}

if ($form_type_filter) {
    $where_conditions[] = "form_type = ?";
    $params[] = $form_type_filter;
}

if ($date_from) {
    $where_conditions[] = "DATE(processed_at) >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $where_conditions[] = "DATE(processed_at) <= ?";
    $params[] = $date_to;
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get processing log entries
$query = "SELECT * FROM email_form_processing {$where_clause} ORDER BY processed_at DESC LIMIT 100";
$stmt = $pdo->prepare($query);

foreach ($params as $index => $param) {
    $stmt->bindValue($index + 1, $param, PDO::PARAM_STR);
}

$stmt->execute();
$processing_logs = $stmt->fetchAll();
$stmt = null;

// Get summary statistics
$days_back = 7; // Number of days to look back for statistics
$stats_query = "SELECT 
    processing_status,
    COUNT(*) as count,
    DATE(processed_at) as date
FROM email_form_processing 
WHERE DATE(processed_at) >= DATE_SUB(CURDATE(), INTERVAL {$days_back} DAY)
GROUP BY processing_status, DATE(processed_at)
ORDER BY date DESC";

$stmt = $pdo->prepare($stats_query);
$stmt->execute();
$stats = $stmt->fetchAll();
$stmt = null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Processing Log - <?php echo TABTITLEPREFIX; ?></title>
    <?php include HEADER; ?>
</head>
<body>
    <?php include NAV; ?>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fa fa-list me-2"></i>Email Processing Log</h2>
                    <div>
                        <a href="/api/email_forms.php/status?api_key=waveguard_api_key_2024" 
                           class="btn btn-info btn-sm" target="_blank">
                            <i class="fa fa-heartbeat me-1"></i>System Status
                        </a>
                    </div>
                </div>

                <?php if (isset($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Filters</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="success" <?php echo $status_filter === 'success' ? 'selected' : ''; ?>>Success</option>
                                    <option value="failed" <?php echo $status_filter === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                    <option value="skipped" <?php echo $status_filter === 'skipped' ? 'selected' : ''; ?>>Skipped</option>
                                    <option value="duplicate" <?php echo $status_filter === 'duplicate' ? 'selected' : ''; ?>>Duplicate</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="form_type" class="form-label">Form Type</label>
                                <select name="form_type" id="form_type" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="estimate" <?php echo $form_type_filter === 'estimate' ? 'selected' : ''; ?>>Estimate</option>
                                    <option value="ltr" <?php echo $form_type_filter === 'ltr' ? 'selected' : ''; ?>>LTR</option>
                                    <option value="contact" <?php echo $form_type_filter === 'contact' ? 'selected' : ''; ?>>Contact</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="date_from" class="form-label">From Date</label>
                                <input type="date" name="date_from" id="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from); ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="date_to" class="form-label">To Date</label>
                                <input type="date" name="date_to" id="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to); ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                    <a href="?" class="btn btn-secondary">Clear</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Processing Log Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Processing History (Last 100 entries)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($processing_logs)): ?>
                        <div class="text-center py-4">
                            <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No processing logs found.</p>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Processed At</th>
                                        <th>Email Account</th>
                                        <th>Form Type</th>
                                        <th>Status</th>
                                        <th>Sender</th>
                                        <th>Lead ID</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($processing_logs as $log): ?>
                                    <tr>
                                        <td><?php echo $log['id']; ?></td>
                                        <td><?php echo date('M j, Y H:i', strtotime($log['processed_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($log['email_account']); ?></td>
                                        <td>
                                            <span class="badge bg-info"><?php echo ucfirst($log['form_type']); ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $status_class = [
                                                'success' => 'bg-success',
                                                'failed' => 'bg-danger',
                                                'skipped' => 'bg-warning',
                                                'duplicate' => 'bg-secondary'
                                            ];
                                            ?>
                                            <span class="badge <?php echo $status_class[$log['processing_status']] ?? 'bg-secondary'; ?>">
                                                <?php echo ucfirst($log['processing_status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($log['sender_email'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php if ($log['lead_id']): ?>
                                                <a href="/leads/view.php?id=<?php echo $log['lead_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    #<?php echo $log['lead_id']; ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-info" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#detailModal<?php echo $log['id']; ?>">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                                <?php if ($log['processing_status'] === 'failed'): ?>
                                                <a href="?action=reprocess&id=<?php echo $log['id']; ?>" 
                                                   class="btn btn-outline-warning"
                                                   onclick="return confirm('Mark this entry for reprocessing?')">
                                                    <i class="fa fa-redo"></i>
                                                </a>
                                                <?php endif; ?>
                                                <a href="?action=delete&id=<?php echo $log['id']; ?>" 
                                                   class="btn btn-outline-danger"
                                                   onclick="return confirm('Delete this log entry?')">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Detail Modal -->
                                    <div class="modal fade" id="detailModal<?php echo $log['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Processing Details - Entry #<?php echo $log['id']; ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <strong>Subject:</strong><br>
                                                            <?php echo htmlspecialchars($log['subject'] ?? 'N/A'); ?>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Message ID:</strong><br>
                                                            <?php echo htmlspecialchars($log['message_id'] ?? 'N/A'); ?>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <?php if ($log['error_message']): ?>
                                                    <div class="alert alert-danger">
                                                        <strong>Error:</strong><br>
                                                        <?php echo htmlspecialchars($log['error_message']); ?>
                                                    </div>
                                                    <?php endif; ?>
                                                    <?php if ($log['parsed_form_data']): ?>
                                                    <div class="mb-3">
                                                        <strong>Parsed Form Data:</strong>
                                                        <pre class="bg-light p-2 rounded"><?php echo htmlspecialchars(json_encode(json_decode($log['parsed_form_data']), JSON_PRETTY_PRINT)); ?></pre>
                                                    </div>
                                                    <?php endif; ?>
                                                    <?php if ($log['raw_email_content']): ?>
                                                    <div class="mb-3">
                                                        <strong>Raw Email Content:</strong>
                                                        <textarea class="form-control" rows="10" readonly><?php echo htmlspecialchars($log['raw_email_content']); ?></textarea>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include FOOTER; ?>
</body>
</html>