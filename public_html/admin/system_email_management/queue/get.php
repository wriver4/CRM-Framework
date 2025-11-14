<?php
/**
 * Email Queue - Data Retrieval Logic
 * Handles fetching email queue data
 */

// Initialize database
$database = new Database();
$pdo = $database->dbcrm();

// Get counts for all statuses
$counts = [
    'total' => 0,
    'pending' => 0,
    'pending_approval' => 0,
    'sent' => 0,
    'failed' => 0
];

$stmt = $pdo->query("SELECT status, COUNT(*) as count FROM email_queue GROUP BY status");
while ($row = $stmt->fetch()) {
    $counts[$row['status']] = $row['count'];
    $counts['total'] += $row['count'];
}

// Get queue items for listing
if ($page === 'list') {
    $where = [];
    $params = [];
    
    // Filter by status
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $where[] = "q.status = ?";
        $params[] = $_GET['status'];
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $sql = "
        SELECT 
            q.*,
            t.template_name,
            t.template_key,
            u.full_name as queued_by_name
        FROM email_queue q
        LEFT JOIN email_templates t ON q.template_id = t.id
        LEFT JOIN users u ON q.created_by = u.id
        $whereClause
        ORDER BY 
            q.scheduled_send_at ASC,
            q.created_at ASC
        LIMIT 100
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $queue_items = $stmt->fetchAll();
}

// Get queue item for viewing
if ($page === 'view') {
    $id = (int)($_GET['id'] ?? 0);
    
    if ($id > 0) {
        $stmt = $pdo->prepare("
            SELECT 
                q.*,
                t.template_name,
                t.template_key,
                u.full_name as queued_by_name,
                u2.full_name as approved_by_name
            FROM email_queue q
            LEFT JOIN email_templates t ON q.template_id = t.id
            LEFT JOIN users u ON q.created_by = u.id
            LEFT JOIN users u2 ON q.approved_by = u2.id
            WHERE q.id = ?
        ");
        $stmt->execute([$id]);
        $queue_item = $stmt->fetch();
        
        if (!$queue_item) {
            $_SESSION['email_queue_message'] = "Queue item not found.";
            $_SESSION['email_queue_message_type'] = "danger";
            header("Location: list.php");
            exit;
        }
    } else {
        $_SESSION['email_queue_message'] = "Invalid queue item ID.";
        $_SESSION['email_queue_message_type'] = "danger";
        header("Location: list.php");
        exit;
    }
}

// Generate nonce for forms
if ($page === 'view') {
    $nonce = new Nonce();
    $nonce_token = $nonce->create('email_queue');
}