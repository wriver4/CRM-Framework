<?php
/**
 * SMTP Configuration - Delete Handler
 * Handles deletion of SMTP configurations
 */

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

// Security check
$security = new Security();
$security->check_user_permissions('admin', 'read');

// Initialize database
$database = new Database();
$pdo = $database->dbcrm();

// Get ID from query string
$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM smtp_config WHERE id = ?");
        $stmt->execute([$id]);
        
        $_SESSION['smtp_message'] = "SMTP configuration deleted successfully.";
        $_SESSION['smtp_message_type'] = "success";
    } catch (Exception $e) {
        $_SESSION['smtp_message'] = "Error deleting configuration: " . $e->getMessage();
        $_SESSION['smtp_message_type'] = "danger";
        error_log("SMTP Config Delete Error: " . $e->getMessage());
    }
} else {
    $_SESSION['smtp_message'] = "Invalid configuration ID.";
    $_SESSION['smtp_message_type'] = "danger";
}

// Redirect back to list
header("Location: list.php");
exit;