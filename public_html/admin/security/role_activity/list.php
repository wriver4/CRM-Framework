<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();

$dir = 'admin';
$subdir = 'security';
$sub_subdir = 'role_activity';
$sub_sub_subdir = '';
$page = 'list';

require LANG . '/en.php';
$title = $lang['role_activity'] ?? 'Role Activity Analysis';
$title_icon = '<i class="fa-solid fa-chart-bar" aria-hidden="true"></i>';

require HEADER;
require BODY;
require NAV;
?>

<div class="container-fluid mt-4">
  <div class="row mb-4">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0"><?php echo $title; ?></h5>
        </div>
        <div class="card-body">
          <div class="row">
            <?php
            $hierarchy = new RoleHierarchy();
            $roles = new Roles();
            $all_roles = $roles->get_all();
            
            foreach ($all_roles as $role) {
              $coverage = $hierarchy->get_role_coverage($role['id']);
              $ancestors = $hierarchy->get_ancestors($role['id']);
              $descendants = $hierarchy->get_descendants($role['id']);
            ?>
              <div class="col-md-6 mb-3">
                <div class="card">
                  <div class="card-header bg-light">
                    <h6 class="mb-0"><?php echo htmlspecialchars($role['role']); ?></h6>
                  </div>
                  <div class="card-body">
                    <p class="mb-2">
                      <strong><?php echo $lang['total_permissions'] ?? 'Total Permissions'; ?>:</strong>
                      <span class="badge badge-primary"><?php echo $coverage['total_permissions'] ?? 0; ?></span>
                    </p>
                    <p class="mb-2">
                      <strong><?php echo $lang['direct_permissions'] ?? 'Direct'; ?>:</strong>
                      <span class="badge badge-success"><?php echo $coverage['direct_permissions'] ?? 0; ?></span>
                    </p>
                    <p class="mb-2">
                      <strong><?php echo $lang['inherited_permissions'] ?? 'Inherited'; ?>:</strong>
                      <span class="badge badge-info"><?php echo $coverage['inherited_permissions'] ?? 0; ?></span>
                    </p>
                    <p class="mb-2">
                      <strong><?php echo $lang['delegated_permissions'] ?? 'Delegated'; ?>:</strong>
                      <span class="badge badge-warning"><?php echo $coverage['delegated_permissions'] ?? 0; ?></span>
                    </p>
                    <p class="mb-2">
                      <strong><?php echo $lang['parent_roles'] ?? 'Parent Roles'; ?>:</strong>
                      <span class="badge badge-secondary"><?php echo count($ancestors); ?></span>
                    </p>
                    <p class="mb-0">
                      <strong><?php echo $lang['child_roles'] ?? 'Child Roles'; ?>:</strong>
                      <span class="badge badge-secondary"><?php echo count($descendants); ?></span>
                    </p>
                  </div>
                </div>
              </div>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0"><?php echo $lang['hierarchy_depth_analysis'] ?? 'Hierarchy Depth Analysis'; ?></h5>
        </div>
        <div class="card-body">
          <table class="table table-striped table-sm">
            <thead class="table-dark">
              <tr>
                <th><?php echo $lang['depth'] ?? 'Depth'; ?></th>
                <th><?php echo $lang['relationships'] ?? 'Relationships'; ?></th>
                <th><?php echo $lang['avg_depth'] ?? 'Average'; ?></th>
              </tr>
            </thead>
            <tbody>
              <?php
              $hierarchy = new RoleHierarchy();
              $depth_analysis = $hierarchy->get_role_hierarchy_depth_analysis();
              foreach ($depth_analysis as $analysis) {
              ?>
                <tr>
                  <td><?php echo $analysis['depth']; ?></td>
                  <td><span class="badge badge-primary"><?php echo $analysis['relationship_count']; ?></span></td>
                  <td><?php echo round($analysis['avg_depth'], 2); ?></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
require FOOTER;
