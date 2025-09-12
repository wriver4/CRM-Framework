<?php
/**
 * Web-based phpList Migration Runner
 * 
 * This script runs the phpList database migration through the web interface
 * Access: /admin/phplist/migrate.php
 */

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

// Check if user is logged in and has admin privileges
$not->loggedin();

// Load language file
$lang = include __DIR__ . '/../languages/en.php';

$migrationResults = [];
$migrationError = null;

// Handle migration execution
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_migration'])) {
    try {
        // Initialize database connection
        $database = new Database();
        $pdo = $database->dbcrm();
        
        $migrationResults[] = "Starting phpList migration...";
        
        // Read the migration SQL file
        $migrationFile = dirname(dirname(dirname(__DIR__))) . '/sql/migrations/simple_safe_migration.sql';
        
        if (!file_exists($migrationFile)) {
            throw new Exception("Migration file not found: $migrationFile");
        }
        
        $sql = file_get_contents($migrationFile);
        
        if ($sql === false) {
            throw new Exception("Failed to read migration file");
        }
        
        $migrationResults[] = "Migration file loaded successfully";
        
        // Split SQL into individual statements
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function($stmt) {
                return !empty($stmt) && !preg_match('/^\s*--/', $stmt);
            }
        );
        
        $migrationResults[] = "Found " . count($statements) . " SQL statements to execute";
        
        // Begin transaction
        $pdo->beginTransaction();
        
        $executedCount = 0;
        
        foreach ($statements as $statement) {
            if (empty(trim($statement))) {
                continue;
            }
            
            $shortStatement = substr(trim($statement), 0, 50) . "...";
            $migrationResults[] = "Executing: $shortStatement";
            
            try {
                $pdo->exec($statement);
                $executedCount++;
                $migrationResults[] = "  ✓ Success";
            } catch (PDOException $e) {
                // Check if it's a "table already exists" error or constraint already exists
                if (strpos($e->getMessage(), 'already exists') !== false) {
                    $migrationResults[] = "  ⚠ Warning: Already exists, skipping...";
                    continue;
                } else {
                    throw $e;
                }
            }
        }
        
        // Commit transaction
        $pdo->commit();
        
        $migrationResults[] = "";
        $migrationResults[] = "Migration completed successfully!";
        $migrationResults[] = "Executed $executedCount SQL statements";
        
        // Verify tables were created
        $migrationResults[] = "";
        $migrationResults[] = "Verifying table creation...";
        
        $tables = ['phplist_subscribers', 'phplist_config', 'phplist_sync_log'];
        
        foreach ($tables as $table) {
            $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            $result = $stmt->fetchAll(); // Consume all results to close the query
            
            if (count($result) > 0) {
                $migrationResults[] = "  ✓ Table '$table' exists";
            } else {
                $migrationResults[] = "  ✗ Table '$table' not found";
            }
            $stmt = null; // Explicitly close the statement
        }
        
        // Check if configuration data was inserted
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM phplist_config");
        $stmt->execute();
        $result = $stmt->fetchAll();
        $configCount = $result[0][0]; // Get the count from the first row, first column
        $stmt = null; // Explicitly close the statement
        
        $migrationResults[] = "  ✓ Configuration records: $configCount";
        
        $migrationResults[] = "";
        $migrationResults[] = "phpList integration is ready to use!";
        $migrationResults[] = "Next steps:";
        $migrationResults[] = "1. Configure phpList settings in the admin panel";
        $migrationResults[] = "2. Set up the cron job for syncing";
        $migrationResults[] = "3. Test the integration by creating a new lead";
        
        $_SESSION['success_message'] = 'phpList migration completed successfully!';
        
    } catch (Exception $e) {
        // Rollback transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollback();
        }
        
        $migrationError = "Migration failed: " . $e->getMessage();
        $migrationResults[] = $migrationError;
        
        $_SESSION['error_message'] = $migrationError;
    }
}

// Check if tables already exist
$tablesExist = false;
try {
    $database = new Database();
    $pdo = $database->dbcrm();
    
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'phplist_subscribers'");
    $stmt->execute();
    $result = $stmt->fetchAll(); // Consume all results
    $tablesExist = (count($result) > 0);
    $stmt = null; // Explicitly close the statement
    
} catch (Exception $e) {
    // Ignore errors for now
}

// Page variables
// Direct routing variables - these determine page navigation and template inclusion
$dir = 'admin';
$subdir = 'phplist';
$page = 'migrate';
$table_page = false;

// Page title
$title = 'phpList Migration';
$title_icon = '<i class="fas fa-database"></i>';

// Include header
include dirname(dirname(__DIR__)) . '/templates/header.php';
?>

