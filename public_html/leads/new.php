<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$dir = "leads";
$page = "new";

$table_page = false;

require LANG . '/en.php';

$title = $lang['lead_new'];

$title_icon = '<i class="fa-solid fa-pencil"></i><i class="fa-solid fa-pencil"></i>';

require 'get.php';

require HEADER;
require BODY;
require NAV;
require SECTIONOPEN;
?>
<p></p>
<form action="post.php"
      method="POST"
      autocomplete="off">

  <!-- Lead Source -->
  <div class="row">
    <div class="col-6">
      <div class="form-group pb-2">
        <label for="lead_source"
               class="required pb-1"><?= $lang['lead_source'] ?? 'Lead Source'; ?></label>
        <select name="lead_source"
                id="lead_source"
                class="form-select"
                required
                autocomplete="off">
          <?php
        $lead_sources = $helpers->get_lead_source_array($lang);
        foreach ($lead_sources as $key => $value) {
          $selected = ($key == '1') ? ' selected' : ''; // Default to Web Estimate (key 1)
          echo '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
        }
        ?>
        </select>
      </div>
    </div>
    <div class="col-6">
      <div class="form-group pb-2">
        <label for="lead_number"
               class="required pb-1"><?= $lang['lead_number']; ?></label>
        <input type="text"
               name="lead_number"
               id="lead_number"
               class="form-control"
               placeholder="<?= $lang['lead_last_number_placeholder_hint'] . ' ' . $last_lead_number; ?>"
               autocomplete="off"
               required>
      </div>
    </div>
  </div>

  <h4><?= $lang['lead_contact_information']; ?></h4>
  <!-- Field 1: First Name & Last Name -->
  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="first_name"
               class="required pb-1"><?= $lang['lead_first_name']; ?></label>
        <input type="text"
               name="first_name"
               maxlength="100"
               id="first_name"
               class="form-control"
               required
               autocomplete="off">
      </div>
    </div>
    <div class="col">
      <div class="form-group pb-2">
        <label for="family_name"
               class="required pb-1"><?= $lang['lead_family_name']; ?></label>
        <input type="text"
               name="family_name"
               maxlength="100"
               id="family_name"
               class="form-control"
               required
               autocomplete="off">
      </div>
    </div>
  </div>

  <!-- Fields 2-3: Cell Phone, Email & Contact Type -->
  <div class="row form-field"
       id="contact-info-section">
    <div class="col form-field"
         id="phone-field">
      <div class="form-group pb-2">
        <label for="cell_phone"
               class="pb-1"
               id="phone-label"><?= $lang['lead_cell_phone']; ?></label>
        <input type="tel"
               name="cell_phone"
               maxlength="15"
               id="cell_phone"
               class="form-control"
               autocomplete="off">
      </div>
    </div>
    <div class="col">
      <div class="form-group pb-2">
        <label for="email"
               class="required pb-1"><?= $lang['lead_email']; ?></label>
        <input type="email"
               name="email"
               maxlength="255"
               id="email"
               class="form-control"
               required
               autocomplete="off">
      </div>
    </div>
  </div>

  <!-- Contact Type & Business Name Fields -->
  <div class="row">
    <div class="col form-field"
         id="contact-type-field">
      <div class="form-group pb-2">
        <label for="ctype"
               class="pb-1"
               id="contact-type-label"><?= $lang['lead_contact_type']; ?></label>
        <select name="ctype"
                id="ctype"
                class="form-select"
                autocomplete="off">
          <option value=""><?= $lang['lead_select_contact_type']; ?></option>
          <?php
        $contact_types = $helpers->get_lead_contact_type_array($lang);
        foreach ($contact_types as $key => $value) {
          $selected = ($key == '1') ? ' selected' : ''; // Default to Owner (key 1)
          echo '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
        }
        ?>
        </select>
      </div>
    </div>
    <div class="col">
      <div class="form-group pb-2">
        <label for="business_name"
               class="pb-1"><?= $lang['lead_business_name'] ?? 'Business Name'; ?></label>
        <input type="text"
               name="business_name"
               maxlength="255"
               id="business_name"
               class="form-control"
               autocomplete="off">
      </div>
    </div>
  </div>



  
  <!-- Address Fields -->
  <div class="form-field"
       id="address-section">
    <h4 id="address-header"><?= $lang['lead_property_address']; ?></h4>
    <div class="row">
      <div class="col">
        <div class="form-group pb-2">
          <label for="form_street_1"
                 class="pb-1"
                 id="street1-label"><?= $lang['lead_street_address_1']; ?></label>
          <input type="text"
                 name="form_street_1"
                 maxlength="100"
                 id="form_street_1"
                 class="form-control"
                 autocomplete="off">
        </div>
      </div>
      <div class="col">
        <div class="form-group pb-2">
          <label for="form_street_2"
                 class="pb-1"
                 id="street2-label"><?= $lang['lead_street_address_2']; ?></label>
          <input type="text"
                 name="form_street_2"
                 maxlength="50"
                 id="form_street_2"
                 class="form-control"
                 autocomplete="off">
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col">
        <div class="form-group pb-2">
          <label for="form_city"
                 class="pb-1"
                 id="city-label"><?= $lang['lead_city']; ?></label>
          <input type="text"
                 name="form_city"
                 maxlength="50"
                 id="form_city"
                 class="form-control"
                 autocomplete="off">
        </div>
      </div>
      <div class="col">
        <div class="form-group pb-2">
          <label for="form_state"
                 class="pb-1"
                 id="state-label"><?= $lang['lead_state']; ?></label>
          <select name="form_state"
                  id="form_state"
                  class="form-select"
                  autocomplete="off">
            <option value=""><?= $lang['select_state']; ?></option>
            <option value="US-AZ"><?= $lang['US-AZ']; ?></option>
            <option value="US-CA"><?= $lang['US-CA']; ?></option>
            <option value="US-CO"><?= $lang['US-CO']; ?></option>
            <option value="US-ID"><?= $lang['US-ID']; ?></option>
            <option value="US-MT"><?= $lang['US-MT']; ?></option>
            <option value="US-NV"><?= $lang['US-NV']; ?></option>
            <option value="US-NM"><?= $lang['US-NM']; ?></option>
            <option value="US-OR"><?= $lang['US-OR']; ?></option>
            <option value="US-TX"><?= $lang['US-TX']; ?></option>
            <option value="US-UT"><?= $lang['US-UT']; ?></option>
            <option value="US-WA"><?= $lang['US-WA']; ?></option>
            <option value="US-WY"><?= $lang['US-WY']; ?></option>
            <option value="US-VA"><?= $lang['US-VA']; ?></option>
            <option value="US-SC"><?= $lang['US-SC']; ?></option>
          </select>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col">
        <div class="form-group pb-2">
          <label for="form_postcode"
                 class="pb-1"
                 id="postcode-label"><?= $lang['lead_postal_code']; ?></label>
          <input type="text"
                 name="form_postcode"
                 maxlength="15"
                 id="form_postcode"
                 class="form-control"
                 autocomplete="off">
        </div>
      </div>
      <div class="col">
        <div class="form-group pb-2">
          <label for="form_country"
                 class="pb-1"
                 id="country-label"><?= $lang['lead_country']; ?></label>
          <select name="form_country"
                  id="form_country"
                  class="form-select"
                  autocomplete="off">
            <option value=""><?= $lang['select_country']; ?></option>
            <option value="US"
                    selected><?= $lang['US']; ?></option>
            <option value="CA"><?= $lang['CA']; ?></option>
            <option value="MX"><?= $lang['MX']; ?></option>
            <option value="UK"><?= $lang['UK']; ?></option>
            <option value="AU"><?= $lang['AU']; ?></option>
            <option value="NZ"><?= $lang['NZ']; ?></option>
            <option value="BR"><?= $lang['BR']; ?></option>
          </select>
        </div>
      </div>
    </div>
    
    <!-- Hidden timezone field - populated by JavaScript based on address -->
    <input type="hidden" name="timezone" id="timezone" value="">
  </div>

  <!-- Field 17: Services Interested In -->
  <div class="row form-field"
       id="services-section">
    <div class="col">
      <div class="form-group pb-2">
        <h4><label class="pb-1"><?= $lang['lead_services_interested_in']; ?></label></h4>
        <?php
        $services = $helpers->get_lead_services_array($lang);
        $service_ids = ['service_wildfire', 'service_assessment', 'service_gutter', 'service_vent', 'service_ltr', 'service_lease', 'service_landscape'];
        $i = 0;
        foreach ($services as $key => $value) {
          echo '<div class="form-check pb-2">';
          echo '<input class="form-check-input" type="checkbox" name="services_interested_in[]" value="' . $key . '" id="' . $service_ids[$i] . '">';
          echo '<label class="form-check-label" for="' . $service_ids[$i] . '">' . $value . '</label>';
          echo '</div>';
          $i++;
        }
        ?>
      </div>
    </div>
  </div>

  <!-- Fields 18-21: Structure Information -->
  <div class="form-field"
       id="structure-section">
    <h4><?= $lang['lead_structure_information']; ?></h4>
    <div class="row">
      <div class="col">
        <div class="form-group pb-2">
          <label for="structure_type"
                 class="pb-1"><?= $lang['lead_structure_type']; ?></label>
          <select name="structure_type"
                  id="structure_type"
                  class="form-select"
                  autocomplete="off">
            <?php
        $structure_types = $helpers->get_lead_structure_type_array($lang);
        foreach ($structure_types as $key => $value) {
          $selected = ($key == '1') ? ' selected' : ''; // Default to Existing Home (key 1)
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
        $structure_ids = ['structure_rambler', 'structure_two', 'structure_three', 'structure_walkout', 'structure_modern', 'structure_other_desc'];
        $i = 0;
        foreach ($structures as $key => $value) {
          echo '<div class="form-check me-3 pb-2">';
          echo '<input class="form-check-input" type="checkbox" name="structure_description[]" value="' . $key . '" id="' . $structure_ids[$i] . '">';
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
          <label for="structure_other"
                 class="pb-1"><?= $lang['lead_structure_other']; ?></label>
          <input type="text"
                 name="structure_other"
                 id="structure_other"
                 class="form-control"
                 autocomplete="off">
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col">
        <div class="form-group pb-2">
          <label for="structure_additional"
                 class="pb-1"><?= $lang['lead_structure_additional']; ?></label>
          <textarea name="structure_additional"
                    id="structure_additional"
                    class="form-control"
                    rows="2"
                    autocomplete="off"></textarea>
        </div>
      </div>
    </div>
  </div>

  <!-- Fields 22-23: Picture & Plans Submitted -->
  <div class="row form-field"
       id="pictures-submitted-section">
    <div class="col-6">
      <div class="form-group pb-2">
        <label class="pb-1"><?= $lang['lead_pictures_submitted']; ?></label>
        <input type="text"
               name="picture_submitted_1"
               id="picture_submitted_1"
               class="form-control mb-2"
               autocomplete="off">
        <input type="text"
               name="picture_submitted_2"
               id="picture_submitted_2"
               class="form-control mb-2"
               autocomplete="off">
        <input type="text"
               name="picture_submitted_3"
               id="picture_submitted_3"
               class="form-control mb-2"
               autocomplete="off">
      </div>
    </div>
    <div class="col-6">
      <div class="form-group pb-2">
        <label class="pb-1"><?= $lang['lead_plans_submitted']; ?></label>
        <input type="text"
               name="plans_submitted_1"
               id="plans_submitted_1"
               class="form-control mb-2"
               autocomplete="off">
        <input type="text"
               name="plans_submitted_2"
               id="plans_submitted_2"
               class="form-control mb-2"
               autocomplete="off">
        <input type="text"
               name="plans_submitted_3"
               id="plans_submitted_3"
               class="form-control mb-2"
               autocomplete="off">
      </div>
    </div>
  </div>

  <!-- Message/Request Details Field (for LTR and Contact forms) -->
  <div class="row form-field"
       id="message-section">
    <div class="col">
      <div class="form-group pb-2">
        <label for="message"
               class="pb-1"
               id="message-label"><?= $lang['lead_message']; ?></label>
        <textarea name="notes"
                  id="message"
                  class="form-control"
                  rows="4"
                  autocomplete="off"></textarea>
      </div>
    </div>
  </div>

  <!-- Receive Updates Field -->
  <div class="row form-field"
       id="updates-section">
    <div class="col">
      <div class="form-group pb-2">
        <label for="get_updates"
               class="pb-1"
               id="updates-label"><?= $lang['lead_get_updates']; ?></label>
        <select name="get_updates"
                id="get_updates"
                class="form-select"
                autocomplete="off">
          <option value="No"><?= $lang['lead_no']; ?></option>
          <option value="Yes"
                  selected><?= $lang['lead_yes']; ?></option>
        </select>
      </div>
    </div>
  </div>

  <!-- Fields 25-26: How did you hear about us -->
  <div class="form-field"
       id="hear-about-section">
    <div class="row">
      <div class="col">
        <div class="form-group pb-2">
          <label class="pb-1"><?= $lang['lead_how_did_you_hear']; ?></label>
          <?php
        $hear_about = $helpers->get_lead_hear_about_array($lang);
        $hear_ids = ['hear_mass_mailing', 'hear_tv_radio', 'hear_internet', 'hear_neighbor', 'hear_trade_show', 'hear_other'];
        $i = 0;
        foreach ($hear_about as $key => $value) {
          echo '<div class="form-check pb-2">';
          echo '<input class="form-check-input" type="checkbox" name="hear_about[]" value="' . $key . '" id="' . $hear_ids[$i] . '">';
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
          <label for="hear_about_other"
                 class="pb-1"><?= $lang['lead_hear_about_other']; ?></label>
          <input type="text"
                 name="hear_about_other"
                 id="hear_about_other"
                 class="form-control"
                 autocomplete="off">
        </div>
      </div>
    </div>
  </div>
  <div class="form-field"
       id="file-upload-section">
    <h4><?= $lang['lead_file_upload_links']; ?></h4>
    <div class="row">
      <div class="col">
        <div class="form-group pb-2">
          <label for="picture_upload_link"
                 class="pb-1"><?= $lang['lead_pictures_upload_link']; ?></label>
          <input type="text"
                 name="picture_upload_link"
                 id="picture_upload_link"
                 class="form-control"
                 autocomplete="off">
        </div>
      </div>
      <div class="col">
        <div class="form-group pb-2">
          <label for="plans_upload_link"
                 class="pb-1"><?= $lang['lead_plans_upload_link']; ?></label>
          <input type="text"
                 name="plans_upload_link"
                 id="plans_upload_link"
                 class="form-control"
                 autocomplete="off">
        </div>
      </div>
    </div>
    <div class="row form-field"
         id="plans-pics-uploaded-section">
      <div class="col">
        <div class="form-group pb-2">
          <label for="plans_and_pics"
                 class="pb-1"><?= $lang['lead_plans_and_pictures_uploaded']; ?></label>
          <select name="plans_and_pics"
                  id="plans_and_pics"
                  class="form-select"
                  autocomplete="off">
            <option value="No"
                    selected><?= $lang['no'] ?? 'No'; ?></option>
            <option value="Yes"><?= $lang['yes'] ?? 'Yes'; ?></option>
          </select>
        </div>
      </div>
    </div>
  </div>
  <input type="hidden"
         name="stage"
         value="1">
  <input type="hidden"
         name="last_edited_by"
         value="<?= $_SESSION['user_id'] ?? null; ?>">
  <input type="hidden"
         name="dir"
         value="<?= $dir; ?>">
  <input type="hidden"
         name="page"
         value="<?= $page; ?>">
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
    <?= $lang['lead_submit']; ?>
  </button>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Function to get timezone from location
  function getTimezoneFromLocation(state, country) {
    // Clean up state code - remove country prefix if present
    state = state.replace(/^(US-|CA-)/, '').toUpperCase();
    country = country.toUpperCase();
    
    // US state to timezone mapping
    const usTimezones = {
      // Pacific Time
      'CA': 'America/Los_Angeles', 'WA': 'America/Los_Angeles', 'OR': 'America/Los_Angeles', 'NV': 'America/Los_Angeles',
      // Mountain Time
      'AZ': 'America/Phoenix', 'UT': 'America/Denver', 'CO': 'America/Denver', 'WY': 'America/Denver', 
      'MT': 'America/Denver', 'NM': 'America/Denver', 'ND': 'America/Denver', 'SD': 'America/Denver', 'ID': 'America/Denver',
      // Central Time
      'TX': 'America/Chicago', 'OK': 'America/Chicago', 'KS': 'America/Chicago', 'NE': 'America/Chicago',
      'MN': 'America/Chicago', 'IA': 'America/Chicago', 'MO': 'America/Chicago', 'AR': 'America/Chicago',
      'LA': 'America/Chicago', 'MS': 'America/Chicago', 'AL': 'America/Chicago', 'TN': 'America/Chicago',
      'KY': 'America/Chicago', 'IN': 'America/Chicago', 'IL': 'America/Chicago', 'WI': 'America/Chicago',
      // Eastern Time
      'MI': 'America/Detroit', 'OH': 'America/New_York', 'WV': 'America/New_York', 'VA': 'America/New_York',
      'PA': 'America/New_York', 'NY': 'America/New_York', 'VT': 'America/New_York', 'NH': 'America/New_York',
      'ME': 'America/New_York', 'MA': 'America/New_York', 'RI': 'America/New_York', 'CT': 'America/New_York',
      'NJ': 'America/New_York', 'DE': 'America/New_York', 'MD': 'America/New_York', 'DC': 'America/New_York',
      'NC': 'America/New_York', 'SC': 'America/New_York', 'GA': 'America/New_York', 'FL': 'America/New_York',
      // Alaska & Hawaii
      'AK': 'America/Anchorage', 'HI': 'Pacific/Honolulu'
    };
    
    // Check for US states first
    if (country === 'US' && usTimezones[state]) {
      return usTimezones[state];
    }
    
    // Country-level timezone defaults
    const countryTimezones = {
      'US': 'America/New_York', // Default to Eastern if state unknown
      'CA': 'America/Toronto',   // Canada
      'MX': 'America/Mexico_City', // Mexico
      'UK': 'Europe/London',     // United Kingdom
      'AU': 'Australia/Sydney',  // Australia
      'NZ': 'Pacific/Auckland',  // New Zealand
      'BR': 'America/Sao_Paulo', // Brazil
    };
    
    return countryTimezones[country] || 'UTC';
  }
  
  // Function to update timezone field
  function updateTimezone() {
    const stateField = document.getElementById('form_state');
    const countryField = document.getElementById('form_country');
    const timezoneField = document.getElementById('timezone');
    
    if (stateField && countryField && timezoneField) {
      const state = stateField.value;
      const country = countryField.value;
      const timezone = getTimezoneFromLocation(state, country);
      timezoneField.value = timezone;
    }
  }
  
  // Update timezone when state or country changes
  const stateField = document.getElementById('form_state');
  const countryField = document.getElementById('form_country');
  
  if (stateField) {
    stateField.addEventListener('change', updateTimezone);
  }
  
  if (countryField) {
    countryField.addEventListener('change', updateTimezone);
  }
  
  // Set initial timezone on page load
  updateTimezone();
});
</script>

<?php
require SECTIONCLOSE;
require FOOTER;