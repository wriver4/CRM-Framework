<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$dir = 'contacts';
$page = 'new';

$table_page = false;

require LANG . '/en.php';
$title = $lang['contact_new'];

$title_icon = '<i class="fa-solid fa-address-book"></i>';

require HEADER;
require BODY;
require NAV;
require SECTIONOPEN;
?>
<form action="post.php"
      method="POST"
      autocomplete="off">
  
  <!-- Hidden fields for form processing -->
  <input type="hidden" name="dir" value="contacts">
  <input type="hidden" name="page" value="new">
  
  <!-- Hidden field for lead_id if coming from leads -->
  <?php 
  $default_ctype = 1; // Default to Primary Owner - each contact can have different type
  $lead_first_name = '';
  $lead_family_name = '';
  $lead_cell_phone = '';
  $lead_email = '';
  if (isset($_GET['lead_id']) && !empty($_GET['lead_id'])): 
    $lead_id = htmlspecialchars($_GET['lead_id']);
    // Get lead data to pre-populate contact form (but NOT ctype)
    $leads = new Leads();
    $result = $leads->get_lead_by_lead_id($lead_id);  // Use lead_id (external number) instead of internal id
    if (!empty($result) && isset($result[0])) {
      $lead_data = $result[0];
      $lead_first_name = $lead_data['first_name'] ?? '';
      $lead_family_name = $lead_data['family_name'] ?? '';
      $lead_cell_phone = $lead_data['cell_phone'] ?? '';
      $lead_email = $lead_data['email'] ?? '';
    }
  ?>
    <input type="hidden" name="lead_id" value="<?= $lead_id ?>">
    <div class="alert alert-info mb-3">
      <i class="fa-solid fa-info-circle me-2"></i>
      Creating contact for Lead #<?= $lead_id ?>
    </div>
  <?php endif; ?>
  
  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="ctype"
               class="required pb-1"><?= $lang['ctype']; ?></label>
        <select name="ctype"
                id="ctype"
                class="form-select"
                required
                autocomplete="off">
          <?php 
          // Use standardized lead contact types for consistency
          $contact_types = $helper->get_lead_contact_type_array($lang);
          foreach ($contact_types as $key => $value) {
            $selected = ($key == $default_ctype) ? ' selected' : '';
            echo '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
          }
          ?></select>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="first_name"
               class="required pb-1 pt-1"><?= $lang['first_name']; ?></label>
        <input type="text"
               name="first_name"
               maxlength="100"
               id="first_name"
               class="form-control"
               value="<?= htmlspecialchars($lead_first_name) ?>"
               required
               autocomplete="off">
      </div>
    </div>
    <div class="col">
      <div class="form-group pb-2">
        <label for="family_name"
               class="required pb-1 pt-1"><?= $lang['family_name']; ?></label>
        <input type="text"
               name="family_name"
               maxlength="100"
               id="family_name"
               class="form-control"
               value="<?= htmlspecialchars($lead_family_name) ?>"
               required
               autocomplete="off">
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="cell_phone"
               class="required pb-1 pt-1"><?= $lang['cell_phone']; ?></label>
        <input type="tel"
               pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}"
               name="cell_phone"
               maxlength="100"
               id="cell_phone"
               class="form-control"
               value="<?= htmlspecialchars($lead_cell_phone) ?>"
               required
               placeholder="<?= $lang['tel_pattern_us']; ?>"
               autocomplete="off">
      </div>
    </div>
    <div class="col">
      <div class="form-group pb-2">
        <label for="business_phone"
               class="pb-1 pt-1"><?= $lang['business_phone']; ?></label>
        <input type="tel"
               pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}"
               name="business_phone"
               maxlength="100"
               id="business_phone"
               class="form-control"
               placeholder="<?= $lang['tel_pattern_us']; ?>"
               autocomplete="off">
      </div>
    </div>
    <div class="col">
      <div class="form-group pb-2">
        <label for="alt_phone"
               class="pb-1 pt-1"><?= $lang['alt_phone']; ?></label>
        <input type="tel"
               pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}"
               name="alt_phone"
               maxlength="100"
               id="alt_phone"
               class="form-control"
               placeholder="<?= $lang['tel_pattern_us']; ?>"
               autocomplete="off">
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="personal_email"
               class="pb-1"><?= $lang['personal_email']; ?></label>
        <input type="email"
               name="personal_email"
               pattern="<?= VALIDEMAIL; ?>"
               maxlength="250"
               id="personal_email"
               class="form-control"
               value="<?= htmlspecialchars($lead_email) ?>"
               autocomplete="off">
      </div>
    </div>
    <div class="col">
      <div class="form-group pb-2">
        <label for="business_email"
               class="pb-1"><?= $lang['business_email']; ?></label>
        <input type="email"
               pattern="<?= VALIDEMAIL; ?>"
               name="business_email"
               size="64"
               maxlength="250"
               id="business_email"
               class="form-control"
               autocomplete="off">
      </div>
    </div>
    <div class="col">
      <div class="form-group pb-2">
        <label for="alt_email"
               class="pb-1"><?= $lang['alt_email']; ?></label>
        <input type="email"
               pattern="<?= VALIDEMAIL; ?>"
               name="alt_email"
               maxlength="250"
               id="alt_email"
               class="form-control"
               autocomplete="off">
      </div>
    </div>
  </div>
  <h4><?= $lang['addresses']; ?></h4>
  <div class="accordion"
       id="accordian_addresses">
    <div class="accordion-item">
      <h2 class="accordion-header"
          id="personal-address">
        <button class="accordion-button collapsed"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#address-personal"
                aria-expanded="false"
                aria-controls="address-personal">
          <?= $lang['personal']; ?></button>
      </h2>
      <div id="address-personal"
           class="accordion-collapse collapse"
           aria-labelledby="address-personal"
           data-bs-parent="#accordian_addresses">
        <div class="accordion-body">
          <div class="form-group pb-2">
            <label for="p_street_1"
                   class="pb-1"><?= $lang['street_address_1']; ?></label>
            <input type="text"
                   name="p_street_1"
                   maxlength="100"
                   id="p_street_1"
                   class="form-control"
                   autocomplete="off">
          </div>
          <div class="form-group pb-2">
            <label for="p_street_2"
                   class="pb-1"><?= $lang['street_address_2']; ?></label>
            <input type="text"
                   name="p_street_2"
                   maxlength="100"
                   id="p_street_2"
                   class="form-control"
                   autocomplete="off">
          </div>
          <div class="row">
            <div class="col">
              <div class="form-group pb-2">
                <label for="p_city"
                       class="pb-1"><?= $lang['city']; ?></label>
                <input type="text"
                       name="p_city"
                       maxlength="100"
                       id="p_city"
                       class="form-control"
                       autocomplete="off">
              </div>
            </div>
            <div class="col">
              <div class="form-group pb-2">
                <label for="p_state"
                       class="pb-1"><?= $lang['state']; ?></label>
                <select name="p_state"
                        id="p_state"
                        class="form-select">
                  <?php $helper->select_us_state($lang); ?></select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col">
              <div class="form-group pb-2">
                <label for="p_postcode"
                       class="pb-1"><?= $lang['postcode']; ?></label>
                <input type="text"
                       name="p_postcode"
                       maxlength="15"
                       id="p_postcode"
                       class="form-control"
                       autocomplete="off">
              </div>
            </div>
            <div class="col">
              <div class="form-group pb-2">
                <label for="p_country"
                       class="pb-2"><?= $lang['country']; ?></label>
                <select name="p_country"
                        id="p_country"
                        class="form-select">
                  <?php $helper->select_country($lang); ?></select>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="accordion-item">
      <h2 class="accordion-header"
          id="business-address">
        <button class="accordion-button collapsed"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#address-business"
                aria-expanded="false"
                aria-controls="address-business">
          <?= $lang['business']; ?></button>
      </h2>
      <div id="address-business"
           class="accordion-collapse collapse"
           aria-labelledby="headingTwo"
           data-bs-parent="#accordian_addresses">
        <div class="accordion-body">
          <div class="form-group pb-2">
            <label for="business_name"
                   class="pb-1"><?= $lang['business_name']; ?></label>
            <input type="text"
                   name="business_name"
                   maxlength="100"
                   id="business_name"
                   class="form-control"
                   autocomplete="off">
          </div>
          <div class="form-group pb-2">
            <label for="b_street_1"
                   class="pb-1"><?= $lang['street_address_1']; ?></label>
            <input type="text"
                   name="b_street_1"
                   maxlength="100"
                   id="b_street_1"
                   class="form-control"
                   autocomplete="off">
          </div>
          <div class="form-group pb-2">
            <label for="b_street_2"
                   class="pb-1"><?= $lang['street_address_2']; ?></label>
            <input type="text"
                   name="b_street_2"
                   maxlength="100"
                   id="b_street_2"
                   class="form-control"
                   autocomplete="off">
          </div>
          <div class="row">
            <div class="col">
              <div class="form-group pb-2">
                <label for="b_city"
                       class="pb-1"><?= $lang['city']; ?></label>
                <input type="text"
                       name="b_city"
                       maxlength="100"
                       id="b_city"
                       class="form-control"
                       autocomplete="off">
              </div>
            </div>
            <div class="col">
              <div class="form-group pb-2">
                <label for="b_state"
                       class="pb-1"><?= $lang['state']; ?></label>
                <select name="b_state"
                        id="b_state"
                        class="form-select">
                  <?php $helper->select_us_state($lang); ?></select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col">
              <div class="form-group pb-2">
                <label for="b_postcode"
                       class="pb-1"><?= $lang['postcode']; ?></label>
                <input type="text"
                       name="b_postcode"
                       maxlength="15"
                       id="b_postcode"
                       class="form-control"
                       autocomplete="off">
              </div>
            </div>
            <div class="col">
              <div class="form-group pb-2">
                <label for="b_country"
                       class="pb-2"><?= $lang['country']; ?></label>
                <select name="b_country"
                        id="b_country"
                        class="form-select">
                  <?php $helper->select_country($lang); ?></select>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="accordion-item">
      <h2 class="accordion-header"
          id="mailing-address">
        <button class="accordion-button collapsed"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#address-mailing"
                aria-expanded="false"
                aria-controls="address-mailing">
          <?= $lang['mailing']; ?></button>
      </h2>
      <div id="address-mailing"
           class="accordion-collapse collapse"
           aria-labelledby="headingThree"
           data-bs-parent="#accordian_addresses">
        <div class="accordion-body">
          <div class="form-group pb-2">
            <label for="m_street_1"
                   class="pb-1"><?= $lang['street_address_1']; ?></label>
            <input type="text"
                   name="m_street_1"
                   maxlength="100"
                   id="m_street_1"
                   class="form-control"
                   autocomplete="off">
          </div>
          <div class="form-group pb-2">
            <label for="m_street_2"
                   class="pb-1"><?= $lang['street_address_2']; ?></label>
            <input type="text"
                   name="m_street_2"
                   maxlength="100"
                   id="m_street_2"
                   class="form-control"
                   autocomplete="off">
          </div>
          <div class="row">
            <div class="col">
              <div class="form-group pb-2">
                <label for="city"
                       class="pb-1"><?= $lang['city']; ?></label>
                <input type="text"
                       name="m_city"
                       maxlength="100"
                       id="m_city"
                       class="form-control"
                       autocomplete="off">
              </div>
            </div>
            <div class="col">
              <div class="form-group pb-2">
                <label for="m_state"
                       class="pb-1"><?= $lang['state']; ?></label>
                <select name="m_state"
                        id="m_state"
                        class="form-select">
                  <?php $helper->select_us_state($lang); ?></select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col">
              <div class="form-group pb-2">
                <label for="m_postcode"
                       class="pb-1"><?= $lang['postcode']; ?></label>
                <input type="text"
                       name="m_postcode"
                       maxlength="15"
                       id="m_postcode"
                       class="form-control"
                       autocomplete="off">
              </div>
            </div>
            <div class="col">
              <div class="form-group pb-2">
                <label for="m_country"
                       class="pb-2"><?= $lang['country']; ?></label>
                <select name="m_country"
                        id="m_country"
                        class="form-select">
                  <?php $helper->select_country($lang); ?></select>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <p></p>
  <!-- Hidden timezone field - populated by JavaScript based on address -->
  <input type="hidden" name="timezone" id="timezone" value="">
  <input type="hidden"
         name="dir"
         value="<?= $dir; ?>">
  <input type="hidden"
         name="page"
         value="<?= $page; ?>">
  <a href="list"
     class="btn btn-danger"
     role="button"
     aria-pressed="false"
     tabindex="0">
    <?= $lang['cancel']; ?></a>
  <button type="submit"
          class="btn btn-success"
          role="button"
          value="submit"
          tabindex="0">
    <?= $lang['submit']; ?></button>
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
    const stateField = document.getElementById('m_state');
    const countryField = document.getElementById('m_country');
    const timezoneField = document.getElementById('timezone');
    
    if (stateField && countryField && timezoneField) {
      const state = stateField.value;
      const country = countryField.value;
      const timezone = getTimezoneFromLocation(state, country);
      timezoneField.value = timezone;
    }
  }
  
  // Update timezone when state or country changes
  const stateField = document.getElementById('m_state');
  const countryField = document.getElementById('m_country');
  
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