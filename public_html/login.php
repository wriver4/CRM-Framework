<?php
//@setcookie( session_name(), "", time()-3600, '/');
//header('Cache-Control: no-store, no-cache, must-revalidate');
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
// Direct routing variables - these determine page navigation and template inclusion
$dir = 'public_html';
$page = 'login';

$table_page = false;
if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
  $browser_language = mb_substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2, 'UTF-8');
} else {
  $browser_language = 'en';
}

/*
if (isset($_SESSION['lang'])) {
  $browser_language = $_SESSION['lang'];
}
// Try to detect the locale
if( function_exists('locale_accept_from_http') ) {
    $localeFromHTTP = locale_accept_from_http($_SERVER['HTTP_ACCEPT_LANGUAGE']);
}
else {
    $explodeLocale = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    $localeFromHTTP = empty($explodeLocale[0])?'en_US':str_replace('-', '_', $explodeLocale[0]);
}
​if ( preg_match_all( "/$locale_re/i", isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '', $matches ) ) {
*/

//for testing
//$browser_language = 'es';
if (file_exists(LOGINLANG . '/' . $browser_language . '.php')) {
  require_once LOGINLANG . '/' . $browser_language . '.php';
} else {
  require_once LOGINLANG . '/en.php';
}

$title = $lang['login'];
$login_error_message = '';


if ($_SERVER["REQUEST_METHOD"] == "POST") {

  
  // public function ...
  if (!empty($_POST["login"]) && $_POST["username"] != '' && $_POST["password"] != '' && $_POST["username"] != 'system') {

    $username = htmlentities(trim($_POST['username']));
    $password = trim($_POST['password']);
    $status = 1;
    $sql = "SELECT * FROM users WHERE username= :username AND status= :status";
    $stmt = $dbcrm->prepare($sql);
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->bindValue(':status', $status, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch();
    if (!$result) {
      $login_error_message = $lang['username_or_password_invalid'];
    } else {
      if ($helper->verify_password($password, $result['password'])) {
        $_SESSION['ua'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 509);
        $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_id'] = $result['id']; // primary key
        $_SESSION['full_name'] = $result['full_name'];
        if (empty($result['role_id'])) {
          $_SESSION['permissions'] = ["1000"];
        } else {
          $_SESSION['permissions'] = $rolesperms->get_session_roles_permissions($result['role_id']);
        }
        $_SESSION['lang'] = isset($result['lang']) ? $result['lang'] : 'en';
        $_SESSION['loggedin'] = true;
        $_SESSION['refresh'] = true;
        $_SESSION['refresh_time'] = 60;
        $_SESSION['last_activity'] = time();
        $audit->log($result['id'], 'login', 'Admin Login page', $_SESSION['ua'], $_SESSION['ip'], $helper->country_by_ip(), null);
        header("location: /");
      } else {
        $login_error_message = $lang['username_or_password_invalid'];
      }
    }
  } else {
    $login_error_message = $lang['username_and_password_required'];
  }
}
//  return $login_error_message; }
require HEADER;
require BODY;
?>
<div class="wrapper">
  <form class="form-login"
        method="POST"
        action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"
        autocomplete="off">
    <div class="text-center mb-4">
      <img class="mb-4"
           src="<?= IMG . '/logo.svg' ?>"
           alt="CRM Logo"
           width="200"
           height="200">
      <h1 class="h3 mb-3 font-weight-normal text-white"><?= $lang['signin']; ?></h1>
    </div>
    <div class="form-group">
      <label for="username" class="visually-hidden"><?= $lang['username']; ?></label>
      <input type="text"
             name="username"
             maxlength="100"
             id="username"
             class="form-control"
             placeholder="<?= $lang['username']; ?>"
             autofocus="autofocus"
             tabindex="1">
    </div>
    <div class="form-group">
      <label for="password" class="visually-hidden"><?= $lang['password']; ?></label>
      <input type="password"
             name="password"
             maxlength="250"
             id="password"
             class="form-control"
             placeholder="<?= $lang['password']; ?>"
             tabindex="2">
    </div>
    <?php
    if ($login_error_message != '') {
      echo '<div class="alert alert-danger alert-dismissible fade show text-center" role="alert">'
        . $login_error_message
        . '</div>';
    }
    ?>
    <input type="hidden"
           name="dir"
           value="<?= $dir; ?>">
    <input type="hidden"
           name="page"
           value="<?= $page; ?>">
    <input type="hidden"
           name="login"
           value="login">
    <div class="d-grid pt-2">
      <button type="submit"
              id="login"
              class="btn btn-rounded btn-outline-light"
              tabindex="0"
              role="button"
              aria-pressed="false">
        <?= $lang['signin_button']; ?>
        <i class="fa fa-arrow-right"
           aria-hidden="true">
        </i></button>
    </div>
  </form>
</div>
<?php require FOOTER;
