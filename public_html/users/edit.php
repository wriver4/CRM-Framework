<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
// Direct routing variables - these determine page navigation and template inclusion
$dir = "users";
$subdir='';
$page = "edit";

$table_page = false;

require LANG . '/en.php';
$title = $lang['user_edit'];
$title_icon = '<i class="fa-solid fa-user-pen" aria-hidden="true"></i>';

//require 'get.php';

require HEADER;
require BODY;
require NAV;
require SECTIONOPEN;
$form ="profile";
require 'get.php';
?>
<h3><?= $lang['user_profile_edit']; ?></h3>
<form action="post.php"
      method="POST">
  <div class="row">
    <div class="col">
      <div class="form-group pb-1">
        <label for="full_name"
               class="required pb-1 pt-1"><?= $lang['full_name']; ?></label>
        <input type="text"
               name="full_name"
               maxlength="100"
               id="full_name"
               class="form-control"
               value="<?= $full_name; ?>"
               tabindex="1">
      </div>
    </div>
    <div class="col">
      <div class="form-group pb-1">
        <label for="username"
               class="pb-1 pt-1"><?= $lang['username']; ?></label>
        <input type="text"
               disabled
               name="username"
               maxlength="100"
               id="username"
               class="form-control"
               value="<?= $username; ?>">
      </div>
    </div>
  </div>
  <div class="form-group pb-1">
    <label for="password"
           class="required pb-1 pt-1"><?= $lang['password']; ?></label>
    <input type="password"
           <?= in_array(8, $_SESSION['permissions']) ? '' : 'disabled'; ?>
           name="password"
           maxlength="250"
           id="password"
           class="form-control"
           value="<?= $password; ?>">
  </div>
  <div class="form-group pb-1">
    <label for="rid"
           class="required pb-1 pt-1"><?= $lang['rname']; ?></label>
    <select name="rid"
            class="form-select"
            id="rid">
      <?php $helper->select_role($lang, $rid); ?></select>
  </div>
  <div class="form-group pb-1">
    <label for="email"
           class="pb-1 pt-1"><?= $lang['email']; ?></label>
    <input type="email"
           pattern="<?= VALIDEMAIL; ?>"
           name="email"
           maxlength="250"
           id="email"
           class="form-control"
           value="<?= $email; ?>">
  </div>
  <div class="form-group pb-1">
    <label for="status"
           class="pb-1 pt-1"><?= $lang['status']; ?></label>
    <input type="text"
           disabled
           name="status"
           maxlength="100"
           id="status"
           class="form-control"
           value="<?php echo $helper->get_status($status); ?>">
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
  <input type="hidden"
         name="form_name"
         value="user_profile">
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