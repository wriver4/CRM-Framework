<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
// Direct routing variables - these determine page navigation and template inclusion
$dir = 'leads';
$page = 'edit';

$table_page = false;
$button_new = true;

require LANG . '/en.php';
$title = $lang['lead_edit'];
$new_button = $lang['contact_new'];
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
          <i class="fa-solid fa-hashtag fa-2x text-primary me-3"></i>
          <span class="fs-4 fw-bold text-dark"><?= htmlspecialchars($lead_id ?? 'N/A') ?></span>
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
        <i class="fa-solid fa-user me-2"></i><?= $lang['lead_contact_information']; ?>
      </h5>
      <div class="d-flex align-items-center gap-3">
        <?php if (isset($property_contacts) && count($property_contacts) > 1): ?>
        <div class="d-flex align-items-center">
          <label for="contact_selector"
                 class="form-label text-white me-2 mb-0">
            <i class="fa-solid fa-users me-1"></i>Contact:
          </label>
          <select name="contact_selector"
                  id="contact_selector"
                  class="form-select form-select-sm contact-selector">
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
          <i class="fa-solid fa-address-book"></i>&ensp;<?= $lang['contact_new']; ?>
        </a>
      </div>
    </div>
    <div class="card-body">

      <!-- Full Name, Cell Phone & Email -->
      <div class="row mb-3">
        <div class="col">
          <div class="form-group">
            <label class="form-label fw-bold text-muted"><?= $lang['lead_full_name']; ?></label>
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
            <label class="form-label fw-bold text-muted"><?= $lang['lead_cell_phone']; ?></label>
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
            <label class="form-label fw-bold text-muted"><?= $lang['lead_email']; ?></label>
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
            <label class="form-label fw-bold text-muted"><?= $lang['lead_contact_type']; ?></label>
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
            <label class="form-label fw-bold text-muted"><?= $lang['lead_business_name']; ?></label>
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
                   class="form-label fw-bold text-muted"><?= $lang['lead_project_name']; ?></label>
            <div class="input-group">
              <span class="input-group-text"><i class="fa-solid fa-project-diagram text-primary"></i></span>
              <input type="text"
                     name="project_name"
                     maxlength="255"
                     id="project_name"
                     class="form-control"
                     value="<?= htmlspecialchars($project_name ?? '') ?>"
                     autocomplete="off"
                     placeholder="<?= $lang['placeholder_project_name']; ?>">
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
        <i class="fa-solid fa-map-marker-alt me-2"></i><?= $lang['address']; ?>
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
            <em class="text-muted"><?= $lang['message_no_address']; ?></em>
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
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center collapsible-header"
         data-bs-toggle="collapse"
         data-bs-target="#servicesInterestedCollapse"
         aria-expanded="false"
         aria-controls="servicesInterestedCollapse">
      <h5 class="mb-0 d-flex align-items-center">
        <i class="fa-solid fa-cogs me-2"></i><?= $lang['lead_services_interested_in']; ?>
      </h5>
      <i class="fa-solid fa-chevron-down collapse-icon"></i>
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
              <em><?= $lang['message_no_services']; ?></em>
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
    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center collapsible-header"
         data-bs-toggle="collapse"
         data-bs-target="#structureInformationCollapse"
         aria-expanded="false"
         aria-controls="structureInformationCollapse">
      <h5 class="mb-0 d-flex align-items-center">
        <i class="fa-solid fa-building me-2"></i><?= $lang['lead_structure_information']; ?>
      </h5>
      <i class="fa-solid fa-chevron-up collapse-icon"></i>
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
            <strong class="text-muted"><?= $lang['lead_structure_type'] ?>:</strong>
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
          <label class="form-label fw-bold text-muted"><?= $lang['lead_structure_description'] ?></label>
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
          <label class="form-label fw-bold text-muted"><?= $lang['lead_structure_other'] ?></label>
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
          <label class="form-label fw-bold text-muted"><?= $lang['lead_structure_additional'] ?></label>
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
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center collapsible-header">
      <h5 class="mb-0">
        <button class="btn p-0 text-start text-white border-0 bg-transparent d-flex align-items-center w-100 radio-no-shadow"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#fileUploadLinksCollapse"
                aria-expanded="false"
                aria-controls="fileUploadLinksCollapse">
          <i class="fa-solid fa-upload me-2"></i><?= $lang['lead_file_upload_links']; ?>
        </button>
      </h5>
      <i class="fa-solid fa-chevron-up collapse-icon"></i>
    </div>
    <div class="collapse"
         id="fileUploadLinksCollapse">
      <div class="card-body">
        <!-- Plans Upload Link -->
        <div class="row mb-3">
          <div class="col-12">
            <div class="form-group">
              <label class="form-label fw-bold text-muted"><?= $lang['lead_plans_upload_link']; ?></label>
              <div class="bg-light p-3 rounded border">
                <div class="d-flex align-items-center">
                  <i class="fa-solid fa-file-alt text-info me-3 fa-lg"></i>
                  <div class="upload-link-content">
                    <?php if (!empty($plans_upload_link)): ?>
                    <span class="text-break"><?= htmlspecialchars($plans_upload_link) ?></span>
                    <?php else: ?>
                    <em class="text-muted"><?= $lang['message_no_plans_link']; ?></em>
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
              <label class="form-label fw-bold text-muted"><?= $lang['lead_pictures_upload_link']; ?></label>
              <div class="bg-light p-3 rounded border">
                <div class="d-flex align-items-center">
                  <i class="fa-solid fa-image text-primary me-3 fa-lg"></i>
                  <div class="upload-link-content">
                    <?php if (!empty($picture_upload_link)): ?>
                    <span class="text-break"><?= htmlspecialchars($picture_upload_link) ?></span>
                    <?php else: ?>
                    <em class="text-muted"><?= $lang['message_no_pictures_link']; ?></em>
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

  <!-- Screening Estimate -->
  <?php
  // Check if user has permission to edit engineering or sales estimates
  $user_role = $_SESSION['user_role'] ?? 'user';
  $user_rid = $_SESSION['user_rid'] ?? 0;
  
  // Define role permissions using Helper class methods
  $can_edit_engineering = $helpers->can_edit_engineering($user_rid);
  $can_edit_sales = $helpers->can_edit_sales($user_rid);
  $can_edit_admin_leads = $helpers->can_edit_admin_leads($user_rid);
  
  // Check if any engineering data exists to determine default collapse state
  $has_engineering_data = !empty($eng_system_cost_low) || !empty($eng_system_cost_high) || !empty($eng_protected_area);
  $default_expanded = $has_engineering_data ? 'show' : '';
  $chevron_direction = $has_engineering_data ? 'up' : 'down';
  ?>

  <div class="card mb-4">
    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center collapsible-header"
         data-bs-toggle="collapse"
         data-bs-target="#screeningEstimatesCollapse"
         aria-expanded="<?= $has_engineering_data ? 'true' : 'false' ?>"
         aria-controls="screeningEstimatesCollapse">
      <h5 class="mb-0 d-flex align-items-center">
        <i class="fa-solid fa-calculator me-2"></i><?= $lang['screening_estimates']; ?>
      </h5>
      <i class="fa-solid fa-chevron-<?= $chevron_direction ?> collapse-icon"></i>
    </div>
    <div class="collapse <?= $default_expanded ?>"
         id="screeningEstimatesCollapse">
      <div class="card-body">

        <!-- Engineering Estimate Row -->
        <div class="row mb-4">
          <div class="col-12">
            <h6 class="text-muted mb-3">
              <i class="fa-solid fa-cogs me-2"></i><?= $lang['engineering_estimates']; ?>
            </h6>
            <div class="row">
              <!-- Engineering System Cost -->
              <div class="col-md-8">
                <div class="form-group">
                  <label class="form-label fw-bold text-muted"><?= $lang['system_cost']; ?></label>
                  <div class="row">
                    <div class="col-6">
                      <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number"
                               name="eng_system_cost_low"
                               id="eng_system_cost_low"
                               class="form-control"
                               value="<?= htmlspecialchars($eng_system_cost_low ?? '') ?>"
                               placeholder="<?= $lang['system_cost_low']; ?>"
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
                               placeholder="<?= $lang['system_cost_high']; ?>"
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
                         class="form-label fw-bold text-muted"><?= $lang['protected_area']; ?></label>
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
                    <span class="input-group-text"><?= $lang['protected_area_sqft']; ?></span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Additional Fields Row -->
            <div class="row mt-3">
              <!-- Cabinets -->
              <div class="col-md-6">
                <div class="form-group">
                  <label for="eng_cabinets"
                         class="form-label fw-bold text-muted"><?= $lang['cabinets']; ?></label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-archive text-secondary"></i></span>
                    <input type="number"
                           name="eng_cabinets"
                           id="eng_cabinets"
                           class="form-control"
                           value="<?= htmlspecialchars($eng_cabinets ?? '') ?>"
                           placeholder="0"
                           min="0"
                           step="1"
                           <?= !$can_edit_engineering && !$can_edit_admin_leads ? 'readonly' : '' ?>>
                  </div>
                </div>
              </div>
              <!-- Total Pumps -->
              <div class="col-md-6">
                <div class="form-group">
                  <label for="eng_total_pumps"
                         class="form-label fw-bold text-muted"><?= $lang['total_pumps']; ?></label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-cog text-primary"></i></span>
                    <input type="number"
                           name="eng_total_pumps"
                           id="eng_total_pumps"
                           class="form-control"
                           value="<?= htmlspecialchars($eng_total_pumps ?? '') ?>"
                           placeholder="0"
                           min="0"
                           step="1"
                           <?= !$can_edit_engineering && !$can_edit_admin_leads ? 'readonly' : '' ?>>
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
          <label class="form-label fw-bold"><?= $lang['label_current_action']; ?></label>
          <div>
            <?php 
            // 🌍 Get internationalized note sources from Helper class
            $note_sources = $helpers->get_note_sources_for_actions($lang);
            foreach ($note_sources as $key => $value): ?>
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
                    placeholder="<?= $lang['placeholder_questions_asked']; ?>"></textarea>
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-12">
          <label class="form-label fw-bold"><?= $lang['label_next_action']; ?></label>
          <div>
            <?php 
            // 🌍 Get internationalized note sources from Helper class (matching Current Action)
            $next_action_sources = $helpers->get_note_sources_for_actions($lang);
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
                    placeholder="<?= $lang['placeholder_what_promised']; ?>"></textarea>
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-md-4">
          <label for="next_action_date"
                 class="form-label fw-bold"><?= $lang['label_next_action_date']; ?></label>
          <input type="date"
                 name="next_action_date"
                 id="next_action_date"
                 class="form-control">
        </div>
        <div class="col-md-4">
          <label for="next_action_time"
                 class="form-label fw-bold"><?= $lang['label_time_optional']; ?> <small
                   class="text-muted"><?= $lang['label_time_note']; ?></small></label>
          <input type="time"
                 name="next_action_time"
                 id="next_action_time"
                 class="form-control"
                 placeholder="<?= $lang['placeholder_optional']; ?>">
        </div>
        <div class="col-md-4">
          <label for="next_action_priority"
                 class="form-label fw-bold"><?= $lang['label_priority_level']; ?></label>
          <?= $helpers->select_next_action_priority($lang, $next_action_priority ?? 5, 'next_action_priority', 'form-select'); ?>
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-12">
          <small class="text-muted">
            <i class="fa-solid fa-clock me-1"></i>
            <?= $lang['label_client_timezone']; ?>: <span id="client-timezone"></span> |
            <?= $lang['label_your_timezone']; ?>: <span id="user-timezone"></span> |
            <span id="time-conversion"
                  class="fw-bold"></span>
          </small>
        </div>
      </div>
      <div class="row">
        <div class="col-12">
          <div class="alert alert-info alert-sm py-2 mt-2">
            <i class="fa-solid fa-calendar-plus me-2"></i>
            <small><strong>Auto Calendar Integration:</strong> Next Actions with dates will automatically create
              calendar events.
              All-day events are created when no time is specified.</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Notes Drawer (Offcanvas) -->
  <div class="offcanvas offcanvas-end"
       tabindex="-1"
       id="notesDrawer"
       class="lead-modal-content">
    <div class="offcanvas-header bg-dark text-white">
      <h5 class="offcanvas-title">
        <i class="fa-solid fa-sticky-note me-2"></i><?= $lang['activity_timeline_lead']; ?>
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
                   placeholder="<?= $lang['placeholder_search_notes']; ?>"
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
           class="text-center p-4 lead-hidden">
        <i class="fa-solid fa-spinner fa-spin fa-2x text-muted"></i>
        <p class="text-muted mt-2"><?= $lang['text_loading_notes']; ?></p>
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
                    <?= $notes_class->get_note_source_array()[$note['source']] ?? $lang['text_unknown'] ?>
                  </span>
                  <?php if (!empty($note['form_source']) && $note['form_source'] != 'leads'): ?>
                  <small class="badge bg-light text-dark"><?= $lang['text_from']; ?>
                    <?= ucfirst($note['form_source']) ?></small>
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

  <!-- Stage -->
  <div class="row">
    <div class="col-6">
      <div class="form-group pb-2">
        <label for="stage"
               class="pb-1"><?= $lang['lead_stage']; ?></label>
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
  <input type="hidden"
         name="note_contact_id"
         id="note_contact_id"
         value="<?= htmlspecialchars($selected_contact_id ?? '') ?>">

  <p></p>
  <a href="list"
     class="btn btn-danger"
     role="button"
     aria-pressed="false"
     tabindex="0">
    <?= $lang['lead_cancel']; ?>
  </a>
  <button type="submit"
          class="btn btn-success"
          role="button"
          value="submit"
          tabindex="0">
    <?= $lang['lead_update']; ?>
  </button>
</form>



<?php
require SECTIONCLOSE;
require FOOTER;