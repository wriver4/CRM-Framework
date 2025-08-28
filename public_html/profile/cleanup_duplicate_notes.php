<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();

// Check if user has admin permissions using rid
if (!isset($_SESSION['rid']) || $_SESSION['rid'] != 1) {
    $_SESSION['error_message'] = 'Access denied. Admin privileges required.';
    header('Location: /dashboard.php');
    exit;
}

$page = 'cleanup_duplicate_notes';
$title = 'Cleanup Duplicate Notes';
$title_icon = '<i class="fa-solid fa-broom"></i>';

require HEADER;
require BODY;
require NAV;
require SECTIONOPEN;

// Get analysis data
$notes_class = new Notes();
$database = new Database();
$pdo = $database->dbcrm();

// Get duplicate analysis
$duplicates_query = "
    SELECT 
        ln.lead_id,
        l.lead_id,
        l.full_name as lead_name,
        n.note_text,
        COUNT(DISTINCT n.id) as duplicate_notes_count,
        GROUP_CONCAT(DISTINCT n.id ORDER BY n.id) as note_ids,
        GROUP_CONCAT(DISTINCT n.date_created ORDER BY n.date_created) as creation_dates,
        MIN(n.date_created) as first_created,
        MAX(n.date_created) as last_created,
        TIMESTAMPDIFF(MINUTE, MIN(n.date_created), MAX(n.date_created)) as minutes_between,
        CHAR_LENGTH(n.note_text) as note_length
    FROM leads_notes ln
    INNER JOIN notes n ON ln.note_id = n.id
    LEFT JOIN leads l ON ln.lead_id = l.id
    WHERE n.note_text != '' AND n.note_text IS NOT NULL
    GROUP BY ln.lead_id, n.note_text
    HAVING COUNT(DISTINCT n.id) > 1
    ORDER BY duplicate_notes_count DESC, ln.lead_id
";

$duplicates_stmt = $pdo->query($duplicates_query);
$duplicates = $duplicates_stmt->fetchAll();

// Categorize duplicates
$safe_cleanup = [];
$manual_review = [];

foreach ($duplicates as $duplicate) {
    if ($duplicate['minutes_between'] <= 10 && 
        $duplicate['duplicate_notes_count'] <= 5 && 
        $duplicate['note_length'] >= 5) {
        $safe_cleanup[] = $duplicate;
    } else {
        $manual_review[] = $duplicate;
    }
}

// Get total counts
$total_notes = $pdo->query("SELECT COUNT(*) FROM notes")->fetchColumn();
$total_junction = $pdo->query("SELECT COUNT(*) FROM leads_notes")->fetchColumn();
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-warning">
            <h5><i class="fa-solid fa-exclamation-triangle me-2"></i>Important Safety Notice</h5>
            <p class="mb-0">This tool will permanently remove duplicate notes. A backup will be created automatically, 
            but please ensure you have a recent database backup before proceeding.</p>
        </div>
    </div>
</div>

<!-- Statistics Dashboard -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Total Notes</h6>
                        <h3><?= number_format($total_notes) ?></h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fa-solid fa-sticky-note fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Duplicate Groups</h6>
                        <h3><?= count($duplicates) ?></h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fa-solid fa-copy fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Safe Auto-Cleanup</h6>
                        <h3><?= count($safe_cleanup) ?></h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fa-solid fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="card-title">Manual Review</h6>
                        <h3><?= count($manual_review) ?></h3>
                    </div>
                    <div class="align-self-center">
                        <i class="fa-solid fa-eye fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Safe Cleanup Section -->
