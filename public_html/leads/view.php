<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$dir = 'leads';
$page = 'view';

$table_page = false;

require LANG . '/en.php';
$title = $lang['lead_view'] ?? 'View Lead';
$title_icon = '<i class="fa-solid fa-eye"></i>';

require 'get.php';

// Section Header Configuration
$section_header = true;
$section_header_subtitle = $last_edited_by_name ? 'Last edited by ' . $last_edited_by_name : '';

// Action buttons for header
$section_header_actions = [
    [
        'href' => 'list',
        'text' => 'Back',
        'icon' => 'fa-solid fa-arrow-left',
        'class' => 'btn-danger',
        'size' => 'btn-sm'
    ],
    [
        'href' => 'edit.php?id=' . $id,
        'text' => 'Edit <i class="fa-solid fa-arrow-right ms-1"></i>',
        'icon' => 'fa-solid fa-edit',
        'class' => 'btn-success',
        'size' => 'btn-sm'
    ]
];

// Record information for header
$section_header_info = [
    'Updated' => $updated_at ? date('M j, Y g:i A', strtotime($updated_at)) : '-',
    'by' => $last_edited_by_name ? $last_edited_by_name : '-'
];

require HEADER;
require BODY;
require NAV;
require SECTIONOPEN;

// Include section header
require __DIR__ . '/../templates/section_header.php';
?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <h6 class="text-muted"><?= $lang['lead_contact_information'] ?? 'Contact Information' ?></h6>
            <p><strong><?= $lang['lead_name'] ?? 'Name' ?>:</strong> <?= htmlspecialchars($first_name . ' ' . $last_name) ?></p>
            <p><strong><?= $lang['email'] ?? 'Email' ?>:</strong>
              <?php if ($email): ?>
              <a href="mailto:<?= htmlspecialchars($email) ?>"><?= htmlspecialchars($email) ?></a>
              <?php else: ?>
              <span class="text-muted">-</span>
              <?php endif; ?>
            </p>
            <p><strong><?= $lang['lead_phone'] ?? 'Phone' ?>:</strong>
              <?php if ($cell_phone): ?>
              <a href="tel:<?= htmlspecialchars($cell_phone) ?>"><?= htmlspecialchars($cell_phone) ?></a>
              <?php else: ?>
              <span class="text-muted">-</span>
              <?php endif; ?>
            </p>
            <p><strong><?= $lang['lead_contact_type'] ?? 'Contact Type' ?>:</strong>
              <?php 
                            $contact_types = $leads->get_lead_contact_type_array();
                            echo $contact_types[$ctype] ?? $ctype ?? '-';
                            ?>
            </p>
            <?php if (!empty($picture_upload_link) || !empty($plans_upload_link)): ?>
            <div class="mt-3">
              <p class="mb-2"><strong><?= $lang['lead_upload_links'] ?? 'Upload Links' ?>:</strong></p>
              <div class="d-flex flex-wrap gap-3">
                <?php if (!empty($picture_upload_link)): ?>
                <div class="d-flex align-items-center">
                  <a href="<?= htmlspecialchars($picture_upload_link) ?>"
                     target="_blank"
                     class="text-decoration-none me-2">
                    <i class="fa-solid fa-camera me-1"></i><?= $lang['lead_pictures_upload_link'] ?? 'Pictures Upload Link' ?>
                  </a>
                  <button type="button"
                          class="btn btn-sm btn-link p-0"
                          onclick="copyToClipboard('<?= htmlspecialchars($picture_upload_link, ENT_QUOTES) ?>', this)"
                          title="<?= $lang['lead_copy_link'] ?? 'Copy link' ?>">
                    <i class="fa-solid fa-copy"></i>
                  </button>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($plans_upload_link)): ?>
                <div class="d-flex align-items-center">
                  <a href="<?= htmlspecialchars($plans_upload_link) ?>"
                     target="_blank"
                     class="text-decoration-none me-2">
                    <i class="fa-solid fa-upload me-1"></i><?= $lang['lead_plans_upload_link'] ?? 'Plans Upload Link' ?>
                  </a>
                  <button type="button"
                          class="btn btn-sm btn-link p-0"
                          onclick="copyToClipboard('<?= htmlspecialchars($plans_upload_link, ENT_QUOTES) ?>', this)"
                          title="<?= $lang['lead_copy_link'] ?? 'Copy link' ?>">
                    <i class="fa-solid fa-copy"></i>
                  </button>
                </div>
                <?php endif; ?>
              </div>
            </div>
            <?php endif; ?>
          </div>
          <div class="col-md-6">
            <?php if ($lead_number): ?>
            <p><strong><?= $lang['lead_number'] ?? 'Lead #' ?>:</strong>
              <span class="badge bg-primary text-white">#<?= htmlspecialchars($lead_number) ?></span>
            </p>
            <?php endif; ?>
            <p><strong><?= $lang['lead_source'] ?? 'Source' ?>:</strong>
              <?php 
                            $lead_sources = $leads->get_lead_source_array();
                            echo $lead_sources[$lead_source] ?? $lead_source ?? '-';
                            ?>
            </p>
            <p><strong><?= $lang['lead_stage'] ?? 'Stage' ?>:</strong>
              <?php
                            // Get the proper stage display name using language translations
                            $stage_display = $leads->get_stage_display_name($stage, $lang);
                            $badge_class = $leads->get_stage_badge_class($stage);
                            ?>
              <span class="<?= $badge_class ?>"><?= htmlspecialchars($stage_display) ?></span>
            </p>
            <p><strong><?= $lang['lead_structure_type'] ?? 'Structure Type' ?>:</strong>
              <?php 
                            $structure_types = $leads->get_lead_structure_type_array();
                            echo $structure_types[$structure_type] ?? $structure_type ?? '-';
                            ?>
            </p>

            <?php 
            // Process structure information for inline display
            $structure_description_display = '';
            if (!empty($structure_description)) {
                $desc_map = $helpers->get_lead_structure_description_array($lang);
                $ids = array_filter(array_map('trim', explode(',', (string)$structure_description)));
                $names = [];
                foreach ($ids as $sid) {
                    if (isset($desc_map[$sid])) { $names[] = $desc_map[$sid]; }
                }
                if (!empty($names)) { $structure_description_display = implode(', ', $names); }
            }
            
            $has_structure_details = !empty($structure_description_display) || !empty($structure_other) || !empty($structure_additional);
            ?>

            <?php if ($has_structure_details): ?>
            <div class="mt-2">
              <?php if (!empty($structure_description_display)): ?>
              <p class="mb-1"><strong><?= $lang['lead_structure_description'] ?? 'Description' ?>:</strong>
                <?php 
                $desc_preview = strlen($structure_description_display) > 80 ? 
                    substr($structure_description_display, 0, 80) . '...' : 
                    $structure_description_display;
                echo htmlspecialchars($desc_preview);
                if (strlen($structure_description_display) > 80): ?>
                <button type="button"
                        class="btn btn-link btn-sm p-0 ms-1"
                        data-bs-toggle="modal"
                        data-bs-target="#structureModal">
                  <?= $lang['lead_more'] ?? 'more' ?>
                </button>
                <?php endif; ?>
              </p>
              <?php endif; ?>

              <?php if (!empty($structure_other)): ?>
              <p class="mb-1"><strong><?= $lang['lead_structure_other'] ?? 'Other Description' ?>:</strong>
                <?php 
                $other_preview = strlen($structure_other) > 60 ? 
                    substr($structure_other, 0, 60) . '...' : 
                    $structure_other;
                echo htmlspecialchars($other_preview);
                if (strlen($structure_other) > 60): ?>
                <button type="button"
                        class="btn btn-link btn-sm p-0 ms-1"
                        data-bs-toggle="modal"
                        data-bs-target="#structureModal">
                  <?= $lang['lead_more'] ?? 'more' ?>
                </button>
                <?php endif; ?>
              </p>
              <?php endif; ?>

              <?php if (!empty($structure_additional)): ?>
              <p class="mb-1"><strong><?= $lang['lead_structure_additional'] ?? 'Additional Buildings' ?>:</strong>
                <?php 
                $additional_preview = strlen($structure_additional) > 60 ? 
                    substr($structure_additional, 0, 60) . '...' : 
                    $structure_additional;
                echo htmlspecialchars($additional_preview);
                if (strlen($structure_additional) > 60): ?>
                <button type="button"
                        class="btn btn-link btn-sm p-0 ms-1"
                        data-bs-toggle="modal"
                        data-bs-target="#structureModal">
                  <?= $lang['lead_more'] ?? 'more' ?>
                </button>
                <?php endif; ?>
              </p>
              <?php endif; ?>


            </div>
            <?php endif; ?>
          </div>
        </div>



        <?php 
                // Activity Notes System
                try {
                  $notes_class = new Notes();
                  $search = $_GET['notes_search'] ?? '';
                  $order = $_GET['notes_order'] ?? 'DESC';
                  $existing_notes = $notes_class->get_notes_by_lead($id ?? 0, $search, $order);
                  $notes_count = $notes_class->get_notes_count_by_lead($id ?? 0, $search);
                  $total_notes = $notes_class->get_notes_count_by_lead($id ?? 0); // Total without search filter
                } catch (Exception $e) {
                  $existing_notes = [];
                  $notes_count = 0;
                  $total_notes = 0;
                  error_log('Notes system error in leads/view.php: ' . $e->getMessage());
                }
                ?>

        <?php if ($total_notes > 0): ?>
        <div class="row mt-3">
          <div class="col-12">
            <div class="border rounded bg-light">
              <!-- Notes Header with Search and Sort -->
              <div class="d-flex justify-content-between align-items-center p-3 border-bottom bg-white">
                <h6 class="text-muted mb-0">
                  Notes & Activity
                  (<span id="notes-count"><?= $notes_count ?></span> of <span
                        id="total-notes"><?= $total_notes ?></span>)
                  <?php if (!empty($search)): ?>
                  <span class="badge bg-info ms-1">filtered</span>
                  <?php endif; ?>
                </h6>
                <form method="GET"
                      id="notesFilterForm"
                      class="d-flex gap-2">
                  <input type="hidden"
                         name="id"
                         value="<?= htmlspecialchars($id) ?>">
                  <div class="input-group input-group-sm"
                       style="width: 200px;">
                    <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
                    <input type="text"
                           class="form-control"
                           name="notes_search"
                           id="notesSearch"
                           placeholder="Search notes..."
                           value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-outline-secondary"
                            type="button"
                            id="clearSearch">
                      <i class="fa-solid fa-times"></i>
                    </button>
                  </div>
                  <div class="input-group input-group-sm"
                       style="width: 140px;">
                    <span class="input-group-text"><i class="fa-solid fa-sort"></i></span>
                    <select class="form-select"
                            name="notes_order"
                            id="notesOrder">
                      <option value="DESC"
                              <?= $order === 'DESC' ? 'selected' : '' ?>>Newest First</option>
                      <option value="ASC"
                              <?= $order === 'ASC' ? 'selected' : '' ?>>Oldest First</option>
                    </select>
                  </div>
                </form>
              </div>

              <!-- Timeline Content -->
              <div class="p-3">
                <div class="timeline">
                  <?php foreach ($existing_notes as $note): ?>
                  <div class="timeline-item mb-4">
                    <div class="timeline-marker">
                      <div
                           class="timeline-marker-icon bg-<?= $note['source'] <= 3 ? 'primary' : ($note['source'] <= 6 ? 'success' : 'secondary') ?>">
                        <i class="fa-solid fa-<?= 
                                              $note['source'] == 2 ? 'phone' : (
                                              $note['source'] == 3 ? 'envelope' : (
                                              $note['source'] == 4 ? 'users' : (
                                              $note['source'] == 5 ? 'map-marker-alt' : (
                                              $note['source'] == 7 ? 'sms' : 'sticky-note'))))
                                            ?>"></i>
                      </div>
                      <div class="timeline-line"></div>
                    </div>
                    <div class="timeline-content">
                      <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                          <span class="<?= $notes_class->get_source_badge_class($note['source']) ?> me-2">
                            <?= $notes_class->get_note_source_array()[$note['source']] ?? 'Unknown' ?>
                          </span>
                          <?php if (!empty($note['form_source']) && $note['form_source'] != 'leads'): ?>
                          <small class="badge bg-light text-dark">from <?= ucfirst($note['form_source']) ?></small>
                          <?php endif; ?>
                        </div>
                        <small class="text-muted">
                          <?= date('M d, Y g:i A', strtotime($note['date_created'])) ?>
                        </small>
                      </div>
                      <div class="note-text mb-2">
                        <?= nl2br(htmlspecialchars($note['note_text'])) ?>
                      </div>
                      <small class="text-muted">
                        <i class="fa-solid fa-user fa-sm me-1"></i>
                        <?= htmlspecialchars($note['full_name'] ?? $note['username'] ?? 'System') ?>
                      </small>
                    </div>
                  </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<style>
