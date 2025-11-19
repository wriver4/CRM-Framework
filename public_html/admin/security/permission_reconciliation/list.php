<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();

$dir = 'admin';
$subdir = 'security';
$sub_subdir = 'permission_reconciliation';
$sub_sub_subdir = '';
$page = 'list';

require LANG . '/en.php';
$title = $lang['permission_reconciliation'] ?? 'Permission Reconciliation Tool';
$title_icon = '<i class="fa-solid fa-sync-alt" aria-hidden="true"></i>';

require HEADER;
require BODY;
require NAV;
?>

<div class="container-fluid mt-4">
  <div class="alert alert-info">
    <h5><?php echo $lang['reconciliation_help'] ?? 'Reconciliation Help'; ?></h5>
    <p><?php echo $lang['reconciliation_description'] ?? 'This tool verifies and synchronizes permissions between the application and database. It can detect orphaned records and inconsistencies.'; ?></p>
  </div>

  <div class="row mb-3">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0"><?php echo $lang['consistency_checks'] ?? 'Consistency Checks'; ?></h5>
        </div>
        <div class="card-body">
          <form method="POST" action="validate.php" class="form-inline">
            <button type="submit" name="action" value="verify_orphaned" class="btn btn-warning me-2">
              <i class="fa-solid fa-search"></i> <?php echo $lang['find_orphaned'] ?? 'Find Orphaned Records'; ?>
            </button>
            <button type="submit" name="action" value="verify_hierarchy" class="btn btn-info me-2">
              <i class="fa-solid fa-sitemap"></i> <?php echo $lang['verify_hierarchy'] ?? 'Verify Hierarchy'; ?>
            </button>
            <button type="submit" name="action" value="verify_delegations" class="btn btn-success">
              <i class="fa-solid fa-check"></i> <?php echo $lang['verify_delegations'] ?? 'Verify Delegations'; ?>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0"><?php echo $lang['statistics'] ?? 'Statistics'; ?></h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-3">
              <div class="card bg-light">
                <div class="card-body text-center">
                  <h6><?php echo $lang['total_roles'] ?? 'Total Roles'; ?></h6>
                  <?php
                  $roles = new Roles();
                  $all_roles = $roles->get_all();
                  echo '<h3 class="text-primary">' . count($all_roles) . '</h3>';
                  ?>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card bg-light">
                <div class="card-body text-center">
                  <h6><?php echo $lang['total_permissions'] ?? 'Total Permissions'; ?></h6>
                  <?php
                  $permissions = new Permissions();
                  $all_perms = $permissions->get_all();
                  echo '<h3 class="text-info">' . count($all_perms) . '</h3>';
                  ?>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card bg-light">
                <div class="card-body text-center">
                  <h6><?php echo $lang['active_delegations'] ?? 'Active Delegations'; ?></h6>
                  <?php
                  $delegations = new PermissionDelegations();
                  $active_dels = $delegations->get_active_delegations();
                  echo '<h3 class="text-success">' . count($active_dels) . '</h3>';
                  ?>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card bg-light">
                <div class="card-body text-center">
                  <h6><?php echo $lang['pending_approvals'] ?? 'Pending Approvals'; ?></h6>
                  <?php
                  $approvals = new PermissionApprovals();
                  $pending = $approvals->get_pending_approvals();
                  echo '<h3 class="text-warning">' . count($pending) . '</h3>';
                  ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
require FOOTER;
