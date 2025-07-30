<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$dir = 'users';
$page = 'delete';

$table_page = false;

require LANG . '/en.php';
$title = $lang['user_delete'];

$title_icon = '<i class="fa-solid fa-user-slash" aria-hidden="true"></i>';

require 'get.php';

require HEADER;
require BODY;
require NAV;
require SECTIONOPEN;
?>
<form action="post.php"
      method="POST">
  <div class="form-group pb-1">
    <label for="full_name"
           class="pb-1 pt-1"><?= $lang['full_name']; ?></label>
    <input type="text"
           name="full_name"
           id="full_name"
           class="form-control"
           placeholder="<?= $lang['full_name']; ?>"
           value="<?= $full_name; ?>"
           readonly>
  </div>
  <div class="form-group pb-1">
    <label for="username"
           class="pb-1 pt-1"><?= $lang['username']; ?></label>
    <input type="text"
           name="username"
           id="username"
           class="form-control"
           placeholder="<?= $lang['username']; ?>"
           value="<?= $username; ?>"
           readonly>
  </div>
  <div class="form-group pb-1">
    <label for="password"
           class="pb-1 pt-1"><?= $lang['password']; ?></label>
    <input type="password"
           name="password"
           id="password"
           class="form-control"
           placeholder="<?= $lang['password']; ?>"
           value="<?= $password; ?>"
           readonly>
  </div>
  <div class="form-group pb-1">
    <label for="rid"
           class="pb-1 pt-1"><?= $lang['rname']; ?></label>
    <input type="text"
           name="rid"
           id="rid"
           class="form-control"
           placeholder="<?= $lang['rname']; ?>"
           value="<?= $rid; ?>"
           readonly>
  </div>
  <div class="form-group pb-1">
    <label for="email"
           class="pb-1 pt-1"><?= $lang['email'];; ?></label>
    <input type="email"
           name="email"
           id="email"
           class="form-control"
           placeholder="<?= $lang['email']; ?>"
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
           placeholder="<?= $lang['status']; ?>"
           value="<?= $status; ?>"
           readonly>
  </div>
  <div class="form-group pb-1">
    <label for="updated_at"
           class="pb-1 pt-1"><?= $lang['updated_at']; ?></label>
    <input type="text"
           name="updated_at"
           id="updated_at"
           class="form-control"
           placeholder="<?= $lang['updated_at']; ?>"
           value="<?= $updated_at; ?>"
           readonly>
  </div>
  <div class="form-group pb-1">
    <label for="created_at"
           class="pb-1 pt-1"><?= $lang['created_at']; ?></label>
    <input type="text"
           name="created_at"
           id="created_at"
           class="form-control"
           placeholder="<?= $lang['created_at']; ?>"
           value="<?= $created_at; ?>"
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
