<?php
if (!isset($_SESSION)) {
  session_start();
}

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../classes/Database.php';
require_once __DIR__ . '/../../../classes/Models/RoleRecommender.php';

$recommender = new RoleRecommender();
$frequently_used = $recommender->get_frequently_used_permissions(20);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Role Recommendations</title>
  <link rel="stylesheet" href="/public_html/css/admin.css">
  <style>
    .recommendations-container { padding: 20px; }
    .rec-section { margin-bottom: 30px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .rec-section h2 { margin-top: 0; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
    .rec-item { padding: 15px; border: 1px solid #e0e0e0; margin-bottom: 10px; border-radius: 4px; }
    .rec-item h4 { margin: 0 0 8px 0; }
    .score { display: inline-block; background: #007bff; color: white; padding: 4px 12px; border-radius: 20px; font-weight: bold; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #f5f5f5; padding: 12px; text-align: left; border-bottom: 2px solid #ddd; }
    td { padding: 10px 12px; border-bottom: 1px solid #eee; }
    tbody tr:hover { background: #f9f9f9; }
  </style>
</head>
<body>
  <div class="recommendations-container">
    <h1>Role Recommendations Engine</h1>
    <p>Analyze user permission patterns and recommend appropriate roles</p>

    <div class="rec-section">
      <h2>Frequently Used Permissions</h2>
      <table>
        <thead>
          <tr><th>Permission</th><th>Module.Action</th><th>Users Using</th><th>Actions</th><th>Days Active</th></tr>
        </thead>
        <tbody>
          <?php foreach ($frequently_used as $perm): ?>
            <tr>
              <td><?php echo htmlspecialchars($perm['permission_name']); ?></td>
              <td><?php echo $perm['module'] . '.' . $perm['action']; ?></td>
              <td><?php echo $perm['users_using']; ?></td>
              <td><?php echo $perm['total_actions']; ?></td>
              <td><?php echo $perm['days_active']; ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="rec-section">
      <h2>Role Adoption Potential</h2>
      <p>Roles that could be adopted by more users based on usage patterns</p>
      <p><em>Select a role to see adoption analysis</em></p>
    </div>

    <div class="rec-section">
      <h2>User Similarity Analysis</h2>
      <p>Find users with similar permission usage patterns</p>
      <p><em>Select a user to find similar users</em></p>
    </div>
  </div>
</body>
</html>
