<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$dir = 'leads';
$page = 'compare_notes';
$table_page = false;
$title = 'Compare Notes';
$title_icon = '<i class="fa-solid fa-code-compare"></i>';

// Get PDO connection
$database = new Database();
$pdo = $database->dbcrm();

// Get lead ID from URL or set to first available
$current_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Get all leads with both notes and lead_lost_notes (non-null lead_lost_notes with actual content)
$sql = "SELECT id, first_name, family_name, notes, lead_lost_notes 
        FROM leads 
        WHERE notes IS NOT NULL 
        AND lead_lost_notes IS NOT NULL 
        AND TRIM(lead_lost_notes) != ''
        AND TRIM(lead_lost_notes) != ' '
        ORDER BY id";
$stmt = $pdo->query($sql);
$leads_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If no current ID set, use first lead
if (!$current_id && !empty($leads_data)) {
    $current_id = $leads_data[0]['id'];
}

// Get current lead data
$current_lead = null;
$current_index = 0;
foreach ($leads_data as $index => $lead) {
    if ($lead['id'] == $current_id) {
        $current_lead = $lead;
        $current_index = $index;
        break;
    }
}

// Get next and previous IDs
$prev_id = $current_index > 0 ? $leads_data[$current_index - 1]['id'] : null;
$next_id = $current_index < count($leads_data) - 1 ? $leads_data[$current_index + 1]['id'] : null;

require HEADER;
require BODY;
require NAV;
require SECTIONOPEN;

