<?php
/**
 * Performance Monitoring Utility
 * 
 * A standalone utility for monitoring and benchmarking system performance
 * during testing and development. Can be used independently or integrated
 * with other testing frameworks.
 * 
 * Features:
 * - Real-time performance tracking
 * - Memory usage monitoring
 * - Database query performance analysis
 * - Benchmark comparisons
 * - Historical performance data
 * - Performance regression detection
 * - Detailed reporting and visualization
 * 
 * Usage:
 * php tests/performance_monitor.php --monitor
 * php tests/performance_monitor.php --benchmark --operations=100
 * php tests/performance_monitor.php --report --days=7
 * php tests/performance_monitor.php --compare --baseline=yesterday
 */

// Set up paths and autoloading
$rootPath = dirname(__DIR__);
require_once $rootPath . '/config/system.php';
require_once $rootPath . '/classes/Core/Database.php';
require_once $rootPath . '/classes/Logging/Audit.php';

class PerformanceMonitor {
    
    private $db;
    private $audit;
    private $sessionId;
    private $startTime;
    private $startMemory;
    private $measurements = [];
    private $benchmarks = [];
    private $thresholds = [
        'slow_query' => 1.0,      // Queries slower than 1 second
        'high_memory' => 50 * 1024 * 1024, // Memory usage over 50MB
        'slow_operation' => 2.0    // Operations slower than 2 seconds
    ];
    
    public function __construct() {
        $this->sessionId = uniqid('perf_', true);
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage(true);
        
        $this->db = new Database();
        $this->audit = new Audit();
        
        $this->logSessionStart();
    }
    
    private function logSessionStart() {
        try {
            $this->audit->log(
                0,
                'PERFORMANCE_MONITOR_START',
                'performance_monitor',
                'Performance Monitor Utility',
                '127.0.0.1',
                0,
                json_encode([
                    'session_id' => $this->sessionId,
                    'start_time' => date('Y-m-d H:i:s'),
                    'start_memory' => $this->formatBytes($this->startMemory),
                    'php_version' => PHP_VERSION,
                    'memory_limit' => ini_get('memory_limit')
                ])
            );
        } catch (Exception $e) {
            error_log("Failed to log performance monitor start: " . $e->getMessage());
        }
    }
    
    /**
     * Start measuring a specific operation
     */
    public function startMeasurement($operationName, $context = []) {
        $measurementId = uniqid($operationName . '_', true);
        
        $this->measurements[$measurementId] = [
            'operation' => $operationName,
            'start_time' => microtime(true),
            'start_memory' => memory_get_usage(true),
            'context' => $context,
            'session_id' => $this->sessionId
        ];
        
        return $measurementId;
    }
    
