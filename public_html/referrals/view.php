<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$dir = 'referrals';
$page = 'view';

$table_page = false;

require LANG . '/en.php';
$title = $lang['referral_view'] ?? 'View Referral';
$title_icon = '<i class="fa-solid fa-eye"></i>';

require 'get.php';

// Section Header Configuration
$section_header = true;
$section_header_subtitle = $last_edited_by_name ? 'Last edited by ' . $last_edited_by_name : '';

// Action buttons for header
$section_header_actions = [
    [
        'href' => 'list',
        'text' => 'Back',
        'icon' => 'fa-solid fa-arrow-left',
        'class' => 'btn-danger',
        'size' => 'btn-sm'
    ],
    [
        'href' => 'edit.php?id=' . $id,
        'text' => 'Edit <i class="fa-solid fa-arrow-right ms-1"></i>',
        'icon' => 'fa-solid fa-edit',
        'class' => 'btn-success',
        'size' => 'btn-sm'
    ]
];

// Record information for header
$section_header_info = [
    'Updated' => $updated_at ? date('M j, Y g:i A', strtotime($updated_at)) : '-',
    'by' => $last_edited_by_name ? $last_edited_by_name : '-'
];

require HEADER;
require BODY;
require NAV;
require SECTIONOPEN;
?>

<!-- Referral Number + Current Stage -->
<div class="card mb-4">
  <div class="card-body bg-light py-3">
    <div class="d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center">
        <i class="fa-solid fa-share-nodes fa-2x text-primary me-3"></i>
        <span class="fs-4 fw-bold text-dark">Referral #<?= htmlspecialchars($lead_id ?? 'N/A') ?></span>
      </div>
      <div class="d-flex align-items-center">
        <i class="fa-solid fa-chart-line fa-2x text-success me-3"></i>
        <?php
        if (isset($stage) && is_numeric($stage)) {
            $leads = new Leads();
            $badge_class = $leads->get_stage_badge_class((int)$stage);
            $stage_text = $leads->get_stage_display_name((int)$stage, $lang);
        } else {
            $badge_class = 'badge bg-secondary';
            $stage_text = '-';
        }
        ?>
        <span class="<?= $badge_class ?> fs-6"><?= htmlspecialchars($stage_text) ?></span>
      </div>
    </div>
  </div>
</div>

<!-- Contact Information -->
<div class="card mb-4">
  <div class="card-header">
    <h5 class="mb-0"><i class="fa-solid fa-user me-2"></i>Contact Information</h5>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col-md-6">
        <p><strong>Name:</strong> <?= htmlspecialchars($first_name . ' ' . $family_name) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($email ?? '-') ?></p>
      </div>
      <div class="col-md-6">
        <p><strong>Phone:</strong> <?= htmlspecialchars($cell_phone ?? '-') ?></p>
        <p><strong>Contact Type:</strong> 
          <?php
          if (isset($contact_type)) {
              $leads = new Leads();
              $contact_types = $leads->get_lead_contact_type_array();
              echo htmlspecialchars($contact_types[$contact_type] ?? 'Unknown');
          } else {
              echo '-';
          }
          ?>
        </p>
      </div>
    </div>
  </div>
</div>

<!-- Structure Information -->
<?php if (!empty($structure_description) || !empty($structure_other) || !empty($structure_additional)): ?>
<div class="card mb-4">
  <div class="card-header">
    <h5 class="mb-0"><i class="fa-solid fa-building me-2"></i>Structure Information</h5>
  </div>
  <div class="card-body">
    <?php if (!empty($structure_description)): ?>
      <p><strong>Description:</strong> <?= htmlspecialchars($structure_description) ?></p>
    <?php endif; ?>
    <?php if (!empty($structure_other)): ?>
      <p><strong>Other Details:</strong> <?= htmlspecialchars($structure_other) ?></p>
    <?php endif; ?>
    <?php if (!empty($structure_additional)): ?>
      <p><strong>Additional Information:</strong> <?= nl2br(htmlspecialchars($structure_additional)) ?></p>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<!-- File Links -->
<?php if (!empty($picture_upload_link) || !empty($plans_upload_link)): ?>
<div class="card mb-4">
  <div class="card-header">
    <h5 class="mb-0"><i class="fa-solid fa-paperclip me-2"></i>Uploaded Files</h5>
  </div>
  <div class="card-body">
    <?php if (!empty($picture_upload_link)): ?>
      <p><strong>Pictures:</strong> <a href="<?= htmlspecialchars($picture_upload_link) ?>" target="_blank" class="btn btn-sm btn-outline-primary">View Pictures</a></p>
    <?php endif; ?>
    <?php if (!empty($plans_upload_link)): ?>
      <p><strong>Plans:</strong> <a href="<?= htmlspecialchars($plans_upload_link) ?>" target="_blank" class="btn btn-sm btn-outline-primary">View Plans</a></p>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<?php
require SECTIONCLOSE;
require FOOTER;