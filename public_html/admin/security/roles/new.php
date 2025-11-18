<?php
require_once '/mnt/Local/newwgadmin.local/config/system.php';

// Direct routing variables - these determine page navigation and template inclusion
$dir = basename(dirname(__FILE__));
$page = substr(basename(__FILE__), 0, strrpos(basename(__FILE__), '.'));
$title = "New Role";
$table_page = false;

$title_icon = '<i class="fa fa-user" aria-hidden="true"></i>';

$role_id = "";
$role = "";

$label_role_id = "Role ID";
$label_role = "Role Name";
$label_submit = "Submit";
$label_cancel = "Cancel";

$aria_label_role_id = "Role ID";
$aria_label_role = "Role Name";
$aria_label_submit = "Submit";
$aria_label_cancel = "Cancel";


try {
  $pdo = new PDO($dsn, $db_user, $db_password, $options);
} catch (\PDOException $e) {
  throw new \PDOException($e->getMessage(), (int)$e->getCode());
  error_log($e->getMessage());
  exit('Something weird happened');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $id = trim($_POST["id"]);
  $role_id = trim($_POST["role_id"]);
  $role = trim($_POST["role"]);

  $sql = "INSERT INTO roles (role_id, role) VALUES (:role_id, :role)";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(':role_id', $role_id, PDO::PARAM_INT);
  $stmt->bindValue(':role', $role, PDO::PARAM_STR);
  if ($stmt->execute()) {
    $stmt = null;
    header("location: list.php");
  } else {
    echo "Something went wrong. Please try again later.";
  }
}
require_once HEADER;
require_once BODY;
require_once NAV;
require_once SECTIONOPEN;
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
           autofocus=" autofocus"
           tabindex="1">
  </div>
  <div class="form-group pb-1">
    <label for="role"
           class="required pb-1"><?php echo $label_role; ?></label>
    <input type="text"
           required=""
           name="role"
           maxlength="100"
           id="role"
           class="form-control"
           placeholder="<?php echo $label_role; ?>">
  </div>
  <p></p>
  <button type="submit"
          class="btn btn-success"
          value="submit"
          tabindex="0"
          role="button"
          aria-pressed="false"><?php echo $label_submit; ?></button>
  <a href="list.php"
     class="btn btn-danger"
     tabindex="0"
     role="button"
     aria-pressed="false"><i class="fa fa-arrow-left"
       aria-hidden="true"></i> <?php echo $label_cancel; ?></a>
</form>
<?php
require_once SECTIONCLOSE;
require_once FOOTER;
