<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();

// Check if user has admin privileges
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /dashboard.php');
    exit;
}

$dir = 'admin/system';
$page = 'sql-logs';

$table_page = false;
$search = false;
$paginate = false;
$button_new = false;
$button_showall = false;
$button_back = true;
$button_refresh = true;

require LANG . '/en.php';
$title = 'SQL Error Logs';
$title_icon = '<i class="fa-solid fa-database" aria-hidden="true"></i>';

// Initialize SQL logger
$sqlLogger = new SqlErrorLogger();

// Handle actions
$action = $_GET['action'] ?? '';
$logType = $_GET['type'] ?? 'errors';

if ($action === 'clear' && $logType === 'errors') {
    $sqlLogger->clearErrorLog();
    $_SESSION['success_message'] = 'SQL error log cleared successfully';
    header('Location: sql-logs.php?type=errors');
    exit;
}

if ($action === 'clear' && $logType === 'execution') {
    $sqlLogger->clearExecutionLog();
    $_SESSION['success_message'] = 'SQL execution log cleared successfully';
    header('Location: sql-logs.php?type=execution');
    exit;
}

// Get logs
$limit = $_GET['limit'] ?? 100;
$errorLogs = $sqlLogger->getRecentErrors($limit);
$executionLogs = $sqlLogger->getExecutionLogs($limit);
$logSizes = $sqlLogger->getLogSizes();

require HEADER;
require BODY;
require NAV;
require SECTIONOPEN;
?>

<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <?= $_SESSION['success_message'] ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['success_message']); endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <?= $_SESSION['error_message'] ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['error_message']); endif; ?>

<!-- Log Type Tabs -->
<ul class="nav nav-tabs mb-4" id="logTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link <?= $logType === 'errors' ? 'active' : '' ?>" 
            onclick="window.location.href='sql-logs.php?type=errors'">
      <i class="fa-solid fa-exclamation-triangle me-2"></i>SQL Errors
      <?php if (count($errorLogs) > 0): ?>
        <span class="badge bg-danger ms-2"><?= count($errorLogs) ?></span>
      <?php endif; ?>
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link <?= $logType === 'execution' ? 'active' : '' ?>" 
            onclick="window.location.href='sql-logs.php?type=execution'">
      <i class="fa-solid fa-list me-2"></i>SQL Execution
      <?php if (count($executionLogs) > 0): ?>
        <span class="badge bg-info ms-2"><?= count($executionLogs) ?></span>
      <?php endif; ?>
    </button>
  </li>
</ul>

<!-- Log Statistics -->
<div class="row mb-4">
  <div class="col-md-6">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">
          <i class="fa-solid fa-chart-bar me-2"></i>Log Statistics
        </h5>
        <div class="row">
          <div class="col-6">
            <div class="text-center">
              <h3 class="text-danger"><?= count($errorLogs) ?></h3>
              <small class="text-muted">Error Entries</small>
            </div>
          </div>
          <div class="col-6">
            <div class="text-center">
              <h3 class="text-info"><?= count($executionLogs) ?></h3>
              <small class="text-muted">Execution Entries</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">
          <i class="fa-solid fa-hdd me-2"></i>Log File Sizes
        </h5>
        <div class="row">
          <div class="col-6">
            <div class="text-center">
              <h3 class="text-danger"><?= number_format($logSizes['error_log_size'] / 1024, 1) ?> KB</h3>
              <small class="text-muted">Error Log</small>
            </div>
          </div>
          <div class="col-6">
            <div class="text-center">
              <h3 class="text-info"><?= number_format($logSizes['execution_log_size'] / 1024, 1) ?> KB</h3>
              <small class="text-muted">Execution Log</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Controls -->
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <label for="limitSelect" class="form-label me-2">Show entries:</label>
    <select id="limitSelect" class="form-select form-select-sm d-inline-block w-auto" 
            onchange="window.location.href='sql-logs.php?type=<?= $logType ?>&limit=' + this.value">
      <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
      <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100</option>
      <option value="200" <?= $limit == 200 ? 'selected' : '' ?>>200</option>
      <option value="500" <?= $limit == 500 ? 'selected' : '' ?>>500</option>
    </select>
  </div>
  <div>
    <?php if ($logType === 'errors' && count($errorLogs) > 0): ?>
      <button class="btn btn-danger btn-sm" onclick="confirmClear('errors')">
        <i class="fa-solid fa-trash me-1"></i>Clear Error Log
      </button>
    <?php endif; ?>
    <?php if ($logType === 'execution' && count($executionLogs) > 0): ?>
      <button class="btn btn-warning btn-sm" onclick="confirmClear('execution')">
        <i class="fa-solid fa-trash me-1"></i>Clear Execution Log
      </button>
    <?php endif; ?>
  </div>
