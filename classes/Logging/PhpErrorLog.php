<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

class PhpErrorLog extends Database
{
    private $logFile;

    public function __construct()
    {
        parent::__construct();
        $this->logFile = dirname(dirname(__DIR__)) . '/logs/php_errors.log';
    }

    /**
     * Get recent PHP errors from log
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
     * Clear PHP error log
     */
    public function clearLog()
    {
        if (file_exists($this->logFile)) {
            return unlink($this->logFile);
        }
        return true;
    }

    /**
     * Get log file size
     */
    public function getLogSize()
    {
        if (file_exists($this->logFile)) {
            return filesize($this->logFile);
        }
        return 0;
    }
}
