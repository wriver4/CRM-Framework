<?php
/**
 * Summernote Integration Test Page
 * 
 * This page tests the Summernote integration to ensure it's working correctly
 */

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();

// Set page variables for template system
$dir = 'leads';
$page = 'edit';
$title = 'Summernote Test';
$table_page = false;

// Include header
include_once '../templates/header.php';
include_once '../templates/nav.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Summernote WYSIWYG Editor Test</h4>
                    <p class="mb-0">Testing the Summernote integration with different toolbar configurations</p>
                </div>
                <div class="card-body">
                    <form method="post" action="#">
                        
                        <!-- Test 1: Basic Notes Editor -->
                        <div class="mb-4">
                            <label for="notes" class="form-label">
                                <strong>Basic Notes Editor</strong>
                                <small class="text-muted">(Should use 'notes' toolbar preset)</small>
                            </label>
                            <textarea name="notes" id="notes" class="form-control" rows="4" 
                                      placeholder="Enter your notes here...">This is a test of the <strong>basic notes</strong> editor with some <em>formatting</em>.</textarea>
                        </div>
                        
                        <!-- Test 2: Project Description Editor -->
                        <div class="mb-4">
                            <label for="project_description" class="form-label">
                                <strong>Project Description Editor</strong>
                                <small class="text-muted">(Should use 'standard' toolbar preset)</small>
                            </label>
                            <textarea name="project_description" id="project_description" class="form-control" rows="6" 
                                      placeholder="Describe the project details...">This is a test of the <strong>project description</strong> editor with more advanced features like <a href="#">links</a> and lists:
<ul>
<li>Feature 1</li>
<li>Feature 2</li>
</ul></textarea>
                        </div>
                        
                        <!-- Test 3: Custom Configuration -->
                        <div class="mb-4">
                            <label for="custom_editor" class="form-label">
                                <strong>Custom Editor</strong>
                                <small class="text-muted">(Will be configured via JavaScript)</small>
                            </label>
                            <textarea name="custom_editor" id="custom_editor" class="form-control" rows="4" 
                                      placeholder="This will be configured with custom settings...">This editor will be configured with <span style="color: red;">custom settings</span> via JavaScript.</textarea>
                        </div>
                        
                        <!-- Test 4: Email Template Editor -->
                        <div class="mb-4">
                            <label for="email_template" class="form-label">
                                <strong>Email Template Editor</strong>
                                <small class="text-muted">(Email-specific toolbar)</small>
                            </label>
                            <textarea name="email_template" id="email_template" class="form-control" rows="8" 
                                      placeholder="Design your email template...">
<h2>Welcome {{customer_name}}!</h2>
<p>Thank you for your interest in our services. Your lead number is <strong>{{lead_number}}</strong>.</p>
<p>We will contact you soon regarding your project: <em>{{project_name}}</em></p>
<p>Best regards,<br>{{user_name}}</p>
                            </textarea>
                        </div>
                        
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Save Test Content</button>
                            <button type="button" class="btn btn-secondary" onclick="testFunctionality()">Test Functions</button>
                            <button type="button" class="btn btn-info" onclick="loadPlugins()">Load Plugins</button>
                        </div>
                        
                    </form>
                    
                    <!-- Test Results -->
                    <div id="test-results" class="mt-4" style="display: none;">
                        <h5>Test Results</h5>
                        <div id="results-content"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Custom configuration for the custom editor
$(document).ready(function() {
    // Configure the custom editor with specific settings
    $('#custom_editor').summernote({
        toolbar: [
            ['style', ['bold', 'italic', 'underline']],
            ['color', ['forecolor', 'backcolor']],
            ['para', ['ul', 'ol']],
            ['insert', ['link']],
            ['misc', ['undo', 'redo']]
        ],
        height: 150,
        placeholder: 'Custom editor with color support...',
        callbacks: {
            onInit: function() {
                console.log('Custom editor initialized');
            },
            onChange: function(contents, $editable) {
                console.log('Custom editor content changed');
            }
        }
    });
    
    // Configure email template editor with template variables
    if (window.summernoteHelper) {
        const emailVariables = [
            { name: 'Customer Name', value: '{{customer_name}}' },
            { name: 'Lead Number', value: '{{lead_number}}' },
            { name: 'Project Name', value: '{{project_name}}' },
            { name: 'User Name', value: '{{user_name}}' }
        ];
        
        window.summernoteHelper.setupTemplateVariables('#email_template', emailVariables);
    }
});

function testFunctionality() {
    const results = [];
    
    // Test 1: Check if editors are initialized
    const editors = $('.summernote');
    results.push(`Found ${editors.length} Summernote editors`);
    
    // Test 2: Get content from each editor
    editors.each(function(index) {
        const id = $(this).attr('id') || $(this).attr('name') || `editor-${index}`;
        const content = $(this).summernote('code');
        const textLength = $(content).text().length;
        results.push(`${id}: ${textLength} characters`);
    });
    
    // Test 3: Check if helper is loaded
    if (window.summernoteHelper) {
        results.push('Summernote helper loaded successfully');
    } else {
        results.push('Summernote helper not found');
    }
    
    // Display results
    const resultsDiv = document.getElementById('results-content');
    resultsDiv.innerHTML = '<ul><li>' + results.join('</li><li>') + '</li></ul>';
    document.getElementById('test-results').style.display = 'block';
}

async function loadPlugins() {
    if (window.summernoteHelper) {
        try {
            await window.summernoteHelper.loadEmailTemplatePlugins();
            alert('Email template plugins loaded successfully! Check the console for details.');
        } catch (error) {
            alert('Error loading plugins: ' + error.message);
        }
    } else {
        alert('Summernote helper not available');
    }
}

// Test export functionality
function exportEmailTemplate() {
    if (window.summernoteHelper) {
        const template = window.summernoteHelper.exportAsEmailTemplate('#email_template');
        console.log('Exported email template:', template);
        
        // Show in modal or alert
        const preview = template.html.substring(0, 200) + '...';
        alert('Email template exported! Check console for full content.\n\nPreview:\n' + preview);
    }
}
</script>

<?php
// Include footer
include_once '../templates/footer.php';
?>