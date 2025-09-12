<?php
require_once '/mnt/Local/newwgadmin.local/config/system.php';
$title = "View User";
$table_page = false;
// Direct routing variables - these determine page navigation and template inclusion
$dir = basename(dirname(__FILE__));
$page = substr(basename(__FILE__), 0, strrpos(basename(__FILE__), '.'));
$title_icon = '<i class="fa fa-user" aria-hidden="true"></i>';
$back_icon = '<i class="fa fa-arrow-left" aria-hidden="true"></i>';

$label_username = "Username";
$label_password = "Password";
$label_fullname = "Full Name";
$label_role = "Role";
$label_email = "Email";
$label_status = "Status";
$label_updated_at = "Updated At";
$label_created_at = "Created At";
$label_back = "Back";


$aria_label_username = "Username";
$aria_label_password = "Password";
$aria_label_fullname = "Full Name";
$aria_label_role = "User Role Select";
$aria_label_email = "Email";
$aria_label_back = "Back";
$aria_updated_at = "Updated At";
$aria_created_at = "Created At";


$username = "";
$password = "";
$fullname = "";
$role = "";
$email = "";
$status = "";
$updated_at = "";
$created_at = "";

try {
  $pdo = new PDO($dsn, $db_user, $db_password, $options);
} catch (Exception $e) {
  error_log($e->getMessage());
  exit('Something weird happened');
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
  $id = trim($_GET["id"]);
  $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
  $stmt->execute([$id]);
  $user = $stmt->fetch();
  $username = $user["username"];
  $password = $user["password"];
  $fullname = $user["fullname"];
  $role = $user["role"];
  $email = $user["email"];
  $status = show_status($user["status"]);
  $updated_at = $user["updated_at"];
  $created_at = $user["created_at"];
}

require_once HEADER;
require_once BODY;
require_once NAV;
require_once SECTIONOPEN;
?>
<form>
  <form>
    <div class="form-group pb-1">
      <label for="fullname"
             class="pb-1 pt-1"><?php echo $label_fullname; ?></label>
      <input type="text"
             name="fullname"
             id="fullname"
             class="form-control"
             placeholder="<?php echo $label_fullname; ?>"
             value="<?php echo $fullname; ?>"
             tabindex="1">
    </div>
    <div class="form-group pb-1">
      <label for="username"
             class="pb-1 pt-1"><?php echo $label_username; ?></label>
      <input type="text"
             disabled
             name="username"
             id="username"
             class="form-control"
             placeholder="<?php echo $label_username; ?>"
             value="<?php echo $username; ?>">
    </div>
    <div class="form-group pb-1">
      <label for="password"
             class="pb-1 pt-1"><?php echo $label_password; ?></label>
      <input type="password"
             name="password"
             id="password"
             class="form-control"
             placeholder="<?php echo $label_password; ?>"
             value="<?php echo $password; ?>">
    </div>
    <div class="form-group pb-1">
      <label for="role"
             class="pb-1 pt-1"><?php echo $label_role; ?></label>
      <select name="role"
              class="form-control"
              id="role">
        <?php edit_enum_values($pdo, 'users', 'role', $role); ?></select>
    </div>
    <div class="form-group pb-1">
      <label for="email"
             class="pb-1 pt-1"><?php echo $label_email; ?></label>
      <input type="email"
             name="email"
             id="email"
             class="form-control"
             placeholder="<?php echo $label_email; ?>"
             value="<?php echo $email; ?>">
    </div>
    <div class="form-group pb-1">
      <label for="status"
             class="pb-1 pt-1"><?php echo $label_status; ?></label>
      <select name="status"
              class="form-control"
              id="status">
        <?php edit_enum_values($pdo, 'users', 'status', $status); ?></select>
    </div>
    <div class="form-group pb-1">
      <label for="updated_at"
             class="pb-1 pt-1"><?php echo $label_updated_at; ?></label>
      <input type="text"
             name="updated_at"
             id="updated_at"
             class="form-control"
             placeholder="<?php echo $label_updated_at; ?>"
             value="<?php echo $updated_at; ?>">
    </div>
    <div class="form-group pb-1">
      <label for="created_at"
             class="pb-1 pt-1"><?php echo $label_created_at; ?></label>
      <input type="text"
             name="created_at"
             id="created_at"
             class="form-control"
             placeholder="<?php echo $label_created_at; ?>"
             value="<?php echo $created_at; ?>">
    </div>
    <p></p>
    <a href="list.php"
       class="btn btn-success"><?php echo $back_icon; ?> <?php echo $label_back; ?></a>
  </form>
  <?php
  require_once SECTIONCLOSE;
  require_once FOOTER;
