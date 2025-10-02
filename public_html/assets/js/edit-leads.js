document.addEventListener('DOMContentLoaded', function () {
    const projectNameInput = document.getElementById('project_name');

    if (projectNameInput && projectNameInput.value.trim() !== '') {
        // Project name exists, make it read-only like full name
        const projectNameValue = projectNameInput.value;
        const parentCol = projectNameInput.closest('.col');
        const formGroup = projectNameInput.closest('.form-group');

        // Create the new read-only display
        const newDisplay = `
            <div class="form-group">
                <label class="form-label fw-bold text-muted">${projectNameInput.previousElementSibling.textContent}</label>
                <div class="bg-light p-2 rounded border">
                    <i class="fa-solid fa-project-diagram text-primary me-2"></i>${projectNameValue}
                </div>
                <input type="hidden" name="project_name" value="${projectNameValue}">
            </div>
        `;

        // Replace the form group content
        formGroup.innerHTML = newDisplay;
    }

    // === TIMEZONE AND TIME CONVERSION FUNCTIONALITY ===
    // Display user's and client's timezone with conversion
    const userTimezoneElement = document.getElementById('user-timezone');
    const clientTimezoneElement = document.getElementById('client-timezone');
    const timeConversionElement = document.getElementById('time-conversion');

    if (userTimezoneElement && clientTimezoneElement && timeConversionElement) {
        try {
            const userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            userTimezoneElement.textContent = userTimezone;

            // Get client timezone based on location
            const clientState = window.leadsEditData?.clientState || '';
            const clientCountry = window.leadsEditData?.clientCountry || '';
            const clientTimezone = getTimezoneFromLocation(clientState, clientCountry);
            clientTimezoneElement.textContent = clientTimezone;

            // Update hidden timezone field for form submission
            const timezoneField = document.getElementById('timezone');
            if (timezoneField) {
                timezoneField.value = clientTimezone;
            }

            // Show time conversion example
            updateTimeConversion(clientTimezone, userTimezone);

            // Update conversion when time field changes
            const timeField = document.getElementById('next_action_time');
            if (timeField) {
                timeField.addEventListener('change', function () {
                    updateTimeConversion(clientTimezone, userTimezone, this.value);
                });
            }

        } catch (e) {
            userTimezoneElement.textContent = window.leadsEditData?.errorUnableDetectTimezone || 'Unable to detect timezone';
            clientTimezoneElement.textContent = window.leadsEditData?.textUnknown || 'Unknown';
            timeConversionElement.textContent = '';
        }
    }

    // === NOTES FUNCTIONALITY ===
    const notesSearch = document.getElementById('notesSearch');
    const notesOrder = document.getElementById('notesOrder');
    const clearSearch = document.getElementById('clearSearch');
    const notesContainer = document.getElementById('notesContainer');
    const notesLoading = document.getElementById('notesLoading');
    const currentCount = document.getElementById('current-count');
    const drawerTotal = document.getElementById('drawer-total');
    const notesCount = document.getElementById('notes-count-header');
    const totalNotes = document.getElementById('total-notes-header');

    const leadId = window.leadsEditData?.leadId || 0;
    let searchTimeout;

    // Search functionality
    if (notesSearch) {
        notesSearch.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadNotes();
            }, 500); // Debounce search
        });
    }

    // Order change
    if (notesOrder) {
        notesOrder.addEventListener('change', function () {
            loadNotes();
        });
    }

    // Clear search
    if (clearSearch) {
        clearSearch.addEventListener('click', function () {
            notesSearch.value = '';
            loadNotes();
        });
    }

    // Load notes initially
    if (leadId > 0) {
        loadNotes();
    }

    // === COLLAPSE FUNCTIONALITY ===
    // Handle structure information collapse
    const structureCollapse = document.getElementById('structureInformationCollapse');
    const structureIcon = structureCollapse?.previousElementSibling?.querySelector('.collapse-icon');

    if (structureCollapse && structureIcon) {
        // Set initial state (collapsed = rotated)
        structureIcon.style.transform = 'rotate(180deg)';

        structureCollapse.addEventListener('show.bs.collapse', function () {
            structureIcon.style.transform = 'rotate(0deg)';
        });

        structureCollapse.addEventListener('hide.bs.collapse', function () {
            structureIcon.style.transform = 'rotate(180deg)';
        });
    }

    // Handle file upload links collapse
    const uploadCollapse = document.getElementById('fileUploadLinksCollapse');
    const uploadIcon = uploadCollapse?.previousElementSibling?.querySelector('.collapse-icon');

    if (uploadCollapse && uploadIcon) {
        // Set initial state (collapsed = rotated)
        uploadIcon.style.transform = 'rotate(180deg)';

        uploadCollapse.addEventListener('show.bs.collapse', function () {
            uploadIcon.style.transform = 'rotate(0deg)';
        });

        uploadCollapse.addEventListener('hide.bs.collapse', function () {
            uploadIcon.style.transform = 'rotate(180deg)';
        });
    }

    // Handle screening estimates collapse
    const screeningCollapse = document.getElementById('screeningEstimatesCollapse');
    const screeningIcon = screeningCollapse?.previousElementSibling?.querySelector('.collapse-icon');

    if (screeningCollapse && screeningIcon) {
        screeningCollapse.addEventListener('show.bs.collapse', function () {
            screeningIcon.classList.remove('fa-chevron-down');
            screeningIcon.classList.add('fa-chevron-up');
        });

        screeningCollapse.addEventListener('hide.bs.collapse', function () {
            screeningIcon.classList.remove('fa-chevron-up');
            screeningIcon.classList.add('fa-chevron-down');
        });
    }

    // Auto-expand screening estimates when user enters data
    const screeningInputs = [
        'eng_system_cost_low', 'eng_system_cost_high', 'eng_protected_area', 'eng_cabinets', 'eng_total_pumps'
    ];

    screeningInputs.forEach(inputId => {
        const input = document.getElementById(inputId);
        if (input) {
            input.addEventListener('focus', function () {
                if (screeningCollapse && !screeningCollapse.classList.contains('show')) {
                    const bsCollapse = new bootstrap.Collapse(screeningCollapse, {
                        show: true
                    });
                }
            });
        }
    });

    // Format currency inputs with commas
    const currencyInputs = [
        'eng_system_cost_low', 'eng_system_cost_high'
    ];

    currencyInputs.forEach(inputId => {
        const input = document.getElementById(inputId);
        if (input) {
            input.addEventListener('input', function (e) {
                // Remove non-numeric characters except for existing commas
                let value = e.target.value.replace(/[^\d]/g, '');

                // Add commas for thousands
                if (value) {
                    value = parseInt(value).toLocaleString();
                }

                e.target.value = value;
            });

            // Remove commas before form submission
            input.addEventListener('blur', function (e) {
                e.target.value = e.target.value.replace(/,/g, '');
            });
        }
    });

    // Handle collapse chevron rotation for Services Interested In
    const servicesCollapse = document.getElementById('servicesInterestedCollapse');
    if (servicesCollapse) {
        const servicesHeader = document.querySelector('[data-bs-target="#servicesInterestedCollapse"]');
        const servicesIcon = servicesHeader ? servicesHeader.querySelector('.collapse-icon') : null;

        // Add hover effects to the entire header
        if (servicesHeader) {
            servicesHeader.addEventListener('mouseenter', function () {
                this.style.backgroundColor = 'rgba(40, 167, 69, 0.9)'; // Slightly darker success
                this.style.transition = 'background-color 0.2s ease';
            });

            servicesHeader.addEventListener('mouseleave', function () {
                this.style.backgroundColor = ''; // Reset to original bg-success
            });
        }

        servicesCollapse.addEventListener('show.bs.collapse', function () {
            if (servicesIcon) {
                servicesIcon.classList.remove('fa-chevron-down');
                servicesIcon.classList.add('fa-chevron-up');
            }
        });

        servicesCollapse.addEventListener('hide.bs.collapse', function () {
            if (servicesIcon) {
                servicesIcon.classList.remove('fa-chevron-up');
                servicesIcon.classList.add('fa-chevron-down');
            }
        });
    }

    // Handle collapse chevron rotation and hover effects for Structure Information
    // Note: structureCollapse is already declared above at line 112
    if (structureCollapse) {
        const structureHeader = document.querySelector('[data-bs-target="#structureInformationCollapse"]');
        const structureHeaderIcon = structureHeader ? structureHeader.querySelector('.collapse-icon') : null;

        // Add hover effects to the entire header
        if (structureHeader) {
            structureHeader.addEventListener('mouseenter', function () {
                this.style.backgroundColor = 'rgba(255, 193, 7, 0.9)'; // Slightly darker warning
                this.style.transition = 'background-color 0.2s ease';
            });

            structureHeader.addEventListener('mouseleave', function () {
                this.style.backgroundColor = ''; // Reset to original bg-warning
            });
        }

        // Additional collapse events for chevron rotation using class-based approach
        if (structureHeaderIcon) {
            structureCollapse.addEventListener('show.bs.collapse', function () {
                structureHeaderIcon.classList.remove('fa-chevron-down');
                structureHeaderIcon.classList.add('fa-chevron-up');
            });

            structureCollapse.addEventListener('hide.bs.collapse', function () {
                structureHeaderIcon.classList.remove('fa-chevron-up');
                structureHeaderIcon.classList.add('fa-chevron-down');
            });
        }
    }

    // Handle collapse chevron rotation for File Upload Links
    const fileUploadCollapse = document.getElementById('fileUploadLinksCollapse');
    if (fileUploadCollapse) {
        const collapseButton = document.querySelector('[data-bs-target="#fileUploadLinksCollapse"]');
        const collapseIcon = document.querySelector('.card-header .collapse-icon');
        const cardHeader = document.querySelector('[data-bs-target="#fileUploadLinksCollapse"]').closest('.card-header');

        // Add hover effects to the entire header
        if (cardHeader) {
            cardHeader.style.cursor = 'pointer';
            cardHeader.addEventListener('mouseenter', function () {
                this.style.backgroundColor = 'rgba(0, 123, 255, 0.9)'; // Slightly darker primary
                this.style.transition = 'background-color 0.2s ease';
            });

            cardHeader.addEventListener('mouseleave', function () {
                this.style.backgroundColor = ''; // Reset to original bg-primary
            });

            // Make entire header clickable
            cardHeader.addEventListener('click', function (e) {
                if (e.target !== collapseButton && !collapseButton.contains(e.target)) {
                    collapseButton.click();
                }
            });
        }

        fileUploadCollapse.addEventListener('show.bs.collapse', function () {
            if (collapseIcon) {
                collapseIcon.classList.remove('fa-chevron-down');
                collapseIcon.classList.add('fa-chevron-up');
            }
        });

        fileUploadCollapse.addEventListener('hide.bs.collapse', function () {
            if (collapseIcon) {
                collapseIcon.classList.remove('fa-chevron-up');
                collapseIcon.classList.add('fa-chevron-down');
            }
        });
    }
});