</div>

<!-- SQL Error Logs -->
<?php if ($logType === 'errors'): ?>
  <div class="card">
    <div class="card-header bg-danger text-white">
      <h5 class="mb-0">
        <i class="fa-solid fa-exclamation-triangle me-2"></i>SQL Error Log
      </h5>
    </div>
    <div class="card-body">
      <?php if (empty($errorLogs)): ?>
        <div class="text-center text-muted py-4">
          <i class="fa-solid fa-check-circle fa-3x mb-3"></i>
          <h5>No SQL errors found</h5>
          <p>This is good news! Your application is running without SQL errors.</p>
        </div>
      <?php else: ?>
        <div class="log-container" style="max-height: 600px; overflow-y: auto;">
          <pre class="bg-dark text-light p-3 rounded" style="font-size: 12px; line-height: 1.4;"><?php
            foreach ($errorLogs as $line) {
              // Highlight different types of log entries
              if (strpos($line, 'SQL ERROR:') !== false) {
                echo '<span class="text-danger">' . htmlspecialchars($line) . '</span>' . "\n";
              } elseif (strpos($line, 'PARAMETER MISMATCH') !== false) {
                echo '<span class="text-warning">' . htmlspecialchars($line) . '</span>' . "\n";
              } elseif (strpos($line, 'FORM ERROR:') !== false) {
                echo '<span class="text-info">' . htmlspecialchars($line) . '</span>' . "\n";
              } else {
                echo htmlspecialchars($line) . "\n";
              }
            }
          ?></pre>
        </div>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<!-- SQL Execution Logs -->
<?php if ($logType === 'execution'): ?>
  <div class="card">
    <div class="card-header bg-info text-white">
      <h5 class="mb-0">
        <i class="fa-solid fa-list me-2"></i>SQL Execution Log
      </h5>
    </div>
    <div class="card-body">
      <?php if (!defined('DEBUG_SQL') || !DEBUG_SQL): ?>
        <div class="alert alert-warning">
          <i class="fa-solid fa-info-circle me-2"></i>
          <strong>SQL Execution Logging is Disabled</strong><br>
          To enable detailed SQL execution logging, set <code>DEBUG_SQL = true</code> in your system configuration.
          <br><small class="text-muted">Note: This will log all SQL queries and should only be enabled for debugging.</small>
        </div>
      <?php endif; ?>
      
      <?php if (empty($executionLogs)): ?>
        <div class="text-center text-muted py-4">
          <i class="fa-solid fa-database fa-3x mb-3"></i>
          <h5>No execution logs found</h5>
          <p>Enable DEBUG_SQL in system configuration to see detailed SQL execution logs.</p>
        </div>
      <?php else: ?>
        <div class="log-container" style="max-height: 600px; overflow-y: auto;">
          <pre class="bg-dark text-light p-3 rounded" style="font-size: 12px; line-height: 1.4;"><?php
            foreach ($executionLogs as $line) {
              // Highlight different types of log entries
              if (strpos($line, 'SUCCESS') !== false) {
                echo '<span class="text-success">' . htmlspecialchars($line) . '</span>' . "\n";
              } elseif (strpos($line, 'FAILED') !== false) {
                echo '<span class="text-danger">' . htmlspecialchars($line) . '</span>' . "\n";
              } elseif (strpos($line, 'SQL Query:') !== false) {
                echo '<span class="text-primary">' . htmlspecialchars($line) . '</span>' . "\n";
              } elseif (strpos($line, 'Parameters:') !== false) {
                echo '<span class="text-warning">' . htmlspecialchars($line) . '</span>' . "\n";
              } else {
                echo htmlspecialchars($line) . "\n";
              }
            }
          ?></pre>
        </div>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<script>
function confirmClear(type) {
    const typeName = type === 'errors' ? 'error' : 'execution';
    if (confirm(`Are you sure you want to clear the SQL ${typeName} log? This action cannot be undone.`)) {
        window.location.href = `sql-logs.php?action=clear&type=${type}`;
    }
}

// Auto-refresh every 30 seconds if there are recent errors
<?php if (count($errorLogs) > 0): ?>
setTimeout(function() {
    window.location.reload();
}, 30000);
<?php endif; ?>
</script>

<?php
require SECTIONCLOSE;
require FOOTER;
?>