<?php
/**
 * phpList Subscribers Management
 * 
 * View and manage phpList subscribers
 */

require_once dirname(dirname($_SERVER['DOCUMENT_ROOT'])) . '/config/system.php';

// Check if user is logged in and has admin privileges
$not->loggedin();

// Load language file
$lang = include dirname(__DIR__) . '/languages/en.php';

// Initialize classes
$phpListSubscribers = new PhpListSubscribers();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'retry_sync' && isset($_POST['subscriber_id'])) {
            $subscriberId = (int)$_POST['subscriber_id'];
            
            // Reset sync status to pending
            $stmt = $phpListSubscribers->dbcrm()->prepare("
                UPDATE phplist_subscribers 
                SET sync_status = 'pending', sync_attempts = 0, error_message = NULL 
                WHERE id = :id
            ");
            $stmt->bindValue(':id', $subscriberId, PDO::PARAM_INT);
            $stmt->execute();
            
            $_SESSION['success_message'] = 'Subscriber queued for retry';
            
        } elseif ($_POST['action'] === 'delete_subscriber' && isset($_POST['subscriber_id'])) {
            $subscriberId = (int)$_POST['subscriber_id'];
            
            $stmt = $phpListSubscribers->dbcrm()->prepare("DELETE FROM phplist_subscribers WHERE id = :id");
            $stmt->bindValue(':id', $subscriberId, PDO::PARAM_INT);
            $stmt->execute();
            
            $_SESSION['success_message'] = 'Subscriber deleted successfully';
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
    }
    
    header('Location: subscribers.php');
    exit;
}

// Get filter parameters
$statusFilter = $_GET['status'] ?? '';
$searchEmail = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 25;
$offset = ($page - 1) * $limit;

// Build query
$whereConditions = [];
$params = [];

if (!empty($statusFilter)) {
    $whereConditions[] = "ps.sync_status = :status";
    $params[':status'] = $statusFilter;
}

if (!empty($searchEmail)) {
    $whereConditions[] = "ps.email LIKE :email";
    $params[':email'] = '%' . $searchEmail . '%';
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Get subscribers
try {
    $stmt = $phpListSubscribers->dbcrm()->prepare("
        SELECT 
            ps.*,
            l.lead_id,
            l.stage as lead_stage,
            c.full_name as contact_name
        FROM phplist_subscribers ps
        LEFT JOIN leads l ON ps.lead_id = l.id
        LEFT JOIN contacts c ON ps.contact_id = c.id
        $whereClause
        ORDER BY ps.created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total count for pagination
    $countStmt = $phpListSubscribers->dbcrm()->prepare("
        SELECT COUNT(*) 
        FROM phplist_subscribers ps
        LEFT JOIN leads l ON ps.lead_id = l.id
        LEFT JOIN contacts c ON ps.contact_id = c.id
        $whereClause
    ");
    
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value, PDO::PARAM_STR);
    }
    $countStmt->execute();
    
    $totalCount = $countStmt->fetchColumn();
    $totalPages = ceil($totalCount / $limit);
    
} catch (Exception $e) {
    $subscribers = [];
    $totalCount = 0;
    $totalPages = 0;
    $error_message = 'Error loading subscribers: ' . $e->getMessage();
}

// Get statistics
$stats = $phpListSubscribers->getSubscriberStats();

// Page title
$page_title = 'phpList Subscribers';

// Include header
include dirname(__DIR__) . '/templates/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-users"></i> phpList Subscribers</h1>
                <div>
                    <a href="config.php" class="btn btn-secondary">
                        <i class="fas fa-cog"></i> Configuration
                    </a>
                    <a href="../dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['success_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['error_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?= $stats['synced'] ?? 0 ?></h4>
                                    <p class="mb-0">Synced</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?= $stats['pending'] ?? 0 ?></h4>
                                    <p class="mb-0">Pending</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-danger">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?= $stats['failed'] ?? 0 ?></h4>
                                    <p class="mb-0">Failed</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-secondary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?= $stats['skipped'] ?? 0 ?></h4>
                                    <p class="mb-0">Skipped</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-ban fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="synced" <?= $statusFilter === 'synced' ? 'selected' : '' ?>>Synced</option>
                                <option value="failed" <?= $statusFilter === 'failed' ? 'selected' : '' ?>>Failed</option>
                                <option value="skipped" <?= $statusFilter === 'skipped' ? 'selected' : '' ?>>Skipped</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search Email</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?= htmlspecialchars($searchEmail) ?>" placeholder="Enter email to search">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <a href="subscribers.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Subscribers Table -->
            <div class="card">
                <div class="card-header">
                    <h5>Subscribers (<?= number_format($totalCount) ?> total)</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($subscribers)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No subscribers found</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Email</th>
                                        <th>Name</th>
                                        <th>Lead</th>
                                        <th>Status</th>
                                        <th>Sync Attempts</th>
                                        <th>Last Sync</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subscribers as $subscriber): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($subscriber['email']) ?></strong>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars(trim($subscriber['first_name'] . ' ' . $subscriber['last_name'])) ?>
                                            </td>
                                            <td>
                                                <?php if ($subscriber['lead_id']): ?>
                                                    <a href="../../leads/view.php?id=<?= $subscriber['lead_id'] ?>" class="btn btn-sm btn-outline-primary">
                                                        Lead #<?= $subscriber['lead_id'] ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = [
                                                    'pending' => 'warning',
                                                    'synced' => 'success',
                                                    'failed' => 'danger',
                                                    'skipped' => 'secondary'
                                                ][$subscriber['sync_status']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?= $statusClass ?>">
                                                    <?= ucfirst($subscriber['sync_status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= $subscriber['sync_attempts'] ?>
                                                <?php if ($subscriber['sync_attempts'] > 0): ?>
                                                    <small class="text-muted">/ <?= $phpListSubscribers->getConfig('max_sync_attempts', 3) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($subscriber['last_sync_attempt']): ?>
                                                    <small><?= date('M j, Y H:i', strtotime($subscriber['last_sync_attempt'])) ?></small>
                                                <?php else: ?>
                                                    <span class="text-muted">Never</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small><?= date('M j, Y H:i', strtotime($subscriber['created_at'])) ?></small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <?php if ($subscriber['sync_status'] === 'failed'): ?>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="action" value="retry_sync">
                                                            <input type="hidden" name="subscriber_id" value="<?= $subscriber['id'] ?>">
                                                            <button type="submit" class="btn btn-outline-warning" title="Retry Sync">
                                                                <i class="fas fa-redo"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($subscriber['error_message'])): ?>
                                                        <button type="button" class="btn btn-outline-info" 
                                                                title="<?= htmlspecialchars($subscriber['error_message']) ?>"
                                                                data-bs-toggle="tooltip">
                                                            <i class="fas fa-info-circle"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <form method="POST" style="display: inline;" 
                                                          onsubmit="return confirm('Are you sure you want to delete this subscriber?')">
                                                        <input type="hidden" name="action" value="delete_subscriber">
                                                        <input type="hidden" name="subscriber_id" value="<?= $subscriber['id'] ?>">
                                                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <nav aria-label="Subscribers pagination">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page - 1 ?>&status=<?= urlencode($statusFilter) ?>&search=<?= urlencode($searchEmail) ?>">Previous</a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>&status=<?= urlencode($statusFilter) ?>&search=<?= urlencode($searchEmail) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $page + 1 ?>&status=<?= urlencode($statusFilter) ?>&search=<?= urlencode($searchEmail) ?>">Next</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
});
</script>

<?php include dirname(__DIR__) . '/templates/footer.php'; ?>