<?php
require_once dirname(__DIR__) . '/config/system.php';

// Simulate actual form submission
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['login'] = 'Login';
$_POST['username'] = 'superadmin';
$_POST['password'] = 'testpass123';
$_POST['dir'] = 'public_html';
$_POST['page'] = 'login';

$login_error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
      $login_error_message = "User not found";
    } else {
      if ($helper->verify_password($password, $result['password'])) {
        // Would set session here
        $login_error_message = "PASSWORD VERIFIED - LOGIN SUCCESS - WOULD REDIRECT TO /";
      } else {
        $login_error_message = "Password verification FAILED";
      }
    }
  } else {
    $login_error_message = "Missing credentials";
  }
}

echo $login_error_message;
?>