<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-database"></i> phpList Migration</h1>
        <div>
          <a href="config.php"
             class="btn btn-secondary">
            <i class="fas fa-cog"></i> Configuration
          </a>
          <a href="../dashboard.php"
             class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
          </a>
        </div>
      </div>

      <?php if (isset($_SESSION['success_message'])): ?>
      <div class="alert alert-success alert-dismissible fade show"
           role="alert">
        <?= htmlspecialchars($_SESSION['success_message']) ?>
        <button type="button"
                class="btn-close"
                data-bs-dismiss="alert"></button>
      </div>
      <?php unset($_SESSION['success_message']); ?>
      <?php endif; ?>

      <?php if (isset($_SESSION['error_message'])): ?>
      <div class="alert alert-danger alert-dismissible fade show"
           role="alert">
        <?= htmlspecialchars($_SESSION['error_message']) ?>
        <button type="button"
                class="btn-close"
                data-bs-dismiss="alert"></button>
      </div>
      <?php unset($_SESSION['error_message']); ?>
      <?php endif; ?>

      <!-- Migration Status -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h5><i class="fas fa-info-circle"></i> Migration Status</h5>
            </div>
            <div class="card-body">
              <?php if ($tablesExist): ?>
              <div class="alert alert-info">
                <i class="fas fa-check-circle"></i>
                <strong>phpList tables already exist!</strong>
                <p class="mb-0 mt-2">The phpList integration tables are already installed. You can run the migration
                  again to update configuration or add missing tables.</p>
              </div>
              <?php else: ?>
              <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>phpList tables not found!</strong>
                <p class="mb-0 mt-2">The phpList integration tables need to be created. Click the button below to run
                  the migration.</p>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Migration Form -->
      <div class="card mb-4">
        <div class="card-header">
          <h5><i class="fas fa-play"></i> Run Migration</h5>
        </div>
        <div class="card-body">
          <p>This migration will create the following tables:</p>
          <ul>
            <li><strong>phplist_subscribers</strong> - Subscriber management and sync tracking</li>
            <li><strong>phplist_config</strong> - phpList integration configuration</li>
            <li><strong>phplist_sync_log</strong> - Sync operation logging</li>
          </ul>

          <p>The migration is safe to run multiple times - existing tables and data will not be affected.</p>

          <form method="POST">
            <button type="submit"
                    name="run_migration"
                    class="btn btn-primary btn-lg"
                    onclick="return confirm('Are you sure you want to run the phpList migration?')">
              <i class="fas fa-database"></i> Run Migration
            </button>
          </form>
        </div>
      </div>

      <!-- Migration Results -->
      <?php if (!empty($migrationResults)): ?>
      <div class="card">
        <div class="card-header">
          <h5>
            <i class="fas fa-terminal"></i> Migration Results
            <?php if ($migrationError): ?>
            <span class="badge bg-danger ms-2">Failed</span>
            <?php else: ?>
            <span class="badge bg-success ms-2">Success</span>
            <?php endif; ?>
          </h5>
        </div>
        <div class="card-body">
          <pre class="bg-dark text-light p-3 rounded"
               style="max-height: 400px; overflow-y: auto;"><?php
                            foreach ($migrationResults as $result) {
                                echo htmlspecialchars($result) . "\n";
                            }
                        ?></pre>

          <?php if (!$migrationError): ?>
          <div class="mt-3">
            <a href="config.php"
               class="btn btn-success">
              <i class="fas fa-cog"></i> Configure phpList Settings
            </a>
            <a href="subscribers.php"
               class="btn btn-info">
              <i class="fas fa-users"></i> View Subscribers
            </a>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Migration Information -->
      <div class="card mt-4">
        <div class="card-header">
          <h5><i class="fas fa-info"></i> What This Migration Does</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <h6>Database Changes</h6>
              <ul>
                <li>Creates phpList subscriber tracking table</li>
                <li>Creates configuration management table</li>
                <li>Creates sync operation logging table</li>
                <li>Adds proper indexes for performance</li>
                <li>Sets up foreign key relationships</li>
              </ul>
            </div>
            <div class="col-md-6">
              <h6>Default Configuration</h6>
              <ul>
                <li>Sync enabled by default</li>
                <li>15-minute sync frequency</li>
                <li>Maximum 3 retry attempts</li>
                <li>Batch size of 50 records</li>
                <li>Geographic and service list mapping</li>
              </ul>
            </div>
          </div>

          <div class="alert alert-info mt-3">
            <strong>Note:</strong> After running the migration, you'll need to configure your phpList API credentials in
            the configuration panel.
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include dirname(dirname(__DIR__)) . '/templates/footer.php'; ?>