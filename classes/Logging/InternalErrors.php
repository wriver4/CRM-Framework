<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

/**
 * InternalErrors class for handling internal application errors
 * 
 * This class manages internal error logging and reporting
 * separate from PHP errors and user-facing errors.
 */
class InternalErrors extends Database
{
    private $logFile;

    public function __construct()
    {
        parent::__construct();
        $this->logFile = dirname(dirname(__DIR__)) . '/logs/internal_errors.log';
    }

    /**
     * Log an internal error
     */
    public function logError($message, $context = [])
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' Context: ' . json_encode($context) : '';
        $logEntry = "[{$timestamp}] INTERNAL ERROR: {$message}{$contextStr}" . PHP_EOL;
        
        // Ensure logs directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Get recent internal errors
     */
    public function getRecentErrors($limit = 100)
    {
        if (!file_exists($this->logFile)) {
            return [];
        }

        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return array_slice(array_reverse($lines), 0, $limit);
    }

    /**
     * Clear error log
     */
    public function clearLog()
    {
        if (file_exists($this->logFile)) {
            return unlink($this->logFile);
        }
        return true;
    }
}