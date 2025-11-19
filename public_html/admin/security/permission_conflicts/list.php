<?php
if (!isset($_SESSION)) {
  session_start();
}

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../classes/Database.php';
require_once __DIR__ . '/../../../classes/Models/PermissionConflictDetector.php';

$detector = new PermissionConflictDetector();
$report = $detector->generate_conflict_report();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Permission Conflict Detection</title>
  <link rel="stylesheet" href="/public_html/css/admin.css">
  <style>
    .conflicts-container { padding: 20px; }
    .conflict-header { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; }
    .severity-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }
    .stat-box { background: #f8f9fa; padding: 15px; border-radius: 4px; text-align: center; }
    .stat-label { font-size: 12px; color: #666; text-transform: uppercase; }
    .stat-value { font-size: 32px; font-weight: bold; margin: 10px 0; }
    .stat-high { color: #e74c3c; }
    .stat-medium { color: #f39c12; }
    .stat-low { color: #3498db; }
    .conflict-section { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .conflict-item { padding: 15px; border: 1px solid #ddd; margin-bottom: 10px; border-left: 4px solid #007bff; border-radius: 4px; }
    .conflict-item.high { border-left-color: #e74c3c; background: #fff5f5; }
    .conflict-item.medium { border-left-color: #f39c12; background: #fffbf0; }
    .conflict-item.low { border-left-color: #3498db; background: #f0f7ff; }
    .conflict-type { display: inline-block; background: #e0e0e0; padding: 4px 8px; border-radius: 3px; font-size: 12px; font-weight: 600; }
    .conflict-severity { display: inline-block; margin-left: 10px; padding: 4px 8px; border-radius: 3px; font-size: 12px; font-weight: 600; }
    .severity-high { background: #f8d7da; color: #721c24; }
    .severity-medium { background: #fff3cd; color: #856404; }
    .severity-low { background: #d1ecf1; color: #0c5460; }
    .conflict-recommendation { font-style: italic; color: #666; margin-top: 10px; padding-top: 10px; border-top: 1px solid #ddd; }
    .btn { display: inline-block; padding: 8px 16px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; margin-top: 10px; }
  </style>
</head>
<body>
  <div class="conflicts-container">
    <h1>Permission Conflict Detection</h1>
    <p>Identify and resolve permission conflicts, inconsistencies, and potential security issues</p>

    <div class="conflict-header">
      <h3>Summary</h3>
      <p><strong>Total Conflicts Detected:</strong> <?php echo $report['total_conflicts']; ?></p>
      <div class="severity-stats">
        <div class="stat-box">
          <div class="stat-label">High Severity</div>
          <div class="stat-value stat-high"><?php echo $report['by_severity']['high'] ?? 0; ?></div>
        </div>
        <div class="stat-box">
          <div class="stat-label">Medium Severity</div>
          <div class="stat-value stat-medium"><?php echo $report['by_severity']['medium'] ?? 0; ?></div>
        </div>
        <div class="stat-box">
          <div class="stat-label">Low Severity</div>
          <div class="stat-value stat-low"><?php echo $report['by_severity']['low'] ?? 0; ?></div>
        </div>
      </div>
    </div>

    <?php if (isset($report['conflicts']['circular_hierarchies']) && count($report['conflicts']['circular_hierarchies']) > 0): ?>
    <div class="conflict-section">
      <h2>Circular Hierarchies</h2>
      <?php foreach ($report['conflicts']['circular_hierarchies'] as $conflict): ?>
      <div class="conflict-item high">
        <p><strong><?php echo htmlspecialchars($conflict['parent_name']); ?></strong> ← (circular) → <strong><?php echo htmlspecialchars($conflict['child_name']); ?></strong></p>
        <span class="conflict-type">Circular Hierarchy</span>
        <span class="conflict-severity severity-<?php echo $conflict['severity']; ?>"><?php echo ucfirst($conflict['severity']); ?></span>
        <div class="conflict-recommendation"><strong>Recommendation:</strong> <?php echo htmlspecialchars($conflict['recommendation']); ?></div>
        <form method="POST" action="resolve.php" style="display: inline;">
          <input type="hidden" name="conflict_id" value="<?php echo $conflict['parent_id']; ?>">
          <input type="hidden" name="resolution_action" value="remove_circular_relationship">
          <button type="submit" class="btn">Resolve</button>
        </form>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (isset($report['conflicts']['mutual_exclusions']) && count($report['conflicts']['mutual_exclusions']) > 0): ?>
    <div class="conflict-section">
      <h2>Mutually Exclusive Permissions</h2>
      <?php foreach ($report['conflicts']['mutual_exclusions'] as $conflict): ?>
      <div class="conflict-item medium">
        <p><strong>Conflict:</strong> Users have both "<?php echo $conflict['pair'][0]; ?>" and "<?php echo $conflict['pair'][1]; ?>" permissions</p>
        <p><strong>Affected Users:</strong> <?php echo count($conflict['affected_users']); ?></p>
        <div class="conflict-recommendation"><strong>Recommendation:</strong> Review and remove conflicting permissions from affected users</div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (isset($report['conflicts']['role_conflicts']) && count($report['conflicts']['role_conflicts']) > 0): ?>
    <div class="conflict-section">
      <h2>Role Inconsistencies</h2>
      <?php foreach (array_slice($report['conflicts']['role_conflicts'], 0, 5) as $conflict): ?>
      <div class="conflict-item medium">
        <p><strong>Role:</strong> <?php echo htmlspecialchars($conflict['role_name']); ?></p>
        <?php foreach ($conflict['inconsistencies'] as $inc): ?>
          <p>• <?php echo htmlspecialchars($inc['issue']); ?> (<?php echo $inc['module']; ?> module)</p>
        <?php endforeach; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="conflict-section">
      <h2>Recommendations</h2>
      <ul>
        <?php foreach ($report['recommendations'] as $rec): ?>
          <li><strong><?php echo ucfirst(str_replace('_', ' ', $rec['category'])); ?>:</strong> <?php echo htmlspecialchars($rec['recommendation']); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</body>
</html>
