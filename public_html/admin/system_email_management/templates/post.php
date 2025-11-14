<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

// Security check
$security = new Security();
$security->check_user_permissions('admin', 'create');

// Verify nonce
$nonce = new Nonce();
if (!$nonce->verify($_POST['nonce'] ?? '', 'email_template')) {
    $_SESSION['email_template_message'] = "Invalid security token. Please try again.";
    $_SESSION['email_template_message_type'] = "danger";
    header("Location: list.php");
    exit;
}

// Initialize EmailTemplate class
$emailTemplate = new EmailTemplate();

$action = $_POST['action'] ?? '';

try {
    if ($action === 'create') {
        // Create new template
        $data = [
            'template_key' => trim($_POST['template_key']),
            'template_name' => trim($_POST['template_name']),
            'description' => trim($_POST['description'] ?? ''),
            'module' => $_POST['module'],
            'category' => $_POST['category'],
            'trigger_event' => trim($_POST['trigger_event'] ?? ''),
            'trigger_conditions' => trim($_POST['trigger_conditions'] ?? ''),
            'requires_approval' => isset($_POST['requires_approval']) ? 1 : 0,
            'active' => isset($_POST['active']) ? 1 : 0,
            'created_by' => $_SESSION['user_id']
        ];
        
        // Validate template_key format
        if (!preg_match('/^[a-z0-9_]+$/', $data['template_key'])) {
            throw new Exception("Template key must contain only lowercase letters, numbers, and underscores.");
        }
        
        // Validate JSON if provided
        if (!empty($data['trigger_conditions'])) {
            $json = json_decode($data['trigger_conditions']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Trigger conditions must be valid JSON.");
            }
        }
        
        $templateId = $emailTemplate->createTemplate($data);
        
        $_SESSION['email_template_message'] = "Template created successfully! Now add content and variables.";
        $_SESSION['email_template_message_type'] = "success";
        header("Location: content.php?id=" . $templateId);
        exit;
        
    } elseif ($action === 'update') {
        // Update existing template
        $id = (int)$_POST['id'];
        
        $data = [
            'template_name' => trim($_POST['template_name']),
            'description' => trim($_POST['description'] ?? ''),
            'module' => $_POST['module'],
            'category' => $_POST['category'],
            'trigger_event' => trim($_POST['trigger_event'] ?? ''),
            'trigger_conditions' => trim($_POST['trigger_conditions'] ?? ''),
            'requires_approval' => isset($_POST['requires_approval']) ? 1 : 0,
            'active' => isset($_POST['active']) ? 1 : 0
        ];
        
        // Validate JSON if provided
        if (!empty($data['trigger_conditions'])) {
            $json = json_decode($data['trigger_conditions']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Trigger conditions must be valid JSON.");
            }
        }
        
        $emailTemplate->updateTemplate($id, $data);
        
        $_SESSION['email_template_message'] = "Template updated successfully!";
        $_SESSION['email_template_message_type'] = "success";
        header("Location: view.php?id=" . $id);
        exit;
        
    } elseif ($action === 'save_content') {
        // Save template content
        $templateId = (int)$_POST['template_id'];
        $languageCode = $_POST['language_code'];
        
        $contentData = [
            'subject' => trim($_POST['subject']),
            'body_html' => $_POST['body_html'], // Don't trim - preserves formatting
            'body_plain_text' => trim($_POST['body_plain_text'] ?? '')
        ];
        
        $emailTemplate->saveTemplateContent($templateId, $languageCode, $contentData);
        
        $_SESSION['email_template_message'] = "Content saved successfully!";
        $_SESSION['email_template_message_type'] = "success";
        header("Location: content.php?id=" . $templateId);
        exit;
        
    } elseif ($action === 'add_variable') {
        // Add template variable
        $templateId = (int)$_POST['template_id'];
        
        $variableData = [
            'variable_key' => trim($_POST['variable_key']),
            'variable_label' => trim($_POST['variable_label']),
            'variable_description' => trim($_POST['variable_description'] ?? ''),
            'variable_type' => $_POST['variable_type'],
            'variable_source' => trim($_POST['variable_source'] ?? ''),
            'default_value' => trim($_POST['default_value'] ?? ''),
            'is_required' => isset($_POST['is_required']) ? 1 : 0,
            'sort_order' => (int)($_POST['sort_order'] ?? 0)
        ];
        
        // Validate variable_key format
        if (!preg_match('/^[a-z0-9_]+$/', $variableData['variable_key'])) {
            throw new Exception("Variable key must contain only lowercase letters, numbers, and underscores.");
        }
        
        $emailTemplate->addTemplateVariable($templateId, $variableData);
        
        $_SESSION['email_template_message'] = "Variable added successfully!";
        $_SESSION['email_template_message_type'] = "success";
        header("Location: content.php?id=" . $templateId . "#variables");
        exit;
        
    } elseif ($action === 'delete_variable') {
        // Delete template variable
        $variableId = (int)$_POST['variable_id'];
        $templateId = (int)$_POST['template_id'];
        
        $emailTemplate->deleteTemplateVariable($variableId);
        
        $_SESSION['email_template_message'] = "Variable deleted successfully!";
        $_SESSION['email_template_message_type'] = "success";
        header("Location: content.php?id=" . $templateId . "#variables");
        exit;
        
    } else {
        throw new Exception("Invalid action specified.");
    }
    
} catch (Exception $e) {
    $_SESSION['email_template_message'] = "Error: " . $e->getMessage();
    $_SESSION['email_template_message_type'] = "danger";
    
    // Redirect back to appropriate page
    if (isset($_POST['id'])) {
        header("Location: edit.php?id=" . $_POST['id']);
    } elseif (isset($_POST['template_id'])) {
        header("Location: content.php?id=" . $_POST['template_id']);
    } else {
        header("Location: list.php");
    }
    exit;
}