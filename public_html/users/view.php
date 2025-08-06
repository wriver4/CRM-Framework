<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$dir = "users";
$subdir='';
$page = "view";

$table_page = false;

require LANG . '/en.php';
$title = $lang['user_view'];

$title_icon = '<i class="fa fa-user" aria-hidden="true"></i>';

require 'get.php';

require HEADER;
require BODY;
require NAV;
require SECTIONOPEN;
?>
<form method="GET">
  <div class="form-group pb-1">
    <label for="rname"
           class="pb-1 pt-1"><?= $lang['rname']; ?></label>
    <input type="text"
           name="rname"
           id="rname"
           class="form-control"
           value="<?= $rname; ?>"
           readonly>
  </div>
  <div class="row">
    <div class="form-group pb-1">
      <label for="full_name"
             class="pb-1 pt-1">
        <?= $lang['full_name']; ?></label>
      <input type="text"
             name="full_name"
             id="full_name"
             class="form-control"
             value="<?= $full_name; ?>"
             readonly>
    </div>
    <div class="form-group pb-1">
      <label for="username"
             class="pb-1 pt-1">
        <?= $lang['username']; ?></label>
      <input type="text"
             name="username"
             id="username"
             class="form-control"
             value="<?= $username; ?>"
             readonly>
    </div>
    <div class="form-group pb-1">
      <label for="password"
             class="pb-1 pt-1">
        <?= $lang['password']; ?></label>
      <input type="password"
             name="password"
             id="password"
             class="form-control"
             value="<?= $password; ?>"
             readonly>
    </div>
    <div class="form-group pb-1">
      <label for="email"
             class="pb-1 pt-1"><?= $lang['email']; ?></label>
      <input type="email"
             name="email"
             id="email"
             class="form-control"
             value="<?= $email; ?>"
             readonly>
    </div>
    <div class="form-group pb-1">
      <label for="status"
             class="pb-1 pt-1"><?= $lang['status']; ?></label>
      <input type="text"
             name="status"
             id="status"
             class="form-control"
             value="<?= $helper->get_status($status); ?>"
             readonly>
    </div>
    <div class="row">
      <div class="col">
        <div class="form-group pb-1">
          <label for="updated_at"
                 class="pb-1 pt-1"><?= $lang['updated_at']; ?></label>
          <input type="text"
                 name="updated_at"
                 id="updated_at"
                 class="form-control"
                 value="<?= $updated_at; ?>"
                 readonly>
        </div>
      </div>
      <div class="col">
        <div class="form-group pb-1">
          <label for="created_at"
                 class="pb-1 pt-1"><?= $lang['created_at']; ?></label>
          <input type="text"
                 name="created_at"
                 id="created_at"
                 class="form-control"
                 value="<?= $created_at; ?>"
                 readonly>
        </div>
      </div>
    </div>
    <p></p>
    <input type="hidden"
           name="dir"
           value="<?= $dir; ?>">
    <input type="hidden"
           name="page"
           value="<?= $page; ?>">
    <a href="list"
       class="btn btn-danger">
      <?= $lang['back']; ?></a>
</form>
<?php
require SECTIONCLOSE;
require FOOTER;