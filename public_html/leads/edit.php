<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$dir = 'leads';
$page = 'edit';

$table_page = false;

require LANG . '/en.php';
$title = $lang['lead_edit'] ?? 'Edit Lead';
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


  <h4><?= $lang['lead_contact_information'] ?? 'Contact Information'; ?></h4>
  <!-- First Name & Last Name -->
  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="first_name"
               class="required pb-1"><?= $lang['lead_first_name'] ?? 'First Name'; ?></label>
        <input type="text"
               name="first_name"
               maxlength="100"
               id="first_name"
               class="form-control"
               value="<?= htmlspecialchars($first_name ?? '') ?>"
               required
               autocomplete="off">
      </div>
    </div>
    <div class="col">
      <div class="form-group pb-2">
        <label for="last_name"
               class="required pb-1"><?= $lang['lead_last_name'] ?? 'Last Name'; ?></label>
        <input type="text"
               name="last_name"
               maxlength="100"
               id="last_name"
               class="form-control"
               value="<?= htmlspecialchars($last_name ?? '') ?>"
               required
               autocomplete="off">
      </div>
    </div>
  </div>

  <!-- Business Name -->
  <div class="row">
    <div class="col-6">
      <div class="form-group pb-2">
        <label for="business_name"
               class="pb-1"><?= $lang['lead_business_name'] ?? 'Business Name'; ?></label>
        <input type="text"
               name="business_name"
               maxlength="255"
               id="business_name"
               class="form-control"
               value="<?= htmlspecialchars($business_name ?? '') ?>"
               autocomplete="off">
      </div>
    </div>
  </div>

  <!-- Cell Phone, Email & Contact Type -->
  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="cell_phone"
               class="pb-1"><?= $lang['lead_cell_phone'] ?? 'Cell Phone'; ?></label>
        <input type="tel"
               name="cell_phone"
               maxlength="15"
               id="cell_phone"
               class="form-control"
               value="<?= htmlspecialchars($cell_phone ?? '') ?>"
               autocomplete="off">
      </div>
    </div>
    <div class="col">
      <div class="form-group pb-2">
        <label for="email"
               class="required pb-1"><?= $lang['lead_email'] ?? 'Email'; ?></label>
        <input type="email"
               name="email"
               maxlength="255"
               id="email"
               class="form-control"
               value="<?= htmlspecialchars($email ?? '') ?>"
               required
               autocomplete="off">
      </div>
    </div>
    <div class="col">
      <div class="form-group pb-2">
        <label for="ctype"
               class="pb-1"><?= $lang['lead_contact_type'] ?? 'Contact Type'; ?></label>
        <select name="ctype"
                id="ctype"
                class="form-select"
                autocomplete="off">
          <?php
        $contact_types = $leads->get_lead_contact_type_array();
        foreach ($contact_types as $key => $value) {
          $selected = ($key == $ctype) ? ' selected' : '';
          echo '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
        }
        ?>
        </select>
      </div>
    </div>
  </div>

  <!-- Lead Number -->
  <div class="row">
    <div class="col-6">
      <div class="form-group pb-2">
        <label for="estimate_number"
               class="pb-1"><?= $lang['lead_number']; ?></label>
        <input type="text"
               name="estimate_number"
               id="estimate_number"
               class="form-control"
               value="<?= htmlspecialchars($estimate_number ?? '') ?>"
               autocomplete="off">
      </div>
    </div>
  </div>

  <!-- Address Fields -->
  <h4><?= $lang['lead_property_address'] ?? 'Property Address'; ?></h4>
  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="form_street_1"
               class="pb-1"><?= $lang['lead_street_address_1'] ?? 'Street Address 1'; ?></label>
        <input type="text"
               name="form_street_1"
               maxlength="100"
               id="form_street_1"
               class="form-control"
               value="<?= htmlspecialchars($form_street_1 ?? '') ?>"
               autocomplete="off">
      </div>
    </div>
    <div class="col">
      <div class="form-group pb-2">
        <label for="form_street_2"
               class="pb-1"><?= $lang['lead_street_address_2'] ?? 'Street Address 2'; ?></label>
        <input type="text"
               name="form_street_2"
               maxlength="50"
               id="form_street_2"
               class="form-control"
               value="<?= htmlspecialchars($form_street_2 ?? '') ?>"
               autocomplete="off">
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="form_city"
               class="pb-1"><?= $lang['lead_city'] ?? 'City'; ?></label>
        <input type="text"
               name="form_city"
               maxlength="50"
               id="form_city"
               class="form-control"
               value="<?= htmlspecialchars($form_city ?? '') ?>"
               autocomplete="off">
      </div>
    </div>
    <div class="col">
      <div class="form-group pb-2">
        <label for="form_state"
               class="pb-1"><?= $lang['lead_state'] ?? 'State'; ?></label>
        <input type="text"
               name="form_state"
               maxlength="10"
               id="form_state"
               class="form-control"
               value="<?= htmlspecialchars($form_state ?? '') ?>"
               autocomplete="off">
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="form_postcode"
               class="pb-1"><?= $lang['lead_postal_code'] ?? 'Postal Code'; ?></label>
        <input type="text"
               name="form_postcode"
               maxlength="15"
               id="form_postcode"
               class="form-control"
               value="<?= htmlspecialchars($form_postcode ?? '') ?>"
               autocomplete="off">
      </div>
    </div>
    <div class="col">
      <div class="form-group pb-2">
        <label for="form_country"
               class="pb-1"><?= $lang['lead_country'] ?? 'Country'; ?></label>
        <input type="text"
               name="form_country"
               maxlength="5"
               id="form_country"
               class="form-control"
               value="<?= htmlspecialchars($form_country ?? 'US') ?>"
               autocomplete="off">
      </div>
    </div>
  </div>

  <!-- Structure Information -->
  <h4><?= $lang['lead_structure_information'] ?? 'Structure Information'; ?></h4>
  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="structure_type"
               class="pb-1"><?= $lang['lead_structure_type'] ?? 'Structure Type'; ?></label>
        <select name="structure_type"
                id="structure_type"
                class="form-select"
                autocomplete="off">
          <?php
        $structure_types = $leads->get_lead_structure_type_array();
        foreach ($structure_types as $key => $value) {
          $selected = ($key == $structure_type) ? ' selected' : '';
          echo '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
        }
        ?>
        </select>
      </div>
    </div>
  </div>

  <!-- Notes -->
  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="notes"
               class="pb-1"><?= $lang['lead_notes'] ?? 'Notes'; ?></label>
        <textarea name="notes"
                  id="notes"
                  class="form-control"
                  rows="4"
                  autocomplete="off"><?= htmlspecialchars($notes ?? '') ?></textarea>
      </div>
    </div>
  </div>

  <!-- Stage -->
  <div class="row">
    <div class="col-6">
      <div class="form-group pb-2">
        <label for="stage"
               class="pb-1"><?= $lang['lead_stage'] ?? 'Stage'; ?></label>
        <input type="text"
               name="stage"
               maxlength="20"
               id="stage"
               class="form-control"
               value="<?= htmlspecialchars($stage ?? 'Lead') ?>"
               autocomplete="off">
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

  <p></p>
  <a href="list"
     class="btn btn-danger"
     role="button"
     aria-pressed="false"
     tabindex="0">
    <?= $lang['lead_cancel'] ?? 'Cancel'; ?>
  </a>
  <button type="submit"
          class="btn btn-success"
          role="button"
          value="submit"
          tabindex="0">
    <?= $lang['lead_update'] ?? 'Update Lead'; ?>
  </button>
</form>

<?php
require SECTIONCLOSE;
require FOOTER;
?>