<?php
// Quick diagnostic test for system_email_management
echo "<h1>System Email Management - Diagnostic Test</h1>";

// Test 1: Check if config loads
echo "<h2>Test 1: Config Loading</h2>";
try {
    require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
    echo "✅ Config loaded successfully<br>";
} catch (Exception $e) {
    echo "❌ Config failed: " . $e->getMessage() . "<br>";
}

// Test 2: Check database connection
echo "<h2>Test 2: Database Connection</h2>";
try {
    $database = new Database();
    $pdo = $database->dbcrm();
    echo "✅ Database connected successfully<br>";
} catch (Exception $e) {
    echo "❌ Database failed: " . $e->getMessage() . "<br>";
}

// Test 3: Check tables exist
echo "<h2>Test 3: Email Template Tables</h2>";
try {
    $tables = ['email_templates', 'email_template_content', 'email_template_variables', 
               'email_global_templates', 'email_trigger_rules', 'email_queue', 'email_send_log'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Table '$table' exists<br>";
        } else {
            echo "❌ Table '$table' NOT FOUND<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Table check failed: " . $e->getMessage() . "<br>";
}

// Test 4: Check permissions
echo "<h2>Test 4: User Permissions</h2>";
try {
    $security = new Security();
    if (isset($_SESSION['user_id'])) {
        echo "✅ User logged in (ID: " . $_SESSION['user_id'] . ")<br>";
        
        // Try to check permissions
        try {
            $security->check_user_permissions('admin', 'read');
            echo "✅ User has admin read permissions<br>";
        } catch (Exception $e) {
            echo "❌ Permission check failed: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "❌ User not logged in<br>";
    }
} catch (Exception $e) {
    echo "❌ Security check failed: " . $e->getMessage() . "<br>";
}

// Test 5: Check file paths
echo "<h2>Test 5: File Paths</h2>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Config Path: " . dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php<br>';
echo "Current File: " . __FILE__ . "<br>";

// Test 6: Links to pages
echo "<h2>Test 6: Navigation Links</h2>";
echo '<a href="index.php">Dashboard</a><br>';
echo '<a href="templates/list.php">Template List</a><br>';
echo '<a href="templates/new.php">New Template</a><br>';
echo '<a href="queue/list.php">Queue List</a><br>';
echo '<a href="logs/list.php">Logs List</a><br>';
echo '<a href="triggers/list.php">Triggers List</a><br>';

echo "<hr>";
echo "<p><strong>If all tests pass, the system is working correctly!</strong></p>";
?>