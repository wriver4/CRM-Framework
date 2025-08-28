/**
 * Contact Selector Functionality
 * Handles contact dropdown in lead edit page and populates contact information fields
 */

document.addEventListener('DOMContentLoaded', function () {
  // Contact selector functionality
  const contactSelector = document.getElementById('contact_selector');
  if (contactSelector) {
    contactSelector.addEventListener('change', function () {
      const selectedOption = this.options[this.selectedIndex];

      // Get contact data from the selected option
      const fullName = selectedOption.getAttribute('data-full-name');
      const email = selectedOption.getAttribute('data-email');
      const cellPhone = selectedOption.getAttribute('data-cell-phone');

      // Update the contact information display fields
      updateContactDisplay('full_name', fullName);
      updateContactDisplay('email', email);
      updateContactDisplay('cell_phone', cellPhone);

      // Also update the hidden form fields
      updateHiddenField('full_name', fullName);
      updateHiddenField('email', email);
      updateHiddenField('cell_phone', cellPhone);

      // Set the selected contact for notes
      const noteContactField = document.getElementById('note_contact_id');
      if (noteContactField) {
        noteContactField.value = selectedOption.value;
      }

      // Show visual feedback
      showContactChangeNotification(fullName);
    });
  }

  // Helper function to update contact display fields
  function updateContactDisplay (fieldName, value) {
    const displayElement = document.querySelector(`input[name="${fieldName}"]`)?.closest('.form-group')?.querySelector('.bg-light');
    if (displayElement) {
      const icon = displayElement.querySelector('i');
      const iconHtml = icon ? icon.outerHTML : '';
      displayElement.innerHTML = iconHtml + (value || '-');

      // Add a subtle animation to show the change
      displayElement.style.transition = 'background-color 0.3s ease';
      displayElement.style.backgroundColor = '#e3f2fd';
      setTimeout(() => {
        displayElement.style.backgroundColor = '';
      }, 1000);
    }
  }

  // Helper function to update hidden form fields
  function updateHiddenField (fieldName, value) {
    const hiddenField = document.querySelector(`input[name="${fieldName}"][type="hidden"]`);
    if (hiddenField) {
      hiddenField.value = value || '';
    }
  }

  // Show notification when contact is changed
  function showContactChangeNotification (contactName) {
    // Create a temporary notification
    const notification = document.createElement('div');
    notification.className = 'alert alert-info alert-dismissible fade show position-fixed';
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
            <i class="fa-solid fa-info-circle me-2"></i>
            Contact information updated to: <strong>${contactName}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

    document.body.appendChild(notification);

    // Auto-remove after 3 seconds
    setTimeout(() => {
      if (notification.parentNode) {
        notification.remove();
      }
    }, 3000);
  }
});