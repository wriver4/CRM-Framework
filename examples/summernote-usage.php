<?php
/**
 * Summernote Usage Examples
 * 
 * This file demonstrates how to use the Summernote integration system
 * in various scenarios throughout the CRM application.
 */

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

// Example 1: Basic page setup (this would typically be in your page controller)
$dir = 'leads';
$page = 'edit';

// The EditorHelper will automatically configure appropriate editors based on page context
// No additional code needed in most cases - it's handled by the template system

?>
<!DOCTYPE html>
<html>
<head>
    <title>Summernote Usage Examples</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <?php
    // Example 2: Manual CSS inclusion (if not using template system)
    $editorHelper = EditorHelper::getInstance();
    echo $editorHelper->getCssIncludes();
    ?>
</head>
<body>
    <div class="container mt-4">
        <h1>Summernote Integration Examples</h1>
        
        <!-- Example 3: Basic textarea that will be enhanced -->
        <div class="mb-3">
            <label for="basic-notes" class="form-label">Basic Notes</label>
            <textarea name="notes" id="basic-notes" class="form-control" rows="4" 
                      placeholder="Enter your notes here..."></textarea>
        </div>
        
        <!-- Example 4: Project description textarea -->
        <div class="mb-3">
            <label for="project-description" class="form-label">Project Description</label>
            <textarea name="project_description" id="project-description" class="form-control" rows="6" 
                      placeholder="Describe the project details..."></textarea>
        </div>
        
        <!-- Example 5: Email template textarea -->
        <div class="mb-3">
            <label for="email-template" class="form-label">Email Template</label>
            <textarea name="email_template" id="email-template" class="form-control" rows="8" 
                      placeholder="Design your email template..."></textarea>
        </div>
        
        <!-- Example 6: Custom configuration button -->
        <button type="button" class="btn btn-primary" onclick="setupCustomEditor()">
            Setup Custom Editor
        </button>
        
        <!-- Example 7: Plugin demonstration button -->
        <button type="button" class="btn btn-secondary" onclick="loadEmailPlugins()">
            Load Email Template Plugins
        </button>
        
        <!-- Example 8: Export functionality -->
        <button type="button" class="btn btn-success" onclick="exportEmailTemplate()">
            Export Email Template
        </button>
    </div>

    <!-- JavaScript includes -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php
    // Example 9: Manual JavaScript inclusion and configuration
    $editorHelper = EditorHelper::getInstance();
    
    // Configure specific textareas with custom settings
    $editorHelper->configureTextareas([
        '#basic-notes' => [
            'toolbar' => 'basic',
            'height' => 150,
            'placeholder' => 'Enter your notes here...'
        ],
        '#project-description' => [
            'toolbar' => 'standard',
            'height' => 200,
            'placeholder' => 'Describe the project details...'
        ],
        '#email-template' => [
            'toolbar' => 'email',
            'height' => 300,
            'placeholder' => 'Design your email template...'
        ]
    ]);
    
    echo $editorHelper->getJsIncludes();
    ?>
    
    <script>
        // Example 10: Custom JavaScript functions
        function setupCustomEditor() {
            // Create a new textarea dynamically
            const $container = $('<div class="mb-3">');
            const $label = $('<label class="form-label">Custom Editor</label>');
            const $textarea = $('<textarea name="custom" class="form-control" rows="4"></textarea>');
            
            $container.append($label, $textarea);
            $('.container').append($container);
            
            // Initialize Summernote with custom configuration
            $textarea.summernote({
                toolbar: [
                    ['style', ['bold', 'italic', 'underline']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol']],
                    ['insert', ['link', 'picture']],
                    ['misc', ['undo', 'redo']]
                ],
                height: 200,
                placeholder: 'Custom editor with specific toolbar...',
                callbacks: {
                    onInit: function() {
                        console.log('Custom editor initialized');
                    }
                }
            });
        }
        
        function loadEmailPlugins() {
            // Load email template plugins
            if (window.summernoteHelper) {
                window.summernoteHelper.loadEmailTemplatePlugins().then(() => {
                    alert('Email template plugins loaded! You can now use emoji, templates, and special characters.');
                });
            }
        }
        
        function exportEmailTemplate() {
            // Export email template
            if (window.summernoteHelper) {
                const template = window.summernoteHelper.exportAsEmailTemplate('#email-template');
                console.log('Exported template:', template);
                
                // Show preview
                const $modal = $(`
                    <div class="modal fade" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Email Template Preview</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <h6>HTML Version:</h6>
                                    <div class="border p-3 mb-3" style="max-height: 300px; overflow-y: auto;">
                                        ${template.html}
                                    </div>
                                    <h6>Plain Text Version:</h6>
                                    <div class="border p-3" style="max-height: 200px; overflow-y: auto; font-family: monospace;">
                                        ${template.text}
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `);
                
                $('body').append($modal);
                $modal.modal('show');
                $modal.on('hidden.bs.modal', function() {
                    $modal.remove();
                });
            }
        }
        
        // Example 11: Template variables setup
        $(document).ready(function() {
            if (window.summernoteHelper) {
                // Setup template variables for email editor
                const emailVariables = [
                    { name: 'Lead ID', value: '{{lead_id}}' },
                    { name: 'Customer Email', value: '{{customer_email}}' },
                    { name: 'Project Status', value: '{{project_status}}' },
                    { name: 'Due Date', value: '{{due_date}}' }
                ];
                
                window.summernoteHelper.setupTemplateVariables('#email-template', emailVariables);
            }
        });
    </script>
</body>
</html>

<?php
/**
 * Additional Usage Examples (for reference)
 */

// Example 12: Page-specific configuration in a controller
function configureLeadEditPage() {
    $editorHelper = EditorHelper::getInstance();
    
    // Override default configuration for specific textareas
    $editorHelper->configureForPage('leads', 'edit', [
        'notes' => [
            'height' => 180,
            'toolbar' => 'advanced',
            'placeholder' => 'Detailed lead notes with full formatting...'
        ],
        'project_description' => [
            'height' => 250,
            'toolbar' => 'standard'
        ]
    ]);
}

// Example 13: Email template setup
function setupEmailTemplateEditor($selector = '#email-content') {
    $editorHelper = EditorHelper::getInstance();
    $editorHelper->setupEmailTemplate($selector);
}

// Example 14: Custom toolbar configuration
function createCustomToolbar() {
    return [
        ['style', ['style', 'bold', 'italic']],
        ['font', ['fontsize', 'color']],
        ['para', ['ul', 'ol', 'paragraph']],
        ['table', ['table']],
        ['insert', ['link', 'picture']],
        ['view', ['codeview']],
        ['misc', ['undo', 'redo']]
    ];
}

// Example 15: Plugin integration for future use
function loadAwesomeSummernotePlugins() {
    // This would be called via JavaScript
    $plugins = [
        'summernote-ext-emoji' => 'https://cdn.jsdelivr.net/npm/summernote-ext-emoji@latest/dist/summernote-ext-emoji.min.js',
        'summernote-ext-mention' => 'https://cdn.jsdelivr.net/npm/summernote-ext-mention@latest/dist/summernote-ext-mention.min.js',
        'summernote-ext-template' => 'https://cdn.jsdelivr.net/npm/summernote-ext-template@latest/dist/summernote-ext-template.min.js'
    ];
    
    return $plugins;
}
?>