<?php
/**
 * Calendar Main Page
 * 
 * Full calendar view with FullCalendar integration
 * Follows framework template and security patterns
 * 
 * @author CRM Framework
 * @version 1.0
 */

// Include system configuration and follow standard framework pattern
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();

// Direct routing variables - these determine page navigation and template inclusion
$dir = 'calendar';
$subdir = '';
$page = 'calendar';

// Calendar page doesn't use DataTables, uses FullCalendar instead
$table_page = false;

// Load language file
require LANG . '/en.php';

// Page title and icons
$title = $lang['calendar'] ?? 'Calendar';
$title_icon = '<i class="fas fa-calendar-alt" aria-hidden="true"></i>';

// Initialize calendar model and get data
// CalendarEvent class is loaded via autoloader
$calendar = new CalendarEvent();
$user_id = $_SESSION['user_id'] ?? 1;

// Get event types and priorities for form
$event_types = $calendar->getEventTypes($lang);
$priorities = $calendar->getPriorities($lang);

// Get today's stats for dashboard cards
$today_stats = $calendar->getEventStats($user_id);

// Generate CSRF token
$csrf_token = $nonce->create('calendar_form');

// Follow standard template sequence
require HEADER;
require BODY;
require NAV;
require SECTIONOPEN;
?>

<!-- Hidden elements for JavaScript -->
<div style="display: none;">
  <input type="hidden"
         id="csrf_token"
         value="<?= htmlspecialchars($csrf_token) ?>">
</div>

<!-- Calendar Content -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card bg-primary text-white">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-md-8">
            <h1 class="card-title mb-0">
              <?= $title_icon ?> <?= htmlspecialchars($title) ?>
            </h1>
            <p class="card-text mt-2 mb-0">Manage your calls, emails, and meetings efficiently</p>
          </div>
          <div class="col-md-4 text-end">
            <button class="btn btn-light btn-lg"
                    data-bs-toggle="modal"
                    data-bs-target="#eventModal">
              <i class="fas fa-plus me-2"></i>New Event
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
  <div class="col-md-3">
    <div class="card border-start border-primary border-4">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="flex-shrink-0">
            <i class="fas fa-phone fa-2x text-primary"></i>
          </div>
          <div class="flex-grow-1 ms-3">
            <div class="fw-bold text-muted">Calls Today</div>
            <div class="h4 mb-0"
                 id="calls-today"><?= $today_stats['phone_calls'] ?? 0 ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card border-start border-success border-4">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="flex-shrink-0">
            <i class="fas fa-envelope fa-2x text-success"></i>
          </div>
          <div class="flex-grow-1 ms-3">
            <div class="fw-bold text-muted">Emails Today</div>
            <div class="h4 mb-0"
                 id="emails-today"><?= $today_stats['emails'] ?? 0 ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card border-start border-warning border-4">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="flex-shrink-0">
            <i class="fas fa-users fa-2x text-warning"></i>
          </div>
          <div class="flex-grow-1 ms-3">
            <div class="fw-bold text-muted">Meetings Today</div>
            <div class="h4 mb-0"
                 id="meetings-today"><?= $today_stats['meetings'] ?? 0 ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card border-start border-danger border-4">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="flex-shrink-0">
            <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
          </div>
          <div class="flex-grow-1 ms-3">
            <div class="fw-bold text-muted">High Priority</div>
            <div class="h4 mb-0"
                 id="high-priority"><?= $today_stats['high_priority'] ?? 0 ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Calendar -->
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div id="calendar">
          <!-- FullCalendar will be rendered here -->
        </div>
      </div>
    </div>
  </div>
</div>

<?php
require SECTIONCLOSE;
require FOOTER;