// === UTILITY FUNCTIONS ===

// Function to estimate timezone from location using server-generated data
function getTimezoneFromLocation (state, country) {
    // US state to timezone mapping from Helper class
    const usTimezones = window.leadsEditData?.usTimezones || {};

    if (country === 'US' && usTimezones[state]) {
        return usTimezones[state];
    }

    // Country timezone mappings from Helper class
    const countryTimezones = window.leadsEditData?.countryTimezones || {};

    return countryTimezones[country] || 'UTC';
}

// Function to update time conversion display
function updateTimeConversion (clientTz, userTz, selectedTime = null) {
    const timeConversionElement = document.getElementById('time-conversion');
    if (!timeConversionElement) return;

    try {
        const now = new Date();
        let timeToConvert = now;

        if (selectedTime) {
            // Use selected time with today's date
            const [hours, minutes] = selectedTime.split(':');
            timeToConvert = new Date();
            timeToConvert.setHours(parseInt(hours), parseInt(minutes), 0, 0);
        } else {
            // Use current time as example
            timeToConvert = new Date();
        }

        if (clientTz !== userTz) {
            // Format time in client timezone
            const clientTime = timeToConvert.toLocaleTimeString('en-US', {
                timeZone: clientTz,
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });

            // Format time in user timezone
            const userTime = timeToConvert.toLocaleTimeString('en-US', {
                timeZone: userTz,
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });

            if (selectedTime) {
                timeConversionElement.textContent = `${clientTime} client time = ${userTime} your time`;
            } else {
                timeConversionElement.textContent = `Current time: ${clientTime} client time = ${userTime} your time`;
            }
        } else {
            timeConversionElement.textContent = 'Same timezone';
        }
    } catch (e) {
        timeConversionElement.textContent = window.leadsEditData?.errorUnableConvertTime || 'Unable to convert time';
    }
}

