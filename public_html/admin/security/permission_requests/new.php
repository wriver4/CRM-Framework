<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();

$dir = 'admin';
$subdir = 'security';
$sub_subdir = 'permission_requests';
$sub_sub_subdir = '';
$page = 'new';

require LANG . '/en.php';
$title = $lang['request_new'] ?? 'Request New Permission';
$title_icon = '<i class="fa-solid fa-file-contract" aria-hidden="true"></i>';

require HEADER;
require BODY;
require NAV;
?>

<div class="container mt-4">
  <div class="row">
    <div class="col-md-8 mx-auto">
      <div class="card">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0"><?php echo $title; ?></h5>
        </div>
        <div class="card-body">
          <form method="POST" action="post.php">
            <div class="form-group mb-3">
              <label for="permission_id" class="form-label"><?php echo $lang['permission'] ?? 'Permission'; ?>*</label>
              <select class="form-control" id="permission_id" name="permission_id" required>
                <option value="">-- Select Permission --</option>
                <?php
                $permissions = new Permissions();
                $all_perms = $permissions->get_all();
                foreach ($all_perms as $perm) {
                  echo '<option value="' . $perm['id'] . '">' . htmlspecialchars($perm['pdescription']) . ' (' . htmlspecialchars($perm['pobject']) . ')</option>';
                }
                ?>
              </select>
            </div>

            <div class="form-group mb-3">
              <label for="reason" class="form-label"><?php echo $lang['reason'] ?? 'Reason'; ?>*</label>
              <textarea class="form-control" id="reason" name="reason" rows="4" required placeholder="Explain why you need this permission..."></textarea>
            </div>

            <div class="form-group mb-3">
              <label for="duration" class="form-label"><?php echo $lang['duration'] ?? 'Duration'; ?></label>
              <select class="form-control" id="duration" name="duration">
                <option value="">-- No End Date --</option>
                <option value="7">7 Days</option>
                <option value="30">30 Days</option>
                <option value="90">90 Days</option>
                <option value="180">6 Months</option>
                <option value="365">1 Year</option>
              </select>
            </div>

            <div class="form-group mb-3">
              <label for="requested_role_id" class="form-label"><?php echo $lang['role'] ?? 'Role'; ?></label>
              <select class="form-control" id="requested_role_id" name="requested_role_id">
                <option value="">-- Optional --</option>
                <?php
                $roles = new Roles();
                $all_roles = $roles->get_all();
                foreach ($all_roles as $role) {
                  echo '<option value="' . $role['id'] . '">' . htmlspecialchars($role['role']) . '</option>';
                }
                ?>
              </select>
            </div>

            <div class="form-group">
              <button type="submit" class="btn btn-primary"><?php echo $lang['submit'] ?? 'Submit'; ?></button>
              <a href="list.php" class="btn btn-secondary"><?php echo $lang['cancel'] ?? 'Cancel'; ?></a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
require FOOTER;
