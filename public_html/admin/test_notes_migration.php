<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();

$table_page = false;
$dir = 'admin';
$page = 'test_notes_migration';

$title = 'Test Notes Migration';
$title_icon = '<i class="fa-solid fa-vial"></i>';

// Test the new undated entry logic
function testNotesParsing() {
    $testCase = [
        'notes' => 'Initial contact made through website form.
Customer seemed very interested.
2/3/25 I emailed, texted, called Sophie Frankel.
2/4/25 Sophie Frankel texted back.
Follow-up needed next week.
12/20/24 Randy created a proposal.
Final note without date.',
        'created_at' => '2024-01-01 00:00:00'
    ];
    
    $results = [];
    $lines = explode("\n", $testCase['notes']);
    $undatedLines = [];
    $isFirstEntry = true;
    $currentEntry = null;
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Check if line starts with a date
        if (preg_match('/^(\d{1,2}\/\d{1,2}\/\d{2,4})\s*(.*)$/', $line, $matches)) {
            $dateStr = $matches[1];
            $text = $matches[2];
            
            // Handle accumulated undated lines
            if (!empty($undatedLines)) {
                if ($isFirstEntry) {
                    // First undated content gets lead creation date
                    $results[] = [
                        'type' => 'first_undated',
                        'date' => $testCase['created_at'],
                        'text' => implode("\n", $undatedLines),
                        'combined' => true
                    ];
                    $isFirstEntry = false;
                } else {
                    // Combine with last dated entry
                    if (!empty($results)) {
                        $lastIndex = count($results) - 1;
                        $results[$lastIndex]['text'] .= "\n" . implode("\n", $undatedLines);
                        $results[$lastIndex]['combined'] = true;
                    }
                }
                $undatedLines = [];
            }
            
            // Save previous dated entry
            if ($currentEntry) {
                $results[] = $currentEntry;
            }
            
            // Parse date
            $parts = explode('/', $dateStr);
            $month = (int)$parts[0];
            $day = (int)$parts[1];
            $year = (int)$parts[2];
            
            if ($year < 100) {
                $year += ($year < 50) ? 2000 : 1900;
            }
            
            $parsedDate = sprintf('%04d-%02d-%02d 00:00:00', $year, $month, $day);
            
            // Start new dated entry
            $currentEntry = [
                'type' => 'dated',
                'date' => $parsedDate,
                'original_date' => $dateStr,
                'text' => $text,
                'combined' => false
            ];
            $isFirstEntry = false;
            
        } else {
            // Undated line
            if ($currentEntry) {
                // Add to current dated entry
                $currentEntry['text'] .= "\n" . $line;
                $currentEntry['combined'] = true;
            } else {
                // Accumulate undated lines
                $undatedLines[] = $line;
            }
        }
    }
    
    // Handle final entries
    if ($currentEntry) {
        $results[] = $currentEntry;
    }
    
    // Handle remaining undated lines
    if (!empty($undatedLines)) {
        if ($isFirstEntry) {
            // All content was undated
            $results[] = [
                'type' => 'all_undated',
                'date' => $testCase['created_at'],
                'text' => implode("\n", $undatedLines),
                'combined' => true
            ];
        } else {
            // Combine with last dated entry
            if (!empty($results)) {
                $lastIndex = count($results) - 1;
                $results[$lastIndex]['text'] .= "\n" . implode("\n", $undatedLines);
                $results[$lastIndex]['combined'] = true;
            }
        }
    }
    
    return $results;
}

$testResults = testNotesParsing();

require HEADER;
require BODY;
require NAV;
require SECTIONOPEN;
?>

<div class="container-fluid">
  <div class="row mb-3">
    <div class="col-12">
      <h2><?= $title_icon ?> <?= $title ?></h2>
      <p class="text-muted">Testing the notes migration parsing logic</p>
    </div>
  </div>

  <div class="row">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header bg-info text-white">
          <h5 class="mb-0"><i class="fa-solid fa-file-text"></i> Sample Notes Text</h5>
        </div>
        <div class="card-body">
          <pre style="font-size: 12px; background: #f8f9fa; padding: 15px; border-radius: 5px;">Initial contact made through website form.
Customer seemed very interested.
2/3/25 I emailed, texted, called Sophie Frankel.
2/4/25 Sophie Frankel texted back.
Follow-up needed next week.
12/20/24 Randy created a proposal.
Final note without date.</pre>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card">
        <div class="card-header bg-success text-white">
          <h5 class="mb-0"><i class="fa-solid fa-list"></i> Parsed Results</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <span class="badge bg-primary">Total Entries: <?= count($testResults) ?></span>
            <span class="badge bg-success">Dated: <?= count(array_filter($testResults, fn($r) => $r['type'] === 'dated')) ?></span>
            <span class="badge bg-info">First Undated: <?= count(array_filter($testResults, fn($r) => $r['type'] === 'first_undated')) ?></span>
            <span class="badge bg-warning">Combined: <?= count(array_filter($testResults, fn($r) => isset($r['combined']) && $r['combined'])) ?></span>
          </div>

          <?php foreach ($testResults as $i => $result): ?>
          <div class="card mb-2 <?= $result['type'] === 'dated' ? 'border-success' : ($result['type'] === 'first_undated' ? 'border-info' : 'border-warning') ?>">
            <div class="card-body p-2">
              <div class="d-flex justify-content-between align-items-start mb-1">
                <small class="text-muted">Entry <?= $i + 1 ?></small>
                <div>
                  <span class="badge <?= $result['type'] === 'dated' ? 'bg-success' : ($result['type'] === 'first_undated' ? 'bg-info' : 'bg-warning') ?>">
                    <?= ucfirst(str_replace('_', ' ', $result['type'])) ?>
                  </span>
                  <?php if (isset($result['combined']) && $result['combined']): ?>
                    <span class="badge bg-secondary ms-1">Combined</span>
                  <?php endif; ?>
                </div>
              </div>
              <div class="mb-1">
                <strong>Date:</strong>
                <code><?= $result['date'] ?></code>
                <?php if (isset($result['original_date'])): ?>
                  <small class="text-muted">(from: <?= $result['original_date'] ?>)</small>
                <?php endif; ?>
              </div>
              <div class="mb-1">
                <strong>Text:</strong>
                <div style="font-size: 12px; white-space: pre-line; background: #f8f9fa; padding: 8px; border-radius: 3px;"><?= htmlspecialchars($result['text']) ?></div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="row mt-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0"><i class="fa-solid fa-info-circle"></i> Migration Instructions</h5>
        </div>
        <div class="card-body">
          <h6>Ready to run the migration?</h6>
          <ol>
            <li><strong>Backup your database first!</strong></li>
            <li>Test with dry-run mode:
              <code>php /workspace/scripts/migrate_notes.php --dry-run</code>
            </li>
            <li>Run live migration:
              <code>php /workspace/scripts/migrate_notes.php --live</code>
            </li>
          </ol>

          <div class="alert alert-warning mt-3">
            <i class="fa-solid fa-exclamation-triangle"></i>
            <strong>Important:</strong> This will create entries in the notes table for each dated entry found in lead
            notes.
            Make sure to backup your database before running the live migration.
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
require SECTIONCLOSE;
require FOOTER;
?>