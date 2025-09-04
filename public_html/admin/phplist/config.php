<?php
/**
 * phpList Configuration Management
 * 
 * Admin interface for managing phpList integration settings
 */

require_once dirname(dirname($_SERVER['DOCUMENT_ROOT'])) . '/config/system.php';

// Check if user is logged in and has admin privileges
$not->loggedin();

// Load language file
$lang = include dirname(__DIR__) . '/languages/en.php';

// Initialize classes
$phpListSubscribers = new PhpListSubscribers();
$helpers = new Helpers();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        $pdo = $phpListSubscribers->dbcrm();
        
        if ($_POST['action'] === 'update_config') {
            // Update configuration values
            $configs = [
                'phplist_api_url' => trim($_POST['phplist_api_url'] ?? ''),
                'phplist_api_username' => trim($_POST['phplist_api_username'] ?? ''),
                'phplist_api_password' => trim($_POST['phplist_api_password'] ?? ''),
                'phplist_default_list_id' => (int)($_POST['phplist_default_list_id'] ?? 1),
                'sync_enabled' => isset($_POST['sync_enabled']) ? '1' : '0',
                'sync_frequency_minutes' => (int)($_POST['sync_frequency_minutes'] ?? 15),
                'max_sync_attempts' => (int)($_POST['max_sync_attempts'] ?? 3),
                'batch_size' => (int)($_POST['batch_size'] ?? 50),
                'api_timeout_seconds' => (int)($_POST['api_timeout_seconds'] ?? 30),
                'debug_mode' => isset($_POST['debug_mode']) ? '1' : '0',
                'auto_create_lists' => isset($_POST['auto_create_lists']) ? '1' : '0'
            ];
            
            foreach ($configs as $key => $value) {
                $stmt = $pdo->prepare("
                    INSERT INTO phplist_config (config_key, config_value) 
                    VALUES (:key, :value)
                    ON DUPLICATE KEY UPDATE config_value = :value, updated_at = CURRENT_TIMESTAMP
                ");
                $stmt->bindValue(':key', $key, PDO::PARAM_STR);
                $stmt->bindValue(':value', $value, PDO::PARAM_STR);
                $stmt->execute();
            }
            
            $_SESSION['success_message'] = 'phpList configuration updated successfully';
            
        } elseif ($_POST['action'] === 'test_connection') {
            // Test API connection
            $apiConfig = [
                'phplist_api_url' => trim($_POST['phplist_api_url'] ?? ''),
                'phplist_api_username' => trim($_POST['phplist_api_username'] ?? ''),
                'phplist_api_password' => trim($_POST['phplist_api_password'] ?? ''),
                'api_timeout_seconds' => (int)($_POST['api_timeout_seconds'] ?? 30),
                'debug_mode' => '1'
            ];
            
            $phpListApi = new PhpListApi($apiConfig);
            $result = $phpListApi->testConnection();
            
            if ($result['success']) {
                $_SESSION['success_message'] = 'phpList API connection successful!';
            } else {
                $_SESSION['error_message'] = 'phpList API connection failed: ' . ($result['error'] ?? 'Unknown error');
            }
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
    }
    
    header('Location: config.php');
    exit;
}

// Get current configuration
$currentConfig = [];
try {
    $stmt = $phpListSubscribers->dbcrm()->prepare("SELECT config_key, config_value FROM phplist_config");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $currentConfig[$row['config_key']] = $row['config_value'];
    }
} catch (Exception $e) {
    $currentConfig = [];
}

// Get subscriber statistics
$stats = $phpListSubscribers->getSubscriberStats();

// Page title
$page_title = 'phpList Configuration';

