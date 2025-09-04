<?php

/**
 * SQL Error Logger
 * 
 * Comprehensive SQL error logging system for all database operations
 * Logs SQL errors, parameter mismatches, and execution details
 */
class SqlErrorLogger extends Database
{
    private $logFile;
    private $detailedLogFile;

    public function __construct()
    {
        parent::__construct();
        $this->logFile = dirname(dirname(__DIR__)) . '/logs/sql_errors.log';
        $this->detailedLogFile = dirname(dirname(__DIR__)) . '/logs/sql_detailed.log';
    }

    /**
     * Log SQL error with full context
     */
    public function logSqlError($error, $context = [])
    {
        $timestamp = date('Y-m-d H:i:s');
        $userId = $_SESSION['user_id'] ?? 'anonymous';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $requestUri = $_SERVER['REQUEST_URI'] ?? 'unknown';
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        // Basic error log entry
        $logEntry = "[{$timestamp}] SQL ERROR: {$error}" . PHP_EOL;
        $logEntry .= "  User ID: {$userId}" . PHP_EOL;
        $logEntry .= "  Request: {$requestUri}" . PHP_EOL;
        $logEntry .= "  IP: {$remoteAddr}" . PHP_EOL;
        
        if (!empty($context)) {
            $logEntry .= "  Context: " . json_encode($context, JSON_PRETTY_PRINT) . PHP_EOL;
        }
        
        $logEntry .= "  Stack Trace:" . PHP_EOL;
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        foreach ($trace as $i => $frame) {
            $file = $frame['file'] ?? 'unknown';
            $line = $frame['line'] ?? 'unknown';
            $function = $frame['function'] ?? 'unknown';
            $class = isset($frame['class']) ? $frame['class'] . '::' : '';
            $logEntry .= "    #{$i} {$file}:{$line} {$class}{$function}()" . PHP_EOL;
        }
        
        $logEntry .= str_repeat('-', 80) . PHP_EOL;
        
        $this->writeToLog($this->logFile, $logEntry);
    }

    /**
     * Log SQL query execution details (for debugging)
     */
    public function logSqlExecution($sql, $parameters = [], $executionTime = null, $success = true)
    {
        if (!$this->shouldLogExecution()) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $userId = $_SESSION['user_id'] ?? 'anonymous';
        $requestUri = $_SERVER['REQUEST_URI'] ?? 'unknown';
        
        $logEntry = "[{$timestamp}] SQL EXECUTION" . PHP_EOL;
        $logEntry .= "  Status: " . ($success ? 'SUCCESS' : 'FAILED') . PHP_EOL;
        $logEntry .= "  User ID: {$userId}" . PHP_EOL;
        $logEntry .= "  Request: {$requestUri}" . PHP_EOL;
        
        if ($executionTime !== null) {
            $logEntry .= "  Execution Time: {$executionTime}ms" . PHP_EOL;
        }
        
        $logEntry .= "  SQL Query:" . PHP_EOL;
        $logEntry .= "    " . $this->formatSql($sql) . PHP_EOL;
        
        if (!empty($parameters)) {
            $logEntry .= "  Parameters:" . PHP_EOL;
            foreach ($parameters as $key => $value) {
                $displayValue = $this->sanitizeParameterValue($value);
                $logEntry .= "    {$key} => {$displayValue}" . PHP_EOL;
            }
        }
        
        $logEntry .= str_repeat('-', 80) . PHP_EOL;
        
        $this->writeToLog($this->detailedLogFile, $logEntry);
    }

    /**
     * Log parameter binding issues
     */
    public function logParameterMismatch($sql, $providedParams, $expectedParams = [])
    {
        $timestamp = date('Y-m-d H:i:s');
        $userId = $_SESSION['user_id'] ?? 'anonymous';
        $requestUri = $_SERVER['REQUEST_URI'] ?? 'unknown';
        
        // Extract parameters from SQL
        if (empty($expectedParams)) {
            preg_match_all('/:(\w+)/', $sql, $matches);
            $expectedParams = $matches[1] ?? [];
        }
        
        $providedKeys = array_keys($providedParams);
        $missing = array_diff($expectedParams, $providedKeys);
        $extra = array_diff($providedKeys, $expectedParams);
        
        $logEntry = "[{$timestamp}] PARAMETER MISMATCH" . PHP_EOL;
        $logEntry .= "  User ID: {$userId}" . PHP_EOL;
        $logEntry .= "  Request: {$requestUri}" . PHP_EOL;
        $logEntry .= "  SQL Query:" . PHP_EOL;
        $logEntry .= "    " . $this->formatSql($sql) . PHP_EOL;
        
        $logEntry .= "  Expected Parameters: [" . implode(', ', $expectedParams) . "]" . PHP_EOL;
        $logEntry .= "  Provided Parameters: [" . implode(', ', $providedKeys) . "]" . PHP_EOL;
        
        if (!empty($missing)) {
            $logEntry .= "  Missing Parameters: [" . implode(', ', $missing) . "]" . PHP_EOL;
        }
        
        if (!empty($extra)) {
            $logEntry .= "  Extra Parameters: [" . implode(', ', $extra) . "]" . PHP_EOL;
        }
        
        $logEntry .= "  Parameter Values:" . PHP_EOL;
        foreach ($providedParams as $key => $value) {
            $displayValue = $this->sanitizeParameterValue($value);
            $status = in_array($key, $missing) ? ' (MISSING)' : (in_array($key, $extra) ? ' (EXTRA)' : '');
            $logEntry .= "    {$key} => {$displayValue}{$status}" . PHP_EOL;
        }
        
        $logEntry .= str_repeat('-', 80) . PHP_EOL;
        
        $this->writeToLog($this->logFile, $logEntry);
    }

