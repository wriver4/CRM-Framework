<?php
/**
 * SMTP Configuration - Data Retrieval Logic
 * Handles fetching SMTP configuration data
 */

// Initialize database
$database = new Database();
$pdo = $database->dbcrm();

// Get all configurations for listing
if ($page === 'list') {
    $stmt = $pdo->prepare("
        SELECT sc.*, u.full_name as user_name 
        FROM smtp_config sc 
        LEFT JOIN users u ON sc.user_id = u.id 
        ORDER BY sc.user_id IS NULL DESC, u.full_name, sc.config_name
    ");
    $stmt->execute();
    $configs = $stmt->fetchAll();
}

// Get configuration data for editing
if ($page === 'edit') {
    $id = (int)($_GET['id'] ?? 0);
    
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM smtp_config WHERE id = ?");
        $stmt->execute([$id]);
        $config_data = $stmt->fetch();
        
        if (!$config_data) {
            $_SESSION['smtp_message'] = "Configuration not found.";
            $_SESSION['smtp_message_type'] = "danger";
            header("Location: list.php");
            exit;
        }
    } else {
        $_SESSION['smtp_message'] = "Invalid configuration ID.";
        $_SESSION['smtp_message_type'] = "danger";
        header("Location: list.php");
        exit;
    }
}

// Get all users for dropdown (used in new.php and edit.php)
if ($page === 'new' || $page === 'edit') {
    $stmt = $pdo->prepare("SELECT id, full_name FROM users ORDER BY full_name");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    // Generate nonce for forms
    $nonce = new Nonce();
    $nonce_token = $nonce->create('smtp_config');
}