// Load notes via AJAX
function loadNotes () {
    const leadId = window.leadsEditData?.leadId || 0;
    if (leadId <= 0) return;

    const notesSearch = document.getElementById('notesSearch');
    const notesOrder = document.getElementById('notesOrder');
    const notesLoading = document.getElementById('notesLoading');
    const notesContainer = document.getElementById('notesContainer');

    const search = notesSearch ? notesSearch.value : '';
    const order = notesOrder ? notesOrder.value : 'DESC';

    // Show loading
    if (notesLoading) notesLoading.style.display = 'block';
    if (notesContainer) notesContainer.style.opacity = '0.5';

    fetch('notes_ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'get_notes',
            lead_id: leadId,
            search: search,
            order: order
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderNotes(data.notes);
                updateCounts(data.notes.length, data.total_count || data.notes.length);
            } else {
                console.error('Error loading notes:', data.error);
                const errorMsg = window.leadsEditData?.errorFailedLoadNotes || 'Failed to load notes';
                const unknownError = window.leadsEditData?.errorUnknownError || 'Unknown error';
                showError(errorMsg + ': ' + (data.error || unknownError));
            }
        })
        .catch(error => {
            console.error('Network error:', error);
            const networkError = window.leadsEditData?.errorNetworkLoadingNotes || 'Network error while loading notes';
            showError(networkError);
        })
        .finally(() => {
            // Hide loading
            if (notesLoading) notesLoading.style.display = 'none';
            if (notesContainer) notesContainer.style.opacity = '1';
        });
}

