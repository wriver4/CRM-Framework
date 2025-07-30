<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$dir = 'help';
$subdir = '';
$page = 'view';

$table_page = false;

require LANG . '/en.php';
$title = $lang['help'];

$title_icon = '<i class="fa-regular fa-circle-question"></i>';

require 'get.php';

require HEADER;
require BODY;
require NAV;
require SECTIONOPEN;
?>

<div class="accordion pt-3"
     id="accordian_help">
  <div class="accordion-item">
    <h2 class="accordion-header"
        id="accordian-help-app">
      <button class="accordion-button collapsed"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#help-app"
              aria-expanded="false"
              aria-controls="common">
        <?= $lang['help_this_app']; ?></button>
    </h2>
    <div id="help-app"
         class="accordion-collapse collapse"
         aria-labelledby="common"
         data-bs-parent="#help-app">
      <div class="accordion-body">
        <?php require 'this_app.php'; ?>
      </div>
    </div>
  </div>
  <div class="accordion-item">
    <h2 class="accordion-header"
        id="accordian-common">
      <button class="accordion-button collapsed"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#help-common"
              aria-expanded="false"
              aria-controls="common">
        <?= $lang['help_common_elements']; ?></button>
    </h2>
    <div id="help-common"
         class="accordion-collapse collapse"
         aria-labelledby="common"
         data-bs-parent="#help-common">
      <div class="accordion-body">
        <?php require 'common_elements.php'; ?>
      </div>
    </div>
  </div>
  <div class="accordion-item">
    <h2 class="accordion-header"
        id="accordian-status">
      <button class="accordion-button collapsed"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#help-status"
              aria-expanded="false"
              aria-controls="status">
        <?= $lang['status']; ?></button>
    </h2>
    <div id="help-status"
         class="accordion-collapse collapse"
         aria-labelledby="status"
         data-bs-parent="#help-status">
      <div class="accordion-body">
        <?php require 'status.php'; ?>
      </div>
    </div>
  </div>
  <div class="accordion-item">
    <h2 class="accordion-header"
        id="accordian-tickets">
      <button class="accordion-button collapsed"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#help-tickets"
              aria-expanded="false"
              aria-controls="tickets">
        <?= $lang['tickets']; ?></button>
    </h2>
    <div id="help-tickets"
         class="accordion-collapse collapse"
         aria-labelledby="tickets"
         data-bs-parent="#help-tickets">
      <div class="accordion-body">
        <?php require 'tickets.php'; ?>
      </div>
    </div>
  </div>
  <div class="accordion-item">
    <h2 class="accordion-header"
        id="accordian-properties">
      <button class="accordion-button collapsed"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#help-properties"
              aria-expanded="false"
              aria-controls="properties">
        <?= $lang['properties']; ?></button>
    </h2>
    <div id="help-properties"
         class="accordion-collapse collapse"
         aria-labelledby="properties"
         data-bs-parent="#help-properties">
      <div class="accordion-body">
        <?php require 'properties.php'; ?>
      </div>
    </div>
  </div>
  <div class="accordion-item">
    <h2 class="accordion-header"
        id="accordian-contacts">
      <button class="accordion-button collapsed"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#help-contacts"
              aria-expanded="false"
              aria-controls="contacts">
        <?= $lang['contacts']; ?></button>
    </h2>
    <div id="help-contacts"
         class="accordion-collapse collapse"
         aria-labelledby="contacts"
         data-bs-parent="#help-contacts">
      <div class="accordion-body">
        <?php require 'contacts.php'; ?>
      </div>
    </div>
  </div>
  <div class="accordion-item">
    <h2 class="accordion-header"
        id="accordian-systems">
      <button class="accordion-button collapsed"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#help-systems"
              aria-expanded="false"
              aria-controls="systems">
        <?= $lang['systems']; ?></button>
    </h2>
    <div id="help-systems"
         class="accordion-collapse collapse"
         aria-labelledby="systems"
         data-bs-parent="#help-systems">
      <div class="accordion-body">
        <?php require 'systems.php'; ?>
      </div>
    </div>
  </div>
  <?php if (in_array(8, $_SESSION['permissions'])) : ?>
  <div class="accordion-item">
    <h2 class="accordion-header"
        id="accordian-users">
      <button class="accordion-button collapsed"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#help-users"
              aria-expanded="false"
              aria-controls="users">
        <?= $lang['users']; ?></button>
    </h2>
    <div id="help-users"
         class="accordion-collapse collapse"
         aria-labelledby="users"
         data-bs-parent="#help-users">
      <div class="accordion-body">
        <?php require 'users.php'; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>
  <div class="accordion-item">
    <h2 class="accordion-header"
        id="accordian-testing">
      <button class="accordion-button collapsed"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#help-testing"
              aria-expanded="false"
              aria-controls="testing">
        <?= $lang['testing']; ?></button>
    </h2>
    <div id="help-testing"
         class="accordion-collapse collapse"
         aria-labelledby="testing"
         data-bs-parent="#help-testing">
      <div class="accordion-body">
        <?php require 'testing.php'; ?>
      </div>
    </div>
  </div>
  <div class="accordion-item">
    <h2 class="accordion-header"
        id="accordian-reports">
      <button class="accordion-button collapsed"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#help-reports"
              aria-expanded="false"
              aria-controls="reports">
        <?= $lang['reports']; ?></button>
    </h2>
    <div id="help-reports"
         class="accordion-collapse collapse"
         aria-labelledby="reports"
         data-bs-parent="#help-reports">
      <div class="accordion-body">
        <?php require 'reports.php'; ?>
      </div>
    </div>
  </div>
  <?php if (in_array(8, $_SESSION['permissions'])) : ?>
  <div class="accordion-item">
    <h2 class="accordion-header"
        id="accordian-administration">
      <button class="accordion-button collapsed"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#help-administration"
              aria-expanded="false"
              aria-controls="administration">
        <?= $lang['help_administration']; ?></button>
    </h2>
    <div id="help-administration"
         class="accordion-collapse collapse"
         aria-labelledby="administration"
         data-bs-parent="#help-administration">
      <div class="accordion-body">
        <?php require 'administration.php'; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php
require SECTIONCLOSE;
require FOOTER;
