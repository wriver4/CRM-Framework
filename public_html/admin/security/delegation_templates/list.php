<?php
if (!isset($_SESSION)) {
  session_start();
}

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../classes/Database.php';
require_once __DIR__ . '/../../../classes/Models/DelegationTemplates.php';

$templates = new DelegationTemplates();
$all_templates = $templates->get_all_templates();
$popular = $templates->get_popular_templates(5);
$stats = $templates->get_template_statistics();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Delegation Templates</title>
  <link rel="stylesheet" href="/public_html/css/admin.css">
  <style>
    .templates-container { padding: 20px; }
    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 30px; }
    .stat-box { background: #f8f9fa; padding: 15px; border-radius: 4px; text-align: center; }
    .stat-label { font-size: 12px; color: #666; text-transform: uppercase; }
    .stat-value { font-size: 24px; font-weight: bold; }
    .template-section { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .template-section h2 { margin-top: 0; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
    .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; margin-bottom: 15px; }
    .btn:hover { background: #0056b3; }
    .btn-secondary { background: #6c757d; }
    .btn-secondary:hover { background: #5a6268; }
    .btn-sm { padding: 6px 12px; font-size: 12px; }
    .btn-danger { background: #dc3545; }
    .btn-danger:hover { background: #c82333; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #f5f5f5; padding: 12px; text-align: left; border-bottom: 2px solid #ddd; }
    td { padding: 10px 12px; border-bottom: 1px solid #eee; }
    tbody tr:hover { background: #f9f9f9; }
    .template-item { background: #f9f9f9; padding: 15px; border: 1px solid #e0e0e0; margin-bottom: 15px; border-radius: 4px; }
    .template-item h4 { margin: 0 0 8px 0; }
    .template-meta { font-size: 12px; color: #666; margin: 8px 0; }
    .template-actions { margin-top: 10px; }
    .badge { display: inline-block; background: #e0e0e0; padding: 4px 8px; border-radius: 3px; font-size: 12px; margin-right: 5px; }
    .badge-active { background: #d4edda; color: #155724; }
    .badge-inactive { background: #f8d7da; color: #721c24; }
  </style>
</head>
<body>
  <div class="templates-container">
    <h1>Delegation Templates</h1>
    <p>Create and manage reusable delegation templates for common permission patterns</p>

    <div class="stats-grid">
      <div class="stat-box">
        <div class="stat-label">Total Templates</div>
        <div class="stat-value"><?php echo $stats['total_templates'] ?? 0; ?></div>
      </div>
      <div class="stat-box">
        <div class="stat-label">Active</div>
        <div class="stat-value"><?php echo $stats['active_templates'] ?? 0; ?></div>
      </div>
      <div class="stat-box">
        <div class="stat-label">Roles</div>
        <div class="stat-value"><?php echo $stats['roles_with_templates'] ?? 0; ?></div>
      </div>
      <div class="stat-box">
        <div class="stat-label">Avg Usage</div>
        <div class="stat-value"><?php echo round($stats['avg_usage_count'] ?? 0); ?></div>
      </div>
    </div>

    <div class="template-section">
      <h2>Templates</h2>
      <a href="new.php" class="btn">Create New Template</a>
      
      <?php if (empty($all_templates)): ?>
        <p>No templates found. <a href="new.php">Create one now</a></p>
      <?php else: ?>
        <table>
          <thead>
            <tr><th>Name</th><th>Description</th><th>Permissions</th><th>Duration</th><th>Status</th><th>Actions</th></tr>
          </thead>
          <tbody>
            <?php foreach ($all_templates as $template): ?>
              <tr>
                <td><?php echo htmlspecialchars($template['name']); ?></td>
                <td><?php echo htmlspecialchars(substr($template['description'] ?? '', 0, 50)); ?>...</td>
                <td><?php echo count(json_decode($template['permissions_json'], true) ?? []); ?></td>
                <td><?php echo $template['duration_days'] ?? 'Indefinite'; ?> days</td>
                <td><span class="badge badge-<?php echo $template['is_active'] ? 'active' : 'inactive'; ?>"><?php echo $template['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
                <td>
                  <a href="edit.php?id=<?php echo $template['id']; ?>" class="btn btn-sm">Edit</a>
                  <a href="apply.php?id=<?php echo $template['id']; ?>" class="btn btn-sm btn-secondary">Apply</a>
                  <a href="delete.php?id=<?php echo $template['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

    <div class="template-section">
      <h2>Most Popular Templates</h2>
      <p>Templates used most frequently</p>
      <?php foreach ($popular as $template): ?>
        <div class="template-item">
          <h4><?php echo htmlspecialchars($template['name']); ?></h4>
          <div class="template-meta">
            <strong>Users Applied To:</strong> <?php echo $template['users_applied'] ?? 0; ?> | 
            <strong>Total Delegations:</strong> <?php echo $template['total_delegations'] ?? 0; ?>
          </div>
          <p><?php echo htmlspecialchars($template['description'] ?? ''); ?></p>
          <div class="template-actions">
            <a href="view.php?id=<?php echo $template['id']; ?>" class="btn btn-sm">View</a>
            <a href="apply.php?id=<?php echo $template['id']; ?>" class="btn btn-sm btn-secondary">Apply to User/Role</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</body>
</html>