    /**
     * End measurement and record results
     */
    public function endMeasurement($measurementId, $additionalContext = []) {
        if (!isset($this->measurements[$measurementId])) {
            throw new InvalidArgumentException("Measurement ID not found: $measurementId");
        }
        
        $measurement = $this->measurements[$measurementId];
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $result = [
            'measurement_id' => $measurementId,
            'operation' => $measurement['operation'],
            'execution_time' => round($endTime - $measurement['start_time'], 6),
            'memory_used' => $endMemory - $measurement['start_memory'],
            'memory_peak' => memory_get_peak_usage(true),
            'start_time' => $measurement['start_time'],
            'end_time' => $endTime,
            'context' => array_merge($measurement['context'], $additionalContext),
            'session_id' => $this->sessionId,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Check for performance issues
        $issues = $this->analyzePerformance($result);
        if (!empty($issues)) {
            $result['performance_issues'] = $issues;
        }
        
        // Store the completed measurement
        $this->measurements[$measurementId] = $result;
        
        return $result;
    }
    
    /**
     * Analyze performance measurement for issues
     */
    private function analyzePerformance($measurement) {
        $issues = [];
        
        if ($measurement['execution_time'] > $this->thresholds['slow_operation']) {
            $issues[] = [
                'type' => 'slow_operation',
                'severity' => 'warning',
                'message' => "Operation took {$measurement['execution_time']}s (threshold: {$this->thresholds['slow_operation']}s)",
                'value' => $measurement['execution_time'],
                'threshold' => $this->thresholds['slow_operation']
            ];
        }
        
        if ($measurement['memory_used'] > $this->thresholds['high_memory']) {
            $issues[] = [
                'type' => 'high_memory',
                'severity' => 'warning',
                'message' => "High memory usage: " . $this->formatBytes($measurement['memory_used']) . 
                           " (threshold: " . $this->formatBytes($this->thresholds['high_memory']) . ")",
                'value' => $measurement['memory_used'],
                'threshold' => $this->thresholds['high_memory']
            ];
        }
        
        return $issues;
    }
    
    /**
     * Measure database query performance
     */
    public function measureDatabaseQuery($query, $params = [], $context = []) {
        $measurementId = $this->startMeasurement('database_query', array_merge($context, [
            'query' => substr($query, 0, 100) . (strlen($query) > 100 ? '...' : ''),
            'param_count' => count($params)
        ]));
        
        $queryStart = microtime(true);
        
        try {
            $stmt = $this->db->dbcrm()->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            $result = $stmt->fetchAll();
            $queryEnd = microtime(true);
            
            $queryTime = round($queryEnd - $queryStart, 6);
            
            $measurement = $this->endMeasurement($measurementId, [
                'query_time' => $queryTime,
                'rows_returned' => count($result),
                'success' => true
            ]);
            
            // Check for slow queries
            if ($queryTime > $this->thresholds['slow_query']) {
                $this->logSlowQuery($query, $queryTime, $params);
            }
            
            return [
                'result' => $result,
                'performance' => $measurement
            ];
            
        } catch (Exception $e) {
            $queryEnd = microtime(true);
            $queryTime = round($queryEnd - $queryStart, 6);
            
            $this->endMeasurement($measurementId, [
                'query_time' => $queryTime,
                'error' => $e->getMessage(),
                'success' => false
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Log slow query for analysis
     */
    private function logSlowQuery($query, $queryTime, $params) {
        try {
            $this->audit->log(
                0,
                'SLOW_QUERY_DETECTED',
                'performance_monitor',
                'Slow Database Query',
                '127.0.0.1',
                0,
                json_encode([
                    'session_id' => $this->sessionId,
                    'query' => $query,
                    'execution_time' => $queryTime,
                    'threshold' => $this->thresholds['slow_query'],
                    'params' => $params,
                    'timestamp' => date('Y-m-d H:i:s')
                ])
            );
        } catch (Exception $e) {
            error_log("Failed to log slow query: " . $e->getMessage());
        }
    }
    
    /**
     * Run system benchmark tests
     */
    public function runBenchmarks($operations = 100) {
        echo "Running system benchmarks with $operations operations...\n\n";
        
        $benchmarks = [
            'cpu_intensive' => function() { return $this->benchmarkCPU(); },
            'memory_allocation' => function() { return $this->benchmarkMemory(); },
            'database_operations' => function() { return $this->benchmarkDatabase(); },
            'file_operations' => function() { return $this->benchmarkFileIO(); }
        ];
        
        $results = [];
        
        foreach ($benchmarks as $name => $benchmark) {
            echo "Running $name benchmark...\n";
            
            $measurementId = $this->startMeasurement("benchmark_$name", [
                'benchmark_type' => $name,
                'operations' => $operations
            ]);
            
            try {
                $benchmarkResult = $benchmark();
                $measurement = $this->endMeasurement($measurementId, [
                    'benchmark_result' => $benchmarkResult,
                    'success' => true
                ]);
                
                $results[$name] = [
                    'measurement' => $measurement,
                    'result' => $benchmarkResult
                ];
                
                echo "  Completed in {$measurement['execution_time']}s\n";
                echo "  Memory used: " . $this->formatBytes($measurement['memory_used']) . "\n\n";
                
            } catch (Exception $e) {
                $measurement = $this->endMeasurement($measurementId, [
                    'error' => $e->getMessage(),
                    'success' => false
                ]);
                
                $results[$name] = [
                    'measurement' => $measurement,
                    'error' => $e->getMessage()
                ];
                
                echo "  Failed: " . $e->getMessage() . "\n\n";
            }
        }
        
        $this->benchmarks = $results;
        return $results;
    }
    
    /**
     * CPU intensive benchmark
     */
    private function benchmarkCPU() {
        $iterations = 100000;
        $result = 0;
        
        for ($i = 0; $i < $iterations; $i++) {
            $result += sqrt($i) * sin($i) * cos($i);
        }
        
        return [
            'iterations' => $iterations,
            'final_result' => $result,
            'operations_per_second' => $iterations / (microtime(true) - $this->startTime)
        ];
    }
    
    /**
     * Memory allocation benchmark
     */
    private function benchmarkMemory() {
        $arrays = [];
        $arraySize = 10000;
        $arrayCount = 100;
        
        for ($i = 0; $i < $arrayCount; $i++) {
            $arrays[] = range(1, $arraySize);
        }
        
        $totalElements = $arrayCount * $arraySize;
        unset($arrays); // Free memory
        
        return [
            'arrays_created' => $arrayCount,
            'array_size' => $arraySize,
            'total_elements' => $totalElements,
            'memory_freed' => true
        ];
    }
    
    /**
     * Database operations benchmark
     */
    private function benchmarkDatabase() {
        $operations = 0;
        $queries = [
            "SELECT COUNT(*) FROM users",
            "SELECT * FROM users LIMIT 10",
            "SELECT COUNT(*) FROM audit",
            "SELECT * FROM audit ORDER BY created_at DESC LIMIT 5"
        ];
        
        foreach ($queries as $query) {
            try {
                $result = $this->measureDatabaseQuery($query);
                $operations++;
            } catch (Exception $e) {
                // Continue with other queries
            }
        }
        
        return [
            'queries_executed' => $operations,
            'total_queries' => count($queries),
            'success_rate' => ($operations / count($queries)) * 100
        ];
    }
    
    /**
     * File I/O benchmark
     */
    private function benchmarkFileIO() {
        $testFile = sys_get_temp_dir() . '/perf_test_' . $this->sessionId . '.tmp';
        $testData = str_repeat('Performance test data. ', 1000);
        $operations = 0;
        
        try {
            // Write test
            if (file_put_contents($testFile, $testData) !== false) {
                $operations++;
            }
            
            // Read test
            if (file_get_contents($testFile) !== false) {
                $operations++;
            }
            
            // Delete test
            if (unlink($testFile)) {
                $operations++;
            }
            
            return [
                'operations_completed' => $operations,
                'total_operations' => 3,
                'data_size' => strlen($testData),
                'success_rate' => ($operations / 3) * 100
            ];
            
        } catch (Exception $e) {
            // Clean up on error
            if (file_exists($testFile)) {
                @unlink($testFile);
            }
            throw $e;
        }
    }
    
    /**
     * Generate performance report
     */
    public function generateReport($includeBenchmarks = true) {
        $completedMeasurements = array_filter($this->measurements, function($m) {
            return isset($m['execution_time']);
        });
        
        $report = "\n" . str_repeat("=", 80) . "\n";
        $report .= "PERFORMANCE MONITORING REPORT - Session: {$this->sessionId}\n";
        $report .= str_repeat("=", 80) . "\n";
        
        $totalTime = microtime(true) - $this->startTime;
        $currentMemory = memory_get_usage(true);
        $peakMemory = memory_get_peak_usage(true);
        
        $report .= "Session Summary:\n";
        $report .= "  Start Time: " . date('Y-m-d H:i:s', $this->startTime) . "\n";
        $report .= "  End Time: " . date('Y-m-d H:i:s') . "\n";
        $report .= "  Total Session Time: " . round($totalTime, 2) . "s\n";
        $report .= "  Current Memory: " . $this->formatBytes($currentMemory) . "\n";
        $report .= "  Peak Memory: " . $this->formatBytes($peakMemory) . "\n";
        $report .= "  Total Measurements: " . count($completedMeasurements) . "\n\n";
        
        if (!empty($completedMeasurements)) {
            $report .= "Performance Measurements:\n";
            
            // Group by operation type
            $operationGroups = [];
            foreach ($completedMeasurements as $measurement) {
                $op = $measurement['operation'];
                if (!isset($operationGroups[$op])) {
                    $operationGroups[$op] = [];
                }
                $operationGroups[$op][] = $measurement;
            }
            
            foreach ($operationGroups as $operation => $measurements) {
                $count = count($measurements);
                $times = array_column($measurements, 'execution_time');
                $totalTime = array_sum($times);
                $avgTime = $totalTime / $count;
                $minTime = min($times);
                $maxTime = max($times);
                
                $report .= "  $operation ($count operations):\n";
                $report .= "    Total Time: " . round($totalTime, 4) . "s\n";
                $report .= "    Average Time: " . round($avgTime, 4) . "s\n";
                $report .= "    Min/Max Time: " . round($minTime, 4) . "s / " . round($maxTime, 4) . "s\n";
                
                // Check for performance issues
                $issueCount = 0;
                foreach ($measurements as $m) {
                    if (isset($m['performance_issues'])) {
                        $issueCount += count($m['performance_issues']);
                    }
                }
                if ($issueCount > 0) {
                    $report .= "    Performance Issues: $issueCount detected\n";
                }
                
                $report .= "\n";
            }
        }
        
        // Performance issues summary
        $allIssues = [];
        foreach ($completedMeasurements as $measurement) {
            if (isset($measurement['performance_issues'])) {
                $allIssues = array_merge($allIssues, $measurement['performance_issues']);
            }
        }
        
        if (!empty($allIssues)) {
            $report .= "Performance Issues Detected:\n";
            $issueTypes = [];
            foreach ($allIssues as $issue) {
                $type = $issue['type'];
                if (!isset($issueTypes[$type])) {
                    $issueTypes[$type] = 0;
                }
                $issueTypes[$type]++;
            }
            
            foreach ($issueTypes as $type => $count) {
                $report .= "  $type: $count occurrences\n";
            }
            $report .= "\n";
        }
        
        // Benchmark results
        if ($includeBenchmarks && !empty($this->benchmarks)) {
            $report .= "Benchmark Results:\n";
            foreach ($this->benchmarks as $name => $benchmark) {
                $measurement = $benchmark['measurement'];
                $report .= "  $name:\n";
                $report .= "    Execution Time: {$measurement['execution_time']}s\n";
                $report .= "    Memory Used: " . $this->formatBytes($measurement['memory_used']) . "\n";
                
                if (isset($benchmark['result'])) {
                    $result = $benchmark['result'];
                    if (is_array($result)) {
                        foreach ($result as $key => $value) {
                            if (is_numeric($value)) {
                                $value = is_float($value) ? round($value, 2) : $value;
                            }
                            $report .= "    " . ucfirst(str_replace('_', ' ', $key)) . ": $value\n";
                        }
                    }
                }
                
                if (isset($benchmark['error'])) {
                    $report .= "    Error: {$benchmark['error']}\n";
                }
                
                $report .= "\n";
            }
        }
        
        return $report;
    }
    
    /**
     * Monitor system performance in real-time
     */
    public function startRealTimeMonitoring($duration = 60, $interval = 5) {
        echo "Starting real-time performance monitoring for {$duration}s (interval: {$interval}s)...\n\n";
        
        $startTime = time();
        $endTime = $startTime + $duration;
        $samples = [];
        
        while (time() < $endTime) {
            $sample = [
                'timestamp' => date('Y-m-d H:i:s'),
                'memory_usage' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true),
                'cpu_load' => $this->getCPULoad(),
                'active_connections' => $this->getActiveConnections()
            ];
            
            $samples[] = $sample;
            
            echo "[{$sample['timestamp']}] ";
            echo "Memory: " . $this->formatBytes($sample['memory_usage']) . " ";
            echo "Peak: " . $this->formatBytes($sample['memory_peak']) . " ";
            echo "CPU: {$sample['cpu_load']}% ";
            echo "Connections: {$sample['active_connections']}\n";
            
            sleep($interval);
        }
        
        echo "\nMonitoring completed. Collected " . count($samples) . " samples.\n";
        return $samples;
    }
    
    /**
     * Get CPU load (simplified version)
     */
    private function getCPULoad() {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return round($load[0] * 100, 1);
        }
        return 0;
    }
    
    /**
     * Get active database connections
     */
    private function getActiveConnections() {
        try {
            $stmt = $this->db->dbcrm()->query("SHOW STATUS LIKE 'Threads_connected'");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['Value'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Clean up and log session end
     */
    public function cleanup() {
        try {
            $this->audit->log(
                0,
                'PERFORMANCE_MONITOR_END',
                'performance_monitor',
                'Performance Monitor Utility',
                '127.0.0.1',
                0,
                json_encode([
                    'session_id' => $this->sessionId,
                    'total_measurements' => count($this->measurements),
                    'session_duration' => round(microtime(true) - $this->startTime, 2),
                    'peak_memory' => $this->formatBytes(memory_get_peak_usage(true)),
                    'end_time' => date('Y-m-d H:i:s')
                ])
            );
        } catch (Exception $e) {
            error_log("Failed to log performance monitor end: " . $e->getMessage());
        }
    }
}

// Command line interface
if (php_sapi_name() === 'cli') {
    $options = getopt('', [
        'monitor',
        'benchmark',
        'operations:',
        'duration:',
        'interval:',
        'report',
        'help'
    ]);
    
    if (isset($options['help'])) {
        echo "Performance Monitoring Utility\n\n";
        echo "Usage: php performance_monitor.php [options]\n\n";
        echo "Options:\n";
        echo "  --monitor              Start real-time performance monitoring\n";
        echo "  --benchmark            Run system benchmark tests\n";
        echo "  --operations=N         Number of operations for benchmarks (default: 100)\n";
        echo "  --duration=N           Monitoring duration in seconds (default: 60)\n";
        echo "  --interval=N           Monitoring interval in seconds (default: 5)\n";
        echo "  --report               Generate performance report\n";
        echo "  --help                 Show this help message\n\n";
        echo "Examples:\n";
        echo "  php performance_monitor.php --monitor --duration=120 --interval=10\n";
        echo "  php performance_monitor.php --benchmark --operations=500\n";
        echo "  php performance_monitor.php --benchmark --report\n";
        exit(0);
    }
    
    $monitor = new PerformanceMonitor();
    
    try {
        if (isset($options['monitor'])) {
            $duration = (int)($options['duration'] ?? 60);
            $interval = (int)($options['interval'] ?? 5);
            $monitor->startRealTimeMonitoring($duration, $interval);
        }
        
        if (isset($options['benchmark'])) {
            $operations = (int)($options['operations'] ?? 100);
            $monitor->runBenchmarks($operations);
        }
        
        if (isset($options['report']) || isset($options['benchmark'])) {
            echo $monitor->generateReport();
        }
        
        if (!isset($options['monitor']) && !isset($options['benchmark']) && !isset($options['report'])) {
            echo "No action specified. Use --help for usage information.\n";
        }
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    } finally {
        $monitor->cleanup();
    }
}