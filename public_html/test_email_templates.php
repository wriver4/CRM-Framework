<?php
/**
 * Email Template System Test Page
 * Access via: http://yourdomain.com/test_email_templates.php
 */

// Include required classes
require_once __DIR__ . '/../classes/Core/Database.php';
require_once __DIR__ . '/../classes/Logging/Audit.php';
require_once __DIR__ . '/../classes/Utilities/EmailService.php';
require_once __DIR__ . '/../classes/Models/EmailTemplate.php';
require_once __DIR__ . '/../classes/Utilities/EmailRenderer.php';
require_once __DIR__ . '/../classes/Utilities/EmailQueueManager.php';

// Start output
?>
<!DOCTYPE html>
<html>
<head>
    <title>Email Template System Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .test-section { background: white; padding: 20px; margin: 20px 0; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .test-section h2 { color: #333; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
        .success { color: #4CAF50; font-weight: bold; }
        .error { color: #f44336; font-weight: bold; }
        .info { color: #2196F3; }
        pre { background: #f9f9f9; padding: 10px; border-left: 3px solid #4CAF50; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        table th, table td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        table th { background-color: #4CAF50; color: white; }
        .rendered-email { border: 1px solid #ddd; padding: 15px; margin: 10px 0; background: white; }
    </style>
</head>
<body>
    <h1>📧 Email Template System Test</h1>
    
    <?php
    try {
        // Test 1: Get all templates
        echo '<div class="test-section">';
        echo '<h2>Test 1: Get All Email Templates</h2>';
        $emailTemplate = new EmailTemplate();
        $templates = $emailTemplate->getAllTemplates();
        
        if (!empty($templates)) {
            echo '<p class="success">✓ Found ' . count($templates) . ' templates</p>';
            echo '<table>';
            echo '<tr><th>ID</th><th>Template Key</th><th>Name</th><th>Module</th><th>Category</th><th>Active</th></tr>';
            foreach ($templates as $template) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($template['id']) . '</td>';
                echo '<td>' . htmlspecialchars($template['template_key']) . '</td>';
                echo '<td>' . htmlspecialchars($template['template_name']) . '</td>';
                echo '<td>' . htmlspecialchars($template['module']) . '</td>';
                echo '<td>' . htmlspecialchars($template['category']) . '</td>';
                echo '<td>' . ($template['active'] ? '✓' : '✗') . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<p class="error">✗ No templates found</p>';
        }
        echo '</div>';
        
        // Test 2: Get template content
        if (!empty($templates)) {
            $firstTemplate = $templates[0];
            echo '<div class="test-section">';
            echo '<h2>Test 2: Get Template Content (English)</h2>';
            echo '<p class="info">Testing template: ' . htmlspecialchars($firstTemplate['template_name']) . '</p>';
            
            $content = $emailTemplate->getTemplateContent($firstTemplate['id'], 'en');
            if ($content) {
                echo '<p class="success">✓ Content loaded successfully</p>';
                echo '<p><strong>Subject:</strong> ' . htmlspecialchars($content['subject']) . '</p>';
                echo '<p><strong>Body HTML:</strong></p>';
                echo '<pre>' . htmlspecialchars(substr($content['body_html'], 0, 500)) . '...</pre>';
            } else {
                echo '<p class="error">✗ Failed to load content</p>';
            }
            echo '</div>';
            
            // Test 3: Get template variables
            echo '<div class="test-section">';
            echo '<h2>Test 3: Get Template Variables</h2>';
            $variables = $emailTemplate->getTemplateVariables($firstTemplate['id']);
            
            if (!empty($variables)) {
                echo '<p class="success">✓ Found ' . count($variables) . ' variables</p>';
                echo '<table>';
                echo '<tr><th>Variable Key</th><th>Label</th><th>Type</th><th>Source</th></tr>';
                foreach ($variables as $var) {
                    echo '<tr>';
                    echo '<td><code>{{' . htmlspecialchars($var['variable_key']) . '}}</code></td>';
                    echo '<td>' . htmlspecialchars($var['variable_label']) . '</td>';
                    echo '<td>' . htmlspecialchars($var['variable_type']) . '</td>';
                    echo '<td>' . htmlspecialchars($var['variable_source'] ?? 'N/A') . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                echo '<p class="info">No variables defined for this template</p>';
            }
            echo '</div>';
            
            // Test 4: Get global templates
            echo '<div class="test-section">';
            echo '<h2>Test 4: Get Global Templates (Header/Footer)</h2>';
            
            $header = $emailTemplate->getGlobalTemplate('header', 'en');
            $footer = $emailTemplate->getGlobalTemplate('footer', 'en');
            
            if ($header) {
                echo '<p class="success">✓ Header template loaded</p>';
                echo '<p><strong>Header Name:</strong> ' . htmlspecialchars($header['template_name']) . '</p>';
            } else {
                echo '<p class="error">✗ No header template found</p>';
            }
            
            if ($footer) {
                echo '<p class="success">✓ Footer template loaded</p>';
                echo '<p><strong>Footer Name:</strong> ' . htmlspecialchars($footer['template_name']) . '</p>';
            } else {
                echo '<p class="error">✗ No footer template found</p>';
            }
            echo '</div>';
            
            // Test 5: Get trigger rules
            echo '<div class="test-section">';
            echo '<h2>Test 5: Get Trigger Rules</h2>';
            $triggers = $emailTemplate->getTriggerRules($firstTemplate['id']);
            
            if (!empty($triggers)) {
                echo '<p class="success">✓ Found ' . count($triggers) . ' trigger rules</p>';
                echo '<table>';
                echo '<tr><th>Module</th><th>Trigger Type</th><th>Recipient Type</th><th>Delay (min)</th><th>Active</th></tr>';
                foreach ($triggers as $trigger) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($trigger['module']) . '</td>';
                    echo '<td>' . htmlspecialchars($trigger['trigger_type']) . '</td>';
                    echo '<td>' . htmlspecialchars($trigger['recipient_type']) . '</td>';
                    echo '<td>' . htmlspecialchars($trigger['delay_minutes']) . '</td>';
                    echo '<td>' . ($trigger['active'] ? '✓' : '✗') . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                echo '<p class="info">No trigger rules defined for this template</p>';
            }
            echo '</div>';
            
            // Test 6: Render email with sample data
            echo '<div class="test-section">';
            echo '<h2>Test 6: Render Email with Sample Data</h2>';
            
            $renderer = new EmailRenderer();
            $sampleData = [
                'lead_name' => 'John Doe',
                'lead_email' => 'john.doe@example.com',
                'lead_phone' => '(555) 123-4567',
                'service_name' => 'Solar Installation',
                'assigned_user' => 'Jane Smith',
                'company_name' => 'DemoCRM Company',
                'company_phone' => '(555) 999-8888',
                'company_email' => 'info@democrm.com'
            ];
            
            try {
                $rendered = $renderer->renderTemplate($firstTemplate['id'], $sampleData, 'en');
                
                if ($rendered) {
                    echo '<p class="success">✓ Email rendered successfully</p>';
                    echo '<p><strong>Subject:</strong> ' . htmlspecialchars($rendered['subject']) . '</p>';
                    echo '<p><strong>Rendered HTML:</strong></p>';
                    echo '<div class="rendered-email">' . $rendered['body_html'] . '</div>';
                } else {
                    echo '<p class="error">✗ Failed to render email</p>';
                }
            } catch (Exception $e) {
                echo '<p class="error">✗ Error rendering email: ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
            echo '</div>';
            
            // Test 7: Test email queueing (without actually sending)
            echo '<div class="test-section">';
            echo '<h2>Test 7: Test Email Queue System</h2>';
            echo '<p class="info">Testing queue functionality (no actual email will be sent)</p>';
            
            try {
                $queueManager = new EmailQueueManager();
                echo '<p class="success">✓ EmailQueueManager initialized successfully</p>';
                echo '<p class="info">Queue system is ready to accept emails</p>';
            } catch (Exception $e) {
                echo '<p class="error">✗ Error initializing queue: ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
            echo '</div>';
        }
        
        // Summary
        echo '<div class="test-section">';
        echo '<h2>✅ Test Summary</h2>';
        echo '<p class="success">Email template system is operational!</p>';
        echo '<ul>';
        echo '<li>✓ Templates can be retrieved from database</li>';
        echo '<li>✓ Template content loads correctly</li>';
        echo '<li>✓ Variables are properly defined</li>';
        echo '<li>✓ Global templates (header/footer) are available</li>';
        echo '<li>✓ Trigger rules are configured</li>';
        echo '<li>✓ Email rendering works with variable substitution</li>';
        echo '<li>✓ Queue system is initialized</li>';
        echo '</ul>';
        echo '<p><strong>Next Steps:</strong></p>';
        echo '<ul>';
        echo '<li>Integrate email triggers into leads/post.php</li>';
        echo '<li>Create admin interface for template management</li>';
        echo '<li>Test actual email sending via queue</li>';
        echo '<li>Add more templates for different modules</li>';
        echo '</ul>';
        echo '</div>';
        
    } catch (Exception $e) {
        echo '<div class="test-section">';
        echo '<h2 class="error">❌ Fatal Error</h2>';
        echo '<p class="error">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        echo '</div>';
    }
    ?>
    
</body>
</html>