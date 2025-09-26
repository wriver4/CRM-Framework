<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
// Direct routing variables - these determine page navigation and template inclusion
$dir = 'contracting';
$page = 'edit';

$table_page = false;
$button_new = true;

require LANG . '/en.php';
$title = $lang['contracting_edit'] ?? 'Edit Contract';
$new_button = $lang['contact_new'] ?? 'New Contact';
$new_icon = '<i class="fa-solid fa-address-book"></i>';
$title_icon = '<i class="fa-solid fa-edit"></i>';

require 'get.php';
require HEADER;
require BODY;
require NAV;
require SECTIONOPEN;
?>

<form action="post.php"
      method="POST"
      autocomplete="off">



  <!-- Lead Number + Current Stage -->
  <div class="card mb-4">
    <div class="card-body bg-light py-3">
      <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
          <i class="fa-solid fa-file-contract fa-2x text-primary me-3"></i>
          <span class="fs-4 fw-bold text-dark">Contract #<?= htmlspecialchars($lead_id ?? 'N/A') ?></span>
          <input type="hidden"
                 name="lead_id"
                 value="<?= htmlspecialchars($lead_id ?? '') ?>">
        </div>
        <div class="d-flex align-items-center">
          <i class="fa-solid fa-chart-line fa-2x text-success me-3"></i>
          <?php
          if (isset($stage) && is_numeric($stage)) {
              $badge_class = $leads->get_stage_badge_class((int)$stage);
              $stage_text = $leads->get_stage_display_name((int)$stage, $lang);
          } else {
              $badge_class = 'badge bg-secondary';
              $stage_text = '-';
          }
          ?>
          <span class="<?= $badge_class ?> fs-6 px-3 py-2"><?= htmlspecialchars($stage_text ?: '-') ?></span>
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
      <h5 class="mb-0">
        <i class="fa-solid fa-user me-2"></i><?= $lang['lead_contact_information'] ?? 'Contact Information'; ?>
      </h5>
      <div class="d-flex align-items-center gap-3">
        <?php if (isset($property_contacts) && count($property_contacts) > 1): ?>
        <div class="d-flex align-items-center">
          <label for="contact_selector" class="form-label text-white me-2 mb-0">
            <i class="fa-solid fa-users me-1"></i>Contact:
          </label>
          <select name="contact_selector" id="contact_selector" class="form-select form-select-sm" style="min-width: 200px;">
            <?php foreach ($property_contacts as $contact): ?>
            <option value="<?= htmlspecialchars($contact['id']) ?>"
                    data-full-name="<?= htmlspecialchars($contact['full_name']) ?>"
                    data-email="<?= htmlspecialchars($contact['email']) ?>"
                    data-cell-phone="<?= htmlspecialchars($contact['cell_phone']) ?>"
                    <?= ($contact['id'] == $selected_contact_id) ? 'selected' : '' ?>>
              <?= htmlspecialchars($contact['full_name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>
        <a href="/contacts/new"
           class="btn btn-success btn-sm"
           tabindex="0"
           role="button"
           aria-pressed="false">
          <i class="fa-solid fa-address-book"></i>&ensp;<?= $lang['contact_new'] ?? 'New Contact'; ?>
        </a>
      </div>
    </div>
    <div class="card-body">

      <!-- Full Name, Cell Phone & Email -->
      <div class="row mb-3">
        <div class="col">
          <div class="form-group">
            <label class="form-label fw-bold text-muted"><?= $lang['lead_full_name'] ?? 'Full Name'; ?></label>
            <div class="bg-light p-2 rounded border">
              <i class="fa-solid fa-user text-primary me-2"></i><?= htmlspecialchars($full_name ?? '-') ?>
            </div>
            <input type="hidden"
                   name="full_name"
                   value="<?= htmlspecialchars($full_name ?? '') ?>">
          </div>
        </div>
        <div class="col">
          <div class="form-group">
            <label class="form-label fw-bold text-muted"><?= $lang['lead_cell_phone'] ?? 'Cell Phone'; ?></label>
            <div class="bg-light p-2 rounded border">
              <i class="fa-solid fa-phone text-success me-2"></i><?= htmlspecialchars($cell_phone ?? '-') ?>
            </div>
            <input type="hidden"
                   name="cell_phone"
                   value="<?= htmlspecialchars($cell_phone ?? '') ?>">
          </div>
        </div>
        <div class="col">
          <div class="form-group">
            <label class="form-label fw-bold text-muted"><?= $lang['lead_email'] ?? 'Email'; ?></label>
            <div class="bg-light p-2 rounded border">
              <i class="fa-solid fa-envelope text-info me-2"></i><?= htmlspecialchars($email ?? '-') ?>
            </div>
            <input type="hidden"
                   name="email"
                   value="<?= htmlspecialchars($email ?? '') ?>">
          </div>
        </div>
      </div>

      <!-- Contact Type, Business Name & Project Name -->
      <div class="row">
        <div class="col">
          <div class="form-group">
            <label class="form-label fw-bold text-muted"><?= $lang['lead_contact_type'] ?? 'Contact Type'; ?></label>
            <?php
            $contact_types = $leads->get_lead_contact_type_array();
            $contact_type_display = $contact_types[$contact_type] ?? '-';
            ?>
            <div class="bg-light p-2 rounded border">
              <i class="fa-solid fa-tag text-warning me-2"></i><?= htmlspecialchars($contact_type_display) ?>
            </div>
            <input type="hidden"
                   name="contact_type"
                   value="<?= htmlspecialchars($contact_type ?? '') ?>">
          </div>
        </div>
        <div class="col">
          <div class="form-group">
            <label class="form-label fw-bold text-muted"><?= $lang['lead_business_name'] ?? 'Business Name'; ?></label>
            <div class="bg-light p-2 rounded border">
              <i class="fa-solid fa-building text-secondary me-2"></i><?= htmlspecialchars($business_name ?? '-') ?>
            </div>
            <input type="hidden"
                   name="business_name"
                   value="<?= htmlspecialchars($business_name ?? '') ?>">
          </div>
        </div>
        <div class="col">
          <div class="form-group">
            <label for="project_name"
                   class="form-label fw-bold text-muted"><?= $lang['lead_project_name'] ?? 'Project Name'; ?></label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-project-diagram text-primary"></i></span>
              <input type="text"
                     name="project_name"
                     maxlength="255"
                     id="project_name"
                     class="form-control"
                     value="<?= htmlspecialchars($project_name ?? '') ?>"
                     autocomplete="off"
                     placeholder="<?= $lang['placeholder_project_name'] ?? 'Enter project name...'; ?>">
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>



  <!-- Address -->
  <div class="card mb-4">
    <div class="card-header bg-primary text-white">
      <h5 class="mb-0">
        <i class="fa-solid fa-map-marker-alt me-2"></i><?= $lang['address'] ?? 'Address'; ?>
      </h5>
    </div>
    <div class="card-body">
      <?php
      $full_address_display = $full_address ?? '';
      if (!$full_address_display) {
          $street1 = trim($form_street_1 ?? '');
          $street2 = trim($form_street_2 ?? '');
          $city = trim($form_city ?? '');
          $state = trim($form_state ?? '');
          $postcode = trim($form_postcode ?? '');
          $country = trim($form_country ?? '');
          $line1 = trim(($street1 . ' ' . $street2));
          $cityPart = $city;
          if ($city !== '' && ($state !== '' || $postcode !== '')) { $cityPart .= ','; }
          $statePost = trim($state . ($postcode !== '' ? ' ' . $postcode : ''));
          $line2 = trim(($cityPart . ' ' . $statePost));
          $parts = [];
          if ($line1 !== '') $parts[] = $line1;
          if ($line2 !== '') $parts[] = $line2;
          if ($country !== '') $parts[] = $country;
          $full_address_display = implode("\n", $parts);
      }
      ?>
      <div class="bg-light p-3 rounded border">
        <div class="d-flex align-items-center">
          <i class="fa-solid fa-home text-info me-3 fa-lg"></i>
          <div class="address-content">
            <?php if ($full_address_display): ?>
            <?= str_replace("\n", ", ", htmlspecialchars($full_address_display)) ?>
            <?php else: ?>
            <em class="text-muted"><?= $lang['message_no_address'] ?? 'No address provided'; ?></em>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <input type="hidden"
             name="full_address"
             value="<?= htmlspecialchars($full_address_display) ?>">
      <input type="hidden"
             name="form_street_1"
             value="<?= htmlspecialchars($form_street_1 ?? '') ?>">
      <input type="hidden"
             name="form_street_2"
             value="<?= htmlspecialchars($form_street_2 ?? '') ?>">
      <input type="hidden"
             name="form_city"
             value="<?= htmlspecialchars($form_city ?? '') ?>">
      <input type="hidden"
             name="form_state"
             value="<?= htmlspecialchars($form_state ?? '') ?>">
      <input type="hidden"
             name="form_postcode"
             value="<?= htmlspecialchars($form_postcode ?? '') ?>">
      <input type="hidden"
             name="form_country"
             value="<?= htmlspecialchars($form_country ?? '') ?>">
      <input type="hidden"
             name="timezone"
             id="timezone"
             value="<?= htmlspecialchars($timezone ?? '') ?>">
    </div>
  </div>

  <!-- Services Interested In -->
  <?php
  $services_display = '';
  if (!empty($services_interested_in)) {
      $services = $helpers->get_lead_services_array($lang);
      $ids = array_filter(array_map('trim', explode(',', (string)$services_interested_in)));
      $names = [];
      foreach ($ids as $sid) {
          if (isset($services[$sid])) {
              $names[] = $services[$sid];
          }
      }
      if (!empty($names)) {
          $services_display = implode(', ', $names);
      }
  }
  ?>
  <div class="card mb-4">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center" 
         style="cursor: pointer;"
         data-bs-toggle="collapse"
         data-bs-target="#servicesInterestedCollapse"
         aria-expanded="false"
         aria-controls="servicesInterestedCollapse">
      <h5 class="mb-0 d-flex align-items-center">
        <i class="fa-solid fa-cogs me-2"></i><?= $lang['lead_services_interested_in'] ?? 'Services Interested In'; ?>
      </h5>
      <i class="fa-solid fa-chevron-down collapse-icon"
         style="transition: transform 0.3s ease; cursor: pointer;"></i>
    </div>
    <div class="collapse"
         id="servicesInterestedCollapse">
      <div class="card-body">
        <?php if ($services_display): ?>
        <div class="bg-light p-3 rounded border">
          <div class="d-flex align-items-center">
            <i class="fa-solid fa-tools text-success me-3 fa-lg"></i>
            <div class="services-content">
              <?= htmlspecialchars($services_display) ?>
            </div>
          </div>
        </div>
        <?php else: ?>
        <div class="bg-light p-3 rounded border">
          <div class="d-flex align-items-center">
            <i class="fa-solid fa-tools text-muted me-3 fa-lg"></i>
            <div class="services-content text-muted">
              <em><?= $lang['message_no_services'] ?? 'No services selected'; ?></em>
            </div>
          </div>
        </div>
        <?php endif; ?>
        <input type="hidden"
               name="services_interested_in"
               value="<?= htmlspecialchars($services_interested_in ?? '') ?>">
      </div>
    </div>
  </div>

  <!-- Structure Information -->
  <?php
  $structure_description_display = '';
  if (!empty($structure_description)) {
      $desc_map = $helpers->get_lead_structure_description_array($lang);
      $ids = array_filter(array_map('trim', explode(',', (string)$structure_description)));
      $names = [];
      foreach ($ids as $sid) {
          if (isset($desc_map[$sid])) { $names[] = $desc_map[$sid]; }
      }
      if (!empty($names)) { $structure_description_display = implode(', ', $names); }
  }
  
  // Always show structure section in edit mode since structure_type is required
  $has_structure_content = true;
  ?>
  <?php if ($has_structure_content): ?>
  <div class="card mb-4">
    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center" 
         style="cursor: pointer;"
         data-bs-toggle="collapse"
         data-bs-target="#structureInformationCollapse"
         aria-expanded="false"
         aria-controls="structureInformationCollapse">
      <h5 class="mb-0 d-flex align-items-center">
        <i class="fa-solid fa-building me-2"></i><?= $lang['lead_structure_information'] ?? 'Structure Information'; ?>
      </h5>
      <i class="fa-solid fa-chevron-up collapse-icon"
         style="transition: transform 0.3s ease; cursor: pointer;"></i>
    </div>
    <div class="collapse"
         id="structureInformationCollapse">
      <div class="card-body">
        <!-- Structure Type - Inline with label -->
        <div class="mb-3">
          <?php
        $structure_types = $helpers->get_lead_structure_type_array($lang);
        $structure_type_display = $structure_types[$structure_type] ?? $structure_type ?? '-';
        ?>
          <p class="mb-0">
            <strong class="text-muted"><?= $lang['lead_structure_type'] ?? 'Structure Type' ?>:</strong>
            <span class="ms-2">
              <i class="fa-solid fa-building text-warning me-1"></i><?= htmlspecialchars($structure_type_display) ?>
            </span>
          </p>
          <input type="hidden"
                 name="structure_type"
                 value="<?= htmlspecialchars($structure_type ?? '') ?>">
        </div>

        <!-- Structure Description -->
        <?php if ($structure_description_display): ?>
        <div class="mb-3">
          <label
                 class="form-label fw-bold text-muted"><?= $lang['lead_structure_description'] ?? 'Structure Description' ?></label>
          <div class="bg-light p-3 rounded border">
            <div class="d-flex align-items-center">
              <i class="fa-solid fa-blueprint text-warning me-3 fa-lg"></i>
              <div>
                <?= htmlspecialchars($structure_description_display) ?>
              </div>
            </div>
          </div>
          <input type="hidden"
                 name="structure_description"
                 value="<?= htmlspecialchars($structure_description ?? '') ?>">
        </div>
        <?php endif; ?>

        <!-- Structure Other -->
        <?php if (!empty($structure_other)): ?>
        <div class="mb-3">
          <label
                 class="form-label fw-bold text-muted"><?= $lang['lead_structure_other'] ?? 'Other Description' ?></label>
          <div class="bg-light p-3 rounded border">
            <div class="d-flex align-items-center">
              <i class="fa-solid fa-plus-circle text-secondary me-3 fa-lg"></i>
              <div>
                <?= htmlspecialchars($structure_other) ?>
              </div>
            </div>
          </div>
          <input type="hidden"
                 name="structure_other"
                 value="<?= htmlspecialchars($structure_other ?? '') ?>">
        </div>
        <?php endif; ?>

        <!-- Structure Additional -->
        <?php if (!empty($structure_additional)): ?>
        <div class="mb-3">
          <label
                 class="form-label fw-bold text-muted"><?= $lang['lead_structure_additional'] ?? 'Additional Buildings' ?></label>
          <div class="bg-light p-3 rounded border">
            <div class="d-flex align-items-start">
              <i class="fa-solid fa-comment-alt text-info me-3 mt-1 fa-lg"></i>
              <div>
                <?= nl2br(htmlspecialchars($structure_additional)) ?>
              </div>
            </div>
          </div>
          <input type="hidden"
                 name="structure_additional"
                 value="<?= htmlspecialchars($structure_additional ?? '') ?>">
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- File Upload Links -->
  <div class="card mb-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center" style="cursor: pointer;">
      <h5 class="mb-0">
        <button class="btn p-0 text-start text-white border-0 bg-transparent d-flex align-items-center w-100"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#fileUploadLinksCollapse"
                aria-expanded="false"
                aria-controls="fileUploadLinksCollapse"
                style="box-shadow: none; cursor: pointer;">
          <i class="fa-solid fa-upload me-2"></i><?= $lang['lead_file_upload_links'] ?? 'File Upload Links'; ?>
        </button>
      </h5>
      <i class="fa-solid fa-chevron-up collapse-icon"
         style="transition: transform 0.3s ease; cursor: pointer;"></i>
    </div>
    <div class="collapse"
         id="fileUploadLinksCollapse">
      <div class="card-body">
        <!-- Plans Upload Link -->
        <div class="row mb-3">
          <div class="col-12">
            <div class="form-group">
              <label
                     class="form-label fw-bold text-muted"><?= $lang['lead_plans_upload_link'] ?? 'Plans Upload Link'; ?></label>
              <div class="bg-light p-3 rounded border">
                <div class="d-flex align-items-center">
                  <i class="fa-solid fa-file-alt text-info me-3 fa-lg"></i>
                  <div class="upload-link-content">
                    <?php if (!empty($plans_upload_link)): ?>
                    <span class="text-break"><?= htmlspecialchars($plans_upload_link) ?></span>
                    <?php else: ?>
                    <em class="text-muted"><?= $lang['message_no_plans_link'] ?? 'No plans upload link provided'; ?></em>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <input type="hidden"
                     name="plans_upload_link"
                     value="<?= htmlspecialchars($plans_upload_link ?? '') ?>">
            </div>
          </div>
        </div>

        <!-- Pictures Upload Link -->
        <div class="row">
          <div class="col-12">
            <div class="form-group">
              <label
                     class="form-label fw-bold text-muted"><?= $lang['lead_pictures_upload_link'] ?? 'Pictures Upload Link'; ?></label>
              <div class="bg-light p-3 rounded border">
                <div class="d-flex align-items-center">
                  <i class="fa-solid fa-image text-primary me-3 fa-lg"></i>
                  <div class="upload-link-content">
                    <?php if (!empty($picture_upload_link)): ?>
                    <span class="text-break"><?= htmlspecialchars($picture_upload_link) ?></span>
                    <?php else: ?>
                    <em class="text-muted"><?= $lang['message_no_pictures_link'] ?? 'No pictures upload link provided'; ?></em>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <input type="hidden"
                     name="picture_upload_link"
                     value="<?= htmlspecialchars($picture_upload_link ?? '') ?>">
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Screening Estimates -->
  <?php
  // Check if user has permission to edit engineering or sales estimates
  $user_role = $_SESSION['user_role'] ?? 'user';
  $user_rid = $_SESSION['user_rid'] ?? 0;
  
  // Define role permissions
  $can_edit_engineering = in_array($user_rid, [1, 2, 4, 5]); // Super Admin, Admin, Operations Technician 1 & 2
  $can_edit_sales = in_array($user_rid, [1, 2, 13, 14, 15]); // Super Admin, Admin, Sales Manager, Sales Assistant, Sales Person
  $can_edit_admin_leads = in_array($user_rid, [1, 2]); // Super Admin, Admin (can edit both)
  
  // Check if any engineering data exists to determine default collapse state
  $has_engineering_data = !empty($eng_system_cost_low) || !empty($eng_system_cost_high) || !empty($eng_protected_area);
  $default_expanded = $has_engineering_data ? 'show' : '';
  $chevron_direction = $has_engineering_data ? 'up' : 'down';
  ?>
  
  <div class="card mb-4">
    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center" 
         style="cursor: pointer;"
         data-bs-toggle="collapse"
         data-bs-target="#screeningEstimatesCollapse"
         aria-expanded="<?= $has_engineering_data ? 'true' : 'false' ?>"
         aria-controls="screeningEstimatesCollapse">
      <h5 class="mb-0 d-flex align-items-center">
        <i class="fa-solid fa-calculator me-2"></i><?= $lang['screening_estimates'] ?? 'Screening Estimates'; ?>
      </h5>
      <i class="fa-solid fa-chevron-<?= $chevron_direction ?> collapse-icon"
         style="transition: transform 0.3s ease; cursor: pointer;"></i>
    </div>
    <div class="collapse <?= $default_expanded ?>"
         id="screeningEstimatesCollapse">
      <div class="card-body">
        
        <!-- Engineering Estimates Row -->
        <div class="row mb-4">
          <div class="col-12">
            <h6 class="text-muted mb-3">
              <i class="fa-solid fa-cogs me-2"></i><?= $lang['engineering_estimates'] ?? 'Engineering Estimates'; ?>
            </h6>
            <div class="row">
              <!-- Engineering System Cost -->
              <div class="col-md-8">
                <div class="form-group">
                  <label class="form-label fw-bold text-muted"><?= $lang['system_cost'] ?? 'System Cost'; ?></label>
                  <div class="row">
                    <div class="col-6">
                      <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number"
                               name="eng_system_cost_low"
                               id="eng_system_cost_low"
                               class="form-control"
                               value="<?= htmlspecialchars($eng_system_cost_low ?? '') ?>"
                               placeholder="<?= $lang['system_cost_low'] ?? 'Low' ?>"
                               min="0"
                               step="1"
                               <?= !$can_edit_engineering && !$can_edit_admin_leads ? 'readonly' : '' ?>>
                      </div>

                    </div>
                    <div class="col-6">
                      <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number"
                               name="eng_system_cost_high"
                               id="eng_system_cost_high"
                               class="form-control"
                               value="<?= htmlspecialchars($eng_system_cost_high ?? '') ?>"
                               placeholder="<?= $lang['system_cost_high'] ?? 'High' ?>"
                               min="0"
                               step="1"
                               <?= !$can_edit_engineering && !$can_edit_admin_leads ? 'readonly' : '' ?>>
                      </div>

                    </div>
                  </div>
                </div>
              </div>
              <!-- Engineering Protected Area -->
              <div class="col-md-4">
                <div class="form-group">
                  <label for="eng_protected_area"
                         class="form-label fw-bold text-muted"><?= $lang['protected_area'] ?? 'Protected Area'; ?></label>
                  <div class="input-group">
                    <input type="number"
                           name="eng_protected_area"
                           id="eng_protected_area"
                           class="form-control"
                           value="<?= htmlspecialchars($eng_protected_area ?? '') ?>"
                           placeholder="0"
                           min="0"
                           step="1"
                           <?= !$can_edit_engineering && !$can_edit_admin_leads ? 'readonly' : '' ?>>
                    <span class="input-group-text"><?= $lang['protected_area_sqft'] ?? 'SQFT' ?></span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        

        
      </div>
    </div>
  </div>

  <!-- Notes System with Drawer -->
  <?php 
  try {
    $notes_class = new Notes();
    $search = $_GET['notes_search'] ?? '';
    $order = $_GET['notes_order'] ?? 'DESC';
    $existing_notes = $notes_class->get_notes_by_lead($id ?? 0, $search, $order);
    $notes_count = $notes_class->get_notes_count_by_lead($id ?? 0, $search);
    $total_notes = $notes_class->get_notes_count_by_lead($id ?? 0); // Total without search filter
  } catch (Exception $e) {
    $existing_notes = [];
    $notes_count = 0;
    $total_notes = 0;
    error_log('Notes system error in leads/edit.php: ' . $e->getMessage());
  }
  ?>



  <!-- Quick Add Note Section -->
  <div class="card mb-4">
    <div class="card-header bg-primary text-white">
      <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
          <i class="fa-solid fa-sticky-note fa-2x text-white me-3"></i>
          <div>
            <h6 class="mb-0">Notes & Actions</h6>
            <small class="text-white-50">
              <span id="notes-count-header"><?= $notes_count ?></span> of
              <span id="total-notes-header"><?= $total_notes ?></span>
              note<?= $total_notes != 1 ? 's' : '' ?>
              <?php if (!empty($search)): ?>
              <span class="badge bg-light text-dark ms-1">filtered</span>
              <?php endif; ?>
            </small>
          </div>
        </div>
        <button type="button"
                class="btn btn-outline-light"
                data-bs-toggle="offcanvas"
                data-bs-target="#notesDrawer">
          <i class="fa-solid fa-eye me-2"></i>View Notes
        </button>
      </div>
    </div>
    <div class="card-body">
      <div class="row mb-3">
        <div class="col-12">
          <label class="form-label fw-bold"><?= $lang['label_current_action'] ?? 'Current Action'; ?></label>
          <div>
            <?php 
            // 🌍 Internationalized note source array with desired order and options
            $custom_note_sources = [
                1 => $lang['note_source_phone_call'] ?? 'Phone Call',
                2 => $lang['note_source_email'] ?? 'Email',
                3 => $lang['note_source_text_message'] ?? 'Text Message',
                5 => $lang['note_source_virtual_meeting'] ?? 'Virtual Meeting',
                6 => $lang['note_source_in_person'] ?? 'In-Person',
                4 => $lang['note_source_internal_note'] ?? 'Internal Note'
            ];
            foreach ($custom_note_sources as $key => $value): ?>
            <div class="form-check form-check-inline">
              <input class="form-check-input"
                     type="radio"
                     name="note_source"
                     id="source<?= $key ?>"
                     value="<?= $key ?>"
                     <?= $key == 1 ? 'checked' : '' ?>>
              <label class="form-check-label"
                     for="source<?= $key ?>">
                <span class="<?= $notes_class->get_source_badge_class($key) ?> px-2 py-1"><?= $value ?></span>
              </label>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-12">
          <textarea name="note_text"
                    id="note_text"
                    class="form-control"
                    rows="2"
                    placeholder="<?= $lang['placeholder_questions_asked'] ?? 'Questions Asked'; ?>"></textarea>
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-12">
          <label class="form-label fw-bold"><?= $lang['label_next_action'] ?? 'Next Action'; ?></label>
          <div>
            <?php 
            // 🌍 Internationalized next action source array with desired order and options (matching Current Action)
            $next_action_sources = [
                1 => $lang['note_source_phone_call'] ?? 'Phone Call',
                2 => $lang['note_source_email'] ?? 'Email',
                3 => $lang['note_source_text_message'] ?? 'Text Message',
                5 => $lang['note_source_virtual_meeting'] ?? 'Virtual Meeting',
                6 => $lang['note_source_in_person'] ?? 'In-Person',
                4 => $lang['note_source_internal_note'] ?? 'Internal Note'
            ];
            foreach ($next_action_sources as $key => $value): ?>
            <div class="form-check form-check-inline">
              <input class="form-check-input"
                     type="radio"
                     name="next_action"
                     id="next_action<?= $key ?>"
                     value="<?= $key ?>">
              <label class="form-check-label"
                     for="next_action<?= $key ?>">
                <span class="<?= $notes_class->get_source_badge_class($key) ?> px-2 py-1"><?= $value ?></span>
              </label>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-12">
          <textarea name="next_action_notes"
                    id="next_action_notes"
                    class="form-control"
                    rows="2"
                    placeholder="<?= $lang['placeholder_what_promised'] ?? 'What you promised...'; ?>"></textarea>
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-md-6">
          <label for="next_action_date"
                 class="form-label fw-bold">Next Action Date</label>
          <input type="date"
                 name="next_action_date"
                 id="next_action_date"
                 class="form-control">
        </div>
        <div class="col-md-6">
          <label for="next_action_time"
                 class="form-label fw-bold"><?= $lang['label_time_optional'] ?? 'Time'; ?> <small class="text-muted"><?= $lang['label_time_note'] ?? '(optional - anytime during working day if not specified)'; ?></small></label>
          <input type="time"
                 name="next_action_time"
                 id="next_action_time"
                 class="form-control"
                 placeholder="<?= $lang['placeholder_optional'] ?? 'Optional'; ?>">
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-12">
          <small class="text-muted">
            <i class="fa-solid fa-clock me-1"></i>
            Client timezone: <span id="client-timezone"></span> |
            Your timezone: <span id="user-timezone"></span> |
            <span id="time-conversion"
                  class="fw-bold"></span>
          </small>
        </div>
      </div>
    </div>
  </div>

  <!-- Notes Drawer (Offcanvas) -->
  <div class="offcanvas offcanvas-end"
       tabindex="-1"
       id="notesDrawer"
       style="width: 700px;">
    <div class="offcanvas-header bg-dark text-white">
      <h5 class="offcanvas-title">
        <i class="fa-solid fa-sticky-note me-2"></i>Activity Timeline - Lead
        #<?= htmlspecialchars($lead_id ?? '') ?>
      </h5>
      <button type="button"
              class="btn-close btn-close-white"
              data-bs-dismiss="offcanvas"></button>
    </div>

    <!-- Search and Filter Controls -->
    <div class="bg-light border-bottom p-3">
      <div class="row g-2">
        <div class="col-md-7">
          <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
            <input type="text"
                   class="form-control"
                   id="notesSearch"
                   placeholder="<?= $lang['placeholder_search_notes'] ?? 'Search notes...'; ?>"
                   value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-outline-secondary"
                    type="button"
                    id="clearSearch">
              <i class="fa-solid fa-times"></i>
            </button>
          </div>
        </div>
        <div class="col-md-5">
          <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="fa-solid fa-sort"></i></span>
            <select class="form-select"
                    id="notesOrder">
              <option value="DESC"
                      <?= $order === 'DESC' ? 'selected' : '' ?>>Newest First</option>
              <option value="ASC"
                      <?= $order === 'ASC' ? 'selected' : '' ?>>Oldest First</option>
            </select>
          </div>
        </div>
      </div>
      <div class="mt-2">
        <small class="text-muted">
          Showing <span id="current-count"><?= $notes_count ?></span> of <span
                id="drawer-total"><?= $total_notes ?></span> notes
        </small>
      </div>
    </div>
    <div class="offcanvas-body p-0">
      <!-- Loading indicator -->
      <div id="notesLoading"
           class="text-center p-4"
           style="display: none;">
        <i class="fa-solid fa-spinner fa-spin fa-2x text-muted"></i>
        <p class="text-muted mt-2">Loading notes...</p>
      </div>

      <!-- Notes container -->
      <div id="notesContainer">
        <?php if (!empty($existing_notes)): ?>
        <div class="timeline p-4">
          <?php foreach ($existing_notes as $note): ?>
          <div class="timeline-item mb-4">
            <div class="timeline-marker">
              <div
                   class="timeline-marker-icon bg-<?= $note['source'] <= 3 ? 'primary' : ($note['source'] <= 6 ? 'success' : 'secondary') ?>">
                <i class="fa-solid fa-<?= 
                  $note['source'] == 2 ? 'phone' : (
                  $note['source'] == 3 ? 'envelope' : (
                  $note['source'] == 4 ? 'users' : (
                  $note['source'] == 5 ? 'map-marker-alt' : (
                  $note['source'] == 7 ? 'sms' : 'sticky-note'))))
                ?>"></i>
              </div>
              <div class="timeline-line"></div>
            </div>
            <div class="timeline-content">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                  <span class="<?= $notes_class->get_source_badge_class($note['source']) ?> me-2">
                    <?= $notes_class->get_note_source_array()[$note['source']] ?? 'Unknown' ?>
                  </span>
                  <?php if (!empty($note['form_source']) && $note['form_source'] != 'leads'): ?>
                  <small class="badge bg-light text-dark">from <?= ucfirst($note['form_source']) ?></small>
                  <?php endif; ?>
                </div>
                <small class="text-muted">
                  <?= date('M d, Y g:i A', strtotime($note['date_created'])) ?>
                </small>
              </div>
              <div class="note-text mb-2">
                <?= nl2br(htmlspecialchars($note['note_text'])) ?>
              </div>
              <small class="text-muted">
                <i class="fa-solid fa-user fa-sm me-1"></i>
                <?= htmlspecialchars($note['full_name'] ?? $note['username'] ?? 'System') ?>
              </small>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center p-5">
          <i class="fa-solid fa-sticky-note fa-4x text-muted opacity-25 mb-3"></i>
          <h6 class="text-muted">No Activity Yet</h6>
          <p class="text-muted">Notes and activity will appear here as they are added to this lead.</p>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <style>
  .timeline {
    position: relative;
  }

  .timeline-item {
    position: relative;
    display: flex;
  }

  .timeline-marker {
    flex-shrink: 0;
    width: 40px;
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .timeline-marker-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
  }

  .timeline-line {
    width: 2px;
    flex-grow: 1;
    background: #dee2e6;
    margin-top: 8px;
  }

  .timeline-item:last-child .timeline-line {
    display: none;
  }

  .timeline-content {
    flex-grow: 1;
    margin-left: 16px;
    padding-bottom: 8px;
  }

  .note-text {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 8px;
    border-left: 3px solid #dee2e6;
  }
  </style>

  <!-- Stage -->
  <div class="row">
    <div class="col-6">
      <div class="form-group pb-2">
        <label for="stage"
               class="pb-1"><?= $lang['lead_stage'] ?? 'Stage'; ?></label>
        <select name="stage"
                id="stage"
                class="form-select"
                autocomplete="off"
                onchange="handleStageChange(this.value)">
          <?php
          $stage_options = $leads->get_lead_stage_array();
          $selected_stage = null;
          if (isset($stage)) {
            if (is_numeric($stage)) {
              $selected_stage = (int)$stage;
            } else {
              $selected_stage = $leads->convert_text_stage_to_number((string)$stage);
            }
          }
          
          // Get valid next stages for current stage
          $valid_next_stages = [];
          if ($selected_stage) {
            $valid_next_stages = $leads->get_valid_next_stages($selected_stage);
            // Always include current stage
            $valid_next_stages[] = $selected_stage;
            $valid_next_stages = array_unique($valid_next_stages);
          } else {
            // If no current stage, show all stages
            $valid_next_stages = array_keys($stage_options);
          }
          
          foreach ($stage_options as $key => $value) {
            // Only show valid next stages or current stage
            if (in_array($key, $valid_next_stages)) {
              $sel = ($selected_stage == (int)$key) ? ' selected' : '';
              $badge_class = $leads->get_stage_badge_class($key);
              $badge_color = '';
              if (strpos($badge_class, 'bg-primary') !== false) $badge_color = '🔵';
              elseif (strpos($badge_class, 'bg-info') !== false) $badge_color = '🔵';
              elseif (strpos($badge_class, 'bg-warning') !== false) $badge_color = '🟡';
              elseif (strpos($badge_class, 'bg-success') !== false) $badge_color = '🟢';
              elseif (strpos($badge_class, 'bg-danger') !== false) $badge_color = '🔴';
              
              echo '<option value="' . $key . '"' . $sel . '>' . $badge_color . ' ' . $value . '</option>';
            }
          }
          ?>
        </select>
      </div>
    </div>
  </div>

  <!-- Hidden fields -->
  <input type="hidden"
         name="last_edited_by"
         value="<?= $_SESSION['user_id'] ?? null; ?>">
  <input type="hidden"
         name="dir"
         value="<?= $dir; ?>">
  <input type="hidden"
         name="page"
         value="<?= $page; ?>">
  <input type="hidden"
         name="id"
         value="<?= $id; ?>">
  <input type="hidden"
         name="current_stage"
         value="<?= htmlspecialchars($stage ?? '') ?>">
  <!-- Hidden field for selected contact ID (for notes) -->
  <input type="hidden" name="note_contact_id" id="note_contact_id" value="<?= htmlspecialchars($selected_contact_id ?? '') ?>">

  <p></p>
  <a href="list"
     class="btn btn-danger"
     role="button"
     aria-pressed="false"
     tabindex="0">
    <?= $lang['contracting_cancel'] ?? 'Cancel'; ?>
  </a>
  <button type="submit"
          class="btn btn-success"
          role="button"
          value="submit"
          tabindex="0">
    <?= $lang['contracting_update'] ?? 'Update Contract'; ?>
  </button>
</form>

<script>
// Notes search and ordering functionality
document.addEventListener('DOMContentLoaded', function() {


  // Display user's and client's timezone with conversion
  const userTimezoneElement = document.getElementById('user-timezone');
  const clientTimezoneElement = document.getElementById('client-timezone');
  const timeConversionElement = document.getElementById('time-conversion');

  if (userTimezoneElement && clientTimezoneElement && timeConversionElement) {
    try {
      const userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
      userTimezoneElement.textContent = userTimezone;

      // Get client timezone based on location
      const clientState = '<?= htmlspecialchars($form_state ?? '') ?>';
      const clientCountry = '<?= htmlspecialchars($form_country ?? '') ?>';
      const clientTimezone = getTimezoneFromLocation(clientState, clientCountry);
      clientTimezoneElement.textContent = clientTimezone;

      // Update hidden timezone field for form submission
      const timezoneField = document.getElementById('timezone');
      if (timezoneField) {
        timezoneField.value = clientTimezone;
      }

      // Show time conversion example
      updateTimeConversion(clientTimezone, userTimezone);

      // Update conversion when time field changes
      const timeField = document.getElementById('next_action_time');
      if (timeField) {
        timeField.addEventListener('change', function() {
          updateTimeConversion(clientTimezone, userTimezone, this.value);
        });
      }

    } catch (e) {
      userTimezoneElement.textContent = 'Unable to detect timezone';
      clientTimezoneElement.textContent = 'Unknown';
      timeConversionElement.textContent = '';
    }
  }

  // Function to estimate timezone from location
  function getTimezoneFromLocation(state, country) {
    // US state to timezone mapping (simplified)
    const usTimezones = {
      'CA': 'America/Los_Angeles',
      'WA': 'America/Los_Angeles',
      'OR': 'America/Los_Angeles',
      'NV': 'America/Los_Angeles',
      'AZ': 'America/Phoenix',
      'UT': 'America/Denver',
      'CO': 'America/Denver',
      'WY': 'America/Denver',
      'MT': 'America/Denver',
      'NM': 'America/Denver',
      'ND': 'America/Denver',
      'SD': 'America/Denver',
      'TX': 'America/Chicago',
      'OK': 'America/Chicago',
      'KS': 'America/Chicago',
      'NE': 'America/Chicago',
      'MN': 'America/Chicago',
      'IA': 'America/Chicago',
      'MO': 'America/Chicago',
      'AR': 'America/Chicago',
      'LA': 'America/Chicago',
      'MS': 'America/Chicago',
      'AL': 'America/Chicago',
      'TN': 'America/Chicago',
      'KY': 'America/Chicago',
      'IN': 'America/Chicago',
      'IL': 'America/Chicago',
      'WI': 'America/Chicago',
      'MI': 'America/Detroit',
      'OH': 'America/New_York',
      'WV': 'America/New_York',
      'VA': 'America/New_York',
      'PA': 'America/New_York',
      'NY': 'America/New_York',
      'VT': 'America/New_York',
      'NH': 'America/New_York',
      'ME': 'America/New_York',
      'MA': 'America/New_York',
      'RI': 'America/New_York',
      'CT': 'America/New_York',
      'NJ': 'America/New_York',
      'DE': 'America/New_York',
      'MD': 'America/New_York',
      'DC': 'America/New_York',
      'NC': 'America/New_York',
      'SC': 'America/New_York',
      'GA': 'America/New_York',
      'FL': 'America/New_York'
    };

    if (country === 'US' && usTimezones[state]) {
      return usTimezones[state];
    }

    // Default fallbacks for other countries
    const countryTimezones = {
      'CA': 'America/Toronto',
      'GB': 'Europe/London',
      'AU': 'Australia/Sydney',
      'DE': 'Europe/Berlin',
      'FR': 'Europe/Paris'
    };

    return countryTimezones[country] || 'UTC';
  }

  // Function to update time conversion display
  function updateTimeConversion(clientTz, userTz, selectedTime = null) {
    try {
      const now = new Date();
      let timeToConvert = now;

      if (selectedTime) {
        // Use selected time with today's date
        const [hours, minutes] = selectedTime.split(':');
        timeToConvert = new Date();
        timeToConvert.setHours(parseInt(hours), parseInt(minutes), 0, 0);
      } else {
        // Use current time as example
        timeToConvert = new Date();
        // Keep the current time instead of setting to 9:00 AM
      }

      if (clientTz !== userTz) {
        // Format time in client timezone
        const clientTime = timeToConvert.toLocaleTimeString('en-US', {
          timeZone: clientTz,
          hour: '2-digit',
          minute: '2-digit',
          hour12: true
        });

        // Format time in user timezone
        const userTime = timeToConvert.toLocaleTimeString('en-US', {
          timeZone: userTz,
          hour: '2-digit',
          minute: '2-digit',
          hour12: true
        });

        if (selectedTime) {
          const clientTime = timeToConvert.toLocaleTimeString('en-US', {
            timeZone: clientTz,
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
          });
          timeConversionElement.textContent = `${clientTime} client time = ${userTime} your time`;
        } else {
          timeConversionElement.textContent = `Current time: ${clientTime} client time = ${userTime} your time`;
        }
      } else {
        timeConversionElement.textContent = 'Same timezone';
      }
    } catch (e) {
      timeConversionElement.textContent = 'Unable to convert time';
    }
  }

  const notesSearch = document.getElementById('notesSearch');
  const notesOrder = document.getElementById('notesOrder');
  const clearSearch = document.getElementById('clearSearch');
  const notesContainer = document.getElementById('notesContainer');
  const notesLoading = document.getElementById('notesLoading');
  const currentCount = document.getElementById('current-count');
  const drawerTotal = document.getElementById('drawer-total');
  const notesCount = document.getElementById('notes-count-header');
  const totalNotes = document.getElementById('total-notes-header');

  const leadId = <?= $internal_id ?? 0 ?>;
  let searchTimeout;

  // Search functionality
  if (notesSearch) {
    notesSearch.addEventListener('input', function() {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        loadNotes();
      }, 500); // Debounce search
    });
  }

  // Order change
  if (notesOrder) {
    notesOrder.addEventListener('change', function() {
      loadNotes();
    });
  }

  // Clear search
  if (clearSearch) {
    clearSearch.addEventListener('click', function() {
      notesSearch.value = '';
      loadNotes();
    });
  }

  // Load notes initially
  loadNotes();

  // Load notes via AJAX
  function loadNotes() {
    if (leadId <= 0) return;

    const search = notesSearch ? notesSearch.value : '';
    const order = notesOrder ? notesOrder.value : 'DESC';

    // Show loading
    if (notesLoading) notesLoading.style.display = 'block';
    if (notesContainer) notesContainer.style.opacity = '0.5';

    fetch('notes_ajax.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          action: 'get_notes',
          lead_id: leadId,
          search: search,
          order: order
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          renderNotes(data.notes);
          updateCounts(data.notes.length, data.total_count || data.notes.length);
        } else {
          console.error('Error loading notes:', data.error);
          showError('Failed to load notes: ' + (data.error || 'Unknown error'));
        }
      })
      .catch(error => {
        console.error('Network error:', error);
        showError('Network error while loading notes');
      })
      .finally(() => {
        // Hide loading
        if (notesLoading) notesLoading.style.display = 'none';
        if (notesContainer) notesContainer.style.opacity = '1';
      });
  }

  // Render notes HTML
  function renderNotes(notes) {
    if (!notesContainer) return;

    if (notes.length === 0) {
      notesContainer.innerHTML = `
                <div class="text-center p-5">
                    <i class="fa-solid fa-sticky-note fa-4x text-muted opacity-25 mb-3"></i>
                    <h6 class="text-muted">No Notes Found</h6>
                    <p class="text-muted">No notes match your search criteria.</p>
                </div>
            `;
      return;
    }

    let html = '<div class="timeline p-4">';

    notes.forEach(note => {
      const sourceColor = note.source <= 3 ? 'primary' : (note.source <= 6 ? 'success' : 'secondary');
      const sourceIcon = getSourceIcon(note.source);

      html += `
                <div class="timeline-item mb-4">
                    <div class="timeline-marker">
                        <div class="timeline-marker-icon bg-${sourceColor}">
                            <i class="fa-solid fa-${sourceIcon} text-white"></i>
                        </div>
                    </div>
                    <div class="timeline-content">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="${note.source_badge} me-2">${note.source_name}</span>
                                ${note.contact_name ? `<small class="badge bg-info text-white me-2"><i class="fa-solid fa-user me-1"></i>${escapeHtml(note.contact_name)}</small>` : ''}
                                ${note.form_source && note.form_source !== 'leads' ? `<small class="badge bg-light text-dark">from ${note.form_source}</small>` : ''}
                            </div>
                            <small class="text-muted">${note.date_formatted}</small>
                        </div>
                        <div class="note-content">
                            <p class="mb-1">${escapeHtml(note.note_text).replace(/\n/g, '<br>')}</p>
                            <small class="text-muted">
                                <i class="fa-solid fa-user me-1"></i>${escapeHtml(note.user_name)}
                            </small>
                        </div>
                    </div>
                </div>
            `;
    });

    html += '</div>';
    notesContainer.innerHTML = html;
  }

  // Update count displays
  function updateCounts(current, total) {
    if (currentCount) currentCount.textContent = current;
    if (drawerTotal) drawerTotal.textContent = total;
    if (notesCount) notesCount.textContent = current;
  }

  // Get icon for note source
  function getSourceIcon(source) {
    const icons = {
      1: 'phone', // Phone Call
      2: 'envelope', // Email  
      3: 'comment-sms', // Text Message
      4: 'sticky-note', // Internal Note
      5: 'handshake', // Meeting
      6: 'map-marker-alt', // Site Visit
      7: 'clock' // Follow-up
    };
    return icons[source] || 'sticky-note';
  }

  // Show error message
  function showError(message) {
    if (notesContainer) {
      notesContainer.innerHTML = `
                <div class="alert alert-danger m-4">
                    <i class="fa-solid fa-exclamation-triangle me-2"></i>${escapeHtml(message)}
                </div>
            `;
    }
  }

  // Escape HTML
  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  // Handle collapse chevron rotation
  // Handle structure information collapse
  const structureCollapse = document.getElementById('structureInformationCollapse');
  const structureIcon = structureCollapse?.previousElementSibling?.querySelector('.collapse-icon');

  if (structureCollapse && structureIcon) {
    // Set initial state (collapsed = rotated)
    structureIcon.style.transform = 'rotate(180deg)';

    structureCollapse.addEventListener('show.bs.collapse', function() {
      structureIcon.style.transform = 'rotate(0deg)';
    });

    structureCollapse.addEventListener('hide.bs.collapse', function() {
      structureIcon.style.transform = 'rotate(180deg)';
    });
  }

  // Handle file upload links collapse
  const uploadCollapse = document.getElementById('fileUploadLinksCollapse');
  const uploadIcon = uploadCollapse?.previousElementSibling?.querySelector('.collapse-icon');

  if (uploadCollapse && uploadIcon) {
    // Set initial state (collapsed = rotated)
    uploadIcon.style.transform = 'rotate(180deg)';

    uploadCollapse.addEventListener('show.bs.collapse', function() {
      uploadIcon.style.transform = 'rotate(0deg)';
    });

    uploadCollapse.addEventListener('hide.bs.collapse', function() {
      uploadIcon.style.transform = 'rotate(180deg)';
    });
  }

  // Handle screening estimates collapse
  const screeningCollapse = document.getElementById('screeningEstimatesCollapse');
  const screeningIcon = screeningCollapse?.previousElementSibling?.querySelector('.collapse-icon');

  if (screeningCollapse && screeningIcon) {
    screeningCollapse.addEventListener('show.bs.collapse', function() {
      screeningIcon.classList.remove('fa-chevron-down');
      screeningIcon.classList.add('fa-chevron-up');
    });

    screeningCollapse.addEventListener('hide.bs.collapse', function() {
      screeningIcon.classList.remove('fa-chevron-up');
      screeningIcon.classList.add('fa-chevron-down');
    });
  }

  // Auto-expand screening estimates when user enters data
  const screeningInputs = [
    'eng_system_cost_low', 'eng_system_cost_high', 'eng_protected_area'
  ];

  screeningInputs.forEach(inputId => {
    const input = document.getElementById(inputId);
    if (input) {
      input.addEventListener('focus', function() {
        if (screeningCollapse && !screeningCollapse.classList.contains('show')) {
          const bsCollapse = new bootstrap.Collapse(screeningCollapse, {
            show: true
          });
        }
      });
    }
  });

  // Format currency inputs with commas
  const currencyInputs = [
    'eng_system_cost_low', 'eng_system_cost_high'
  ];

  currencyInputs.forEach(inputId => {
    const input = document.getElementById(inputId);
    if (input) {
      input.addEventListener('input', function(e) {
        // Remove non-numeric characters except for existing commas
        let value = e.target.value.replace(/[^\d]/g, '');
        
        // Add commas for thousands
        if (value) {
          value = parseInt(value).toLocaleString();
        }
        
        e.target.value = value;
      });

      // Remove commas before form submission
      input.addEventListener('blur', function(e) {
        e.target.value = e.target.value.replace(/,/g, '');
      });
    }
  });

});

