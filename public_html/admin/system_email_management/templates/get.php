<?php
/**
 * Email Templates - Data Retrieval Logic
 * Handles fetching email template data
 */

// Initialize database
$database = new Database();
$pdo = $database->dbcrm();

// Get all templates for listing
if ($page === 'list') {
    $where = [];
    $params = [];
    
    // Filter by module
    if (isset($_GET['module']) && !empty($_GET['module'])) {
        $where[] = "t.module = ?";
        $params[] = $_GET['module'];
    }
    
    // Filter by status
    if (isset($_GET['status']) && $_GET['status'] == 'inactive') {
        $where[] = "t.active = 0";
    } else {
        // Default: show only active
        if (!isset($_GET['showall'])) {
            $where[] = "t.active = 1";
        }
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $sql = "
        SELECT 
            t.*,
            (SELECT COUNT(*) FROM email_template_content WHERE template_id = t.id) as language_count,
            (SELECT COUNT(*) FROM email_template_variables WHERE template_id = t.id) as variable_count,
            (SELECT COUNT(*) FROM email_trigger_rules WHERE template_id = t.id) as trigger_count,
            u.full_name as created_by_name
        FROM email_templates t
        LEFT JOIN users u ON t.created_by = u.id
        $whereClause
        ORDER BY t.module, t.template_name
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $templates = $stmt->fetchAll();
}

// Get template data for viewing/editing
if ($page === 'view' || $page === 'edit' || $page === 'content') {
    $id = (int)($_GET['id'] ?? 0);
    
    if ($id > 0) {
        $stmt = $pdo->prepare("
            SELECT t.*, u.full_name as created_by_name 
            FROM email_templates t
            LEFT JOIN users u ON t.created_by = u.id
            WHERE t.id = ?
        ");
        $stmt->execute([$id]);
        $template_data = $stmt->fetch();
        
        if (!$template_data) {
            $_SESSION['email_template_message'] = "Template not found.";
            $_SESSION['email_template_message_type'] = "danger";
            header("Location: list.php");
            exit;
        }
        
        // Get content for all languages
        $stmt = $pdo->prepare("
            SELECT * FROM email_template_content 
            WHERE template_id = ? 
            ORDER BY language_code
        ");
        $stmt->execute([$id]);
        $template_contents = $stmt->fetchAll();
        
        // Get variables
        $stmt = $pdo->prepare("
            SELECT * FROM email_template_variables 
            WHERE template_id = ? 
            ORDER BY sort_order, variable_key
        ");
        $stmt->execute([$id]);
        $template_variables = $stmt->fetchAll();
        
        // Get triggers
        $stmt = $pdo->prepare("
            SELECT * FROM email_trigger_rules 
            WHERE template_id = ? 
            ORDER BY trigger_type
        ");
        $stmt->execute([$id]);
        $template_triggers = $stmt->fetchAll();
        
    } else {
        $_SESSION['email_template_message'] = "Invalid template ID.";
        $_SESSION['email_template_message_type'] = "danger";
        header("Location: list.php");
        exit;
    }
}

// Generate nonce for forms
if ($page === 'new' || $page === 'edit' || $page === 'content') {
    $nonce = new Nonce();
    $nonce_token = $nonce->create('email_template');
}