<?php if (!empty($safe_cleanup)): ?>
<div class="card mb-4">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fa-solid fa-magic me-2"></i>Safe Auto-Cleanup Candidates
        </h5>
        <form method="POST" action="cleanup_duplicate_notes_do.php" class="d-inline">
            <input type="hidden" name="action" value="safe_cleanup">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)) ?>">
            <?php if (!isset($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); ?>
            <button type="submit" class="btn btn-light btn-sm" 
                    onclick="return confirm('This will remove <?= array_sum(array_column($safe_cleanup, 'duplicate_notes_count')) - count($safe_cleanup) ?> duplicate notes. Continue?')">
                <i class="fa-solid fa-broom me-1"></i>Clean Up Safe Duplicates
            </button>
        </form>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">
            These duplicates were created within 10 minutes of each other and are likely accidental double-submissions.
            The oldest note will be kept, newer duplicates will be removed.
        </p>
        
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Lead</th>
                        <th>Note Preview</th>
                        <th>Duplicates</th>
                        <th>Time Between</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($safe_cleanup, 0, 20) as $duplicate): ?>
                    <tr>
                        <td>
                            <strong>#<?= htmlspecialchars($duplicate['lead_id'] ?? 'N/A') ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($duplicate['lead_name'] ?? 'No Name') ?></small>
                        </td>
                        <td>
                            <div class="text-truncate" style="max-width: 300px;">
                                <?= htmlspecialchars(substr($duplicate['note_text'], 0, 100)) ?>
                                <?= strlen($duplicate['note_text']) > 100 ? '...' : '' ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-warning"><?= $duplicate['duplicate_notes_count'] ?></span>
                        </td>
                        <td>
                            <?= $duplicate['minutes_between'] ?> min
                        </td>
                        <td>
                            <form method="POST" action="cleanup_duplicate_notes_do.php" class="d-inline">
                                <input type="hidden" name="action" value="cleanup_single">
                                <input type="hidden" name="lead_id" value="<?= $duplicate['lead_id'] ?>">
                                <input type="hidden" name="note_text" value="<?= htmlspecialchars($duplicate['note_text']) ?>">
                                <input type="hidden" name="note_ids" value="<?= htmlspecialchars($duplicate['note_ids']) ?>">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-success" 
                                        onclick="return confirm('Clean up this duplicate group?')">
                                    <i class="fa-solid fa-broom"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (count($safe_cleanup) > 20): ?>
        <p class="text-muted mt-3">
            <i class="fa-solid fa-info-circle me-1"></i>
            Showing first 20 of <?= count($safe_cleanup) ?> safe cleanup candidates. 
            Use "Clean Up Safe Duplicates" button to process all.
        </p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Manual Review Section -->
<?php if (!empty($manual_review)): ?>
<div class="card mb-4">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">
            <i class="fa-solid fa-eye me-2"></i>Manual Review Required
        </h5>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">
            These duplicates require manual review due to longer time spans, excessive duplicates, or other complexity factors.
        </p>
        
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Lead</th>
                        <th>Note Preview</th>
                        <th>Duplicates</th>
                        <th>Time Span</th>
                        <th>Reason for Review</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($manual_review as $duplicate): ?>
                    <tr>
                        <td>
                            <strong>#<?= htmlspecialchars($duplicate['lead_id'] ?? 'N/A') ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($duplicate['lead_name'] ?? 'No Name') ?></small>
                        </td>
                        <td>
                            <div class="text-truncate" style="max-width: 300px;">
                                <?= htmlspecialchars(substr($duplicate['note_text'], 0, 100)) ?>
                                <?= strlen($duplicate['note_text']) > 100 ? '...' : '' ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-danger"><?= $duplicate['duplicate_notes_count'] ?></span>
                        </td>
                        <td>
                            <?php if ($duplicate['minutes_between'] < 60): ?>
                                <?= $duplicate['minutes_between'] ?> min
                            <?php else: ?>
                                <?= round($duplicate['minutes_between'] / 60, 1) ?> hrs
                            <?php endif; ?>
                        </td>
                        <td>
                            <small class="text-muted">
                                <?php
                                $reasons = [];
                                if ($duplicate['duplicate_notes_count'] > 5) $reasons[] = 'Excessive duplicates';
                                if ($duplicate['minutes_between'] > 60) $reasons[] = 'Long time span';
                                if ($duplicate['note_length'] < 5) $reasons[] = 'Very short note';
                                echo implode(', ', $reasons);
                                ?>
                            </small>
                        </td>
                        <td>
                            <a href="/leads/view.php?id=<?= $duplicate['lead_id'] ?>" 
                               class="btn btn-sm btn-outline-primary" target="_blank">
                                <i class="fa-solid fa-external-link-alt"></i> View Lead
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (empty($duplicates)): ?>
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fa-solid fa-check-circle fa-4x text-success mb-3"></i>
        <h4>No Duplicate Notes Found</h4>
        <p class="text-muted">Your notes database is clean! No duplicate content was detected.</p>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add confirmation dialogs for safety
    const cleanupForms = document.querySelectorAll('form[action*="cleanup_duplicate_notes_do.php"]');
    cleanupForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const action = form.querySelector('input[name="action"]').value;
            if (action === 'safe_cleanup') {
                const confirmed = confirm(
                    'This will create a backup and remove duplicate notes automatically.\n\n' +
                    'Are you sure you want to proceed?'
                );
                if (!confirmed) {
                    e.preventDefault();
                }
            }
        });
    });
});
</script>

<?php
require SECTIONCLOSE;
require FOOTER;
?>