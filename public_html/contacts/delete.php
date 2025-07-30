<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$dir = 'contacts';
$page = 'delete';

$table_page = false;

require LANG . '/en.php';
$title = $lang['contact_delete'];
$title_icon = '<i class="fa-solid fa-address-book"></i>';

require 'get.php';

require HEADER;
require BODY;
require NAV;
require SECTIONOPEN;
?>
<form action="post.php"
      method="POST">
  <div class="form-group pb-2">
    <label for="prop_id"
           class="pb-1"><?= $lang['prop_id']; ?></label>
    <input type="text"
           name="prop_id"
           id="prop_id"
           class="form-control"
           value="<?= $result['prop_id']; ?>"
           readonly>
  </div>
  <div class="form-group pb-2">
    <label for="ctype"
           class="pb-1"><?= $lang['ctype']; ?></label>
    <input type="text"
           name="ctype"
           id="ctype"
           class="form-control"
           value="<?= $helper->get_contact_type($lang, $result['ctype']) ?>"
           readonly>
  </div>
  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="first_name"
               class="pb-1 pt-1"><?= $lang['first_name']; ?></label>
        <input type="text"
               name="first_name"
               id="first_name"
               class="form-control"
               value="<?= $result['first_name']; ?>"
               readonly>
      </div>
    </div>
    <div class="col">
      <div class="form-group pb-2">
        <label for="family_name"
               class="pb-1 pt-1"><?= $lang['family_name']; ?></label>
        <input type="text"
               name="family_name"
               id="family_name"
               class="form-control"
               value="<?= $result['family_name']; ?>"
               readonly>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="cell_phone"
               class="pb-1 pt-1"><?= $lang['cell_phone']; ?></label>
        <input type="tel"
               name="cell_phone"
               id="cell_phone"
               class="form-control"
               value="<?= $result['cell_phone']; ?>"
               readonly>
      </div>
    </div>
    <div class="col">
      <div class="form-group pb-2">
        <label for="business_phone"
               class="pb-1 pt-1"><?= $lang['business_phone']; ?></label>
        <input type="tel"
               name="business_phone"
               id="business_phone"
               class="form-control"
               value="<?= $result['business_phone']; ?>"
               readonly>
      </div>
    </div>
    <div class="col">
      <div class="form-group pb-2">
        <label for="alt_phone"
               class="pb-1 pt-1"><?= $lang['alt_phone']; ?></label>
        <input type="tel"
               name="alt_phone"
               id="alt_phone"
               class="form-control"
               value="<?= $result['alt_phone']; ?>"
               readonly>
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
               id="personal_email"
               class="form-control"
               value="<?= $result['personal_email']; ?>"
               readonly>
      </div>
    </div>
    <div class="col">
      <div class="form-group pb-2">
        <label for="business_email"
               class="pb-1"><?= $lang['business_email']; ?></label>
        <input type="email"
               name="business_email"
               id="business_email"
               class="form-control"
               value="<?= $result['business_email']; ?>"
               readonly>
      </div>
    </div>
    <div class="col">
      <div class="form-group pb-2">
        <label for="alt_email"
               class="pb-1"><?= $lang['alt_email']; ?></label>
        <input type="email"
               name="alt_email"
               id="alt_email"
               class="form-control"
               value="<?= $result['alt_email']; ?>"
               readonly>
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
                aria-controls="address-personal"
                readonly>
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
                   id="p_street_1"
                   class="form-control"
                   value="<?= $result['p_street_1']; ?>"
                   readonly>
          </div>
          <div class="form-group pb-2">
            <label for="p_street_2"
                   class="pb-1"><?= $lang['street_address_2']; ?></label>
            <input type="text"
                   name="p_street_2"
                   id="p_street_2"
                   class="form-control"
                   value="<?= $result['p_street_2']; ?>"
                   readonly>
          </div>
          <div class="form-group pb-2">
            <label for="p_city"
                   class="pb-1"><?= $lang['city']; ?></label>
            <input type="text"
                   name="p_city"
                   id="p_city"
                   class="form-control"
                   value="<?= $result['p_city']; ?>"
                   readonly>
          </div>
          <div class="row">
            <div class="col">
              <div class="form-group pb-2">
                <label for="p_state"
                       class="pb-1"><?= $lang['state']; ?></label>
                <input type="text"
                       name="p_state"
                       id="p_state"
                       class="form-control"
                       value="<?= $result['p_state']; ?>"
                       readonly>
              </div>
            </div>
            <div class="col">
              <div class="form-group pb-2">
                <label for="p_postcode"
                       class="pb-1"><?= $lang['postcode']; ?></label>
                <input type="text"
                       name="p_postcode"
                       id="p_postcode"
                       class="form-control"
                       value="<?= $result['p_postcode']; ?>"
                       readonly>
              </div>
            </div>
          </div>
          <div class="form-group pb-2">
            <label for="p_country"
                   class="pb-2"><?= $lang['country']; ?></label>
            <input type="text"
                   name="p_country"
                   id="p_country"
                   class="form-control"
                   value="<?= $result['p_country']; ?>"
                   readonly>
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
                aria-controls="address-business"
                readonly>
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
                   id="business_name"
                   class="form-control"
                   value="<?= $result['business_name']; ?>"
                   readonly>
          </div>
          <div class="form-group pb-2">
            <label for="b_street_1"
                   class="pb-1"><?= $lang['street_address_1']; ?></label>
            <input type="text"
                   name="b_street_1"
                   id="b_street_1"
                   class="form-control"
                   value="<?= $result['b_street_1']; ?>"
                   readonly>
          </div>
          <div class="form-group pb-2">
            <label for="b_street_2"
                   class="pb-1"><?= $lang['street_address_2']; ?></label>
            <input type="text"
                   name="b_street_2"
                   id="b_street_2"
                   class="form-control"
                   value="<?= $result['b_street_2']; ?>"
                   readonly>
          </div>
          <div class="form-group pb-2">
            <label for="b_city"
                   class="pb-1"><?= $lang['city']; ?></label>
            <input type="text"
                   name="b_city"
                   id="b_city"
                   class="form-control"
                   value="<?= $result['b_city']; ?>"
                   readonly>
          </div>
          <div class="row">
            <div class="col">
              <div class="form-group pb-2">
                <label for="b_state"
                       class="pb-1"><?= $lang['state']; ?></label>
                <input type="text"
                       name="b_state"
                       id="b_state"
                       class="form-control"
                       value="<?= $result['b_state']; ?>"
                       readonly>
              </div>
            </div>
            <div class="col">
              <div class="form-group pb-2">
                <label for="b_postcode"
                       class="pb-1"><?= $lang['postcode']; ?></label>
                <input type="text"
                       name="b_postcode"
                       id="b_postcode"
                       class="form-control"
                       value="<?= $result['b_postcode']; ?>"
                       readonly>
              </div>
            </div>
          </div>
          <div class="form-group pb-2">
            <label for="b_country"
                   class="pb-2"><?= $lang['country']; ?></label>
            <input type="text"
                   name="b_country"
                   id="b_country"
                   class="form-control"
                   value="<?= $result['b_country']; ?>"
                   readonly>
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
                aria-controls="address-mailing"
                readonly>
          <?= $lang['mailing']; ?></button>
      </h2>
      <div id="address-mailing"
           class="accordion-collapse collapse"
           aria-labelledby="headingThree"
           data-bs-parent="#accordian_addresses"
           readonly>
        <div class="accordion-body">
          <div class="form-group pb-2">
            <label for="m_street_1"
                   class="pb-1"><?= $lang['street_address_1']; ?></label>
            <input type="text"
                   name="m_street_1"
                   id="m_street_1"
                   class="form-control"
                   value="<?= $result['m_street_1']; ?>"
                   readonly>
          </div>
          <div class="form-group pb-2">
            <label for="m_street_2"
                   class="pb-1"><?= $lang['street_address_2']; ?></label>
            <input type="text"
                   name="m_street_2"
                   id="m_street_2"
                   class="form-control"
                   value="<?= $result['m_street_2']; ?>"
                   readonly>
          </div>
          <div class="form-group pb-2">
            <label for="city"
                   class="pb-1"><?= $lang['city']; ?></label>
            <input type="text"
                   name="m_city"
                   id="m_city"
                   class="form-control"
                   value="<?= $result['m_city']; ?>"
                   readonly>
          </div>
          <div class="row">
            <div class="col">
              <div class="form-group pb-2">
                <label for="m_state"
                       class="pb-1"><?= $lang['state']; ?></label>
                <input type="text"
                       name="m_state"
                       id="m_state"
                       class="form-control"
                       value="<?= $result['m_state']; ?>"
                       readonly>
              </div>
            </div>
            <div class="col">
              <div class="form-group pb-2">
                <label for="m_postcode"
                       class="pb-1"><?= $lang['postcode']; ?></label>
                <input type="text"
                       name="m_postcode"
                       id="m_postcode"
                       class="form-control"
                       value="<?= $result['m_postcode']; ?>"
                       readonly>
              </div>
            </div>
          </div>
          <div class="form-group pb-2">
            <label for="m_country"
                   class="pb-2"><?= $lang['country']; ?></label>
            <input type="text"
                   name="m_country"
                   id="m_country"
                   class="form-control"
                   readonly>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="updated_at"
               class="pb-1 pt-1"><?= $lang['updated_at']; ?></label>
        <input type="text"
               name="updated_at"
               id="updated_at"
               class="form-control"
               value="<?= $result['updated_at']; ?>"
               readonly>
      </div>
    </div>
    <div class="col">
      <div class="form-group pb-2">
        <label for="created_at"
               class="pb-1 pt-1"><?= $lang['created_at']; ?></label>
        <input type="text"
               name="created_at"
               id="created_at"
               class="form-control"
               value="<?= $result['created_at']; ?>"
               readonly>
      </div>
    </div>
  </div>
  <div class="form-group pb-2">
    <label for="status"
           class="pb-1"><?= $lang['status']; ?></label>
    <input type="text"
           name="status"
           id="status"
           class="form-control"
           value="<?= $helper->get_status($result["status"]); ?>"
           readonly>
  </div>
  <p></p>
  <input type="hidden"
         name="id"
         value="<?= $id; ?>">
  <input type="hidden"
         name="dir"
         value="<?= $dir; ?>">
  <input type="hidden"
         name="page"
         value="<?= $page; ?>">
  <a href="list"
     class="btn btn-success">
    <?= $lang['back']; ?></a>
  <button type="submit"
          class="btn btn-danger"
          value="Submit">
    <?= $lang['delete']; ?></button>
</form>
<?php
require SECTIONCLOSE;
require FOOTER;
