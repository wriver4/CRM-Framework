<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();

$dir = 'admin';
$subdir = 'security';
$sub_subdir = 'audit_log';
$sub_sub_subdir = '';
$page = 'list';

$table_page = true;
$table_header = true;

$search = true;
$button_showall = true;
$button_new = false;
$button_refresh = false;
$button_back = false;
$paginate = true;

require LANG . '/en.php';
$title = $lang['audit_log'] ?? 'Audit Log';
$new_button = '';

$title_icon = '<i class="fa-solid fa-list" aria-hidden="true"></i>';
$new_icon = '';

require HEADER;
require BODY;
require NAV;
?>

<div class="container mt-4">
  <div class="row mb-3">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0"><?php echo $title; ?> - Analytics</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-3">
              <div class="card bg-light">
                <div class="card-body text-center">
                  <h6><?php echo $lang['total_actions'] ?? 'Total Actions'; ?></h6>
                  <?php
                  $audit = new PermissionAuditLog();
                  $total = $audit->count_logs();
                  echo '<h3 class="text-primary">' . $total . '</h3>';
                  ?>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card bg-light">
                <div class="card-body text-center">
                  <h6><?php echo $lang['recent_24h'] ?? 'Last 24 Hours'; ?></h6>
                  <?php
                  $start = date('Y-m-d H:i:s', strtotime('-24 hours'));
                  $end = date('Y-m-d H:i:s');
                  $count_24h = $audit->count_logs($start, $end);
                  echo '<h3 class="text-info">' . $count_24h . '</h3>';
                  ?>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card bg-light">
                <div class="card-body text-center">
                  <h6><?php echo $lang['high_risk'] ?? 'High Risk Actions'; ?></h6>
                  <?php
                  $high_risk = count($audit->get_high_risk_activities(1000, 0));
                  echo '<h3 class="text-danger">' . $high_risk . '</h3>';
                  ?>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card bg-light">
                <div class="card-body text-center">
                  <h6><?php echo $lang['export'] ?? 'Export'; ?></h6>
                  <a href="export.php" class="btn btn-sm btn-success"><?php echo $lang['csv'] ?? 'CSV'; ?></a>
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
require LISTOPEN;
require 'get.php';
require LISTCLOSE;
require FOOTER;