// Render notes HTML
function renderNotes (notes) {
    const notesContainer = document.getElementById('notesContainer');
    if (!notesContainer) return;

    if (notes.length === 0) {
        notesContainer.innerHTML = `
            <div class="text-center p-5">
                <i class="fa-solid fa-sticky-note fa-4x text-muted opacity-25 mb-3"></i>
                <h6 class="text-muted">No Notes Found</h6>
                <p class="text-muted">No notes match your search criteria.</p>
            </div>
        `;
        return;
    }

    let html = '<div class="timeline p-4">';

    notes.forEach(note => {
        const sourceColor = note.source <= 3 ? 'primary' : (note.source <= 6 ? 'success' : 'secondary');
        const sourceIcon = getSourceIcon(note.source);
        const textFrom = window.leadsEditData?.textFrom || 'from';

        html += `
            <div class="timeline-item mb-4">
                <div class="timeline-marker">
                    <div class="timeline-marker-icon bg-${sourceColor}">
                        <i class="fa-solid fa-${sourceIcon} text-white"></i>
                    </div>
                </div>
                <div class="timeline-content">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="${note.source_badge} me-2">${note.source_name}</span>
                            ${note.contact_name ? `<small class="badge bg-info text-white me-2"><i class="fa-solid fa-user me-1"></i>${escapeHtml(note.contact_name)}</small>` : ''}
                            ${note.form_source && note.form_source !== 'leads' ? `<small class="badge bg-light text-dark">${textFrom} ${note.form_source}</small>` : ''}
                        </div>
                        <small class="text-muted">${note.date_formatted}</small>
                    </div>
                    <div class="note-content">
                        <p class="mb-1">${escapeHtml(note.note_text).replace(/\n/g, '<br>')}</p>
                        <small class="text-muted">
                            <i class="fa-solid fa-user me-1"></i>${escapeHtml(note.user_name)}
                        </small>
                    </div>
                </div>
            </div>
        `;
    });

    html += '</div>';
    notesContainer.innerHTML = html;
}

// Update count displays
function updateCounts (current, total) {
    const currentCount = document.getElementById('current-count');
    const drawerTotal = document.getElementById('drawer-total');
    const notesCount = document.getElementById('notes-count-header');

    if (currentCount) currentCount.textContent = current;
    if (drawerTotal) drawerTotal.textContent = total;
    if (notesCount) notesCount.textContent = current;
}

// Get icon for note source
function getSourceIcon (source) {
    const icons = {
        1: 'phone', // Phone Call
        2: 'envelope', // Email  
        3: 'comment-sms', // Text Message
        4: 'sticky-note', // Internal Note
        5: 'handshake', // Meeting
        6: 'map-marker-alt', // Site Visit
        7: 'clock' // Follow-up
    };
    return icons[source] || 'sticky-note';
}

