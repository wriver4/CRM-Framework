<?php
require_once '/mnt/Local/newwgadmin.local/config/system.php';
$title = "Assign Role Permissions";
$table_page = false;
$page = basename(dirname(__FILE__));
$title_icon = '<i class="fa-solid fa-user-lock"></i>';

$label_rid = "Role ID";
$label_rpid = "Role Permissions";
$label_submit = "Submit";
$label_cancel = "Cancel";

$aria_label_rid = "Role ID";
$aria_label_rpid = "Role Permissions";
$aria_label_submit = "Submit";
$aria_label_cancel = "Cancel";

$rid = "";
$rpid = "";

try {
  $pdo = new PDO($dsn, $db_user, $db_password, $options);
} catch (\PDOException $e) {
  throw new \PDOException($e->getMessage(), (int)$e->getCode());
  error_log($e->getMessage());
  exit('Something weird happened');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $rid = trim($_POST["rid"]);
  $rpid = trim($_POST["rpid"]);

  $sql = "INSERT INTO users (rid, rpid) VALUES (:rid, :rpid)";
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':rid', $rid, PDO::PARAM_INT);
  $stmt->bindParam(':rpid', $rpid, PDO::PARAM_INT);
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
<form name="new-user"
      action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"
      method="POST">
  <div class="form-group pb-1">
    <label for="rid"
           class="required pb-1 pt-1"><?php echo $label_rid; ?></label>
    <input type="text"
           required=""
           name="rid"
           maxlength="100"
           id="rid"
           class="form-control"
           placeholder="<?php echo $label_rid; ?>"
           autofocus=" autofocus"
           tabindex="1">
  </div>
  <div class="form-group pb-1">
    <label for=" rpid"
           class="required pb-1"><?php echo $label_rpid; ?></label>
    <input type="text"
           required=""
           name="rpid"
           maxlength="100"
           id="rpid"
           class="form-control"
           placeholder="<?php echo $label_rpid; ?>">
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
