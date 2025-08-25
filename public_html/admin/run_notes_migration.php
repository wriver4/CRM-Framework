<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();

$table_page = false;

$dir = 'admin';
$page = 'run_notes_migration';

$title = 'Run Notes Migration';
$title_icon = '<i class="fa-solid fa-database"></i>';

// Handle form submission
$migrationResults = null;
$error = null;

if ($_POST && isset($_POST['action'])) {
    try {
        require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/scripts/migrate_notes.php';
        
        $migration = new NotesMigration();
        $dryRun = ($_POST['action'] === 'dry_run');
        
        // Capture output
        ob_start();
        $log = $migration->migrate($dryRun);
        $output = ob_get_clean();
        
        $migrationResults = [
            'log' => $log,
            'output' => $output,
            'mode' => $dryRun ? 'DRY RUN' : 'LIVE MIGRATION'
        ];
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

require HEADER;
require BODY;
require NAV;
require SECTIONOPEN;
?>

<div class="container-fluid">
  <div class="row mb-3">
    <div class="col-12">
      <h2><?= $title_icon ?> <?= $title ?></h2>
      <p class="text-muted">Migrate notes from leads table to structured notes table</p>
    </div>
  </div>

  <?php if ($error): ?>
  <div class="alert alert-danger">
    <i class="fa-solid fa-exclamation-circle"></i>
    <strong>Error:</strong> <?= htmlspecialchars($error) ?>
  </div>
  <?php endif; ?>

  <?php if ($migrationResults): ?>
  <div class="row mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header <?= $migrationResults['mode'] === 'DRY RUN' ? 'bg-info' : 'bg-success' ?> text-white">
          <h5 class="mb-0">
            <i class="fa-solid fa-<?= $migrationResults['mode'] === 'DRY RUN' ? 'eye' : 'check-circle' ?>"></i>
            <?= $migrationResults['mode'] ?> Results
          </h5>
        </div>
        <div class="card-body">
          <pre
               style="background: #f8f9fa; padding: 15px; border-radius: 5px; max-height: 400px; overflow-y: auto; font-size: 12px;"><?php
                    foreach ($migrationResults['log'] as $line) {
                        echo htmlspecialchars($line) . "\n";
                    }
                    ?></pre>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <div class="row">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0"><i class="fa-solid fa-play"></i> Migration Controls</h5>
        </div>
        <div class="card-body">
          <form method="POST">
            <div class="mb-3">
              <h6>Choose Migration Mode:</h6>
              <div class="btn-group w-100"
                   role="group">
                <button type="submit"
                        name="action"
                        value="dry_run"
                        class="btn btn-info">
                  <i class="fa-solid fa-eye"></i> Dry Run
                  <br><small>Preview changes without modifying database</small>
                </button>
                <button type="submit"
                        name="action"
                        value="live"
                        class="btn btn-success"
                        onclick="return confirm('Are you sure you want to run the live migration? Make sure you have backed up your database first!')">
                  <i class="fa-solid fa-database"></i> Live Migration
                  <br><small>Actually modify the database</small>
                </button>
              </div>
            </div>

            <div class="alert alert-warning">
              <i class="fa-solid fa-exclamation-triangle"></i>
              <strong>Before running live migration:</strong>
              <ul class="mb-0 mt-2">
                <li>Backup your database</li>
                <li>Run a dry-run first to preview changes</li>
                <li>Ensure you have sufficient disk space</li>
                <li>Consider running during low-traffic hours</li>
              </ul>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card">
        <div class="card-header bg-secondary text-white">
          <h5 class="mb-0"><i class="fa-solid fa-info"></i> What This Does</h5>
        </div>
        <div class="card-body">
          <h6>Migration Process:</h6>
          <ol style="font-size: 14px;">
            <li>Reads all leads with notes</li>
            <li>Parses notes for date patterns</li>
            <li>Extracts dated entries</li>
            <li>Creates separate note records</li>
            <li>Links to original lead</li>
          </ol>

          <h6 class="mt-3">Supported Date Formats:</h6>
          <ul style="font-size: 14px;">
            <li><code>2/3/25</code></li>
            <li><code>12/20/24</code></li>
            <li><code>1/15/2025</code></li>
            <li><code>M/D/YY H:MM</code></li>
          </ul>
        </div>
      </div>

      <div class="card mt-3">
        <div class="card-header bg-warning text-dark">
          <h5 class="mb-0"><i class="fa-solid fa-link"></i> Quick Links</h5>
        </div>
        <div class="card-body">
          <a href="test_notes_migration.php"
             class="btn btn-sm btn-outline-primary mb-2 w-100">
            <i class="fa-solid fa-vial"></i> Test Parser
          </a>
          <a href="../leads/list.php"
             class="btn btn-sm btn-outline-secondary mb-2 w-100">
            <i class="fa-solid fa-list"></i> View Leads
          </a>
          <a href="../leads/compare_notes.php"
             class="btn btn-sm btn-outline-success w-100">
            <i class="fa-solid fa-code-compare"></i> Compare Notes
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
require SECTIONCLOSE;
require FOOTER;
?>