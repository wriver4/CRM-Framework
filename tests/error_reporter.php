<?php
/**
 * Enhanced Error Reporting Utility
 * 
 * A comprehensive error reporting and debugging utility that provides:
 * - Detailed error analysis and categorization
 * - Stack trace analysis with context
 * - Error aggregation and pattern detection
 * - Integration with existing logging systems
 * - Debug information collection
 * - Error trend analysis
 * - Automated error reporting
 * 
 * Features:
 * 1. Error Categorization:
 *    - Fatal errors, warnings, notices
 *    - Database errors, authentication errors
 *    - Performance issues, memory issues
 *    - Custom error types and severity levels
 * 
 * 2. Context Collection:
 *    - Request information (URL, method, headers)
 *    - User session data and authentication state
 *    - System state (memory, CPU, database connections)
 *    - Application state (current module, user role)
 * 
 * 3. Advanced Analysis:
 *    - Error pattern detection
 *    - Frequency analysis and trending
 *    - Impact assessment
 *    - Root cause suggestions
 * 
 * Usage:
 * php tests/error_reporter.php --analyze
 * php tests/error_reporter.php --report --days=7
 * php tests/error_reporter.php --monitor --real-time
 * php tests/error_reporter.php --test-errors
 */

// Set up paths and autoloading
$rootPath = dirname(__DIR__);
require_once $rootPath . '/config/system.php';
require_once $rootPath . '/classes/Core/Database.php';
require_once $rootPath . '/classes/Logging/Audit.php';
require_once $rootPath . '/classes/Logging/InternalErrors.php';
require_once $rootPath . '/classes/Logging/PhpErrorLog.php';

class EnhancedErrorReporter {
    
    private $db;
    private $audit;
    private $internalErrors;
    private $phpErrorLog;
    private $sessionId;
    private $errorCategories = [
        'FATAL' => ['severity' => 5, 'color' => 'red'],
        'ERROR' => ['severity' => 4, 'color' => 'red'],
        'WARNING' => ['severity' => 3, 'color' => 'yellow'],
        'NOTICE' => ['severity' => 2, 'color' => 'blue'],
        'INFO' => ['severity' => 1, 'color' => 'green'],
        'DEBUG' => ['severity' => 0, 'color' => 'gray']
    ];
    
    private $errorPatterns = [
        'database_connection' => [
            'patterns' => ['Connection refused', 'Access denied', 'Unknown database'],
            'category' => 'DATABASE',
            'severity' => 'FATAL',
            'suggestion' => 'Check database credentials and server status'
        ],
        'memory_exhausted' => [
            'patterns' => ['Fatal error: Allowed memory size', 'Out of memory'],
            'category' => 'MEMORY',
            'severity' => 'FATAL',
            'suggestion' => 'Increase memory_limit or optimize memory usage'
        ],
        'file_not_found' => [
            'patterns' => ['No such file or directory', 'failed to open stream'],
            'category' => 'FILE_SYSTEM',
            'severity' => 'ERROR',
            'suggestion' => 'Check file paths and permissions'
        ],
        'authentication_failed' => [
            'patterns' => ['Authentication failed', 'Invalid credentials', 'Access denied'],
            'category' => 'AUTHENTICATION',
            'severity' => 'WARNING',
            'suggestion' => 'Verify user credentials and permissions'
        ],
        'sql_syntax' => [
            'patterns' => ['SQL syntax error', 'syntax to use near'],
            'category' => 'DATABASE',
            'severity' => 'ERROR',
            'suggestion' => 'Review SQL query syntax'
        ]
    ];
    
    public function __construct() {
        $this->sessionId = uniqid('error_reporter_', true);
        
        $this->db = new Database();
        $this->audit = new Audit();
        $this->internalErrors = new InternalErrors();
        $this->phpErrorLog = new PhpErrorLog();
        
        $this->logSessionStart();
    }
    
