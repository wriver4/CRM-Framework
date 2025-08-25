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