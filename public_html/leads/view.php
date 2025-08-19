<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$dir = 'leads';
$page = 'view';

$table_page = false;

require LANG . '/en.php';
$title = $lang['lead_view'] ?? 'View Lead';
$title_icon = '<i class="fa-solid fa-eye"></i>';

require 'get.php';
require HEADER;
require BODY;
require NAV;
require SECTIONOPEN;
?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fa-solid fa-user me-2"></i>
                    <?= htmlspecialchars($first_name . ' ' . $last_name) ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">Contact Information</h6>
                        <p><strong>Email:</strong> 
                            <?php if ($email): ?>
                                <a href="mailto:<?= htmlspecialchars($email) ?>"><?= htmlspecialchars($email) ?></a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </p>
                        <p><strong>Phone:</strong> 
                            <?php if ($cell_phone): ?>
                                <a href="tel:<?= htmlspecialchars($cell_phone) ?>"><?= htmlspecialchars($cell_phone) ?></a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </p>
                        <p><strong>Contact Type:</strong> 
                            <?php 
                            $contact_types = $leads->get_lead_contact_type_array();
                            echo $contact_types[$ctype] ?? $ctype ?? '-';
                            ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Lead Information</h6>
                        <p><strong>Source:</strong> 
                            <?php 
                            $lead_sources = $leads->get_lead_source_array();
                            echo $lead_sources[$lead_source] ?? $lead_source ?? '-';
                            ?>
                        </p>
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
                        <p><strong>Structure Type:</strong> 
                            <?php 
                            $structure_types = $leads->get_lead_structure_type_array();
                            echo $structure_types[$structure_type] ?? $structure_type ?? '-';
                            ?>
                        </p>
                        <?php if ($estimate_number): ?>
                        <p><strong>Estimate #:</strong> 
                            <span class="badge bg-light text-dark">#<?= htmlspecialchars($estimate_number) ?></span>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($notes): ?>
                <div class="row mt-3">
                    <div class="col-12">
                        <h6 class="text-muted">Notes</h6>
                        <div class="border rounded p-3 bg-light">
                            <?= nl2br(htmlspecialchars($notes)) ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Record Information</h6>
            </div>
            <div class="card-body">
                <p><small class="text-muted">Created:</small><br>
                   <?= $created_at ? date('F j, Y g:i A', strtotime($created_at)) : '-' ?>
                </p>
                <p><small class="text-muted">Last Updated:</small><br>
                   <?= $updated_at ? date('F j, Y g:i A', strtotime($updated_at)) : '-' ?>
                </p>
                <?php if ($last_edited_by_name): ?>
                <p><small class="text-muted">Last Edited By:</small><br>
                   <?= htmlspecialchars($last_edited_by_name) ?>
                </p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title mb-0">Actions</h6>
            </div>
            <div class="card-body">
                <a href="edit?id=<?= $id ?>" class="btn btn-warning btn-sm mb-2">
                    <i class="fa-solid fa-edit me-1"></i> Edit Lead
                </a><br>
                <a href="delete?id=<?= $id ?>" class="btn btn-danger btn-sm mb-2">
                    <i class="fa-solid fa-trash me-1"></i> Delete Lead
                </a><br>
                <a href="list" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>
    </div>
</div>

<?php
require SECTIONCLOSE;
require FOOTER;
?>