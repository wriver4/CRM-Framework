<?php

/**
 * Email Processing Cron Job
 * Processes emails from configured accounts and creates leads
 * Follows existing CRM framework patterns
 */

// Set error reporting for cron environment
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Set working directory to project root
chdir(dirname(__DIR__));

// Include autoloader and configuration
require_once 'vendor/autoload.php';
require_once 'config/system.php';

// Initialize logging
$logFile = 'logs/email_cron.log';

function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] {$message}\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    echo $logEntry; // Also output to console for debugging
}

try {
    logMessage("Starting email processing cron job");
    
    // Initialize email processor
    $emailProcessor = new EmailFormProcessor();
    
    // Process all emails
    $results = $emailProcessor->processAllEmails();
    
    // Log results
    $totalProcessed = array_sum(array_filter($results, 'is_numeric'));
    logMessage("Email processing completed. Total processed: {$totalProcessed}");
    
    foreach ($results as $account => $count) {
        if (is_numeric($count)) {
            logMessage("  - {$account}: {$count} emails processed");
        } else {
            logMessage("  - {$account}: {$count}");
        }
    }
    
    // Process CRM sync queue (if implemented)
    try {
        if (class_exists('CRMSyncManager')) {
            $syncManager = new CRMSyncManager();
            $syncResults = $syncManager->processPendingSyncs();
            logMessage("CRM sync completed. Results: " . json_encode($syncResults));
        }
    } catch (Exception $e) {
        logMessage("CRM sync error: " . $e->getMessage());
    }
    
    logMessage("Email processing cron job completed successfully");
    
} catch (Exception $e) {
    logMessage("Email processing cron job failed: " . $e->getMessage());
    logMessage("Stack trace: " . $e->getTraceAsString());
    exit(1);
}

exit(0);