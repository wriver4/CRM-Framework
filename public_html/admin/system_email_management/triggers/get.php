<?php
/**
 * Email Triggers - Data Retrieval Logic
 * Handles fetching email trigger data
 */

// Initialize database
$database = new Database();
$pdo = $database->dbcrm();

// Get all triggers for listing
if ($page === 'list') {
    $where = [];
    $params = [];
    
    // Filter by module
    if (isset($_GET['module']) && !empty($_GET['module'])) {
        $where[] = "tr.module = ?";
        $params[] = $_GET['module'];
    }
    
    // Filter by status
    if (isset($_GET['status']) && $_GET['status'] == 'inactive') {
        $where[] = "tr.active = 0";
    } else {
        // Default: show only active
        if (!isset($_GET['showall'])) {
            $where[] = "tr.active = 1";
        }
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $sql = "
        SELECT 
            tr.*,
            t.template_name,
            t.template_key
        FROM email_trigger_rules tr
        INNER JOIN email_templates t ON tr.template_id = t.id
        $whereClause
        ORDER BY tr.module, tr.trigger_type, t.template_name
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $triggers = $stmt->fetchAll();
}

// Get trigger data for editing
if ($page === 'edit') {
    $id = (int)($_GET['id'] ?? 0);
    
    if ($id > 0) {
        $stmt = $pdo->prepare("
            SELECT tr.*, t.template_name 
            FROM email_trigger_rules tr
            INNER JOIN email_templates t ON tr.template_id = t.id
            WHERE tr.id = ?
        ");
        $stmt->execute([$id]);
        $trigger_data = $stmt->fetch();
        
        if (!$trigger_data) {
            $_SESSION['email_trigger_message'] = "Trigger not found.";
            $_SESSION['email_trigger_message_type'] = "danger";
            header("Location: list.php");
            exit;
        }
    } else {
        $_SESSION['email_trigger_message'] = "Invalid trigger ID.";
        $_SESSION['email_trigger_message_type'] = "danger";
        header("Location: list.php");
        exit;
    }
}

// Get all templates for dropdown (used in new.php and edit.php)
if ($page === 'new' || $page === 'edit') {
    $stmt = $pdo->prepare("
        SELECT id, template_key, template_name, module 
        FROM email_templates 
        WHERE active = 1
        ORDER BY module, template_name
    ");
    $stmt->execute();
    $templates = $stmt->fetchAll();
    
    // Pre-select template if provided
    $preselected_template_id = (int)($_GET['template_id'] ?? 0);
    
    // Generate nonce for forms
    $nonce = new Nonce();
    $nonce_token = $nonce->create('email_trigger');
}