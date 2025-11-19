<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();

$dir = 'admin';
$subdir = 'security';
$sub_subdir = 'role_hierarchy';
$sub_sub_subdir = '';
$page = 'new';

require LANG . '/en.php';
$title = $lang['relationship_new'] ?? 'Create Role Hierarchy';
$title_icon = '<i class="fa-solid fa-sitemap" aria-hidden="true"></i>';

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
              <label for="parent_role_id" class="form-label"><?php echo $lang['parent_role'] ?? 'Parent Role'; ?>*</label>
              <select class="form-control" id="parent_role_id" name="parent_role_id" required>
                <option value="">-- Select Parent Role --</option>
                <?php
                $roles = new Roles();
                $all_roles = $roles->get_all();
                foreach ($all_roles as $role) {
                  echo '<option value="' . $role['id'] . '">' . htmlspecialchars($role['role']) . '</option>';
                }
                ?>
              </select>
            </div>

            <div class="form-group mb-3">
              <label for="child_role_id" class="form-label"><?php echo $lang['child_role'] ?? 'Child Role'; ?>*</label>
              <select class="form-control" id="child_role_id" name="child_role_id" required>
                <option value="">-- Select Child Role --</option>
                <?php
                foreach ($all_roles as $role) {
                  echo '<option value="' . $role['id'] . '">' . htmlspecialchars($role['role']) . '</option>';
                }
                ?>
              </select>
              <small class="form-text text-muted"><?php echo $lang['child_inherits_from_parent'] ?? 'Child role will inherit permissions from parent role'; ?></small>
            </div>

            <div class="form-group mb-3">
              <label for="inheritance_type" class="form-label"><?php echo $lang['inheritance_type'] ?? 'Inheritance Type'; ?>*</label>
              <select class="form-control" id="inheritance_type" name="inheritance_type" required>
                <option value="full"><?php echo $lang['full'] ?? 'Full'; ?> - <?php echo $lang['all_permissions'] ?? 'All permissions'; ?></option>
                <option value="partial"><?php echo $lang['partial'] ?? 'Partial'; ?> - <?php echo $lang['selected_permissions'] ?? 'Selected permissions'; ?></option>
                <option value="none"><?php echo $lang['none'] ?? 'None'; ?> - <?php echo $lang['no_inheritance'] ?? 'No inheritance'; ?></option>
              </select>
            </div>

            <div class="alert alert-info">
              <strong><?php echo $lang['warning'] ?? 'Warning'; ?></strong>: <?php echo $lang['circular_hierarchy_warning'] ?? 'Circular role hierarchies will be automatically prevented.'; ?>
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