    /**
     * Log form submission errors
     */
    public function logFormError($formName, $error, $formData = [])
    {
        $timestamp = date('Y-m-d H:i:s');
        $userId = $_SESSION['user_id'] ?? 'anonymous';
        $requestUri = $_SERVER['REQUEST_URI'] ?? 'unknown';
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        $logEntry = "[{$timestamp}] FORM ERROR: {$formName}" . PHP_EOL;
        $logEntry .= "  Error: {$error}" . PHP_EOL;
        $logEntry .= "  User ID: {$userId}" . PHP_EOL;
        $logEntry .= "  Request: {$requestUri}" . PHP_EOL;
        $logEntry .= "  IP: {$remoteAddr}" . PHP_EOL;
        
        if (!empty($formData)) {
            $logEntry .= "  Form Data:" . PHP_EOL;
            foreach ($formData as $key => $value) {
                $displayValue = $this->sanitizeParameterValue($value);
                $logEntry .= "    {$key} => {$displayValue}" . PHP_EOL;
            }
        }
        
        $logEntry .= str_repeat('-', 80) . PHP_EOL;
        
        $this->writeToLog($this->logFile, $logEntry);
    }

    /**
     * Get recent SQL errors
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
     * Get SQL execution logs
     */
    public function getExecutionLogs($limit = 100)
    {
        if (!file_exists($this->detailedLogFile)) {
            return [];
        }

        $lines = file($this->detailedLogFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return array_slice(array_reverse($lines), 0, $limit);
    }

    /**
     * Clear SQL error logs
     */
    public function clearErrorLog()
    {
        if (file_exists($this->logFile)) {
            return unlink($this->logFile);
        }
        return true;
    }

    /**
     * Clear SQL execution logs
     */
    public function clearExecutionLog()
    {
        if (file_exists($this->detailedLogFile)) {
            return unlink($this->detailedLogFile);
        }
        return true;
    }

    /**
     * Get log file sizes
     */
    public function getLogSizes()
    {
        return [
            'error_log_size' => file_exists($this->logFile) ? filesize($this->logFile) : 0,
            'execution_log_size' => file_exists($this->detailedLogFile) ? filesize($this->detailedLogFile) : 0
        ];
    }

    /**
     * Write to log file with proper error handling
     */
    private function writeToLog($logFile, $content)
    {
        try {
            // Ensure logs directory exists
            $logDir = dirname($logFile);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            
            // Write to log file
            file_put_contents($logFile, $content, FILE_APPEND | LOCK_EX);
            
            // Rotate log if it gets too large (> 10MB)
            if (file_exists($logFile) && filesize($logFile) > 10 * 1024 * 1024) {
                $this->rotateLog($logFile);
            }
        } catch (Exception $e) {
            // Fallback to PHP error log if our logging fails
            error_log("SqlErrorLogger failed to write to {$logFile}: " . $e->getMessage());
        }
    }

    /**
     * Rotate log file when it gets too large
     */
    private function rotateLog($logFile)
    {
        $rotatedFile = $logFile . '.' . date('Y-m-d-H-i-s');
        rename($logFile, $rotatedFile);
        
        // Keep only last 5 rotated files
        $pattern = $logFile . '.*';
        $files = glob($pattern);
        if (count($files) > 5) {
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            $filesToDelete = array_slice($files, 0, count($files) - 5);
            foreach ($filesToDelete as $file) {
                unlink($file);
            }
        }
    }

    /**
     * Format SQL for better readability in logs
     */
    private function formatSql($sql)
    {
        // Basic SQL formatting - replace multiple spaces/newlines with single space
        $formatted = preg_replace('/\s+/', ' ', trim($sql));
        
        // Add line breaks for better readability in logs
        $keywords = ['SELECT', 'FROM', 'WHERE', 'JOIN', 'LEFT JOIN', 'RIGHT JOIN', 'INNER JOIN', 
                    'GROUP BY', 'ORDER BY', 'HAVING', 'LIMIT', 'INSERT INTO', 'UPDATE', 'SET', 'DELETE FROM'];
        
        foreach ($keywords as $keyword) {
            $formatted = str_ireplace($keyword, "\n    " . $keyword, $formatted);
        }
        
        return trim($formatted);
    }

    /**
     * Sanitize parameter values for logging (hide sensitive data)
     */
    private function sanitizeParameterValue($value)
    {
        if ($value === null) {
            return 'NULL';
        }
        
        if (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        }
        
        if (is_array($value)) {
            return '[Array with ' . count($value) . ' elements]';
        }
        
        if (is_object($value)) {
            return '[Object: ' . get_class($value) . ']';
        }
        
        $stringValue = (string)$value;
        
        // Hide sensitive data
        $sensitiveFields = ['password', 'passwd', 'pwd', 'secret', 'token', 'key', 'auth'];
        foreach ($sensitiveFields as $field) {
            if (stripos($field, $field) !== false) {
                return '[HIDDEN]';
            }
        }
        
        // Truncate very long values
        if (strlen($stringValue) > 200) {
            return substr($stringValue, 0, 200) . '... [TRUNCATED]';
        }
        
        return "'" . $stringValue . "'";
    }

    /**
     * Check if execution logging should be enabled (based on config or debug mode)
     */
    private function shouldLogExecution()
    {
        // Enable execution logging in debug mode or if specifically configured
        return defined('DEBUG_SQL') && DEBUG_SQL === true;
    }
}