.timeline {
  position: relative;
}

.timeline-item {
  position: relative;
  display: flex;
}

.timeline-marker {
  flex-shrink: 0;
  width: 40px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.timeline-marker-icon {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 12px;
}

.timeline-line {
  width: 2px;
  flex-grow: 1;
  background: #dee2e6;
  margin-top: 8px;
}

.timeline-item:last-child .timeline-line {
  display: none;
}

.timeline-content {
  flex-grow: 1;
  margin-left: 16px;
  padding-bottom: 8px;
}

.note-text {
  background: #f8f9fa;
  padding: 12px;
  border-radius: 8px;
  border-left: 3px solid #dee2e6;
}

/* Floating Action Buttons */
.floating-actions {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 1050;
  display: none;
  flex-direction: column;
  gap: 10px;
}

.floating-actions.show {
  display: flex;
}

.floating-btn {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transition: all 0.3s ease;
  font-size: 18px;
  color: white;
}

.floating-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
  color: white;
  text-decoration: none;
}

.floating-btn.btn-danger {
  background-color: #dc3545;
}

.floating-btn.btn-success {
  background-color: #198754;
}

.floating-btn.btn-danger:hover {
  background-color: #bb2d3b;
}

.floating-btn.btn-success:hover {
  background-color: #157347;
}