// Show error message
function showError (message) {
    const notesContainer = document.getElementById('notesContainer');
    if (notesContainer) {
        notesContainer.innerHTML = `
            <div class="alert alert-danger m-4">
                <i class="fa-solid fa-exclamation-triangle me-2"></i>${escapeHtml(message)}
            </div>
        `;
    }
}

// Escape HTML
function escapeHtml (text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Stage change handler
function handleStageChange (newStage) {
    const stageNames = window.leadsEditData?.stageNames || {};
    const currentStage = window.leadsEditData?.selectedStage || 1;
    const leadId = window.leadsEditData?.leadId || '';

    if (newStage != currentStage) {
        let message = '';
        let redirectUrl = '';

        // Determine where the record will move based on new stage
        switch (parseInt(newStage)) {
            case 4: // Referral
                message = `This lead will be moved to the Referrals list when you save.`;
                redirectUrl = '/referrals/list';
                break;
            case 5:
            case 6:
            case 7:
            case 8:
            case 9:
            case 10:
            case 11:
            case 12:
                message = `This lead will be moved to the Prospects list when you save.`;
                redirectUrl = '/prospects/list';
                break;
            case 13: // Contracting
                message = `This lead will be moved to the Contracting list when you save.`;
                redirectUrl = '/contracting/list';
                break;
            case 14: // Closed Won
                message = `This lead will be marked as Closed Won and moved to the appropriate list.`;
                break;
            case 15: // Closed Lost
                message = `This lead will be marked as Closed Lost.`;
                break;
        }

        if (message) {
            // Show confirmation dialog
            if (confirm(
                `Stage Change: ${stageNames[currentStage]} → ${stageNames[newStage]}\n\n${message}\n\nDo you want to continue?`
            )) {
                // User confirmed, let the form submission handle the stage change
                return true;
            } else {
                // User cancelled, revert the dropdown
                const stageSelect = document.getElementById('stage');
                if (stageSelect) {
                    stageSelect.value = currentStage;
                }
                return false;
            }
        }
    }
    return true;
}

// Copy to clipboard function for upload links
function copyToClipboard (text, button) {
    navigator.clipboard.writeText(text).then(function () {
        // Success feedback
        const originalIcon = button.innerHTML;
        button.innerHTML = '<i class="fa-solid fa-check text-success"></i>';
        button.classList.add('btn-success');
        button.classList.remove('btn-outline-secondary');

        setTimeout(function () {
            button.innerHTML = originalIcon;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-secondary');
        }, 2000);
    }).catch(function (err) {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            // Success feedback
            const originalIcon = button.innerHTML;
            button.innerHTML = '<i class="fa-solid fa-check text-success"></i>';
            button.classList.add('btn-success');
            button.classList.remove('btn-outline-secondary');

            setTimeout(function () {
                button.innerHTML = originalIcon;
                button.classList.remove('btn-success');
                button.classList.add('btn-outline-secondary');
            }, 2000);
        } catch (err) {
            console.error('Failed to copy text: ', err);
        }
        document.body.removeChild(textArea);
    });
}

