document.addEventListener('DOMContentLoaded', function() {
    console.log('Conditional forms script loaded');
    const leadSourceSelect = document.getElementById('lead_source');
    
    // Only proceed if lead source select exists
    if (!leadSourceSelect) {
        console.log('Lead source select not found');
        return;
    }
    console.log('Lead source select found');
    
    // Form field configurations for each lead source
    const formConfigs = {
        'Web Estimate': {
            fields: {
                phone: { show: true, required: true },
                contactType: { show: true, required: true },
                message: { show: false, required: false },
                address: { show: true, required: true },
                updates: { show: true, required: false },
                estimateNumber: { show: true, required: false },
                services: { show: true, required: false },
                structure: { show: true, required: false },
                picturesSubmitted: { show: true, required: false },
                fileUpload: { show: true, required: false },
                plansPicsUploaded: { show: true, required: false },
                hearAbout: { show: true, required: false }
            },
            labels: {
                'address-header': 'Property Address'
            }
        },
        'LTR Form': {
            fields: {
                phone: { show: true, required: true },
                contactType: { show: false, required: false },
                message: { show: true, required: true },
                address: { show: true, required: true },
                updates: { show: true, required: false },
                estimateNumber: { show: false, required: false },
                services: { show: false, required: false },
                structure: { show: false, required: false },
                picturesSubmitted: { show: false, required: false },
                fileUpload: { show: false, required: false },
                plansPicsUploaded: { show: false, required: false },
                hearAbout: { show: true, required: false }
            },
            labels: {
                'message-label': 'Request Details',
                'address-header': 'Property Address'
            }
        },
        'Contact Form': {
            fields: {
                phone: { show: false, required: false },
                contactType: { show: false, required: false },
                message: { show: true, required: false },
                address: { show: false, required: false },
                updates: { show: false, required: false },
                estimateNumber: { show: false, required: false },
                services: { show: false, required: false },
                structure: { show: false, required: false },
                picturesSubmitted: { show: false, required: false },
                fileUpload: { show: false, required: false },
                plansPicsUploaded: { show: false, required: false },
                hearAbout: { show: false, required: false }
            },
            labels: {
                'message-label': 'Message',
                'address-header': 'Address'
            }
        },
        'Phone Inquiry': {
            fields: {
                phone: { show: true, required: true },
                contactType: { show: true, required: false },
                message: { show: true, required: false },
                address: { show: false, required: false },
                updates: { show: false, required: false },
                estimateNumber: { show: false, required: false },
                services: { show: false, required: false },
                structure: { show: false, required: false },
                picturesSubmitted: { show: false, required: false },
                fileUpload: { show: false, required: false },
                plansPicsUploaded: { show: false, required: false },
                hearAbout: { show: false, required: false }
            },
            labels: {
                'message-label': 'Notes',
                'address-header': 'Address'
            }
        },
        'Cold Call': {
            fields: {
                phone: { show: true, required: true },
                contactType: { show: true, required: false },
                message: { show: true, required: false },
                address: { show: false, required: false },
                updates: { show: false, required: false },
                estimateNumber: { show: false, required: false },
                services: { show: false, required: false },
                structure: { show: false, required: false },
                picturesSubmitted: { show: false, required: false },
                fileUpload: { show: false, required: false },
                plansPicsUploaded: { show: false, required: false },
                hearAbout: { show: false, required: false }
            },
            labels: {
                'address-header': 'Address'
            }
        },
        'In Person': {
            fields: {
                phone: { show: true, required: true },
                contactType: { show: true, required: true },
                message: { show: true, required: false },
                address: { show: true, required: true },
                updates: { show: false, required: false },
                estimateNumber: { show: false, required: false },
                services: { show: true, required: false },
                structure: { show: true, required: false },
                picturesSubmitted: { show: false, required: false },
                fileUpload: { show: false, required: false },
                plansPicsUploaded: { show: false, required: false },
                hearAbout: { show: false, required: false }
            },
            labels: {
                'address-header': 'Property Address'
            }
        }
    };
    
    function updateFormFields() {
        const selectedSource = leadSourceSelect.value;
        console.log('Selected lead source:', selectedSource);
        const config = formConfigs[selectedSource];
        
        if (!config) {
            console.log('No config found for', selectedSource, 'using Web Estimate config');
            // For other lead sources, use Web Estimate config
            updateFieldsWithConfig(formConfigs['Web Estimate']);
            return;
        }
        
        console.log('Using config for', selectedSource);
        updateFieldsWithConfig(config);
    }
    
    function updateFieldsWithConfig(config) {
        // Phone field
        updateField('phone-field', 'cell_phone', 'phone-label', config.fields.phone);
        
        // Contact Type field
        updateField('contact-type-field', 'ctype', 'contact-type-label', config.fields.contactType);
        
        // Message field
        updateField('message-section', 'message', 'message-label', config.fields.message);
        
        // Address section
        updateAddressSection(config.fields.address);
        
        // Updates field
        updateField('updates-section', 'get_updates', 'updates-label', config.fields.updates);
        
        // Estimate Number field
        updateField('estimate-number-section', 'estimate_number', null, config.fields.estimateNumber);
        
        // Services Interested In field
        updateField('services-section', null, null, config.fields.services);
        
        // Pictures Submitted field
        updateField('pictures-submitted-section', null, null, config.fields.picturesSubmitted);
        
        // File Upload Links field
        updateField('file-upload-section', null, null, config.fields.fileUpload);
        
        // Plans and Pictures Uploaded field
        updateField('plans-pics-uploaded-section', 'plans_and_pics', null, config.fields.plansPicsUploaded);
        
        // Structure Information field
        updateField('structure-section', null, null, config.fields.structure);
        
        // How did you hear about us field
        updateField('hear-about-section', null, null, config.fields.hearAbout);
        
        // Update labels
        updateLabels(config.labels);
    }
    
    function updateField(sectionId, fieldId, labelId, fieldConfig) {
        const section = document.getElementById(sectionId);
        const field = document.getElementById(fieldId);
        const label = document.getElementById(labelId);
        
        if (!section) return;
        
        if (fieldConfig.show) {
            section.style.display = '';
            section.classList.remove('d-none');
        } else {
            section.style.display = 'none';
            section.classList.add('d-none');
        }
        
        if (field) {
            if (fieldConfig.required) {
                field.setAttribute('required', '');
            } else {
                field.removeAttribute('required');
            }
        }
        
        if (label) {
            if (fieldConfig.required) {
                label.classList.add('required');
            } else {
                label.classList.remove('required');
            }
        }
    }
    
    function updateAddressSection(addressConfig) {
        const addressSection = document.getElementById('address-section');
        if (!addressSection) return;
        
        if (addressConfig.show) {
            addressSection.style.display = '';
            addressSection.classList.remove('d-none');
        } else {
            addressSection.style.display = 'none';
            addressSection.classList.add('d-none');
        }
        
        // Update required status for address fields
        const addressFields = ['p_street_1', 'p_city', 'p_state', 'p_postcode', 'p_country'];
        const addressLabels = ['street1-label', 'city-label', 'state-label', 'postcode-label', 'country-label'];
        
        addressFields.forEach((fieldId, index) => {
            const field = document.getElementById(fieldId);
            const label = document.getElementById(addressLabels[index]);
            
            if (field) {
                if (addressConfig.required) {
                    field.setAttribute('required', '');
                } else {
                    field.removeAttribute('required');
                }
            }
            
            if (label) {
                if (addressConfig.required) {
                    label.classList.add('required');
                } else {
                    label.classList.remove('required');
                }
            }
        });
    }
    
    function updateLabels(labelUpdates) {
        for (const [elementId, text] of Object.entries(labelUpdates)) {
            const element = document.getElementById(elementId);
            if (element) {
                element.textContent = text;
            }
        }
    }
    
    // Add event listener
    leadSourceSelect.addEventListener('change', updateFormFields);
    
    // Trigger on page load to handle pre-selected values
    updateFormFields();
});