/* Tooltip for floating buttons */
.floating-btn[data-bs-toggle="tooltip"] {
  position: relative;
}

@media (max-width: 768px) {
  .floating-actions {
    bottom: 15px;
    right: 15px;
  }

  .floating-btn {
    width: 48px;
    height: 48px;
    font-size: 16px;
  }
}
</style>

<!-- Floating Action Buttons -->
<div class="floating-actions"
     id="floatingActions">
  <a href="list"
     class="floating-btn btn-danger"
     data-bs-toggle="tooltip"
     data-bs-placement="left"
     title="Back to List">
    <i class="fa-solid fa-arrow-left"></i>
  </a>
  <a href="edit.php?id=<?= htmlspecialchars($id) ?>"
     class="floating-btn btn-success"
     data-bs-toggle="tooltip"
     data-bs-placement="left"
     title="Edit Lead">
    <i class="fa-solid fa-edit"></i>
  </a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Auto-submit form when search input changes (with debounce)
  let searchTimeout;
  const searchInput = document.getElementById('notesSearch');
  const orderSelect = document.getElementById('notesOrder');
  const clearButton = document.getElementById('clearSearch');
  const form = document.getElementById('notesFilterForm');

  if (searchInput) {
    searchInput.addEventListener('input', function() {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(function() {
        form.submit();
      }, 500); // 500ms debounce
    });
  }

  // Auto-submit when sort order changes
  if (orderSelect) {
    orderSelect.addEventListener('change', function() {
      form.submit();
    });
  }

  // Clear search functionality
  if (clearButton) {
    clearButton.addEventListener('click', function() {
      searchInput.value = '';
      form.submit();
    });
  }

  // Floating Action Buttons functionality
  const floatingActions = document.getElementById('floatingActions');

  function checkPageHeight() {
    const documentHeight = document.documentElement.scrollHeight;
    const viewportHeight = window.innerHeight;
    const pageHeightInVh = (documentHeight / viewportHeight) * 100;

    if (pageHeightInVh > 200) {
      floatingActions.classList.add('show');
    } else {
      floatingActions.classList.remove('show');
    }
  }

  // Check page height on load
  checkPageHeight();

  // Check page height on window resize
  window.addEventListener('resize', checkPageHeight);

  // Initialize Bootstrap tooltips for floating buttons
  if (typeof bootstrap !== 'undefined') {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  }
});

