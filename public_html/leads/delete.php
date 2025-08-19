<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$dir = 'leads';
$page = 'delete';

$table_page = false;

require LANG . '/en.php';
$title = $lang['lead_delete'] ?? 'Delete Lead';
$title_icon = '<i class="fa-solid fa-trash"></i>';

require 'get.php';
require HEADER;
require BODY;
require NAV;
require SECTIONOPEN;
?>

<div class="alert alert-warning" role="alert">
    <h4 class="alert-heading">
        <i class="fa-solid fa-exclamation-triangle me-2"></i>
        Confirm Deletion
    </h4>
    <p>You are about to permanently delete this lead. This action cannot be undone.</p>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Lead to be deleted:</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Name:</strong> <?= htmlspecialchars($first_name . ' ' . $last_name) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($email ?? '-') ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($cell_phone ?? '-') ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Stage:</strong> 
                    <?php
                    $badge_class = 'badge bg-secondary';
                    switch (strtolower($stage)) {
                        case 'lead': $badge_class = 'badge bg-primary'; break;
                        case 'prospect': $badge_class = 'badge bg-info'; break;
                        case 'qualified': $badge_class = 'badge bg-warning'; break;
                        case 'completed estimate': $badge_class = 'badge bg-success'; break;
                        case 'closed lost': $badge_class = 'badge bg-danger'; break;
                    }
                    ?>
                    <span class="<?= $badge_class ?>"><?= htmlspecialchars($stage ?? '-') ?></span>
                </p>
                <p><strong>Created:</strong> <?= $created_at ? date('F j, Y g:i A', strtotime($created_at)) : '-' ?></p>
                <?php if ($last_edited_by_name): ?>
                <p><strong>Last Edited By:</strong> <?= htmlspecialchars($last_edited_by_name) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<form action="post.php" method="POST" class="mt-4">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" value="<?= $id ?>">
    <input type="hidden" name="dir" value="<?= $dir ?>">
    <input type="hidden" name="page" value="<?= $page ?>">
    
    <div class="d-flex gap-2">
        <a href="list" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>
            Cancel
        </a>
        <a href="view?id=<?= $id ?>" class="btn btn-info">
            <i class="fa-solid fa-eye me-1"></i>
            View Lead
        </a>
        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you absolutely sure you want to delete this lead? This action cannot be undone.')">
            <i class="fa-solid fa-trash me-1"></i>
            Delete Lead Permanently
        </button>
    </div>
</form>

<?php
require SECTIONCLOSE;
require FOOTER;
?>