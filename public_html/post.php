<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
// client roles #'s
$rid_denied = [18, 19, 20, 21, 22];

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['dir'] == 'public_html' && $_POST['dir'] == 'login') {
  // public function ...
  if (!empty($_POST["login"]) && $_POST["username"] != '' && $_POST["password"] != '' && $_POST["username"] != 'system') {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $status = 1;
    $sql = "SELECT * FROM users WHERE username= :username AND status= :status";
    $stmt = $dbadmin->prepare($sql);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':status', $status, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch();

    if (!$result || in_array($result['rid'], $rid_denied)) {
      $login_error_message = $lang['not_valid_login'];
    } else {
      if ($helper->verify_password($password, $result['password'])) {
        $_SESSION['ua'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 509);
        $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_id'] = $result['id']; // primary key
        $_SESSION['full_name'] = $result['full_name'];
        $_SESSION['permissions'] = $rolesperms->get_session_roles_permissions($result['rid']);
        $_SESSION['lang'] = isset($result['lang']) ? $result['lang'] : 'en';
        $_SESSION['loggedin'] = true;
        $_SESSION['refresh'] = true;
        $_SESSION['refresh_time'] = 60;
        $_SESSION['last_activity'] = time();
        $audit->log($result['id'], 'login', 'Admin Login page', $_SESSION['ua'], $_SESSION['ip'], country_by_ip(), null);
        // send manifest file to browser
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
