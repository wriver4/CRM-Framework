<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$dir = "admin/leads";
$page = "edit";

$table_page = false;

require LANG . '/en.php';

$title = 'Admin Edit Lead';
$title_icon = '<i class="fa-solid fa-user-shield"></i>';

require 'get.php';

require HEADER;
require BODY;
require NAV;
require SECTIONOPEN;
?>

<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <?= $_SESSION['success_message'] ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['success_message']); endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <?= $_SESSION['error_message'] ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['error_message']); endif; ?>

<div class="card mb-4">
  <div class="card-header bg-warning text-dark">
    <h5 class="mb-0">
      <i class="fa-solid fa-exclamation-triangle me-2"></i>Admin Edit Mode - All Fields Editable
    </h5>
  </div>
  <div class="card-body">
    <p class="mb-0">You are editing lead <strong>#<?= htmlspecialchars($lead_number ?? 'N/A') ?></strong> in admin mode. All fields can be modified.</p>
  </div>
</div>

<form action="post.php" method="POST" autocomplete="off">
  <input type="hidden" name="id" value="<?= htmlspecialchars($_GET['id'] ?? '') ?>">
  
  <!-- Lead Source & Lead Number -->
  <div class="row">
    <div class="col-6">
      <div class="form-group pb-2">
        <label for="lead_source" class="required pb-1"><?= $lang['lead_source'] ?? 'Lead Source'; ?></label>
        <select name="lead_source" id="lead_source" class="form-select" required autocomplete="off">
          <?php
          $lead_sources = $helpers->get_lead_source_array($lang);
          foreach ($lead_sources as $key => $value) {
            $selected = ($key == ($lead_source ?? '1')) ? ' selected' : '';
            echo '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
          }
          ?>
        </select>
      </div>
    </div>
    <div class="col-6">
      <div class="form-group pb-2">
        <label for="lead_number" class="required pb-1"><?= $lang['lead_number']; ?></label>
        <input type="text" name="lead_number" id="lead_number" class="form-control" 
               value="<?= htmlspecialchars($lead_number ?? '') ?>" autocomplete="off" required>
      </div>
    </div>
  </div>

  <h4><?= $lang['lead_contact_information']; ?></h4>
  <!-- First Name & Last Name -->
  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="first_name" class="required pb-1"><?= $lang['lead_first_name']; ?></label>
        <input type="text" name="first_name" maxlength="100" id="first_name" class="form-control" 
               value="<?= htmlspecialchars($first_name ?? '') ?>" required autocomplete="off">
      </div>
    </div>
    <div class="col">
      <div class="form-group pb-2">
        <label for="last_name" class="required pb-1"><?= $lang['lead_last_name']; ?></label>
        <input type="text" name="last_name" maxlength="100" id="last_name" class="form-control" 
               value="<?= htmlspecialchars($last_name ?? '') ?>" required autocomplete="off">
      </div>
    </div>
  </div>

  <!-- Full Name -->
  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="full_name" class="pb-1"><?= $lang['lead_full_name'] ?? 'Full Name'; ?></label>
        <input type="text" name="full_name" maxlength="200" id="full_name" class="form-control" 
               value="<?= htmlspecialchars($full_name ?? '') ?>" autocomplete="off">
      </div>
    </div>
  </div>

  <!-- Cell Phone & Email -->
  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="cell_phone" class="pb-1"><?= $lang['lead_cell_phone']; ?></label>
        <input type="tel" name="cell_phone" maxlength="15" id="cell_phone" class="form-control" 
               value="<?= htmlspecialchars($cell_phone ?? '') ?>" autocomplete="off">
      </div>
    </div>
    <div class="col">
      <div class="form-group pb-2">
        <label for="email" class="required pb-1"><?= $lang['lead_email']; ?></label>
        <input type="email" name="email" maxlength="255" id="email" class="form-control" 
               value="<?= htmlspecialchars($email ?? '') ?>" required autocomplete="off">
      </div>
    </div>
  </div>

  <!-- Contact Type & Business Name -->
  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="ctype" class="pb-1"><?= $lang['lead_contact_type']; ?></label>
        <select name="ctype" id="ctype" class="form-select" autocomplete="off">
          <option value=""><?= $lang['lead_select_contact_type']; ?></option>
          <?php
          $contact_types = $helpers->get_lead_contact_type_array($lang);
          foreach ($contact_types as $key => $value) {
            $selected = ($key == ($ctype ?? '1')) ? ' selected' : '';
            echo '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
          }
          ?>
        </select>
      </div>
    </div>
    <div class="col">
      <div class="form-group pb-2">
        <label for="business_name" class="pb-1"><?= $lang['lead_business_name'] ?? 'Business Name'; ?></label>
        <input type="text" name="business_name" maxlength="255" id="business_name" class="form-control" 
               value="<?= htmlspecialchars($business_name ?? '') ?>" autocomplete="off">
      </div>
    </div>
  </div>

  <!-- Project Name -->
  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="project_name" class="pb-1"><?= $lang['lead_project_name'] ?? 'Project Name'; ?></label>
        <input type="text" name="project_name" maxlength="255" id="project_name" class="form-control" 
               value="<?= htmlspecialchars($project_name ?? '') ?>" autocomplete="off">
      </div>
    </div>
  </div>

  <!-- Address Fields -->
  <h4><?= $lang['lead_property_address']; ?></h4>
  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="form_street_1" class="pb-1"><?= $lang['lead_street_address_1']; ?></label>
        <input type="text" name="form_street_1" maxlength="100" id="form_street_1" class="form-control" 
               value="<?= htmlspecialchars($form_street_1 ?? '') ?>" autocomplete="off">
      </div>
    </div>
    <div class="col">
      <div class="form-group pb-2">
        <label for="form_street_2" class="pb-1"><?= $lang['lead_street_address_2']; ?></label>
        <input type="text" name="form_street_2" maxlength="50" id="form_street_2" class="form-control" 
               value="<?= htmlspecialchars($form_street_2 ?? '') ?>" autocomplete="off">
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="form_city" class="pb-1"><?= $lang['lead_city']; ?></label>
        <input type="text" name="form_city" maxlength="50" id="form_city" class="form-control" 
               value="<?= htmlspecialchars($form_city ?? '') ?>" autocomplete="off">
      </div>
    </div>
    <div class="col">
      <div class="form-group pb-2">
        <label for="form_state" class="pb-1"><?= $lang['lead_state']; ?></label>
        <select name="form_state" id="form_state" class="form-select" autocomplete="off">
          <option value=""><?= $lang['select_state']; ?></option>
          <?php
          $states = [
            'US-AZ' => $lang['US-AZ'] ?? 'Arizona',
            'US-CA' => $lang['US-CA'] ?? 'California', 
            'US-CO' => $lang['US-CO'] ?? 'Colorado',
            'US-ID' => $lang['US-ID'] ?? 'Idaho',
            'US-MT' => $lang['US-MT'] ?? 'Montana',
            'US-NV' => $lang['US-NV'] ?? 'Nevada',
            'US-NM' => $lang['US-NM'] ?? 'New Mexico',
            'US-OR' => $lang['US-OR'] ?? 'Oregon',
            'US-TX' => $lang['US-TX'] ?? 'Texas',
            'US-UT' => $lang['US-UT'] ?? 'Utah',
            'US-WA' => $lang['US-WA'] ?? 'Washington',
            'US-WY' => $lang['US-WY'] ?? 'Wyoming',
            'US-VA' => $lang['US-VA'] ?? 'Virginia',
            'US-SC' => $lang['US-SC'] ?? 'South Carolina'
          ];
          foreach ($states as $key => $value) {
            $selected = ($key == ($form_state ?? '')) ? ' selected' : '';
            echo '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
          }
          ?>
        </select>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="form_postcode" class="pb-1"><?= $lang['lead_postal_code']; ?></label>
        <input type="text" name="form_postcode" maxlength="15" id="form_postcode" class="form-control" 
               value="<?= htmlspecialchars($form_postcode ?? '') ?>" autocomplete="off">
      </div>
    </div>
    <div class="col">
      <div class="form-group pb-2">
        <label for="form_country" class="pb-1"><?= $lang['lead_country']; ?></label>
        <select name="form_country" id="form_country" class="form-select" autocomplete="off">
          <option value=""><?= $lang['select_country']; ?></option>
          <?php
          $countries = [
            'US' => $lang['US'] ?? 'United States',
            'CA' => $lang['CA'] ?? 'Canada',
            'MX' => $lang['MX'] ?? 'Mexico',
            'UK' => $lang['UK'] ?? 'United Kingdom',
            'AU' => $lang['AU'] ?? 'Australia',
            'NZ' => $lang['NZ'] ?? 'New Zealand',
            'BR' => $lang['BR'] ?? 'Brazil'
          ];
          foreach ($countries as $key => $value) {
            $selected = ($key == ($form_country ?? 'US')) ? ' selected' : '';
            echo '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
          }
          ?>
        </select>
      </div>
    </div>
  </div>

  <!-- Full Address -->
  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="full_address" class="pb-1"><?= $lang['lead_full_address'] ?? 'Full Address'; ?></label>
        <textarea name="full_address" id="full_address" class="form-control" rows="3" 
                  autocomplete="off" placeholder="Complete address..."><?= htmlspecialchars($full_address ?? '') ?></textarea>
      </div>
    </div>
  </div>

  <!-- Hidden timezone field -->
  <input type="hidden" name="timezone" id="timezone" value="<?= htmlspecialchars($timezone ?? '') ?>">

  <!-- Services Interested In -->
  <h4><?= $lang['lead_services_interested_in']; ?></h4>
  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <?php
        $services = $helpers->get_lead_services_array($lang);
        $selected_services = !empty($services_interested_in) ? explode(',', $services_interested_in) : [];
        $service_ids = ['service_wildfire', 'service_assessment', 'service_gutter', 'service_vent', 'service_ltr', 'service_lease', 'service_landscape'];
        $i = 0;
        foreach ($services as $key => $value) {
          $checked = in_array($key, $selected_services) ? ' checked' : '';
          echo '<div class="form-check pb-2">';
          echo '<input class="form-check-input" type="checkbox" name="services_interested_in[]" value="' . $key . '" id="' . $service_ids[$i] . '"' . $checked . '>';
          echo '<label class="form-check-label" for="' . $service_ids[$i] . '">' . $value . '</label>';
          echo '</div>';
          $i++;
        }
        ?>
      </div>
    </div>
  </div>

  <!-- Structure Information -->
  <h4><?= $lang['lead_structure_information']; ?></h4>
  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="structure_type" class="pb-1"><?= $lang['lead_structure_type']; ?></label>
        <select name="structure_type" id="structure_type" class="form-select" autocomplete="off">
          <?php
          $structure_types = $helpers->get_lead_structure_type_array($lang);
          foreach ($structure_types as $key => $value) {
            $selected = ($key == ($structure_type ?? '1')) ? ' selected' : '';
            echo '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
          }
          ?>
        </select>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label class="pb-1"><?= $lang['lead_structure_description']; ?></label>
        <div class="d-flex flex-wrap">
          <?php
          $structures = $helpers->get_lead_structure_description_array($lang);
          $selected_structures = !empty($structure_description) ? explode(',', $structure_description) : [];
          $structure_ids = ['structure_rambler', 'structure_two', 'structure_three', 'structure_walkout', 'structure_modern', 'structure_other_desc'];
          $i = 0;
          foreach ($structures as $key => $value) {
            $checked = in_array($key, $selected_structures) ? ' checked' : '';
            echo '<div class="form-check me-3 pb-2">';
            echo '<input class="form-check-input" type="checkbox" name="structure_description[]" value="' . $key . '" id="' . $structure_ids[$i] . '"' . $checked . '>';
            echo '<label class="form-check-label" for="' . $structure_ids[$i] . '">' . $value . '</label>';
            echo '</div>';
            $i++;
          }
          ?>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="structure_other" class="pb-1"><?= $lang['lead_structure_other']; ?></label>
        <input type="text" name="structure_other" id="structure_other" class="form-control" 
               value="<?= htmlspecialchars($structure_other ?? '') ?>" autocomplete="off">
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="structure_additional" class="pb-1"><?= $lang['lead_structure_additional']; ?></label>
        <textarea name="structure_additional" id="structure_additional" class="form-control" rows="2" 
                  autocomplete="off"><?= htmlspecialchars($structure_additional ?? '') ?></textarea>
      </div>
    </div>
  </div>

  <!-- Pictures & Plans Submitted -->
  <h4><?= $lang['lead_pictures_submitted'] ?? 'Pictures & Plans'; ?></h4>
  <div class="row">
    <div class="col-6">
      <div class="form-group pb-2">
        <label class="pb-1"><?= $lang['lead_pictures_submitted']; ?></label>
        <input type="text" name="picture_submitted_1" id="picture_submitted_1" class="form-control mb-2" 
               value="<?= htmlspecialchars($picture_submitted_1 ?? '') ?>" autocomplete="off">
        <input type="text" name="picture_submitted_2" id="picture_submitted_2" class="form-control mb-2" 
               value="<?= htmlspecialchars($picture_submitted_2 ?? '') ?>" autocomplete="off">
        <input type="text" name="picture_submitted_3" id="picture_submitted_3" class="form-control mb-2" 
               value="<?= htmlspecialchars($picture_submitted_3 ?? '') ?>" autocomplete="off">
      </div>
    </div>
    <div class="col-6">
      <div class="form-group pb-2">
        <label class="pb-1"><?= $lang['lead_plans_submitted']; ?></label>
        <input type="text" name="plans_submitted_1" id="plans_submitted_1" class="form-control mb-2" 
               value="<?= htmlspecialchars($plans_submitted_1 ?? '') ?>" autocomplete="off">
        <input type="text" name="plans_submitted_2" id="plans_submitted_2" class="form-control mb-2" 
               value="<?= htmlspecialchars($plans_submitted_2 ?? '') ?>" autocomplete="off">
        <input type="text" name="plans_submitted_3" id="plans_submitted_3" class="form-control mb-2" 
               value="<?= htmlspecialchars($plans_submitted_3 ?? '') ?>" autocomplete="off">
      </div>
    </div>
  </div>

  <!-- Message/Notes Field -->
  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="notes" class="pb-1"><?= $lang['lead_message']; ?></label>
        <textarea name="notes" id="notes" class="form-control" rows="4" 
                  autocomplete="off"><?= htmlspecialchars($notes ?? '') ?></textarea>
      </div>
    </div>
  </div>

  <!-- Receive Updates Field -->
  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="get_updates" class="pb-1"><?= $lang['lead_get_updates']; ?></label>
        <select name="get_updates" id="get_updates" class="form-select" autocomplete="off">
          <option value="No" <?= ($get_updates ?? 'Yes') == 'No' ? 'selected' : '' ?>><?= $lang['lead_no']; ?></option>
          <option value="Yes" <?= ($get_updates ?? 'Yes') == 'Yes' ? 'selected' : '' ?>><?= $lang['lead_yes']; ?></option>
        </select>
      </div>
    </div>
  </div>

  <!-- How did you hear about us -->
  <h4><?= $lang['lead_how_did_you_hear']; ?></h4>
  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <?php
        $hear_about_options = $helpers->get_lead_hear_about_array($lang);
        $selected_hear_about = !empty($hear_about) ? explode(',', $hear_about) : [];
        $hear_ids = ['hear_mass_mailing', 'hear_tv_radio', 'hear_internet', 'hear_neighbor', 'hear_trade_show', 'hear_other'];
        $i = 0;
        foreach ($hear_about_options as $key => $value) {
          $checked = in_array($key, $selected_hear_about) ? ' checked' : '';
          echo '<div class="form-check pb-2">';
          echo '<input class="form-check-input" type="checkbox" name="hear_about[]" value="' . $key . '" id="' . $hear_ids[$i] . '"' . $checked . '>';
          echo '<label class="form-check-label" for="' . $hear_ids[$i] . '">' . $value . '</label>';
          echo '</div>';
          $i++;
        }
        ?>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="hear_about_other" class="pb-1"><?= $lang['lead_hear_about_other']; ?></label>
        <input type="text" name="hear_about_other" id="hear_about_other" class="form-control" 
               value="<?= htmlspecialchars($hear_about_other ?? '') ?>" autocomplete="off">
      </div>
    </div>
  </div>

  <!-- File Upload Links -->
  <h4><?= $lang['lead_file_upload_links']; ?></h4>
  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="picture_upload_link" class="pb-1"><?= $lang['lead_pictures_upload_link']; ?></label>
        <input type="text" name="picture_upload_link" id="picture_upload_link" class="form-control" 
               value="<?= htmlspecialchars($picture_upload_link ?? '') ?>" autocomplete="off">
      </div>
    </div>
    <div class="col">
      <div class="form-group pb-2">
        <label for="plans_upload_link" class="pb-1"><?= $lang['lead_plans_upload_link']; ?></label>
        <input type="text" name="plans_upload_link" id="plans_upload_link" class="form-control" 
               value="<?= htmlspecialchars($plans_upload_link ?? '') ?>" autocomplete="off">
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="plans_and_pics" class="pb-1"><?= $lang['lead_plans_and_pictures_uploaded']; ?></label>
        <select name="plans_and_pics" id="plans_and_pics" class="form-select" autocomplete="off">
          <option value="No" <?= ($plans_and_pics ?? 'No') == 'No' ? 'selected' : '' ?>><?= $lang['no'] ?? 'No'; ?></option>
          <option value="Yes" <?= ($plans_and_pics ?? 'No') == 'Yes' ? 'selected' : '' ?>><?= $lang['yes'] ?? 'Yes'; ?></option>
        </select>
      </div>
    </div>
  </div>

  <!-- Stage Field -->
  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="stage" class="pb-1"><?= $lang['lead_stage'] ?? 'Stage'; ?></label>
        <select name="stage" id="stage" class="form-select" autocomplete="off">
          <?php
          $stages = $leads->get_lead_stage_array_multilingual($lang);
          foreach ($stages as $key => $value) {
            $selected = ($key == ($stage ?? 1)) ? ' selected' : '';
            echo '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
          }
          ?>
        </select>
      </div>
    </div>
  </div>

  <input type="hidden" name="last_edited_by" value="<?= $_SESSION['user_id'] ?? null; ?>">
  <input type="hidden" name="dir" value="<?= $dir; ?>">
  <input type="hidden" name="page" value="<?= $page; ?>">

  <!-- Notes Section -->
  <h4><i class="fa-solid fa-sticky-note me-2"></i>Lead Notes</h4>
  <?php if (!empty($lead_notes)): ?>
    <div class="border rounded bg-light mb-4">
      <div class="p-3 border-bottom bg-white">
        <h6 class="text-muted mb-0">
          Notes & Activity (<?= count($lead_notes) ?>)
        </h6>
      </div>
      <div class="p-3">
        <div class="timeline">
          <?php 
          // Create Notes instance to get source information
          $notes_class = new Notes();
          foreach ($lead_notes as $note): 
          ?>
            <div class="timeline-item mb-4">
              <div class="timeline-marker">
                <div class="timeline-marker-icon bg-<?= $note['source'] <= 3 ? 'primary' : ($note['source'] <= 6 ? 'success' : 'secondary') ?>">
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
      </div>
    </div>
  <?php else: ?>
    <div class="card mb-4">
      <div class="card-body">
        <p class="text-muted mb-0">
          <i class="fa-solid fa-info-circle me-2"></i>No notes found for this lead.
        </p>
      </div>
    </div>
  <?php endif; ?>

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

  <!-- Navigation and Action Buttons -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <!-- Lead Navigation -->
    <div class="btn-group" role="group" aria-label="Lead Navigation">
      <?php if (!empty($navigation['previous'])): ?>
        <a href="<?= URL ?>/admin/leads/edit?id=<?= $navigation['previous'] ?>" 
           class="btn btn-outline-secondary" title="Previous Lead">
          <i class="fa-solid fa-chevron-left me-1"></i>Previous
        </a>
      <?php else: ?>
        <button class="btn btn-outline-secondary" disabled>
          <i class="fa-solid fa-chevron-left me-1"></i>Previous
        </button>
      <?php endif; ?>
      
      <span class="btn btn-outline-info disabled">
        Lead <?= htmlspecialchars($lead_number ?? 'N/A') ?>
      </span>
      
      <?php if (!empty($navigation['next'])): ?>
        <a href="<?= URL ?>/admin/leads/edit?id=<?= $navigation['next'] ?>" 
           class="btn btn-outline-secondary" title="Next Lead">
          Next<i class="fa-solid fa-chevron-right ms-1"></i>
        </a>
      <?php else: ?>
        <button class="btn btn-outline-secondary" disabled>
          Next<i class="fa-solid fa-chevron-right ms-1"></i>
        </button>
      <?php endif; ?>
    </div>

    <!-- Action Buttons -->
    <div class="btn-group" role="group" aria-label="Form Actions">
      <a href="<?= URL ?>/admin/leads/list" class="btn btn-secondary" role="button" tabindex="0">
        <i class="fa-solid fa-list me-1"></i>Back to Admin Leads
      </a>
      <a href="<?= URL ?>/leads/view?id=<?= htmlspecialchars($_GET['id'] ?? '') ?>" 
         class="btn btn-info" role="button" tabindex="0">
        <i class="fa-solid fa-eye me-1"></i>View Lead
      </a>
      <button type="submit" class="btn btn-success" role="button" value="submit" tabindex="0">
        <i class="fa-solid fa-save me-1"></i>Update Lead
      </button>
    </div>
  </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Function to get timezone from location
  function getTimezoneFromLocation(state, country) {
    // Clean up state code - remove country prefix if present
    if (state && state.includes('-')) {
      state = state.split('-')[1];
    }
    
    // Default timezone mappings
    const timezones = {
      'US': {
        'AZ': 'America/Phoenix',
        'CA': 'America/Los_Angeles', 
        'CO': 'America/Denver',
        'ID': 'America/Boise',
        'MT': 'America/Denver',
        'NV': 'America/Los_Angeles',
        'NM': 'America/Denver',
        'OR': 'America/Los_Angeles',
        'TX': 'America/Chicago',
        'UT': 'America/Denver',
        'WA': 'America/Los_Angeles',
        'WY': 'America/Denver',
        'VA': 'America/New_York',
        'SC': 'America/New_York'
      },
      'CA': {
        'default': 'America/Toronto'
      }
    };
    
    if (country && timezones[country]) {
      if (state && timezones[country][state]) {
        return timezones[country][state];
      } else if (timezones[country]['default']) {
        return timezones[country]['default'];
      }
    }
    
    return 'America/Denver'; // Default fallback
  }
  
  // Update timezone when state or country changes
  function updateTimezone() {
    const state = document.getElementById('form_state').value;
    const country = document.getElementById('form_country').value;
    const timezone = getTimezoneFromLocation(state, country);
    document.getElementById('timezone').value = timezone;
  }
  
  // Add event listeners
  document.getElementById('form_state').addEventListener('change', updateTimezone);
  document.getElementById('form_country').addEventListener('change', updateTimezone);
  
  // Set initial timezone if not already set
  if (!document.getElementById('timezone').value) {
    updateTimezone();
  }
});
</script>

<?php
require SECTIONCLOSE;
require FOOTER;
?>