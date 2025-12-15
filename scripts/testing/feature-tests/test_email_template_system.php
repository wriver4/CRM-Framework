<?php
/**
 * Test Email Template System
 * Verifies that the email template system is working correctly
 */

require_once __DIR__ . '/classes/Core/Database.php';
require_once __DIR__ . '/classes/Logging/Audit.php';
require_once __DIR__ . '/classes/Models/EmailTemplate.php';
require_once __DIR__ . '/classes/Utilities/EmailService.php';
require_once __DIR__ . '/classes/Utilities/EmailRenderer.php';
require_once __DIR__ . '/classes/Utilities/EmailQueueManager.php';
require_once __DIR__ . '/classes/Utilities/EmailTriggerHandler.php';

echo "=== Email Template System Test ===\n\n";

// Initialize classes
$emailTemplate = new EmailTemplate();
$emailRenderer = new EmailRenderer();
$emailQueueManager = new EmailQueueManager();

// Test 1: Get all templates
echo "Test 1: Get All Templates\n";
echo "----------------------------\n";
$templates = $emailTemplate->getAllTemplates();
echo "Found " . count($templates) . " templates:\n";
foreach ($templates as $template) {
    echo "  - {$template['template_name']} (Key: {$template['template_key']}, Module: {$template['module']})\n";
}
echo "\n";

// Test 2: Get template by key
echo "Test 2: Get Template by Key\n";
echo "----------------------------\n";
$template = $emailTemplate->getTemplateByKey('lead_welcome');
if ($template) {
    echo "Template: {$template['template_name']}\n";
    echo "Module: {$template['module']}\n";
    echo "Category: {$template['category']}\n";
    echo "Active: " . ($template['active'] ? 'Yes' : 'No') . "\n";
} else {
    echo "Template 'lead_welcome' not found!\n";
}
echo "\n";

// Test 3: Get template content
echo "Test 3: Get Template Content\n";
echo "----------------------------\n";
if ($template) {
    $contentEn = $emailTemplate->getTemplateContent($template['id'], 'en');
    $contentEs = $emailTemplate->getTemplateContent($template['id'], 'es');
    
    echo "English Content:\n";
    echo "  Subject: {$contentEn['subject']}\n";
    echo "  Body (first 100 chars): " . substr($contentEn['body_html'], 0, 100) . "...\n";
    
    echo "\nSpanish Content:\n";
    echo "  Subject: {$contentEs['subject']}\n";
    echo "  Body (first 100 chars): " . substr($contentEs['body_html'], 0, 100) . "...\n";
}
echo "\n";

// Test 4: Get template variables
echo "Test 4: Get Template Variables\n";
echo "----------------------------\n";
if ($template) {
    $variables = $emailTemplate->getTemplateVariables($template['id']);
    echo "Found " . count($variables) . " variables:\n";
    foreach ($variables as $var) {
        echo "  - {{" . $var['variable_key'] . "}} => {$var['variable_label']} (Source: {$var['variable_source']})\n";
    }
}
echo "\n";

// Test 5: Get global templates
echo "Test 5: Get Global Templates\n";
echo "----------------------------\n";
$headerEn = $emailTemplate->getGlobalTemplate('header', 'en');
$footerEn = $emailTemplate->getGlobalTemplate('footer', 'en');
echo "Header (EN): " . (strlen($headerEn['html_content'] ?? '') > 0 ? "Found (" . strlen($headerEn['html_content']) . " chars)" : "Not found") . "\n";
echo "Footer (EN): " . (strlen($footerEn['html_content'] ?? '') > 0 ? "Found (" . strlen($footerEn['html_content']) . " chars)" : "Not found") . "\n";
echo "\n";

// Test 6: Get trigger rules
echo "Test 6: Get Trigger Rules\n";
echo "----------------------------\n";
$assignmentTemplate = $emailTemplate->getTemplateByKey('lead_assignment');
if ($assignmentTemplate) {
    $rules = $emailTemplate->getTriggerRules($assignmentTemplate['id']);
    echo "Found " . count($rules) . " trigger rules for 'lead_assignment':\n";
    foreach ($rules as $rule) {
        echo "  - Module: {$rule['module']}, Type: {$rule['trigger_type']}, Recipient: {$rule['recipient_type']}\n";
    }
}
echo "\n";

// Test 7: Test rendering with sample lead data
echo "Test 7: Test Email Rendering\n";
echo "----------------------------\n";
echo "NOTE: This requires a real lead record in the database.\n";
echo "Checking for leads...\n";

try {
    $db = new Database();
    $stmt = $db->conn()->prepare("SELECT id, full_name, email FROM leads WHERE email IS NOT NULL LIMIT 1");
    $stmt->execute();
    $lead = $stmt->fetch();
    
    if ($lead && $template) {
        echo "Found lead: {$lead['full_name']} (ID: {$lead['id']})\n";
        echo "Rendering 'lead_welcome' template...\n\n";
        
        $rendered = $emailRenderer->renderEmail($template['id'], 'leads', $lead['id'], 'en');
        
        echo "Rendered Email:\n";
        echo "  Subject: {$rendered['subject']}\n";
        echo "  HTML Length: " . strlen($rendered['html']) . " chars\n";
        echo "  Plain Text Length: " . strlen($rendered['plain_text']) . " chars\n";
        echo "  Variables Used: " . count($rendered['variables']) . "\n";
        
        echo "\n  Variable Values:\n";
        foreach ($rendered['variables'] as $key => $value) {
            $displayValue = strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value;
            echo "    - $key: $displayValue\n";
        }
        
        // Test 8: Queue the email (without actually sending)
        echo "\n\nTest 8: Queue Email\n";
        echo "----------------------------\n";
        echo "Queueing email for {$lead['full_name']}...\n";
        
        $queueId = $emailQueueManager->queueEmail(
            $template['id'],
            'leads',
            $lead['id'],
            $lead['email'],
            $lead['full_name'],
            'en',
            true // Require approval so it doesn't actually send
        );
        
        if ($queueId) {
            echo "✓ Email queued successfully! Queue ID: $queueId\n";
            echo "  Status: Pending approval (not sent)\n";
            
            // Get the queued email details
            $stmt = $db->conn()->prepare("SELECT * FROM email_queue WHERE id = :id");
            $stmt->execute([':id' => $queueId]);
            $queuedEmail = $stmt->fetch();
            
            echo "  Recipient: {$queuedEmail['recipient_name']} <{$queuedEmail['recipient_email']}>\n";
            echo "  Subject: {$queuedEmail['subject']}\n";
            echo "  Language: {$queuedEmail['language_code']}\n";
        } else {
            echo "✗ Failed to queue email\n";
        }
        
    } else {
        echo "No leads found with email addresses. Skipping render test.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";