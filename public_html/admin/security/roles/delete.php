<?php
require_once '/mnt/Local/newwgadmin.local/config/system.php';
$title = "Delete User";
$table_page = false;
$page = basename(dirname(__FILE__));
$title_icon = '<i class="fa fa-user" aria-hidden="true"></i>';

try {
  $pdo = new PDO($dsn, $db_user, $db_password, $options);
} catch (Exception $e) {
  error_log($e->getMessage());
  exit('Something weird happened');
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
  $id = trim($_GET["id"]);
  $sql = "SELECT * FROM users WHERE id = ?";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$id]);
  $user = $stmt->fetch();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $id = trim($_POST["id"]);
  $sql = "DELETE FROM users WHERE id = ?";
  $users = $pdo->prepare($sql);
  if ($users->execute([$id])) {
    $users = null;
    header("location: list.php");
  } else {
    echo "Something went wrong. Please try again later.";
  }
} else {
  echo "NO ID.";
}
require_once HEADER;
require_once BODY;
require_once NAV;
require_once SECTIONOPEN;
?>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
  <?php foreach ($user as $key => $value) {
    if ($key == 'id') {
      echo '<input type="hidden" name="' . $key . '" value="' . $value . '">';
    } else if ($key !== 'password') {
      echo '<div class="form-group row"><label for="' . $key . '" class="col-sm-2 col-form-label">' . ucfirst($key) . '</label>
      <div class="col-sm-10">
      <input type="text" readonly class="form-control" id="' . $key . '" value="' . $value . '">';
      echo '</div></div>';
    } else {
      echo '<div class="form-group row"><label for="' . $key . '" class="col-sm-2 col-form-label">' . ucfirst($key) . '</label>
      <div class="col-sm-10">
      <input type="password" readonly class="form-control" id="' . $key . '" value="' . $value . '">';
      echo '</div></div>';
    }
  }
  ?>
  <p></p>
  <a href="list.php" class="btn btn-success"><i class="fa fa-arrow-left" aria-hidden="true"></i>
    Back</a>
  <button type="submit" class="btn btn-danger" value="Submit"><i class="far fa-trash-alt"></i> Delete</button>
</form>
<?php
require_once SECTIONCLOSE;
require_once FOOTER;
