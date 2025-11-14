<?php
/**
 * Email Logs - Data Retrieval Logic
 * Handles fetching email send log data
 */

// Initialize database
$database = new Database();
$pdo = $database->dbcrm();

// Get statistics
$stats = [
    'total' => 0,
    'success' => 0,
    'failed' => 0
];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM email_send_log");
$stats['total'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) as count FROM email_send_log WHERE success = 1");
$stats['success'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) as count FROM email_send_log WHERE success = 0");
$stats['failed'] = $stmt->fetchColumn();

// Get all templates for filter dropdown
$stmt = $pdo->query("SELECT id, template_name FROM email_templates ORDER BY template_name");
$templates = $stmt->fetchAll();

// Get logs for listing
if ($page === 'list') {
    $where = [];
    $params = [];
    
    // Filter by template
    if (isset($_GET['template_id']) && !empty($_GET['template_id'])) {
        $where[] = "l.template_id = ?";
        $params[] = (int)$_GET['template_id'];
    }
    
    // Filter by status
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        if ($_GET['status'] === 'success') {
            $where[] = "l.success = 1";
        } elseif ($_GET['status'] === 'failed') {
            $where[] = "l.success = 0";
        }
    }
    
    // Filter by date range
    if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
        $where[] = "DATE(l.sent_at) >= ?";
        $params[] = $_GET['date_from'];
    }
    
    if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
        $where[] = "DATE(l.sent_at) <= ?";
        $params[] = $_GET['date_to'];
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $sql = "
        SELECT 
            l.*,
            t.template_name,
            t.template_key
        FROM email_send_log l
        LEFT JOIN email_templates t ON l.template_id = t.id
        $whereClause
        ORDER BY l.sent_at DESC
        LIMIT 100
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $logs = $stmt->fetchAll();
}

// Get log details for viewing
if ($page === 'view') {
    $id = (int)($_GET['id'] ?? 0);
    
    if ($id > 0) {
        $stmt = $pdo->prepare("
            SELECT 
                l.*,
                t.template_name,
                t.template_key,
                q.template_data
            FROM email_send_log l
            LEFT JOIN email_templates t ON l.template_id = t.id
            LEFT JOIN email_queue q ON l.queue_id = q.id
            WHERE l.id = ?
        ");
        $stmt->execute([$id]);
        $log_item = $stmt->fetch();
        
        if (!$log_item) {
            $_SESSION['email_log_message'] = "Log entry not found.";
            $_SESSION['email_log_message_type'] = "danger";
            header("Location: list.php");
            exit;
        }
    } else {
        $_SESSION['email_log_message'] = "Invalid log ID.";
        $_SESSION['email_log_message_type'] = "danger";
        header("Location: list.php");
        exit;
    }
}