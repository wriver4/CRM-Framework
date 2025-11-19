<?php
if (!isset($_SESSION)) {
  session_start();
}

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../classes/Database.php';
require_once __DIR__ . '/../../../classes/Models/ComplianceReportGenerator.php';

$generator = new ComplianceReportGenerator();

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

$report = $generator->generate_permission_compliance_report($start_date, $end_date);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Compliance Reports</title>
  <link rel="stylesheet" href="/public_html/css/admin.css">
  <style>
    .report-container { padding: 20px; }
    .report-header { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; }
    .report-section { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .report-section h2 { margin-top: 0; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
    .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
    .metric-box { background: #f8f9fa; padding: 15px; border-radius: 4px; border-left: 4px solid #007bff; }
    .metric-label { font-size: 12px; color: #666; text-transform: uppercase; }
    .metric-value { font-size: 24px; font-weight: bold; color: #333; }
    .issue-item { padding: 15px; border-left: 4px solid #ff6b6b; background: #fff5f5; margin-bottom: 10px; border-radius: 4px; }
    .issue-high { border-left-color: #e74c3c; }
    .issue-medium { border-left-color: #f39c12; }
    .issue-low { border-left-color: #3498db; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #f5f5f5; padding: 12px; text-align: left; border-bottom: 2px solid #ddd; }
    td { padding: 10px 12px; border-bottom: 1px solid #eee; }
    .btn { display: inline-block; padding: 8px 16px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
    .btn:hover { background: #0056b3; }
  </style>
</head>
<body>
  <div class="report-container">
    <h1>Compliance Report Dashboard</h1>
    
    <div class="report-header">
      <h3>Report Period: <?php echo $start_date; ?> to <?php echo $end_date; ?></h3>
      <a href="?start_date=<?php echo date('Y-m-01'); ?>&end_date=<?php echo date('Y-m-d'); ?>" class="btn">This Month</a>
      <a href="?start_date=<?php echo date('Y-01-01'); ?>&end_date=<?php echo date('Y-m-d'); ?>" class="btn">This Year</a>
      <a href="export.php?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" class="btn">Export to CSV</a>
    </div>

    <div class="report-section">
      <h2>Access Control Summary</h2>
      <div class="metrics-grid">
        <div class="metric-box">
          <div class="metric-label">Active Users</div>
          <div class="metric-value"><?php echo $report['access_control_summary']['active_users'] ?? 0; ?></div>
        </div>
        <div class="metric-box">
          <div class="metric-label">Permissions Affected</div>
          <div class="metric-value"><?php echo $report['access_control_summary']['permissions_affected'] ?? 0; ?></div>
        </div>
        <div class="metric-box">
          <div class="metric-label">Total Actions</div>
          <div class="metric-value"><?php echo $report['access_control_summary']['total_actions'] ?? 0; ?></div>
        </div>
        <div class="metric-box">
          <div class="metric-label">Days Active</div>
          <div class="metric-value"><?php echo $report['access_control_summary']['days_active'] ?? 0; ?></div>
        </div>
      </div>
    </div>

    <div class="report-section">
      <h2>Permission Changes</h2>
      <div class="metrics-grid">
        <div class="metric-box">
          <div class="metric-label">Granted</div>
          <div class="metric-value"><?php echo $report['permission_changes']['granted'] ?? 0; ?></div>
        </div>
        <div class="metric-box">
          <div class="metric-label">Revoked</div>
          <div class="metric-value"><?php echo $report['permission_changes']['revoked'] ?? 0; ?></div>
        </div>
        <div class="metric-box">
          <div class="metric-label">Delegated</div>
          <div class="metric-value"><?php echo $report['permission_changes']['delegated'] ?? 0; ?></div>
        </div>
        <div class="metric-box">
          <div class="metric-label">Modified</div>
          <div class="metric-value"><?php echo $report['permission_changes']['modified'] ?? 0; ?></div>
        </div>
      </div>
    </div>

    <div class="report-section">
      <h2>Compliance Metrics</h2>
      <div class="metrics-grid">
        <div class="metric-box">
          <div class="metric-label">Grant Ratio</div>
          <div class="metric-value"><?php echo $report['compliance_metrics']['permission_grant_ratio'] ?? 0; ?>%</div>
        </div>
        <div class="metric-box">
          <div class="metric-label">Approval Rate</div>
          <div class="metric-value"><?php echo $report['compliance_metrics']['approval_rate'] ?? 0; ?>%</div>
        </div>
        <div class="metric-box">
          <div class="metric-label">Resolution Rate</div>
          <div class="metric-value"><?php echo $report['compliance_metrics']['timely_resolution_rate'] ?? 0; ?>%</div>
        </div>
      </div>
    </div>

    <div class="report-section">
      <h2>Audit Findings</h2>
      <?php foreach ($report['audit_findings'] as $finding): ?>
        <div class="issue-item issue-<?php echo $finding['severity']; ?>">
          <strong><?php echo ucfirst(str_replace('_', ' ', $finding['type'])); ?></strong>
          <p><?php echo htmlspecialchars($finding['details']); ?> (<?php echo $finding['count']; ?> issues)</p>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="report-section">
      <h2>High-Risk Actions</h2>
      <table>
        <thead>
          <tr><th>User</th><th>Action</th><th>Permission</th><th>Target</th><th>Date</th></tr>
        </thead>
        <tbody>
          <?php foreach (array_slice($report['high_risk_actions'], 0, 10) as $action): ?>
            <tr>
              <td><?php echo htmlspecialchars($action['acting_user']); ?></td>
              <td><?php echo ucfirst($action['action_type']); ?></td>
              <td><?php echo htmlspecialchars($action['permission_name'] ?? 'N/A'); ?></td>
              <td><?php echo htmlspecialchars($action['target_user'] ?? 'N/A'); ?></td>
              <td><?php echo date('Y-m-d H:i', strtotime($action['created_at'])); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
