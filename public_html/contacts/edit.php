<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$dir = 'contacts';
$page = 'edit';

$table_page = false;

require LANG . '/en.php';
$title = $lang['contact_edit'];

$title_icon = '<i class="fa-solid fa-address-book"></i>';

require 'get.php';

require HEADER;
require BODY;
require NAV;
require SECTIONOPEN;
?>
<form action="post.php"
      method="POST">
  <div class="row align-items-start">
    <div class="row">
      <div class="col">
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
      </div>
      <div class="col">
        <div class="form-group pb-2">
          <label for="call-order"
                 class="pb-1"><?= $lang['contact_call_order']; ?></label>
          <input type="number"
                 name="call_order"
                 step="1"
                 min="1"
                 max="10"
                 id="call-order"
                 class="form-control "
                 autocomplete="off">
        </div>
      </div>
      <div class="col">
        <div class="form-group pt-lg-4 float-end">
          <label> </label>
          <button type="button"
                  class="btn btn-info"
                  data-bs-toggle="modal"
                  data-bs-target="#call-order-list"
                  tabindex="0"
                  role="button"
                  aria-pressed="false"><?= $lang['contact_current_order_list_button'];?></button>
          <?php require 'call_order_list.php'; ?>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <div class="form-group pb-2">
          <label for="first_name"
                 class="pb-1 pt-1"><?= $lang['first_name']; ?></label>
          <input type="text"
                 name="first_name"
                 maxlength="100"
                 id="first_name"
                 class="form-control"
                 value="<?= $first_name; ?>"
                 readonly>
        </div>
      </div>
      <div class="col">
        <div class="form-group pb-2">
          <label for="family_name"
                 class="pb-1 pt-1"><?= $lang['family_name']; ?></label>
          <input type="text"
                 name="family_name"
                 maxlength="100"
                 id="family_name"
                 class="form-control"
                 value="<?= $family_name; ?>"
                 readonly>
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
                 value="<?= $cell_phone; ?>"
                 required
                 placeholder="<?= $lang['tel_pattern_us']; ?>"
                 autocomplete="off"
                 tabindex="1">
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
                 value="<?= $business_phone; ?>"
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
                 value="<?= $alt_phone; ?>"
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
                 value="<?= $personal_email; ?>"
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
                 value="<?= $business_email; ?>"
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
                 value="<?= $alt_email; ?>"
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
                     value="<?= $p_street_1; ?>"
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
                     value="<?= $p_street_2; ?>"
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
                         value="<?= $p_city; ?>"
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
                    <?php $helper->select_us_state($lang, $p_state); ?></select>
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
                         value="<?= $p_postcode; ?>"
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
                    <?php $helper->select_country($lang, $p_country); ?></select>
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
                     value="<?= $business_name; ?>"
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
                     value="<?= $b_street_1; ?>"
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
                     value="<?= $b_street_2; ?>"
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
                         value="<?= $b_city; ?>"
                         autocomplete="off">
                </div>
              </div>
              <div class="col">
                <div class="form-group pb-2">
                  <label for="b_state"
                         class="pb-1"><?= $lang['state']; ?></label>
                  <select name="b_state"
                          id="b_state"
                          class="form-select"
                          tabindex="23">
                    <?php $helper->select_us_state($lang, $b_state); ?></select>
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
                         value="<?= $b_postcode; ?>"
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
                    <?php $helper->select_country($lang, $b_country); ?></select>
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
             data-bs-parent="#accordian_addresses"
             tabindex="27">
          <div class="accordion-body">
            <div class="form-group pb-2">
              <label for="m_street_1"
                     class="pb-1"><?= $lang['street_address_1']; ?></label>
              <input type="text"
                     name="m_street_1"
                     maxlength="100"
                     id="m_street_1"
                     class="form-control"
                     value="<?= $m_street_1; ?>"
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
                     value="<?= $m_street_2; ?>"
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
                         value="<?= $m_city; ?>"
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
                    <?php $helper->select_us_state($lang, $m_state); ?></select>
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
                         value="<?= $m_postcode; ?>"
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
                    <?php $helper->select_country($lang, $m_country); ?></select>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
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
       class="btn btn-danger"
       tabindex="0"
       role="button"
       aria-pressed="false"><?= $lang['cancel']; ?></a>
    <button type="submit"
            class="btn btn-success"
            value="Submit"
            tabindex="0"
            role="button"
            aria-pressed="false"><?= $lang['submit_edit']; ?></button>
</form>
<?php
require SECTIONCLOSE;
require FOOTER;