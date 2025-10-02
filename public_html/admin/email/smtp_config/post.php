<?php
/**
 * SMTP Configuration - Form Submission Handler
 * Handles add and edit operations
 */

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';

// Security check
$security = new Security();
$security->check_user_permissions('admin', 'read');

// Initialize database
$database = new Database();
$pdo = $database->dbcrm();

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: list.php");
    exit;
}

// Validate nonce
$nonce = new Nonce();
if (!$nonce->validate($_POST['nonce'] ?? '')) {
    $_SESSION['smtp_message'] = "Invalid security token. Please try again.";
    $_SESSION['smtp_message_type'] = "danger";
    header("Location: list.php");
    exit;
}

// Get form data
$action = $_POST['action'] ?? '';
$id = (int)($_POST['id'] ?? 0);
$config_name = trim($_POST['config_name'] ?? '');
$user_id = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;
$smtp_host = trim($_POST['smtp_host'] ?? '');
$smtp_port = (int)($_POST['smtp_port'] ?? 587);
$smtp_encryption = $_POST['smtp_encryption'] ?? 'tls';
$smtp_username = trim($_POST['smtp_username'] ?? '');
$smtp_password = trim($_POST['smtp_password'] ?? '');
$from_email = trim($_POST['from_email'] ?? '');
$from_name = trim($_POST['from_name'] ?? '');
$reply_to_email = trim($_POST['reply_to_email'] ?? '');
$is_default = isset($_POST['is_default']) ? 1 : 0;
$is_active = isset($_POST['is_active']) ? 1 : 0;

try {
    if ($action === 'add') {
        // Validate required fields
        if (empty($config_name) || empty($smtp_host) || empty($smtp_username) || 
            empty($smtp_password) || empty($from_email) || empty($from_name)) {
            $_SESSION['smtp_message'] = "All required fields must be filled.";
            $_SESSION['smtp_message_type'] = "danger";
            header("Location: new.php");
            exit;
        }
        
        // If setting as default, unset other defaults for this user
        if ($is_default) {
            if ($user_id) {
                $stmt = $pdo->prepare("UPDATE smtp_config SET is_default = 0 WHERE user_id = ?");
                $stmt->execute([$user_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE smtp_config SET is_default = 0 WHERE user_id IS NULL");
                $stmt->execute();
            }
        }
        
        // Encrypt password
        $encrypted_password = base64_encode($smtp_password);
        
        $stmt = $pdo->prepare("INSERT INTO smtp_config 
            (config_name, user_id, smtp_host, smtp_port, smtp_encryption, smtp_username, smtp_password, 
             from_email, from_name, reply_to_email, is_default, is_active) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $config_name,
            $user_id,
            $smtp_host,
            $smtp_port,
            $smtp_encryption,
            $smtp_username,
            $encrypted_password,
            $from_email,
            $from_name,
            $reply_to_email,
            $is_default,
            $is_active
        ]);
        
        $_SESSION['smtp_message'] = "SMTP configuration added successfully.";
        $_SESSION['smtp_message_type'] = "success";
        
    } elseif ($action === 'edit' && $id > 0) {
        // Validate required fields
        if (empty($config_name) || empty($smtp_host) || empty($smtp_username) || 
            empty($from_email) || empty($from_name)) {
            $_SESSION['smtp_message'] = "All required fields must be filled.";
            $_SESSION['smtp_message_type'] = "danger";
            header("Location: edit.php?id=" . $id);
            exit;
        }
        
        // If setting as default, unset other defaults for this user
        if ($is_default) {
            if ($user_id) {
                $stmt = $pdo->prepare("UPDATE smtp_config SET is_default = 0 WHERE user_id = ? AND id != ?");
                $stmt->execute([$user_id, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE smtp_config SET is_default = 0 WHERE user_id IS NULL AND id != ?");
                $stmt->execute([$id]);
            }
        }
        
        // Update configuration
        if ($smtp_password) {
            // Update with new password
            $encrypted_password = base64_encode($smtp_password);
            $stmt = $pdo->prepare("UPDATE smtp_config SET 
                config_name = ?, user_id = ?, smtp_host = ?, smtp_port = ?, 
                smtp_encryption = ?, smtp_username = ?, smtp_password = ?, 
                from_email = ?, from_name = ?, reply_to_email = ?, 
                is_default = ?, is_active = ?, updated_at = NOW() 
                WHERE id = ?");
            
            $stmt->execute([
                $config_name,
                $user_id,
                $smtp_host,
                $smtp_port,
                $smtp_encryption,
                $smtp_username,
                $encrypted_password,
                $from_email,
                $from_name,
                $reply_to_email,
                $is_default,
                $is_active,
                $id
            ]);
        } else {
            // Update without changing password
            $stmt = $pdo->prepare("UPDATE smtp_config SET 
                config_name = ?, user_id = ?, smtp_host = ?, smtp_port = ?, 
                smtp_encryption = ?, smtp_username = ?, 
                from_email = ?, from_name = ?, reply_to_email = ?, 
                is_default = ?, is_active = ?, updated_at = NOW() 
                WHERE id = ?");
            
            $stmt->execute([
                $config_name,
                $user_id,
                $smtp_host,
                $smtp_port,
                $smtp_encryption,
                $smtp_username,
                $from_email,
                $from_name,
                $reply_to_email,
                $is_default,
                $is_active,
                $id
            ]);
        }
        
        $_SESSION['smtp_message'] = "SMTP configuration updated successfully.";
        $_SESSION['smtp_message_type'] = "success";
        
    } else {
        $_SESSION['smtp_message'] = "Invalid action.";
        $_SESSION['smtp_message_type'] = "danger";
    }
    
} catch (Exception $e) {
    $_SESSION['smtp_message'] = "Error: " . $e->getMessage();
    $_SESSION['smtp_message_type'] = "danger";
    error_log("SMTP Config Error: " . $e->getMessage());
}

// Redirect back to list
header("Location: list.php");
exit;