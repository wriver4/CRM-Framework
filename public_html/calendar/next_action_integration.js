/**
 * Next Action Calendar Integration
 * 
 * JavaScript to integrate Next Action fields with Calendar
 * Automatically creates calendar events from Next Action data
 * 
 * @author CRM Framework
 * @version 1.0
 */

class NextActionCalendarIntegration {
  constructor() {
    this.csrfToken = null;
    this.leadId = null;
    this.init();
  }

  init () {
    // Get CSRF token and lead ID from the page
    this.csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
    this.leadId = this.extractLeadId();

    if (!this.csrfToken || !this.leadId) {
      console.warn('Calendar integration: Missing CSRF token or lead ID');
      return;
    }

    this.setupEventListeners();
    this.addCalendarButton();
  }

  extractLeadId () {
    // Extract lead ID from URL or form
    const urlParams = new URLSearchParams(window.location.search);
    const leadId = urlParams.get('id');

    if (leadId) {
      return parseInt(leadId);
    }

    // Try to get from form
    const leadIdInput = document.querySelector('input[name="lead_id"]');
    if (leadIdInput) {
      return parseInt(leadIdInput.value);
    }

    return null;
  }

  setupEventListeners () {
    // Listen for changes to Next Action fields
    const nextActionInputs = document.querySelectorAll('input[name="next_action"]');
    const nextActionDate = document.querySelector('input[name="next_action_date"]');
    const nextActionTime = document.querySelector('input[name="next_action_time"]');
    const nextActionNotes = document.querySelector('textarea[name="next_action_notes"]');

    // Add change listeners
    [nextActionDate, nextActionTime, nextActionNotes].forEach(element => {
      if (element) {
        element.addEventListener('change', () => this.updateCalendarButton());
      }
    });

    nextActionInputs.forEach(input => {
      input.addEventListener('change', () => this.updateCalendarButton());
    });
  }

  addCalendarButton () {
    // Find the Next Action section
    const nextActionSection = document.querySelector('input[name="next_action_date"]')?.closest('.row');

    if (!nextActionSection) {
      return;
    }

    // Create calendar integration button
    const buttonContainer = document.createElement('div');
    buttonContainer.className = 'col-12 mt-2';
    buttonContainer.innerHTML = `
            <button type="button" 
                    id="createCalendarEventBtn" 
                    class="btn btn-outline-primary btn-sm"
                    disabled>
                <i class="fas fa-calendar-plus me-1"></i>
                Create Calendar Event
            </button>
            <small class="text-muted ms-2" id="calendarEventStatus"></small>
        `;

    // Insert after the Next Action time row
    const timeRow = nextActionSection.parentNode;
    timeRow.parentNode.insertBefore(buttonContainer, timeRow.nextSibling);

    // Add click listener
    document.getElementById('createCalendarEventBtn').addEventListener('click', () => {
      this.createCalendarEvent();
    });

    // Initial button state update
    this.updateCalendarButton();
  }

  updateCalendarButton () {
    const button = document.getElementById('createCalendarEventBtn');
    const status = document.getElementById('calendarEventStatus');

    if (!button || !status) {
      return;
    }

    const nextActionData = this.getNextActionData();

    if (this.isValidNextAction(nextActionData)) {
      button.disabled = false;
      button.className = 'btn btn-primary btn-sm';
      status.textContent = 'Ready to create calendar event';
      status.className = 'text-success ms-2';
    } else {
      button.disabled = true;
      button.className = 'btn btn-outline-primary btn-sm';
      status.textContent = 'Please fill in Next Action details';
      status.className = 'text-muted ms-2';
    }
  }

  getNextActionData () {
    const selectedAction = document.querySelector('input[name="next_action"]:checked');
    const nextActionDate = document.querySelector('input[name="next_action_date"]');
    const nextActionTime = document.querySelector('input[name="next_action_time"]');
    const nextActionNotes = document.querySelector('textarea[name="next_action_notes"]');

    return {
      next_action: selectedAction?.value,
      next_action_date: nextActionDate?.value,
      next_action_time: nextActionTime?.value,
      next_action_notes: nextActionNotes?.value,
      lead_id: this.leadId
    };
  }

  isValidNextAction (data) {
    return data.next_action && data.next_action_date && data.lead_id;
  }

  async createCalendarEvent () {
    const button = document.getElementById('createCalendarEventBtn');
    const status = document.getElementById('calendarEventStatus');

    if (!button || !status) {
      return;
    }

    const nextActionData = this.getNextActionData();

    if (!this.isValidNextAction(nextActionData)) {
      this.showStatus('error', 'Please fill in required Next Action fields');
      return;
    }

    // Update button state
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Creating...';
    this.showStatus('info', 'Creating calendar event...');

    try {
      // Add CSRF token
      nextActionData.csrf_token = this.csrfToken;

      const response = await fetch('/calendar/api.php?action=from_next_action', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(nextActionData)
      });

      const result = await response.json();

      if (result.success) {
        this.showStatus('success', `Calendar event created successfully! <a href="/calendar/" target="_blank">View Calendar</a>`);
        button.innerHTML = '<i class="fas fa-check me-1"></i>Event Created';
        button.className = 'btn btn-success btn-sm';

        // Optionally clear Next Action fields or mark as completed
        this.markNextActionAsScheduled();

      } else {
        throw new Error(result.error || 'Failed to create calendar event');
      }

    } catch (error) {
      console.error('Error creating calendar event:', error);
      this.showStatus('error', 'Error: ' + error.message);

      // Reset button
      button.disabled = false;
      button.innerHTML = '<i class="fas fa-calendar-plus me-1"></i>Create Calendar Event';
      button.className = 'btn btn-primary btn-sm';
    }
  }

  showStatus (type, message) {
    const status = document.getElementById('calendarEventStatus');
    if (!status) return;

    const classes = {
      'success': 'text-success',
      'error': 'text-danger',
      'info': 'text-info',
      'warning': 'text-warning'
    };

    status.innerHTML = message;
    status.className = `${classes[type] || 'text-muted'} ms-2`;
  }

  markNextActionAsScheduled () {
    // Add a visual indicator that this Next Action has been scheduled
    const nextActionSection = document.querySelector('input[name="next_action_date"]')?.closest('.card-body');

    if (nextActionSection) {
      // Add a small badge or indicator
      const indicator = document.createElement('div');
      indicator.className = 'alert alert-success alert-sm mt-2';
      indicator.innerHTML = `
                <i class="fas fa-calendar-check me-1"></i>
                <small>This Next Action has been added to your calendar</small>
            `;

      nextActionSection.appendChild(indicator);
    }
  }

  // Static method to initialize on page load
  static init () {
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => {
        new NextActionCalendarIntegration();
      });
    } else {
      new NextActionCalendarIntegration();
    }
  }
}

// Auto-initialize when script loads
NextActionCalendarIntegration.init();

// Export for manual initialization if needed
window.NextActionCalendarIntegration = NextActionCalendarIntegration;