<?php

/**
 * CRM Sync Queue Management
 * View and manage external CRM synchronization queue
 */

// Load system configuration
require_once '../../config/system.php';

// Security check
$security = new Security();
$security->check_user_permissions('admin', 'read');

// Set page variables for navigation
$dir = 'admin/email';
$page = 'sync_queue';

// Load language file
$lang = include LANG . '/en.php';

// Initialize database
$database = new Database();
$pdo = $database->dbcrm();

// Handle actions
$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);

// Process actions
switch ($action) {
    case 'retry':
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare("UPDATE crm_sync_queue SET 
                    sync_status = 'pending', 
                    retry_count = 0, 
                    next_retry_at = NULL, 
                    last_error = NULL 
                    WHERE id = ?");
                $stmt->bindValue(1, $id, PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
                
                $message = "Sync entry marked for retry.";
                $messageType = "success";
            } catch (Exception $e) {
                $message = "Error marking for retry: " . $e->getMessage();
                $messageType = "danger";
            }
        }
        break;
        
    case 'delete':
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM crm_sync_queue WHERE id = ?");
                $stmt->bindValue(1, $id, PDO::PARAM_INT);
                $stmt->execute();
                $stmt = null;
                
                $message = "Sync entry deleted successfully.";
                $messageType = "success";
            } catch (Exception $e) {
                $message = "Error deleting entry: " . $e->getMessage();
                $messageType = "danger";
            }
        }
        break;
        
    case 'clear_completed':
        try {
            $stmt = $pdo->prepare("DELETE FROM crm_sync_queue WHERE sync_status = 'completed' AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)");
            $stmt->execute();
            $deleted_count = $stmt->rowCount();
            $stmt = null;
            
            $message = "Cleared {$deleted_count} completed sync entries older than 7 days.";
            $messageType = "success";
        } catch (Exception $e) {
            $message = "Error clearing completed entries: " . $e->getMessage();
            $messageType = "danger";
        }
        break;
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$system_filter = $_GET['system'] ?? '';

// Build query with filters
$where_conditions = [];
$params = [];

if ($status_filter) {
    $where_conditions[] = "sync_status = ?";
    $params[] = $status_filter;
}

