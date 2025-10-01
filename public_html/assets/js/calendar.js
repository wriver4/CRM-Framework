/**
 * Calendar Module JavaScript - Read-Only Version
 * 
 * FullCalendar integration for viewing events only
 * Events can only be added through /leads/edit.php
 * 
 * @author CRM Framework
 * @version 2.0
 */

document.addEventListener('DOMContentLoaded', function () {
  let calendar;
  const csrfToken = document.getElementById('csrf_token').value;

  // Initialize calendar
  initializeCalendar();

  function initializeCalendar () {
    const calendarEl = document.getElementById('calendar');

    if (!calendarEl) {
      console.error('Calendar element not found!');
      return;
    }

    // Ensure the calendar container is properly positioned before initializing
    calendarEl.style.position = 'relative';
    calendarEl.style.zIndex = '1';
    calendarEl.style.width = '100%';
    calendarEl.style.maxWidth = '100%';
    calendarEl.style.overflow = 'hidden';

    console.log('Initializing FullCalendar...');
    console.log('Calendar element parent:', calendarEl.parentElement);
    console.log('Calendar element position:', window.getComputedStyle(calendarEl).position);

    calendar = new FullCalendar.Calendar(calendarEl, {
      // CRITICAL: Use Bootstrap 5 theme system
      themeSystem: 'bootstrap5',
      initialView: 'dayGridMonth',
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
      },
      height: 'auto',
      editable: false, // Disable editing
      selectable: false, // Disable date selection
      selectMirror: false,
      dayMaxEvents: true,
      weekends: true,

      // Event sources
      events: function (fetchInfo, successCallback, failureCallback) {
        fetch(`api.php?action=events&start=${fetchInfo.startStr}&end=${fetchInfo.endStr}`)
          .then(response => response.json())
          .then(data => {
            if (Array.isArray(data)) {
              successCallback(data);
            } else {
              console.error('Invalid events data:', data);
              failureCallback('Failed to load events');
            }
          })
          .catch(error => {
            console.error('Error loading events:', error);
            failureCallback(error);
          });
      },

      // Event click - show details only
      eventClick: function (info) {
        showEventDetails(info.event);
      },

      // Simple rendering callbacks
      viewDidMount: function (info) {
        console.log('FullCalendar view mounted:', info.view.type);
      },

      datesSet: function (info) {
        console.log('FullCalendar dates set:', info.start, 'to', info.end);
      }
    });

    // Render the calendar
    calendar.render();
    console.log('FullCalendar rendered with Bootstrap 5 theme');
  }

  function showEventDetails (event) {
    const props = event.extendedProps;

    let html = `
            <div class="row">
                <div class="col-12">
                    <h6><i class="${props.event_type_icon || 'fas fa-calendar'} me-2"></i>${event.title}</h6>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-6"><strong>Start:</strong></div>
                <div class="col-6">${formatDateTime(event.start)}</div>
            </div>
        `;

    if (event.end) {
      html += `
                <div class="row mb-2">
                    <div class="col-6"><strong>End:</strong></div>
                    <div class="col-6">${formatDateTime(event.end)}</div>
                </div>
            `;
    }

    html += `
            <div class="row mb-2">
                <div class="col-6"><strong>Type:</strong></div>
                <div class="col-6">${props.event_type_name || 'Unknown'}</div>
            </div>
            <div class="row mb-2">
                <div class="col-6"><strong>Priority:</strong></div>
                <div class="col-6">${props.priority_name || 'Normal'} (${props.priority || 5})</div>
            </div>
        `;

    if (props.location) {
      html += `
                <div class="row mb-2">
                    <div class="col-6"><strong>Location:</strong></div>
                    <div class="col-6">${props.location}</div>
                </div>
            `;
    }

    if (props.company_name) {
      html += `
                <div class="row mb-2">
                    <div class="col-6"><strong>Company:</strong></div>
                    <div class="col-6">${props.company_name}</div>
                </div>
            `;
    }

    if (props.contact_name) {
      html += `
                <div class="row mb-2">
                    <div class="col-6"><strong>Contact:</strong></div>
                    <div class="col-6">${props.contact_name}</div>
                </div>
            `;
    }

    // Add lead link if lead_id exists
    if (props.lead_id) {
      html += `
                <div class="row mb-2">
                    <div class="col-6"><strong>Lead:</strong></div>
                    <div class="col-6">
                        <a href="../leads/edit.php?id=${props.lead_id}" 
                           class="btn btn-sm btn-outline-primary" 
                           target="_blank">
                            <i class="fas fa-external-link-alt me-1"></i>
                            View Lead #${props.lead_id}
                        </a>
                    </div>
                </div>
            `;
    }

    if (props.description) {
      html += `
                <div class="row mb-2">
                    <div class="col-12"><strong>Description:</strong></div>
                    <div class="col-12">${props.description}</div>
                </div>
            `;
    }

    if (props.notes) {
      html += `
                <div class="row mb-2">
                    <div class="col-12"><strong>Notes:</strong></div>
                    <div class="col-12">${props.notes}</div>
                </div>
            `;
    }

    // Add notice about editing
    html += `
            <div class="row mt-3">
                <div class="col-12">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> Events can only be added or edited through the 
                        <a href="../leads/edit.php" class="alert-link">Leads Edit page</a>.
                    </div>
                </div>
            </div>
        `;

    document.getElementById('eventDetailContent').innerHTML = html;

    // Remove edit button functionality - show view-only modal
    const editBtn = document.getElementById('editEventBtn');
    if (editBtn) {
      editBtn.style.display = 'none';
    }

    const modal = new bootstrap.Modal(document.getElementById('eventDetailModal'));
    modal.show();
  }

  function updateStats () {
    fetch('api.php?action=stats')
      .then(response => response.json())
      .then(stats => {
        document.getElementById('calls-today').textContent = stats.phone_calls || 0;
        document.getElementById('emails-today').textContent = stats.emails || 0;
        document.getElementById('meetings-today').textContent = stats.meetings || 0;
        document.getElementById('high-priority').textContent = stats.high_priority || 0;
      })
      .catch(error => {
        console.error('Error updating stats:', error);
      });
  }

  function formatDateTime (date) {
    if (!date) return '';
    return new Date(date).toLocaleString();
  }

  function showAlert (type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

    // Insert at top of section
    const section = document.querySelector('.section-content');
    if (section) {
      section.insertBefore(alertDiv, section.firstChild);
    }

    // Auto-dismiss after 5 seconds
    setTimeout(() => {
      if (alertDiv.parentNode) {
        alertDiv.remove();
      }
    }, 5000);
  }

  // Disable the "New Event" button if it exists
  const newEventBtn = document.querySelector('[data-bs-target="#eventModal"]');
  if (newEventBtn) {
    newEventBtn.disabled = true;
    newEventBtn.innerHTML = '<i class="fas fa-plus me-2"></i>Add Events via Leads';
    newEventBtn.onclick = function (e) {
      e.preventDefault();
      showAlert('info', 'Events can only be added through the <a href="../leads/edit.php" class="alert-link">Leads Edit page</a>.');
    };
  }

  // Initial stats update
  updateStats();

  // Refresh stats every 5 minutes
  setInterval(updateStats, 300000);
});