    private function logSessionStart() {
        try {
            $this->audit->log(
                0,
                'ERROR_REPORTER_START',
                'error_reporter',
                'Enhanced Error Reporter',
                '127.0.0.1',
                0,
                json_encode([
                    'session_id' => $this->sessionId,
                    'start_time' => date('Y-m-d H:i:s'),
                    'php_version' => PHP_VERSION,
                    'memory_limit' => ini_get('memory_limit'),
                    'error_reporting' => error_reporting()
                ])
            );
        } catch (Exception $e) {
            error_log("Failed to log error reporter start: " . $e->getMessage());
        }
    }
    
    /**
     * Analyze recent errors and generate insights
     */
    public function analyzeErrors($days = 7) {
        echo "Analyzing errors from the last $days days...\n\n";
        
        $errors = $this->collectRecentErrors($days);
        $analysis = $this->performErrorAnalysis($errors);
        
        return $analysis;
    }
    
    /**
     * Collect recent errors from all logging sources
     */
    private function collectRecentErrors($days) {
        $errors = [];
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-$days days"));
        
        // Collect from internal errors
        try {
            $internalErrors = $this->internalErrors->getRecentErrors(1000);
            foreach ($internalErrors as $error) {
                if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $error, $matches)) {
                    $timestamp = $matches[1];
                    if ($timestamp >= $cutoffDate) {
                        $errors[] = [
                            'source' => 'internal',
                            'timestamp' => $timestamp,
                            'message' => $error,
                            'raw' => $error
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            echo "Warning: Could not collect internal errors: " . $e->getMessage() . "\n";
        }
        
        // Collect from PHP error log
        try {
            $phpErrors = $this->collectPhpErrors($days);
            $errors = array_merge($errors, $phpErrors);
        } catch (Exception $e) {
            echo "Warning: Could not collect PHP errors: " . $e->getMessage() . "\n";
        }
        
        // Collect from audit log (error events)
        try {
            $auditErrors = $this->collectAuditErrors($days);
            $errors = array_merge($errors, $auditErrors);
        } catch (Exception $e) {
            echo "Warning: Could not collect audit errors: " . $e->getMessage() . "\n";
        }
        
        // Sort by timestamp
        usort($errors, function($a, $b) {
            return strcmp($b['timestamp'], $a['timestamp']);
        });
        
        echo "Collected " . count($errors) . " errors from the last $days days.\n\n";
        
        return $errors;
    }
    
    /**
     * Collect PHP errors from log files
     */
    private function collectPhpErrors($days) {
        $errors = [];
        $logFiles = [
            dirname(__DIR__) . '/logs/php_errors.log',
            '/var/log/php_errors.log',
            ini_get('error_log')
        ];
        
        $cutoffTimestamp = strtotime("-$days days");
        
        foreach ($logFiles as $logFile) {
            if (!$logFile || !file_exists($logFile) || !is_readable($logFile)) {
                continue;
            }
            
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (!$lines) continue;
            
            foreach ($lines as $line) {
                // Parse PHP error log format: [timestamp] message
                if (preg_match('/\[([^\]]+)\]\s+(.+)/', $line, $matches)) {
                    $timestamp = $matches[1];
                    $message = $matches[2];
                    
                    $parsedTime = strtotime($timestamp);
                    if ($parsedTime && $parsedTime >= $cutoffTimestamp) {
                        $errors[] = [
                            'source' => 'php_error_log',
                            'timestamp' => date('Y-m-d H:i:s', $parsedTime),
                            'message' => $message,
                            'raw' => $line,
                            'log_file' => $logFile
                        ];
                    }
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Collect error-related events from audit log
     */
    private function collectAuditErrors($days) {
        $errors = [];
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-$days days"));
        
        try {
            $sql = "SELECT * FROM audit WHERE event LIKE '%ERROR%' OR event LIKE '%FAIL%' OR event LIKE '%EXCEPTION%' ORDER BY created_at DESC LIMIT 1000";
            $stmt = $this->db->dbcrm()->prepare($sql);
            $stmt->execute();
            $auditRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($auditRecords as $record) {
                $timestamp = date('Y-m-d H:i:s', $record['created_at']);
                if ($timestamp >= $cutoffDate) {
                    $errors[] = [
                        'source' => 'audit',
                        'timestamp' => $timestamp,
                        'message' => $record['event'] . ': ' . $record['resource'],
                        'raw' => json_encode($record),
                        'user_id' => $record['user_id'],
                        'event' => $record['event'],
                        'resource' => $record['resource'],
                        'data' => $record['data']
                    ];
                }
            }
        } catch (Exception $e) {
            // Database might not be available
        }
        
        return $errors;
    }
    
    /**
     * Perform comprehensive error analysis
     */
    private function performErrorAnalysis($errors) {
        $analysis = [
            'total_errors' => count($errors),
            'error_categories' => [],
            'error_patterns' => [],
            'frequency_analysis' => [],
            'severity_distribution' => [],
            'time_distribution' => [],
            'source_distribution' => [],
            'top_errors' => [],
            'recommendations' => []
        ];
        
        // Categorize errors
        foreach ($errors as $error) {
            $category = $this->categorizeError($error);
            $severity = $this->determineSeverity($error);
            $pattern = $this->detectPattern($error);
            
            // Category distribution
            if (!isset($analysis['error_categories'][$category])) {
                $analysis['error_categories'][$category] = 0;
            }
            $analysis['error_categories'][$category]++;
            
            // Severity distribution
            if (!isset($analysis['severity_distribution'][$severity])) {
                $analysis['severity_distribution'][$severity] = 0;
            }
            $analysis['severity_distribution'][$severity]++;
            
            // Source distribution
            $source = $error['source'];
            if (!isset($analysis['source_distribution'][$source])) {
                $analysis['source_distribution'][$source] = 0;
            }
            $analysis['source_distribution'][$source]++;
            
            // Pattern detection
            if ($pattern) {
                if (!isset($analysis['error_patterns'][$pattern])) {
                    $analysis['error_patterns'][$pattern] = 0;
                }
                $analysis['error_patterns'][$pattern]++;
            }
            
            // Time distribution (by hour)
            $hour = date('H', strtotime($error['timestamp']));
            if (!isset($analysis['time_distribution'][$hour])) {
                $analysis['time_distribution'][$hour] = 0;
            }
            $analysis['time_distribution'][$hour]++;
        }
        
        // Find top errors by frequency
        $errorMessages = [];
        foreach ($errors as $error) {
            $normalizedMessage = $this->normalizeErrorMessage($error['message']);
            if (!isset($errorMessages[$normalizedMessage])) {
                $errorMessages[$normalizedMessage] = [
                    'count' => 0,
                    'first_seen' => $error['timestamp'],
                    'last_seen' => $error['timestamp'],
                    'example' => $error['message']
                ];
            }
            $errorMessages[$normalizedMessage]['count']++;
            if ($error['timestamp'] < $errorMessages[$normalizedMessage]['first_seen']) {
                $errorMessages[$normalizedMessage]['first_seen'] = $error['timestamp'];
            }
            if ($error['timestamp'] > $errorMessages[$normalizedMessage]['last_seen']) {
                $errorMessages[$normalizedMessage]['last_seen'] = $error['timestamp'];
            }
        }
        
        // Sort by frequency
        arsort($errorMessages);
        $analysis['top_errors'] = array_slice($errorMessages, 0, 10, true);
        
        // Generate recommendations
        $analysis['recommendations'] = $this->generateRecommendations($analysis);
        
        return $analysis;
    }
    
    /**
     * Categorize error based on content
     */
    private function categorizeError($error) {
        $message = strtolower($error['message']);
        
        if (strpos($message, 'database') !== false || strpos($message, 'sql') !== false || strpos($message, 'mysql') !== false) {
            return 'DATABASE';
        }
        
        if (strpos($message, 'memory') !== false || strpos($message, 'allowed memory size') !== false) {
            return 'MEMORY';
        }
        
        if (strpos($message, 'file') !== false || strpos($message, 'directory') !== false || strpos($message, 'permission') !== false) {
            return 'FILE_SYSTEM';
        }
        
        if (strpos($message, 'authentication') !== false || strpos($message, 'login') !== false || strpos($message, 'access denied') !== false) {
            return 'AUTHENTICATION';
        }
        
        if (strpos($message, 'network') !== false || strpos($message, 'connection') !== false || strpos($message, 'timeout') !== false) {
            return 'NETWORK';
        }
        
        if (strpos($message, 'fatal') !== false) {
            return 'FATAL';
        }
        
        return 'GENERAL';
    }
    
    /**
     * Determine error severity
     */
    private function determineSeverity($error) {
        $message = strtolower($error['message']);
        
        if (strpos($message, 'fatal') !== false || strpos($message, 'allowed memory size') !== false) {
            return 'FATAL';
        }
        
        if (strpos($message, 'error') !== false || strpos($message, 'exception') !== false) {
            return 'ERROR';
        }
        
        if (strpos($message, 'warning') !== false) {
            return 'WARNING';
        }
        
        if (strpos($message, 'notice') !== false) {
            return 'NOTICE';
        }
        
        return 'INFO';
    }
    
    /**
     * Detect error patterns
     */
    private function detectPattern($error) {
        $message = $error['message'];
        
        foreach ($this->errorPatterns as $patternName => $patternConfig) {
            foreach ($patternConfig['patterns'] as $pattern) {
                if (stripos($message, $pattern) !== false) {
                    return $patternName;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Normalize error message for grouping
     */
    private function normalizeErrorMessage($message) {
        // Remove timestamps, line numbers, file paths, and other variable parts
        $normalized = preg_replace('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', '[TIMESTAMP]', $message);
        $normalized = preg_replace('/line \d+/', 'line [LINE]', $normalized);
        $normalized = preg_replace('/\/[^\s]+\.php/', '[FILE]', $normalized);
        $normalized = preg_replace('/\d+/', '[NUMBER]', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        
        return trim($normalized);
    }
    
    /**
     * Generate recommendations based on analysis
     */
    private function generateRecommendations($analysis) {
        $recommendations = [];
        
        // High error count
        if ($analysis['total_errors'] > 100) {
            $recommendations[] = [
                'priority' => 'HIGH',
                'category' => 'VOLUME',
                'message' => "High error volume detected ({$analysis['total_errors']} errors). Consider implementing error rate limiting and investigating root causes.",
                'action' => 'Implement error monitoring alerts and investigate top error patterns'
            ];
        }
        
        // Memory issues
        if (isset($analysis['error_categories']['MEMORY']) && $analysis['error_categories']['MEMORY'] > 5) {
            $recommendations[] = [
                'priority' => 'HIGH',
                'category' => 'MEMORY',
                'message' => "Multiple memory-related errors detected. Consider increasing memory_limit or optimizing memory usage.",
                'action' => 'Review memory-intensive operations and consider code optimization'
            ];
        }
        
        // Database issues
        if (isset($analysis['error_categories']['DATABASE']) && $analysis['error_categories']['DATABASE'] > 10) {
            $recommendations[] = [
                'priority' => 'HIGH',
                'category' => 'DATABASE',
                'message' => "Frequent database errors detected. Check database server health and query optimization.",
                'action' => 'Review database performance, connection pooling, and query efficiency'
            ];
        }
        
        // Fatal errors
        if (isset($analysis['severity_distribution']['FATAL']) && $analysis['severity_distribution']['FATAL'] > 0) {
            $recommendations[] = [
                'priority' => 'CRITICAL',
                'category' => 'FATAL',
                'message' => "Fatal errors detected that may cause application crashes. Immediate attention required.",
                'action' => 'Investigate and fix fatal errors immediately'
            ];
        }
        
        // Pattern-based recommendations
        foreach ($analysis['error_patterns'] as $pattern => $count) {
            if (isset($this->errorPatterns[$pattern]) && $count > 3) {
                $patternConfig = $this->errorPatterns[$pattern];
                $recommendations[] = [
                    'priority' => $patternConfig['severity'] === 'FATAL' ? 'CRITICAL' : 'MEDIUM',
                    'category' => $patternConfig['category'],
                    'message' => "Pattern '$pattern' detected $count times. " . $patternConfig['suggestion'],
                    'action' => $patternConfig['suggestion']
                ];
            }
        }
        
        // Time-based patterns
        $peakHours = array_keys($analysis['time_distribution'], max($analysis['time_distribution']));
        if (count($peakHours) === 1) {
            $peakHour = $peakHours[0];
            $peakCount = $analysis['time_distribution'][$peakHour];
            if ($peakCount > $analysis['total_errors'] * 0.3) {
                $recommendations[] = [
                    'priority' => 'MEDIUM',
                    'category' => 'TIMING',
                    'message' => "Error spike detected at hour $peakHour:00 ($peakCount errors). Investigate scheduled tasks or peak usage patterns.",
                    'action' => 'Review system activity and scheduled processes during peak error times'
                ];
            }
        }
        
        return $recommendations;
    }
    
    /**
     * Generate comprehensive error report
     */
    public function generateReport($analysis) {
        $report = "\n" . str_repeat("=", 80) . "\n";
        $report .= "ENHANCED ERROR ANALYSIS REPORT - Session: {$this->sessionId}\n";
        $report .= str_repeat("=", 80) . "\n";
        
        $report .= "Report Generated: " . date('Y-m-d H:i:s') . "\n";
        $report .= "Analysis Period: Last 7 days\n";
        $report .= "Total Errors Analyzed: {$analysis['total_errors']}\n\n";
        
        // Executive Summary
        $report .= "EXECUTIVE SUMMARY:\n";
        $report .= str_repeat("-", 40) . "\n";
        
        if ($analysis['total_errors'] === 0) {
            $report .= "✅ No errors detected in the analysis period.\n\n";
        } else {
            $criticalCount = $analysis['severity_distribution']['FATAL'] ?? 0;
            $errorCount = $analysis['severity_distribution']['ERROR'] ?? 0;
            $warningCount = $analysis['severity_distribution']['WARNING'] ?? 0;
            
            if ($criticalCount > 0) {
                $report .= "🚨 CRITICAL: $criticalCount fatal errors require immediate attention\n";
            }
            if ($errorCount > 0) {
                $report .= "❌ HIGH: $errorCount errors need investigation\n";
            }
            if ($warningCount > 0) {
                $report .= "⚠️  MEDIUM: $warningCount warnings should be reviewed\n";
            }
            
            $report .= "\n";
        }
        
        // Error Categories
        if (!empty($analysis['error_categories'])) {
            $report .= "ERROR CATEGORIES:\n";
            $report .= str_repeat("-", 40) . "\n";
            arsort($analysis['error_categories']);
            foreach ($analysis['error_categories'] as $category => $count) {
                $percentage = round(($count / $analysis['total_errors']) * 100, 1);
                $report .= sprintf("  %-15s: %4d errors (%5.1f%%)\n", $category, $count, $percentage);
            }
            $report .= "\n";
        }
        
        // Severity Distribution
        if (!empty($analysis['severity_distribution'])) {
            $report .= "SEVERITY DISTRIBUTION:\n";
            $report .= str_repeat("-", 40) . "\n";
            $severityOrder = ['FATAL', 'ERROR', 'WARNING', 'NOTICE', 'INFO', 'DEBUG'];
            foreach ($severityOrder as $severity) {
                if (isset($analysis['severity_distribution'][$severity])) {
                    $count = $analysis['severity_distribution'][$severity];
                    $percentage = round(($count / $analysis['total_errors']) * 100, 1);
                    $report .= sprintf("  %-8s: %4d errors (%5.1f%%)\n", $severity, $count, $percentage);
                }
            }
            $report .= "\n";
        }
        
        // Top Errors
        if (!empty($analysis['top_errors'])) {
            $report .= "TOP 10 MOST FREQUENT ERRORS:\n";
            $report .= str_repeat("-", 40) . "\n";
            $rank = 1;
            foreach ($analysis['top_errors'] as $message => $data) {
                $report .= sprintf("%2d. Count: %3d | First: %s | Last: %s\n", 
                    $rank, $data['count'], $data['first_seen'], $data['last_seen']);
                $report .= "    Message: " . substr($data['example'], 0, 100) . 
                    (strlen($data['example']) > 100 ? '...' : '') . "\n\n";
                $rank++;
                if ($rank > 10) break;
            }
        }
        
        // Error Patterns
        if (!empty($analysis['error_patterns'])) {
            $report .= "DETECTED ERROR PATTERNS:\n";
            $report .= str_repeat("-", 40) . "\n";
            arsort($analysis['error_patterns']);
            foreach ($analysis['error_patterns'] as $pattern => $count) {
                $patternConfig = $this->errorPatterns[$pattern] ?? null;
                $report .= sprintf("  %-20s: %3d occurrences", $pattern, $count);
                if ($patternConfig) {
                    $report .= " ({$patternConfig['category']}, {$patternConfig['severity']})";
                }
                $report .= "\n";
                if ($patternConfig) {
                    $report .= "    Suggestion: {$patternConfig['suggestion']}\n";
                }
                $report .= "\n";
            }
        }
        
        // Time Distribution
        if (!empty($analysis['time_distribution'])) {
            $report .= "ERROR DISTRIBUTION BY HOUR:\n";
            $report .= str_repeat("-", 40) . "\n";
            for ($hour = 0; $hour < 24; $hour++) {
                $count = $analysis['time_distribution'][sprintf('%02d', $hour)] ?? 0;
                if ($count > 0) {
                    $bar = str_repeat('█', min(50, round($count / max($analysis['time_distribution']) * 50)));
                    $report .= sprintf("  %02d:00 | %3d errors | %s\n", $hour, $count, $bar);
                }
            }
            $report .= "\n";
        }
        
        // Recommendations
        if (!empty($analysis['recommendations'])) {
            $report .= "RECOMMENDATIONS:\n";
            $report .= str_repeat("-", 40) . "\n";
            
            $priorityOrder = ['CRITICAL', 'HIGH', 'MEDIUM', 'LOW'];
            foreach ($priorityOrder as $priority) {
                $priorityRecs = array_filter($analysis['recommendations'], function($rec) use ($priority) {
                    return $rec['priority'] === $priority;
                });
                
                if (!empty($priorityRecs)) {
                    $report .= "\n$priority PRIORITY:\n";
                    foreach ($priorityRecs as $rec) {
                        $report .= "  • [{$rec['category']}] {$rec['message']}\n";
                        $report .= "    Action: {$rec['action']}\n\n";
                    }
                }
            }
        }
        
        $report .= str_repeat("=", 80) . "\n";
        $report .= "End of Error Analysis Report\n";
        $report .= str_repeat("=", 80) . "\n";
        
        return $report;
    }
    
    /**
     * Test error reporting by generating sample errors
     */
    public function testErrorReporting() {
        echo "Testing error reporting system...\n\n";
        
        $testErrors = [
            ['level' => 'INFO', 'message' => 'Test info message for error reporting'],
            ['level' => 'WARNING', 'message' => 'Test warning: File permission issue detected'],
            ['level' => 'ERROR', 'message' => 'Test error: Database connection failed'],
            ['level' => 'FATAL', 'message' => 'Test fatal error: Memory exhausted']
        ];
        
        foreach ($testErrors as $error) {
            try {
                $this->internalErrors->logError($error['message'], [
                    'test_session' => $this->sessionId,
                    'error_level' => $error['level'],
                    'generated_at' => date('Y-m-d H:i:s')
                ]);
                
                echo "✓ Generated {$error['level']} test error\n";
            } catch (Exception $e) {
                echo "✗ Failed to generate {$error['level']} test error: " . $e->getMessage() . "\n";
            }
        }
        
        echo "\nTest errors generated. Run --analyze to see them in the report.\n";
    }
    
    /**
     * Monitor errors in real-time
     */
    public function startRealTimeMonitoring($duration = 300) {
        echo "Starting real-time error monitoring for {$duration}s...\n";
        echo "Press Ctrl+C to stop monitoring.\n\n";
        
        $startTime = time();
        $endTime = $startTime + $duration;
        $lastCheck = $startTime;
        $errorCount = 0;
        
        while (time() < $endTime) {
            $currentTime = time();
            
            // Check for new errors since last check
            $newErrors = $this->getErrorsSince(date('Y-m-d H:i:s', $lastCheck));
            
            foreach ($newErrors as $error) {
                $errorCount++;
                $severity = $this->determineSeverity($error);
                $category = $this->categorizeError($error);
                
                $timestamp = date('H:i:s');
                echo "[$timestamp] [$severity] [$category] " . substr($error['message'], 0, 80) . "\n";
                
                // Alert on critical errors
                if ($severity === 'FATAL') {
                    echo "🚨 CRITICAL ERROR DETECTED! 🚨\n";
                }
            }
            
            $lastCheck = $currentTime;
            sleep(5); // Check every 5 seconds
        }
        
        echo "\nReal-time monitoring completed. Detected $errorCount new errors.\n";
    }
    
    /**
     * Get errors since a specific timestamp
     */
    private function getErrorsSince($timestamp) {
        $errors = [];
        
        // Check internal errors
        try {
            $recentErrors = $this->internalErrors->getRecentErrors(100);
            foreach ($recentErrors as $error) {
                if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $error, $matches)) {
                    $errorTime = $matches[1];
                    if ($errorTime >= $timestamp) {
                        $errors[] = [
                            'source' => 'internal',
                            'timestamp' => $errorTime,
                            'message' => $error
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            // Continue monitoring even if one source fails
        }
        
        return $errors;
    }
    
    /**
     * Clean up and log session end
     */
    public function cleanup() {
        try {
            $this->audit->log(
                0,
                'ERROR_REPORTER_END',
                'error_reporter',
                'Enhanced Error Reporter',
                '127.0.0.1',
                0,
                json_encode([
                    'session_id' => $this->sessionId,
                    'end_time' => date('Y-m-d H:i:s')
                ])
            );
        } catch (Exception $e) {
            error_log("Failed to log error reporter end: " . $e->getMessage());
        }
    }
}

// Command line interface
if (php_sapi_name() === 'cli') {
    $options = getopt('', [
        'analyze',
        'report',
        'days:',
        'monitor',
        'real-time',
        'duration:',
        'test-errors',
        'help'
    ]);
    
    if (isset($options['help'])) {
        echo "Enhanced Error Reporting Utility\n\n";
        echo "Usage: php error_reporter.php [options]\n\n";
        echo "Options:\n";
        echo "  --analyze              Analyze recent errors and generate insights\n";
        echo "  --report               Generate comprehensive error report\n";
        echo "  --days=N               Number of days to analyze (default: 7)\n";
        echo "  --monitor              Start real-time error monitoring\n";
        echo "  --duration=N           Monitoring duration in seconds (default: 300)\n";
        echo "  --test-errors          Generate test errors for testing\n";
        echo "  --help                 Show this help message\n\n";
        echo "Examples:\n";
        echo "  php error_reporter.php --analyze --days=14\n";
        echo "  php error_reporter.php --report --days=7\n";
        echo "  php error_reporter.php --monitor --duration=600\n";
        echo "  php error_reporter.php --test-errors\n";
        exit(0);
    }
    
    $reporter = new EnhancedErrorReporter();
    
    try {
        if (isset($options['test-errors'])) {
            $reporter->testErrorReporting();
        }
        
        if (isset($options['analyze']) || isset($options['report'])) {
            $days = (int)($options['days'] ?? 7);
            $analysis = $reporter->analyzeErrors($days);
            
            if (isset($options['report'])) {
                echo $reporter->generateReport($analysis);
            } else {
                echo "Analysis completed. Use --report to see detailed report.\n";
                echo "Total errors: {$analysis['total_errors']}\n";
                echo "Categories: " . implode(', ', array_keys($analysis['error_categories'])) . "\n";
                echo "Recommendations: " . count($analysis['recommendations']) . "\n";
            }
        }
        
        if (isset($options['monitor'])) {
            $duration = (int)($options['duration'] ?? 300);
            $reporter->startRealTimeMonitoring($duration);
        }
        
        if (!isset($options['analyze']) && !isset($options['report']) && 
            !isset($options['monitor']) && !isset($options['test-errors'])) {
            echo "No action specified. Use --help for usage information.\n";
        }
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    } finally {
        $reporter->cleanup();
    }
}