// Copy to clipboard function for upload links
function copyToClipboard(text, button) {
  navigator.clipboard.writeText(text).then(function() {
    // Success feedback
    const originalIcon = button.innerHTML;
    button.innerHTML = '<i class="fa-solid fa-check text-success"></i>';
    button.classList.add('text-success');

    setTimeout(function() {
      button.innerHTML = originalIcon;
      button.classList.remove('text-success');
    }, 2000);
  }).catch(function(err) {
    // Fallback for older browsers
    const textArea = document.createElement('textarea');
    textArea.value = text;
    document.body.appendChild(textArea);
    textArea.select();
    document.execCommand('copy');
    document.body.removeChild(textArea);

    // Success feedback
    const originalIcon = button.innerHTML;
    button.innerHTML = '<i class="fa-solid fa-check text-success"></i>';
    button.classList.add('text-success');

    setTimeout(function() {
      button.innerHTML = originalIcon;
      button.classList.remove('text-success');
    }, 2000);
  });
}
</script>

<!-- Structure Details Modal -->
<?php if ($has_structure_details): ?>
<div class="modal fade"
     id="structureModal"
     tabindex="-1"
     aria-labelledby="structureModalLabel"
     aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title"
            id="structureModalLabel">
          <i
             class="fa-solid fa-building me-2"></i><?= $lang['lead_structure_information'] ?? 'Structure Information'; ?>
        </h5>
        <button type="button"
                class="btn-close"
                data-bs-dismiss="modal"
                aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php if (!empty($structure_description_display)): ?>
        <div class="mb-4">
          <h6 class="text-muted mb-2">
            <i
               class="fa-solid fa-blueprint text-warning me-2"></i><?= $lang['lead_structure_description'] ?? 'Structure Description'; ?>
          </h6>
          <div class="bg-light p-3 rounded border">
            <?= htmlspecialchars($structure_description_display) ?>
          </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($structure_other)): ?>
        <div class="mb-4">
          <h6 class="text-muted mb-2">
            <i
               class="fa-solid fa-plus-circle text-secondary me-2"></i><?= $lang['lead_structure_other'] ?? 'Other Description'; ?>
          </h6>
          <div class="bg-light p-3 rounded border">
            <?= htmlspecialchars($structure_other) ?>
          </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($structure_additional)): ?>
        <div class="mb-0">
          <h6 class="text-muted mb-2">
            <i
               class="fa-solid fa-building text-info me-2"></i><?= $lang['lead_structure_additional'] ?? 'Additional Buildings'; ?>
          </h6>
          <div class="bg-light p-3 rounded border">
            <?= nl2br(htmlspecialchars($structure_additional)) ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <button type="button"
                class="btn btn-secondary"
                data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<?php
require SECTIONCLOSE;
require FOOTER;
?>