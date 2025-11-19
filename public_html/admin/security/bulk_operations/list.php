<?php
if (!isset($_SESSION)) {
  session_start();
}

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../classes/Database.php';
require_once __DIR__ . '/../../../classes/Models/BulkPermissionAssignment.php';

$bulk = new BulkPermissionAssignment();
$history = $bulk->get_bulk_assignment_history(20);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Bulk Permission Operations</title>
  <link rel="stylesheet" href="/public_html/css/admin.css">
  <style>
    .bulk-container { padding: 20px; }
    .operation-section { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .operation-section h2 { margin-top: 0; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
    .operation-tabs { display: flex; gap: 10px; margin-bottom: 20px; }
    .tab-btn { padding: 10px 20px; background: #e0e0e0; border: none; cursor: pointer; border-radius: 4px; }
    .tab-btn.active { background: #007bff; color: white; }
    .tab-content { display: none; }
    .tab-content.active { display: block; }
    .form-group { margin-bottom: 15px; }
    label { display: block; margin-bottom: 5px; font-weight: 600; }
    input, textarea, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
    .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 4px; }
    .btn:hover { background: #0056b3; }
    .btn-secondary { background: #6c757d; }
    .btn-secondary:hover { background: #5a6268; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    th { background: #f5f5f5; padding: 12px; text-align: left; border-bottom: 2px solid #ddd; }
    td { padding: 10px 12px; border-bottom: 1px solid #eee; }
    tbody tr:hover { background: #f9f9f9; }
    .status-pending { background: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 3px; }
    .status-completed { background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 3px; }
    .status-failed { background: #f8d7da; color: #721c24; padding: 4px 8px; border-radius: 3px; }
  </style>
  <script>
    function switchTab(tabName) {
      document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
      document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
      document.getElementById(tabName).classList.add('active');
      event.target.classList.add('active');
    }
  </script>
</head>
<body>
  <div class="bulk-container">
    <h1>Bulk Permission Operations</h1>
    <p>Efficiently manage permissions for multiple users and permissions at once</p>

    <div class="operation-section">
      <div class="operation-tabs">
        <button class="tab-btn active" onclick="switchTab('import-tab')">Import Permissions</button>
        <button class="tab-btn" onclick="switchTab('assign-tab')">Bulk Assign Role</button>
        <button class="tab-btn" onclick="switchTab('revoke-tab')">Revoke Permissions</button>
        <button class="tab-btn" onclick="switchTab('export-tab')">Export Permissions</button>
      </div>

      <div id="import-tab" class="tab-content active">
        <h2>Import Permissions from CSV</h2>
        <form method="POST" action="import.php" enctype="multipart/form-data">
          <div class="form-group">
            <label>CSV File (User ID, Permission ID, Assignment Type, Duration)</label>
            <input type="file" name="csv_file" accept=".csv" required>
            <small>CSV format: user_id,permission_id,assignment_type,duration_days</small>
          </div>
          <div class="form-group">
            <label><input type="checkbox" name="dry_run"> Perform dry run (validate without applying)</label>
          </div>
          <button type="submit" class="btn">Import</button>
        </form>
      </div>

      <div id="assign-tab" class="tab-content">
        <h2>Assign Role to Multiple Users</h2>
        <form method="POST" action="assign_role.php">
          <div class="form-group">
            <label>Role</label>
            <select name="role_id" required>
              <option value="">Select Role</option>
              <option value="1">Admin</option>
              <option value="2">Manager</option>
              <option value="3">User</option>
              <option value="4">Viewer</option>
            </select>
          </div>
          <div class="form-group">
            <label>User IDs (comma-separated)</label>
            <textarea name="user_ids" rows="5" required placeholder="1,2,3,4,5"></textarea>
          </div>
          <div class="form-group">
            <label><input type="checkbox" name="dry_run"> Perform dry run</label>
          </div>
          <button type="submit" class="btn">Assign Role</button>
        </form>
      </div>

      <div id="revoke-tab" class="tab-content">
        <h2>Revoke Permissions</h2>
        <form method="POST" action="revoke.php">
          <div class="form-group">
            <label>User ID</label>
            <input type="number" name="user_id" required>
          </div>
          <div class="form-group">
            <label>Permission IDs (comma-separated)</label>
            <textarea name="permission_ids" rows="5" required placeholder="1,2,3"></textarea>
          </div>
          <button type="submit" class="btn btn-secondary">Revoke Permissions</button>
        </form>
      </div>

      <div id="export-tab" class="tab-content">
        <h2>Export Permissions</h2>
        <form method="POST" action="export.php">
          <div class="form-group">
            <label><input type="radio" name="export_scope" value="all" checked> All Users</label>
            <label><input type="radio" name="export_scope" value="user"> Specific User</label>
            <input type="number" name="user_id" placeholder="User ID (if specific user selected)">
          </div>
          <button type="submit" class="btn">Export to CSV</button>
        </form>
      </div>
    </div>

    <div class="operation-section">
      <h2>Recent Bulk Operations</h2>
      <table>
        <thead>
          <tr><th>User</th><th>Action Count</th><th>Last Action</th><th>Action Types</th></tr>
        </thead>
        <tbody>
          <?php foreach ($history as $op): ?>
            <tr>
              <td><?php echo htmlspecialchars($op['acting_user']); ?></td>
              <td><?php echo $op['action_count']; ?></td>
              <td><?php echo date('Y-m-d H:i', strtotime($op['last_action'])); ?></td>
              <td><?php echo htmlspecialchars($op['action_types']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
