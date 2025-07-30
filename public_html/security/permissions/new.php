<?php
require_once '/mnt/Local/newwgadmin.local/config/system.php';
$title = "New Permission";
$table_page = false;
$page = basename(dirname(__FILE__));
$title_icon = '<i class="fa fa-user" aria-hidden="true"></i>';

$pid = "";
$pmodule = "";
$pdescription = "";

$label_pid = "Permission ID";
$label_pname = "Permission Module";
$label_pdescription = "Permission Description";
$label_submit = "Submit";
$label_cancel = "Cancel";

$aria_label_pid = "Permission ID";
$aria_label_pname = "Permission Module";
$aria_label_pdescription = "Permission Description";
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
  $pid = trim($_POST["pid"]);
  $pname = trim($_POST["pname"]);
  $pdescription = trim($_POST["pdescription"]);

  $sql = "INSERT INTO users (pid, pname) VALUES (:pid, :pname)";
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':pid', $pid, PDO::PARAM_INT);
  $stmt->bindParam(':pname', $pname, PDO::PARAM_STR);
  $stmt->bindParam(':pdescription', $pdescription, PDO::PARAM_STR);
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
<form name="new-user" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
  <div class="form-group pb-1">
    <label for="pid" class="required pb-1 pt-1"><?php echo $label_pid; ?></label>
    <input type="text" required="" name="pid" maxlength="11" id="pid" class="form-control" placeholder="<?php echo $label_pid; ?>" autofocus=" autofocus" tabindex="1">
  </div>
  <div class="form-group pb-1">
    <label for="pname" class="required pb-1"><?php echo $label_pname; ?></label>
    <input type="text" required="" name="pname" maxlength="15" id="pname" class="form-control" placeholder="<?php echo $label_pname; ?>">
  </div>
  <div class="form-group pb-1">
    <label for="pdescription" class="required pb-1"><?php echo $label_pdescription; ?></label>
    <input type="text" required="" name="pdescription" maxlength="100" id="pdescription" class="form-control" placeholder="<?php echo $label_pname; ?>">
  </div>
  <p></p>
  <button type="submit" class="btn btn-success" value="submit" tabindex="0" role="button" aria-pressed="false"><?php echo $label_submit; ?></button>
  <a href="list.php" class="btn btn-danger" tabindex="0" role="button" aria-pressed="false"><i class="fa fa-arrow-left" aria-hidden="true"></i> <?php echo $label_cancel; ?></a>
</form>
<?php
require_once SECTIONCLOSE;
require_once FOOTER;
