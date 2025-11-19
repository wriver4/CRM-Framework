<?php
if (!isset($_SESSION)) {
  session_start();
}

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../classes/Database.php';
require_once __DIR__ . '/../../../classes/Models/DelegationAnalytics.php';
require_once __DIR__ . '/../../../classes/Views/DelegationAnalyticsView.php';

$analytics = new DelegationAnalytics();
$view = new DelegationAnalyticsView();

$analytics_data = $analytics->export_analytics_to_array();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Delegation Analytics Dashboard</title>
  <link rel="stylesheet" href="/public_html/css/admin.css">
  <style>
    .analytics-dashboard {
      padding: 20px;
    }
    .summary-cards {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      gap: 15px;
      margin-bottom: 30px;
    }
    .card {
      padding: 20px;
      border-radius: 8px;
      color: white;
      text-align: center;
    }
    .card-blue { background-color: #3498db; }
    .card-green { background-color: #27ae60; }
    .card-orange { background-color: #e67e22; }
    .card-red { background-color: #e74c3c; }
    .card-gray { background-color: #95a5a6; }
    .card h3 {
      margin: 0;
      font-size: 14px;
      font-weight: 600;
    }
    .card .value {
      margin: 10px 0 0;
      font-size: 28px;
      font-weight: bold;
    }
    .chart-container {
      background: white;
      padding: 20px;
      margin-bottom: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .chart-container h3 {
      margin-top: 0;
      color: #333;
    }
    .trends-table, .data-table {
      width: 100%;
      border-collapse: collapse;
    }
    .trends-table th, .data-table th {
      background-color: #f5f5f5;
      padding: 12px;
      text-align: left;
      font-weight: 600;
      border-bottom: 2px solid #ddd;
    }
    .trends-table td, .data-table td {
      padding: 10px 12px;
      border-bottom: 1px solid #eee;
    }
    .trends-table tbody tr:hover, .data-table tbody tr:hover {
      background-color: #f9f9f9;
    }
    .expiration-analysis {
      background: white;
      padding: 20px;
      margin-bottom: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .expiration-analysis p {
      margin: 10px 0;
    }
    .expiration-analysis .highlight {
      background-color: #fff3cd;
      padding: 10px;
      border-left: 4px solid #ffc107;
    }
    .badge {
      display: inline-block;
      padding: 4px 8px;
      border-radius: 3px;
      font-size: 12px;
      font-weight: 600;
    }
    .badge-success { background-color: #d4edda; color: #155724; }
    .badge-danger { background-color: #f8d7da; color: #721c24; }
    .badge-warning { background-color: #fff3cd; color: #856404; }
  </style>
</head>
<body>
  <div class="container">
    <h1>Delegation Analytics Dashboard</h1>
    <p>Comprehensive analysis of permission delegations, approvals, and patterns</p>

    <?php echo $view->render_analytics_dashboard($analytics_data); ?>

    <div class="chart-container">
      <h3>Approval Performance</h3>
      <table class="data-table">
        <thead>
          <tr>
            <th>Approver</th>
            <th>Total Reviewed</th>
            <th>Approved</th>
            <th>Rejected</th>
            <th>Avg Review Time (hours)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($analytics_data['approval_performance'] as $approver): ?>
            <tr>
              <td><?php echo htmlspecialchars($approver['approver_name']); ?></td>
              <td><?php echo $approver['total_reviewed']; ?></td>
              <td><?php echo $approver['approved_count']; ?></td>
              <td><?php echo $approver['rejected_count']; ?></td>
              <td><?php echo round($approver['avg_review_hours'] ?? 0, 2); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="chart-container">
      <h3>Role Delegation Analysis</h3>
      <table class="data-table">
        <thead>
          <tr>
            <th>Role</th>
            <th>Total Delegations</th>
            <th>Users with Delegation</th>
            <th>Approved</th>
            <th>Rejected</th>
            <th>Avg Duration (days)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($analytics_data['role_analysis'] as $role): ?>
            <tr>
              <td><?php echo htmlspecialchars($role['role_name']); ?></td>
              <td><?php echo $role['total_delegations']; ?></td>
              <td><?php echo $role['users_with_delegation']; ?></td>
              <td><?php echo $role['approved']; ?></td>
              <td><?php echo $role['rejected']; ?></td>
              <td><?php echo round($role['avg_duration_days'] ?? 0, 1); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
