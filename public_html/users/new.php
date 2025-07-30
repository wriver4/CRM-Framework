<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$dir = "users";
$page = "new";

$table_page = false;

$last_user_id = $users->last_row_id() + 1;

require LANG . '/en.php';
$title = $lang['user_new'];

$title_icon = '<i class="fa fa-user-plus" aria-hidden="true"></i>';

require HEADER;
require BODY;
require NAV;
require SECTIONOPEN;
?>
<form action="post.php"
      method="POST"
      autocomplete="off">
  <div class="form-group pb-2">
    <label for="rid"
           class="required pb-1"><?= $lang['rname']; ?></label>
    <select name="rid"
            id="rid"
            class="form-select"
            required=""
            autocomplete="off"
            autofocus
            tabindex="1">
      <?php $helper->select_role($lang); ?></select>
  </div>
  <div class="row">
    <div class="col">
      <div class="form-group pb-2">
        <label for="prop_id"
               class="required pb-1 pt-1"><?= $lang['prop_id']; ?></label>
        <input type="text"
               name="prop_id"
               id="prop_id"
               class="form-control"
               placeholder="<?= $lang['new_user_prop_id_placeholder']; ?>"
               required=""
               autocomplete="off"
               tabindex="2">
      </div>
    </div>
    <div class="col">
      <div class="form-group pb-2">
        <label for="find_prop_id"
               class="pb-2"><?= $lang['find_prop_id']; ?></label>
        <select id="prop_id"
                class="form-select"
                autocomplete="off"
                readonly="readonly"
                tabindex="-1">
          <?php $helper->find_property_id($lang); ?></select>
      </div>
    </div>
  </div>
  <div class="form-group pb-2">
    <label for="full_name"
           class="required pb-1 pt-1"><?= $lang['full_name']; ?></label>
    <input type="text"
           name="full_name"
           maxlength="100"
           id="full_name"
           class="form-control"
           required=""
           autocomplete="off"
           tabindex="3">
  </div>
  <div class="form-group pb-2">
    <label for="username"
           class="required pb-1"><?= $lang['username']; ?></label>
    <div id="username_note"
         class="form-text">
      <span class="text-warning">
        <?= $lang['username_note']; ?>
      </span>
    </div>
    <input type="text"
           name="username"
           maxlength="100"
           id="username"
           class="form-control"
           required=""
           autocomplete="off"
           tabindex="4">
  </div>
  <div class="form-group pb-2">
    <label for="email"
           class="pb-1"><?= $lang['email']; ?></label>
    <input type="email"
           pattern="<?= VALIDEMAIL; ?>"
           name="email"
           maxlength="250"
           id="email"
           class="form-control"
           autocomplete="off"
           tabindex="5">
  </div>
  <div class="form-group pb-2">
    <label for="password"
           class="required pb-1"><?= $lang['password']; ?></label>
    <div id="password_note"
         class="form-text">
      <?php echo '<span class="text-warning">'
                            . $lang['password_note_1']
                            . '<span class="text-dark">'
                            . $last_user_id
                            . '</span>'
                            . '"<br>'
                            . $lang['password_note_2']
                            . '</span>'
                            . '&emsp;'
                            . '<span class="text-info">'
                            . $lang['password_note_3']
                            . '</span>';
                     ?>
    </div>
    <input type="password"
           name="password"
           maxlength="250"
           id="password"
           class="form-control"
           required=""
           autocomplete="off"
           tabindex="6">

  </div>
  <p></p>
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
     tabindex="7">
    <?= $lang['cancel']; ?></a>
  <button type="submit"
          class="btn btn-success"
          role="button"
          value="submit"
          tabindex="8">
    <?= $lang['submit']; ?></button>
</form>
<?php
require SECTIONCLOSE;
require FOOTER;