// Include header
include dirname(__DIR__) . '/templates/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-envelope"></i> phpList Configuration</h1>
                <div>
                    <a href="../dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['success_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['error_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <!-- Subscriber Statistics -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-chart-bar"></i> Subscriber Statistics</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h3 class="text-success"><?= $stats['synced'] ?? 0 ?></h3>
                                        <p class="mb-0">Synced</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h3 class="text-warning"><?= $stats['pending'] ?? 0 ?></h3>
                                        <p class="mb-0">Pending</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h3 class="text-danger"><?= $stats['failed'] ?? 0 ?></h3>
                                        <p class="mb-0">Failed</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h3 class="text-muted"><?= $stats['skipped'] ?? 0 ?></h3>
                                        <p class="mb-0">Skipped</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuration Form -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-cog"></i> phpList Integration Settings</h5>
                </div>
                <div class="card-body">
                    <form method="POST" id="configForm">
                        <input type="hidden" name="action" value="update_config">
                        
                        <!-- API Configuration -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">API Configuration</h6>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phplist_api_url" class="form-label">phpList API URL</label>
                                    <input type="url" class="form-control" id="phplist_api_url" name="phplist_api_url" 
                                           value="<?= htmlspecialchars($currentConfig['phplist_api_url'] ?? '') ?>"
                                           placeholder="https://your-phplist-domain.com/lists/admin/">
                                    <div class="form-text">Full URL to your phpList admin directory</div>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="phplist_api_username" class="form-label">API Username</label>
                                    <input type="text" class="form-control" id="phplist_api_username" name="phplist_api_username" 
                                           value="<?= htmlspecialchars($currentConfig['phplist_api_username'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="phplist_api_password" class="form-label">API Password</label>
                                    <input type="password" class="form-control" id="phplist_api_password" name="phplist_api_password" 
                                           value="<?= htmlspecialchars($currentConfig['phplist_api_password'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Sync Configuration -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">Sync Configuration</h6>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="sync_enabled" name="sync_enabled" 
                                               <?= ($currentConfig['sync_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="sync_enabled">
                                            Enable Sync
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="sync_frequency_minutes" class="form-label">Sync Frequency (minutes)</label>
                                    <input type="number" class="form-control" id="sync_frequency_minutes" name="sync_frequency_minutes" 
                                           value="<?= htmlspecialchars($currentConfig['sync_frequency_minutes'] ?? '15') ?>" min="1" max="1440">
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="max_sync_attempts" class="form-label">Max Sync Attempts</label>
                                    <input type="number" class="form-control" id="max_sync_attempts" name="max_sync_attempts" 
                                           value="<?= htmlspecialchars($currentConfig['max_sync_attempts'] ?? '3') ?>" min="1" max="10">
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="batch_size" class="form-label">Batch Size</label>
                                    <input type="number" class="form-control" id="batch_size" name="batch_size" 
                                           value="<?= htmlspecialchars($currentConfig['batch_size'] ?? '50') ?>" min="1" max="500">
                                </div>
                            </div>
                        </div>

                        <!-- Advanced Configuration -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">Advanced Settings</h6>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="phplist_default_list_id" class="form-label">Default List ID</label>
                                    <input type="number" class="form-control" id="phplist_default_list_id" name="phplist_default_list_id" 
                                           value="<?= htmlspecialchars($currentConfig['phplist_default_list_id'] ?? '1') ?>" min="1">
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="api_timeout_seconds" class="form-label">API Timeout (seconds)</label>
                                    <input type="number" class="form-control" id="api_timeout_seconds" name="api_timeout_seconds" 
                                           value="<?= htmlspecialchars($currentConfig['api_timeout_seconds'] ?? '30') ?>" min="5" max="300">
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="debug_mode" name="debug_mode" 
                                               <?= ($currentConfig['debug_mode'] ?? '0') === '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="debug_mode">
                                            Debug Mode
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="auto_create_lists" name="auto_create_lists" 
                                               <?= ($currentConfig['auto_create_lists'] ?? '0') === '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="auto_create_lists">
                                            Auto Create Lists
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Configuration
                                    </button>
                                    
                                    <button type="button" class="btn btn-info" onclick="testConnection()">
                                        <i class="fas fa-plug"></i> Test Connection
                                    </button>
                                    
                                    <a href="subscribers.php" class="btn btn-secondary">
                                        <i class="fas fa-users"></i> View Subscribers
                                    </a>
                                    
                                    <a href="sync_log.php" class="btn btn-secondary">
                                        <i class="fas fa-history"></i> Sync Log
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function testConnection() {
    // Create a temporary form for testing connection
    const form = document.createElement('form');
    form.method = 'POST';
    form.style.display = 'none';
    
    // Add action field
    const actionField = document.createElement('input');
    actionField.type = 'hidden';
    actionField.name = 'action';
    actionField.value = 'test_connection';
    form.appendChild(actionField);
    
    // Add current form values
    const formData = new FormData(document.getElementById('configForm'));
    for (let [key, value] of formData.entries()) {
        if (key !== 'action') {
            const field = document.createElement('input');
            field.type = 'hidden';
            field.name = key;
            field.value = value;
            form.appendChild(field);
        }
    }
    
    document.body.appendChild(form);
    form.submit();
}
</script>

<?php include dirname(__DIR__) . '/templates/footer.php'; ?>