// Stage change handler
function handleStageChange(newStage) {
  const currentStage = <?= $selected_stage ?? 1 ?>;
  const stageNames = <?= json_encode($leads->get_lead_stage_array()) ?>;
  const leadId = '<?= htmlspecialchars($lead_id ?? '') ?>';
  
  if (newStage != currentStage) {
    let message = '';
    let redirectUrl = '';
    
    // Determine where the record will move based on new stage
    switch (parseInt(newStage)) {
      case 4: // Referral
        message = `This lead will be moved to the Referrals list when you save.`;
        redirectUrl = '/referrals/list';
        break;
      case 5: case 6: case 7: case 8: case 9: case 10: case 11: case 12: case 13: // Prospect stages
        message = `This lead will be moved to the Prospects list when you save.`;
        redirectUrl = '/prospects/list';
        break;
      case 13: // Contracting
        message = `This lead will be moved to the Contracting list when you save.`;
        redirectUrl = '/contracting/list';
        break;
      case 14: // Closed Won
        message = `This lead will be marked as Closed Won and moved to the appropriate list.`;
        break;
      case 15: // Closed Lost
        message = `This lead will be marked as Closed Lost.`;
        break;
    }
    
    if (message) {
      // Show confirmation dialog
      if (confirm(`Stage Change: ${stageNames[currentStage]} → ${stageNames[newStage]}\n\n${message}\n\nDo you want to continue?`)) {
        // User confirmed, let the form submission handle the stage change
        return true;
      } else {
        // User cancelled, revert the dropdown
        document.getElementById('stage').value = currentStage;
        return false;
      }
    }
  }
  return true;
}
</script>

<!-- Contact Selector JavaScript -->
<script src="/assets/js/contact-selector.js"></script>

<?php
require SECTIONCLOSE;
require FOOTER;
