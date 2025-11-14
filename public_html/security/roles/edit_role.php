<?php
require '/home/adminrnd/config/system.php';
$dir = basename(dirname(__FILE__));
$page = substr(basename(__FILE__), 0, strrpos(basename(__FILE__), '.'));
// page type
$table_page = false;
//form page variables

$title = "Edit A Role";
$title_icon = '';

$password = "";
$fullname = "";
$role = "";
$email = "";
$status = "";

$label_username = "Username";
$label_password = "Password";
$label_fullname = "Full Name";
$label_role = "Role";
$label_role_id = "Role ID";
$label_role_name = "Role Name";
$label_email = "Email";
$label_submit = "Submit";
$label_cancel = "Cancel";

$aria_label_username = "Username";
$aria_label_password = "Password";
$aria_label_fullname = "Full Name";
$aria_label_role = "User Role Select";
$aria_label_role_id = "Role ID";
$aria_label_role_name = "Role Name";
$aria_label_email = "Email";
$aria_label_submit = "Submit";
$aria_label_cancel = "Cancel";

$username_err = "";
$password_err = "";
$fullname_err = "";
$role_err = "";
$email_err = "";
$status_err = "";


try {
  $pdo = new PDO($dsn, $db_user, $db_password, $options);
} catch (Exception $e) {
  error_log($e->getMessage());
  exit('Something weird happened');
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
  $id = $_GET["id"];
  $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
  $stmt->execute([$id]);
  $user = $stmt->fetch();
  $stmt = null;
  $fullname = $user["fullname"];
  $username = $user["username"];
  $password = $user["password"];
  $email = $user["email"];
  $role = $user["role"];
  $status = $user["status"];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $id = trim($_POST["id"]);
  $role_id = trim($_POST["role_id"]);
  $role_name = trim($_POST["role"]);;
  $sql = "UPDATE users SET ";
    $stmt->bindValue(':role_id', $role_id, PDO::PARAM_INT);
    $stmt->bindValue(':role', $role_name, PDO::PARAM_STR);
  if ($stmt->execute()) {
    header("location: list.php");
    exit();
  } else {
    echo "Something went wrong. Please try again later.";
  }
  $stmt = null;
}

require HEADER;
require BODY;
require NAV;
require SECTIONOPEN;
?>
<form name="new-role"
      action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"
      method="POST">
  <div class="form-group pb-1">
    <label for="role_id"
           class="required pb-1 pt-1"><?php echo $label_role_id; ?></label>
    <input type="text"
           required=""
           name="role_id"
           maxlength="100"
           id="role_id"
           class="form-control"
           placeholder="<?php echo $label_role_id; ?>"
           autofocus="autofocus"
           tabindex="1">
  </div>
  <div class="form-group pb-1">
    <label for="role"
           class="required pb-1"><?php echo $label_role_name; ?></label>
    <input type="text"
           required=""
           name="role"
           maxlength="100"
           id="role"
           class="form-control"
           placeholder="<?php echo $label_role_name; ?>">
  </div>
  <p></p>
  <button type="submit"
          class="btn btn-success"
          value="submit"
          tabindex="0"
          role="button"
          aria-pressed="false"><?php echo $label_submit; ?></button>
  <a href="list"
     class="btn btn-danger"
     tabindex="0"
     role="button"
     aria-pressed="false">
    <i class="fa fa-arrow-left"
       aria-hidden="true"></i> <?php echo $label_cancel; ?></a>
</form>
<?php
require SECTIONCLOSE;
require FOOTER;