// ===== TIMEZONE FUNCTIONS =====
// Get timezone from location (state/country)
function getTimezoneFromLocation (state, country) {
    // US State to Timezone mapping (based on Helpers.php)
    const usTimezones = {
        // Pacific Time
        'CA': 'America/Los_Angeles',
        'WA': 'America/Los_Angeles',
        'OR': 'America/Los_Angeles',
        'NV': 'America/Los_Angeles',
        // Mountain Time (Arizona uses Phoenix - no DST)
        'AZ': 'America/Phoenix',
        'UT': 'America/Denver',
        'CO': 'America/Denver',
        'WY': 'America/Denver',
        'MT': 'America/Denver',
        'NM': 'America/Denver',
        'ND': 'America/Denver',
        'SD': 'America/Denver',
        // Central Time
        'TX': 'America/Chicago',
        'OK': 'America/Chicago',
        'KS': 'America/Chicago',
        'NE': 'America/Chicago',
        'MN': 'America/Chicago',
        'IA': 'America/Chicago',
        'MO': 'America/Chicago',
        'AR': 'America/Chicago',
        'LA': 'America/Chicago',
        'MS': 'America/Chicago',
        'AL': 'America/Chicago',
        'TN': 'America/Chicago',
        'KY': 'America/Chicago',
        'IN': 'America/Chicago',
        'IL': 'America/Chicago',
        'WI': 'America/Chicago',
        // Eastern Time (Michigan has Detroit timezone)
        'MI': 'America/Detroit',
        'OH': 'America/New_York',
        'WV': 'America/New_York',
        'VA': 'America/New_York',
        'PA': 'America/New_York',
        'NY': 'America/New_York',
        'VT': 'America/New_York',
        'NH': 'America/New_York',
        'ME': 'America/New_York',
        'MA': 'America/New_York',
        'RI': 'America/New_York',
        'CT': 'America/New_York',
        'NJ': 'America/New_York',
        'DE': 'America/New_York',
        'MD': 'America/New_York',
        'DC': 'America/New_York',
        'NC': 'America/New_York',
        'SC': 'America/New_York',
        'GA': 'America/New_York',
        'FL': 'America/New_York'
    };

    // Check if it's a US state
    if (country === 'USA' || country === 'United States' || country === 'US' || !country) {
        const upperState = (state || '').toUpperCase();
        if (usTimezones[upperState]) {
            return usTimezones[upperState];
        }
    }

    // Default fallback based on common countries
    const countryTimezones = {
        'Canada': 'America/Toronto',
        'Mexico': 'America/Mexico_City',
        'UK': 'Europe/London',
        'United Kingdom': 'Europe/London',
        'Germany': 'Europe/Berlin',
        'France': 'Europe/Paris',
        'Australia': 'Australia/Sydney',
        'Japan': 'Asia/Tokyo',
        'China': 'Asia/Shanghai'
    };

    return countryTimezones[country] || 'America/New_York'; // Default to Eastern Time
}

// Update time conversion display
function updateTimeConversion (clientTimezone, userTimezone, specificTime = null) {
    const timeConversionElement = document.getElementById('time-conversion');
    if (!timeConversionElement || clientTimezone === userTimezone) {
        if (timeConversionElement) {
            timeConversionElement.textContent = window.leadsEditData?.textSameTimezone || 'Same timezone';
        }
        return;
    }

    try {
        const now = new Date();
        let baseTime = now;

        // If specific time is provided, use today's date with that time
        if (specificTime) {
            const [hours, minutes] = specificTime.split(':').map(Number);
            baseTime = new Date();
            baseTime.setHours(hours, minutes, 0, 0);
        }

        // Format time for client's timezone
        const clientTimeOptions = {
            timeZone: clientTimezone,
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        };
        const clientTime = baseTime.toLocaleString('en-US', clientTimeOptions);

        // Format time for user's timezone
        const userTimeOptions = {
            timeZone: userTimezone,
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        };
        const userTime = baseTime.toLocaleString('en-US', userTimeOptions);

        // Get timezone abbreviations
        const clientTzShort = getTimezoneAbbreviation(clientTimezone);
        const userTzShort = getTimezoneAbbreviation(userTimezone);

        const conversionText = window.leadsEditData?.textTimeConversion || '{clientTime} ({clientTz}) = {userTime} ({userTz})';
        timeConversionElement.textContent = conversionText
            .replace('{clientTime}', clientTime)
            .replace('{clientTz}', clientTzShort)
            .replace('{userTime}', userTime)
            .replace('{userTz}', userTzShort);

    } catch (error) {
        console.warn('Error updating time conversion:', error);
        timeConversionElement.textContent = window.leadsEditData?.errorTimeConversion || 'Time conversion error';
    }
}

// Get timezone abbreviation
function getTimezoneAbbreviation (timezone) {
    const abbreviations = {
        'America/Los_Angeles': 'PST/PDT',
        'America/Phoenix': 'MST',
        'America/Denver': 'MST/MDT',
        'America/Chicago': 'CST/CDT',
        'America/Detroit': 'EST/EDT',
        'America/New_York': 'EST/EDT',
        'America/Toronto': 'EST/EDT',
        'America/Mexico_City': 'CST/CDT',
        'Europe/London': 'GMT/BST',
        'Europe/Berlin': 'CET/CEST',
        'Europe/Paris': 'CET/CEST',
        'Australia/Sydney': 'AEST/AEDT',
        'Asia/Tokyo': 'JST',
        'Asia/Shanghai': 'CST'
    };

    return abbreviations[timezone] || timezone.split('/').pop().replace('_', ' ');
}