// Show success/error messages
if (isset($_GET['saved'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-check-circle"></i> Changes saved successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
}
if (isset($_GET['error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-exclamation-circle"></i> An error occurred while saving changes.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
}
?>

<div class="container-fluid">
  <div class="row mb-3">
    <div class="col-12">
      <h2><?= $title_icon ?> <?= $title ?></h2>
      <?php if ($current_lead): ?>
      <p class="text-muted">Lead ID: <?= $current_lead['id'] ?> -
        <?= htmlspecialchars($current_lead['first_name'] . ' ' . $current_lead['family_name']) ?></p>
      <p class="text-muted">Record <?= $current_index + 1 ?> of <?= count($leads_data) ?></p>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($current_lead): ?>
  <!-- Navigation -->
  <div class="row mb-3">
    <div class="col-12">
      <div class="btn-group"
           role="group">
        <?php if ($prev_id): ?>
        <a href="?id=<?= $prev_id ?>"
           class="btn btn-secondary">
          <i class="fa-solid fa-chevron-left"></i> Previous
        </a>
        <?php endif; ?>

        <?php if ($next_id): ?>
        <a href="?id=<?= $next_id ?>"
           class="btn btn-secondary">
          Next <i class="fa-solid fa-chevron-right"></i>
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Form for updating -->
  <form id="updateForm"
        method="POST"
        action="compare_notes_save.php">
    <input type="hidden"
           name="lead_id"
           value="<?= $current_lead['id'] ?>">
    <input type="hidden"
           name="next_id"
           value="<?= $next_id ?>">

    <div class="row">
      <!-- Notes Column -->
      <div class="col-md-6">
        <div class="card">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fa-solid fa-sticky-note"></i> Notes</h5>
          </div>
          <div class="card-body">
            <textarea class="form-control"
                      name="notes"
                      rows="20"
                      style="font-family: monospace; font-size: 12px;"><?= htmlspecialchars($current_lead['notes']) ?></textarea>
          </div>
        </div>
      </div>

      <!-- Lead Lost Notes Column -->
      <div class="col-md-6">
        <div class="card">
          <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><i class="fa-solid fa-times-circle"></i> Lead Lost Notes</h5>
          </div>
          <div class="card-body">
            <textarea class="form-control"
                      name="lead_lost_notes"
                      rows="20"
                      style="font-family: monospace; font-size: 12px;"><?= htmlspecialchars($current_lead['lead_lost_notes']) ?></textarea>
          </div>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mt-3">
      <div class="col-12">
        <div class="btn-group"
             role="group">
          <button type="submit"
                  name="action"
                  value="save"
                  class="btn btn-success">
            <i class="fa-solid fa-save"></i> Save Changes
          </button>
          <button type="submit"
                  name="action"
                  value="clear_lead_lost_notes"
                  class="btn btn-danger">
            <i class="fa-solid fa-trash"></i> Clear Lead Lost Notes
          </button>
          <button type="submit"
                  name="action"
                  value="save_and_next"
                  class="btn btn-primary">
            <i class="fa-solid fa-forward"></i> Save & Next
          </button>
        </div>
      </div>
    </div>
  </form>

  <?php else: ?>
  <div class="alert alert-info">
    <i class="fa-solid fa-info-circle"></i> No leads found with both notes and lead_notes fields populated.
  </div>
  <?php endif; ?>
</div>

<script>
// Auto-save functionality with Ctrl+S
document.addEventListener('keydown', function(e) {
  if ((e.ctrlKey || e.metaKey) && e.key === 's') {
    e.preventDefault();
    document.getElementById('updateForm').submit();
  }

  // Navigate with arrow keys (Ctrl + Left/Right)
  if ((e.ctrlKey || e.metaKey) && e.key === 'ArrowLeft') {
    e.preventDefault();
    const prevBtn = document.querySelector('a[href*="id=<?= $prev_id ?>"]');
    if (prevBtn) prevBtn.click();
  }

  if ((e.ctrlKey || e.metaKey) && e.key === 'ArrowRight') {
    e.preventDefault();
    const nextBtn = document.querySelector('a[href*="id=<?= $next_id ?>"]');
    if (nextBtn) nextBtn.click();
  }
});

// Highlight differences and provide comparison feedback
document.addEventListener('DOMContentLoaded', function() {
  const notesTextarea = document.querySelector('textarea[name="notes"]');
  const leadLostNotesTextarea = document.querySelector('textarea[name="lead_lost_notes"]');

  function updateComparison() {
    if (notesTextarea && leadLostNotesTextarea) {
      const notesText = notesTextarea.value.trim();
      const leadLostNotesText = leadLostNotesTextarea.value.trim();

      // Compare normalized content (ignore spacing and line breaks)
      const normalizedNotes = notesText.replace(/\s+/g, ' ').replace(/[\r\n]/g, '');
      const normalizedLeadLostNotes = leadLostNotesText.replace(/\s+/g, ' ').replace(/[\r\n]/g, '');

      if (normalizedNotes === normalizedLeadLostNotes) {
        notesTextarea.style.borderColor = '#28a745';
        leadLostNotesTextarea.style.borderColor = '#28a745';
        notesTextarea.style.borderWidth = '3px';
        leadLostNotesTextarea.style.borderWidth = '3px';
      } else {
        notesTextarea.style.borderColor = '#dc3545';
        leadLostNotesTextarea.style.borderColor = '#dc3545';
        notesTextarea.style.borderWidth = '3px';
        leadLostNotesTextarea.style.borderWidth = '3px';
      }
    }
  }

  function adjustTextareaHeights() {
    if (notesTextarea && leadLostNotesTextarea) {
      // Calculate lines needed for each textarea
      const notesLines = notesTextarea.value.split('\n').length;
      const leadLostNotesLines = leadLostNotesTextarea.value.split('\n').length;
      
      // Use the maximum lines, with a minimum of 20 - no maximum limit
      const maxLines = Math.max(notesLines, leadLostNotesLines, 20);
      
      // Set both textareas to the same height to show all content
      notesTextarea.rows = maxLines;
      leadLostNotesTextarea.rows = maxLines;
    }
  }

  // Initial comparison and height adjustment
  updateComparison();
  adjustTextareaHeights();

  // Update on change
  if (notesTextarea) {
    notesTextarea.addEventListener('input', function() {
      updateComparison();
      adjustTextareaHeights();
    });
  }
  if (leadLostNotesTextarea) {
    leadLostNotesTextarea.addEventListener('input', function() {
      updateComparison();
      adjustTextareaHeights();
    });
  }

  // Auto-dismiss alerts after 3 seconds
  setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
      const bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
    });
  }, 3000);
});

// Confirmation for clearing lead lost notes
document.querySelector('button[name="action"][value="clear_lead_lost_notes"]').addEventListener('click', function(e) {
  if (!confirm('Are you sure you want to clear the lead lost notes? This action cannot be undone.')) {
    e.preventDefault();
  }
});
</script>

<?php
require SECTIONCLOSE;
require FOOTER;
