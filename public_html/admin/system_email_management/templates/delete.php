<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

// Security check
$security = new Security();
$security->check_user_permissions('admin', 'delete');

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        $emailTemplate = new EmailTemplate();
        $emailTemplate->deleteTemplate($id);
        
        $_SESSION['email_template_message'] = "Template deleted successfully!";
        $_SESSION['email_template_message_type'] = "success";
    } catch (Exception $e) {
        $_SESSION['email_template_message'] = "Error deleting template: " . $e->getMessage();
        $_SESSION['email_template_message_type'] = "danger";
    }
} else {
    $_SESSION['email_template_message'] = "Invalid template ID.";
    $_SESSION['email_template_message_type'] = "danger";
}

header("Location: list.php");
exit;