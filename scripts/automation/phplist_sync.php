<?php
/**
 * phpList Sync Cron Job
 * 
 * This script syncs pending subscribers with phpList
 * Run this script via cron job every 15 minutes or as configured
 * 
 * Usage: php /path/to/phplist_sync.php
 */

// Set script execution time limit
set_time_limit(300); // 5 minutes max

// Include system configuration
require_once dirname(__DIR__) . '/config/system.php';

// Initialize classes
$phpListSubscribers = new PhpListSubscribers();
$audit = new Audit();

// Check if sync is enabled
if (!$phpListSubscribers->isSyncEnabled()) {
    echo "phpList sync is disabled. Exiting.\n";
    exit(0);
}

// Get configuration
$batchSize = (int)$phpListSubscribers->getConfig('batch_size', 50);
$debugMode = $phpListSubscribers->getConfig('debug_mode', '0') === '1';

if ($debugMode) {
    echo "Starting phpList sync process...\n";
    echo "Batch size: $batchSize\n";
}

// Initialize API client
$apiConfig = [
    'phplist_api_url' => $phpListSubscribers->getConfig('phplist_api_url'),
    'phplist_api_username' => $phpListSubscribers->getConfig('phplist_api_username'),
    'phplist_api_password' => $phpListSubscribers->getConfig('phplist_api_password'),
    'api_timeout_seconds' => $phpListSubscribers->getConfig('api_timeout_seconds', 30),
    'debug_mode' => $phpListSubscribers->getConfig('debug_mode', '0')
];

$phpListApi = new PhpListApi($apiConfig);

// Test API connection first
$connectionTest = $phpListApi->testConnection();
if (!$connectionTest['success']) {
    $errorMsg = "phpList API connection failed: " . ($connectionTest['error'] ?? 'Unknown error');
    echo $errorMsg . "\n";
    error_log($errorMsg);
    
    // Log the connection failure
    $phpListSubscribers->logSyncOperation(
        null, 
        'bulk_sync', 
        'error', 
        null, 
        $errorMsg
    );
    
    exit(1);
}

if ($debugMode) {
    echo "API connection successful\n";
}

// Get pending subscribers
$pendingSubscribers = $phpListSubscribers->getPendingSubscribers($batchSize);

if (empty($pendingSubscribers)) {
    if ($debugMode) {
        echo "No pending subscribers found\n";
    }
    exit(0);
}

$totalSubscribers = count($pendingSubscribers);
$successCount = 0;
$errorCount = 0;
$skippedCount = 0;

if ($debugMode) {
    echo "Processing $totalSubscribers pending subscribers...\n";
}

// Process each subscriber
foreach ($pendingSubscribers as $subscriber) {
    $startTime = microtime(true);
    
    try {
        if ($debugMode) {
            echo "Processing subscriber ID {$subscriber['id']} ({$subscriber['email']})...\n";
        }
        
        // Check if subscriber already exists in phpList
        if (!empty($subscriber['phplist_subscriber_id'])) {
            // Update existing subscriber
            $result = $phpListApi->updateSubscriber($subscriber['phplist_subscriber_id'], $subscriber);
        } else {
            // Create new subscriber
            $result = $phpListApi->addSubscriber($subscriber);
        }
        
        $processingTime = round((microtime(true) - $startTime) * 1000);
        
        if ($result['success']) {
            // Update sync status to success
            $phpListSubscribers->updateSyncStatus(
                $subscriber['id'],
                'synced',
                $result['subscriber_id'] ?? $subscriber['phplist_subscriber_id'],
                null
            );
            
            // Log successful sync
            $phpListSubscribers->logSyncOperation(
                $subscriber['id'],
                empty($subscriber['phplist_subscriber_id']) ? 'create' : 'update',
                'success',
                json_encode($result['api_response'] ?? []),
                null,
                $processingTime
            );
            
            $successCount++;
            
            if ($debugMode) {
                echo "  ✓ Success (Processing time: {$processingTime}ms)\n";
            }
            
        } else {
            // Update sync status to failed
            $phpListSubscribers->updateSyncStatus(
                $subscriber['id'],
                'failed',
                null,
                $result['error'] ?? 'Unknown error'
            );
            
            // Log failed sync
            $phpListSubscribers->logSyncOperation(
                $subscriber['id'],
                empty($subscriber['phplist_subscriber_id']) ? 'create' : 'update',
                'error',
                json_encode($result['api_response'] ?? []),
                $result['error'] ?? 'Unknown error',
                $processingTime
            );
            
            $errorCount++;
            
            if ($debugMode) {
                echo "  ✗ Failed: " . ($result['error'] ?? 'Unknown error') . "\n";
            }
        }
        
    } catch (Exception $e) {
        $processingTime = round((microtime(true) - $startTime) * 1000);
        
        // Update sync status to failed
        $phpListSubscribers->updateSyncStatus(
            $subscriber['id'],
            'failed',
            null,
            'Exception: ' . $e->getMessage()
        );
        
        // Log exception
        $phpListSubscribers->logSyncOperation(
            $subscriber['id'],
            'create',
            'error',
            null,
            'Exception: ' . $e->getMessage(),
            $processingTime
        );
        
        $errorCount++;
        
        if ($debugMode) {
            echo "  ✗ Exception: " . $e->getMessage() . "\n";
        }
        
        error_log("phpList sync exception for subscriber {$subscriber['id']}: " . $e->getMessage());
    }
    
    // Small delay to avoid overwhelming the API
    usleep(100000); // 0.1 second delay
}

// Log overall sync results
$totalProcessingTime = round((microtime(true) - $startTime) * 1000);
$syncSummary = "Processed $totalSubscribers subscribers: $successCount successful, $errorCount failed, $skippedCount skipped";

$phpListSubscribers->logSyncOperation(
    null,
    'bulk_sync',
    $errorCount > 0 ? 'warning' : 'success',
    $syncSummary,
    null,
    $totalProcessingTime
);

// Audit log the sync operation
$audit->log(
    1, // System user ID
    'phplist_bulk_sync',
    'phplist_sync_job',
    'Cron Job',
    $_SERVER['SERVER_ADDR'] ?? 'localhost',
    0,
    $syncSummary
);

if ($debugMode) {
    echo "\nSync completed:\n";
    echo "  Total processed: $totalSubscribers\n";
    echo "  Successful: $successCount\n";
    echo "  Failed: $errorCount\n";
    echo "  Skipped: $skippedCount\n";
    echo "  Total processing time: {$totalProcessingTime}ms\n";
}

// Output summary for cron log
echo $syncSummary . "\n";

// Exit with appropriate code
exit($errorCount > 0 ? 1 : 0);