if ($system_filter) {
    $where_conditions[] = "external_system = ?";
    $params[] = $system_filter;
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get sync queue entries with lead information
$query = "SELECT sq.*, l.full_name as lead_name, l.email as lead_email 
          FROM crm_sync_queue sq 
          LEFT JOIN leads l ON sq.lead_id = l.id 
          {$where_clause} 
          ORDER BY sq.created_at DESC 
          LIMIT 100";

$stmt = $pdo->prepare($query);

foreach ($params as $index => $param) {
    $stmt->bindValue($index + 1, $param, PDO::PARAM_STR);
}

$stmt->execute();
$sync_entries = $stmt->fetchAll();
$stmt = null;

// Get summary statistics
$stats_query = "SELECT 
    sync_status,
    external_system,
    COUNT(*) as count
FROM crm_sync_queue 
GROUP BY sync_status, external_system
ORDER BY sync_status, external_system";

$stmt = $pdo->prepare($stats_query);
$stmt->execute();
$stats = $stmt->fetchAll();
$stmt = null;

// Group stats by status
$status_stats = [];
foreach ($stats as $stat) {
    $status_stats[$stat['sync_status']][$stat['external_system']] = $stat['count'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Sync Queue - <?php echo TABTITLEPREFIX; ?></title>
    <?php include HEADER; ?>
</head>
<body>
    <?php include NAV; ?>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fa fa-sync me-2"></i>CRM Sync Queue</h2>
                    <div>
                        <a href="?action=clear_completed" 
                           class="btn btn-warning btn-sm"
                           onclick="return confirm('Clear all completed sync entries older than 7 days?')">
                            <i class="fa fa-broom me-1"></i>Clear Completed
                        </a>
                    </div>
                </div>

                <?php if (isset($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <?php
                    $status_colors = [
                        'pending' => 'primary',
                        'in_progress' => 'info',
                        'completed' => 'success',
                        'failed' => 'danger'
                    ];
                    
                    foreach ($status_colors as $status => $color):
                        $total = 0;
                        if (isset($status_stats[$status])) {
                            $total = array_sum($status_stats[$status]);
                        }
                    ?>
                    <div class="col-md-3">
                        <div class="card border-<?php echo $color; ?>">
                            <div class="card-body text-center">
                                <h5 class="card-title text-<?php echo $color; ?>"><?php echo ucfirst($status); ?></h5>
                                <h3 class="card-text"><?php echo $total; ?></h3>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Filters</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="in_progress" <?php echo $status_filter === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="failed" <?php echo $status_filter === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="system" class="form-label">External System</label>
                                <select name="system" id="system" class="form-select">
                                    <option value="">All Systems</option>
                                    <option value="hubspot" <?php echo $system_filter === 'hubspot' ? 'selected' : ''; ?>>HubSpot</option>
                                    <option value="salesforce" <?php echo $system_filter === 'salesforce' ? 'selected' : ''; ?>>Salesforce</option>
                                    <option value="mailchimp" <?php echo $system_filter === 'mailchimp' ? 'selected' : ''; ?>>Mailchimp</option>
                                    <option value="custom" <?php echo $system_filter === 'custom' ? 'selected' : ''; ?>>Custom</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                    <a href="?" class="btn btn-secondary">Clear</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Sync Queue Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Sync Queue (Last 100 entries)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($sync_entries)): ?>
                        <div class="text-center py-4">
                            <i class="fa fa-sync fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No sync entries found.</p>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Created</th>
                                        <th>Lead</th>
                                        <th>Action</th>
                                        <th>System</th>
                                        <th>Status</th>
                                        <th>Retries</th>
                                        <th>Next Retry</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sync_entries as $entry): ?>
                                    <tr>
                                        <td><?php echo $entry['id']; ?></td>
                                        <td><?php echo date('M j, H:i', strtotime($entry['created_at'])); ?></td>
                                        <td>
                                            <div>
                                                <a href="/leads/view.php?id=<?php echo $entry['lead_id']; ?>" class="fw-bold">
                                                    #<?php echo $entry['lead_id']; ?>
                                                </a>
                                                <?php if ($entry['lead_name']): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($entry['lead_name']); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo ucfirst($entry['sync_action']); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo ucfirst($entry['external_system']); ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $status_class = [
                                                'pending' => 'bg-primary',
                                                'in_progress' => 'bg-info',
                                                'completed' => 'bg-success',
                                                'failed' => 'bg-danger'
                                            ];
                                            ?>
                                            <span class="badge <?php echo $status_class[$entry['sync_status']] ?? 'bg-secondary'; ?>">
                                                <?php echo ucfirst($entry['sync_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo $entry['retry_count']; ?>/<?php echo $entry['max_retries']; ?>
                                        </td>
                                        <td>
                                            <?php if ($entry['next_retry_at']): ?>
                                                <?php echo date('M j, H:i', strtotime($entry['next_retry_at'])); ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-info" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#detailModal<?php echo $entry['id']; ?>">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                                <?php if (in_array($entry['sync_status'], ['failed', 'completed'])): ?>
                                                <a href="?action=retry&id=<?php echo $entry['id']; ?>" 
                                                   class="btn btn-outline-warning"
                                                   onclick="return confirm('Retry this sync entry?')">
                                                    <i class="fa fa-redo"></i>
                                                </a>
                                                <?php endif; ?>
                                                <a href="?action=delete&id=<?php echo $entry['id']; ?>" 
                                                   class="btn btn-outline-danger"
                                                   onclick="return confirm('Delete this sync entry?')">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Detail Modal -->
                                    <div class="modal fade" id="detailModal<?php echo $entry['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Sync Details - Entry #<?php echo $entry['id']; ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Lead:</strong><br>
                                                            <a href="/leads/view.php?id=<?php echo $entry['lead_id']; ?>">
                                                                #<?php echo $entry['lead_id']; ?> - <?php echo htmlspecialchars($entry['lead_name'] ?? 'Unknown'); ?>
                                                            </a>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>External ID:</strong><br>
                                                            <?php echo htmlspecialchars($entry['external_id'] ?? 'Not set'); ?>
                                                        </div>
                                                    </div>
                                                    
                                                    <?php if ($entry['last_error']): ?>
                                                    <div class="alert alert-danger">
                                                        <strong>Last Error:</strong><br>
                                                        <?php echo htmlspecialchars($entry['last_error']); ?>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($entry['sync_data']): ?>
                                                    <div class="mb-3">
                                                        <strong>Sync Data:</strong>
                                                        <pre class="bg-light p-2 rounded"><?php echo htmlspecialchars(json_encode(json_decode($entry['sync_data']), JSON_PRETTY_PRINT)); ?></pre>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <strong>Created:</strong><br>
                                                            <?php echo date('M j, Y H:i:s', strtotime($entry['created_at'])); ?>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Updated:</strong><br>
                                                            <?php echo date('M j, Y H:i:s', strtotime($entry['updated_at'])); ?>
                                                        </div>
                                                    </div>
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