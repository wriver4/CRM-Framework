<?php
/**
 * Simple Calendar Integration Test
 * 
 * Basic testing for Calendar/FullCalendar integration
 * Uses bootstrap approach to avoid CLI logging issues
 */

// Set CLI environment variables to avoid HTTP-specific errors
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/test';
$_SERVER['REQUEST_METHOD'] = 'GET';

// Include bootstrap for CLI-safe testing
require_once __DIR__ . '/bootstrap.php';

class SimpleCalendarTest
{
    private $database;
    private $test_results = [];
    private $start_time;
    
    public function __construct()
    {
        $this->start_time = microtime(true);
        echo "=== Simple Calendar Integration Test ===\n";
        echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";
    }
    
    public function runTests()
    {
        try {
            // Test database connection
            $this->testDatabaseConnection();
            
            // Test database schema
            $this->testDatabaseSchema();
            
            // Test CalendarEvent model loading
            $this->testModelLoading();
            
            // Generate report
            $this->generateReport();
            
        } catch (Exception $e) {
            echo "FATAL ERROR: " . $e->getMessage() . "\n";
            echo "Stack trace: " . $e->getTraceAsString() . "\n";
        }
    }
    
    private function testDatabaseConnection()
    {
        echo "Testing database connection...\n";
        
        try {
            $this->database = new Database();
            $this->recordTest("Database connection", true, "Connected successfully");
            
            // Test basic query
            $stmt = $this->database->connection->prepare("SELECT 1 as test");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->recordTest("Database query", $result['test'] == 1, "Basic query works");
            
        } catch (Exception $e) {
            $this->recordTest("Database connection", false, "Error: " . $e->getMessage());
        }
        
        echo "Database connection tests completed.\n\n";
    }
    
    private function testDatabaseSchema()
    {
        echo "Testing database schema...\n";
        
        if (!$this->database) {
            $this->recordTest("Database schema", false, "No database connection");
            return;
        }
        
        $tables_to_check = [
            'calendar_events',
            'calendar_event_attendees', 
            'calendar_event_reminders',
            'calendar_user_settings',
            'calendar_event_types',
            'calendar_priorities'
        ];
        
        foreach ($tables_to_check as $table) {
            try {
                $query = "SHOW TABLES LIKE '$table'";
                $stmt = $this->database->connection->prepare($query);
                $stmt->execute();
                $result = $stmt->fetch();
                
                if ($result) {
                    $this->recordTest("Schema - $table exists", true, "Table found");
                    
                    // Check if table has data (for lookup tables)
                    if (in_array($table, ['calendar_event_types', 'calendar_priorities'])) {
                        $count_query = "SELECT COUNT(*) as count FROM $table";
                        $count_stmt = $this->database->connection->prepare($count_query);
                        $count_stmt->execute();
                        $count_result = $count_stmt->fetch(PDO::FETCH_ASSOC);
                        
                        $this->recordTest("Schema - $table populated", $count_result['count'] > 0, "Found {$count_result['count']} records");
                    }
                } else {
                    $this->recordTest("Schema - $table exists", false, "Table not found");
                }
            } catch (Exception $e) {
                $this->recordTest("Schema - $table", false, "Error: " . $e->getMessage());
            }
        }
        
        echo "Database schema tests completed.\n\n";
    }
    
    private function testModelLoading()
    {
        echo "Testing model loading...\n";
        
        try {
            $calendar = new CalendarEvent();
            $lang = include DOCPUBLIC . '/admin/languages/en.php';
            $this->recordTest("CalendarEvent model loading", true, "Model loaded successfully");
            
            // Test basic methods exist
            $methods_to_check = [
                'getEventsForUser',
                'getEventsForCalendar', 
                'createEvent',
                'updateEvent',
                'deleteEvent',
                'getEventById',
                'getEventTypes',
                'getPriorities'
            ];
            
            foreach ($methods_to_check as $method) {
                $exists = method_exists($calendar, $method);
                $this->recordTest("Method - $method exists", $exists, $exists ? "Method found" : "Method missing");
            }
            
            // Test getting event types
            try {
                $types = $calendar->getEventTypes($lang);
                $this->recordTest("Get event types", is_array($types), "Retrieved " . count($types) . " event types");
            } catch (Exception $e) {
                $this->recordTest("Get event types", false, "Error: " . $e->getMessage());
            }
            
            // Test getting priorities
            try {
                $priorities = $calendar->getPriorities($lang);
                $this->recordTest("Get priorities", is_array($priorities), "Retrieved " . count($priorities) . " priorities");
            } catch (Exception $e) {
                $this->recordTest("Get priorities", false, "Error: " . $e->getMessage());
            }
            
        } catch (Exception $e) {
            $this->recordTest("CalendarEvent model loading", false, "Error: " . $e->getMessage());
        }
        
        echo "Model loading tests completed.\n\n";
    }
    
    private function recordTest($test_name, $passed, $message = '')
    {
        $this->test_results[] = [
            'name' => $test_name,
            'passed' => $passed,
            'message' => $message,
            'timestamp' => microtime(true)
        ];
        
        $status = $passed ? '✅ PASS' : '❌ FAIL';
        echo "  $status: $test_name";
        if ($message) {
            echo " - $message";
        }
        echo "\n";
    }
    
    private function generateReport()
    {
        $end_time = microtime(true);
        $duration = round($end_time - $this->start_time, 3);
        
        $total_tests = count($this->test_results);
        $passed_tests = count(array_filter($this->test_results, function($test) {
            return $test['passed'];
        }));
        $failed_tests = $total_tests - $passed_tests;
        
        echo "\n=== TEST SUMMARY ===\n";
        echo "Total Tests: $total_tests\n";
        echo "Passed: $passed_tests\n";
        echo "Failed: $failed_tests\n";
        echo "Duration: {$duration}s\n";
        echo "Success Rate: " . round(($passed_tests / $total_tests) * 100, 1) . "%\n";
        
        if ($failed_tests > 0) {
            echo "\n=== FAILED TESTS ===\n";
            foreach ($this->test_results as $test) {
                if (!$test['passed']) {
                    echo "❌ {$test['name']}: {$test['message']}\n";
                }
            }
        }
        
        echo "\n=== Test completed at " . date('Y-m-d H:i:s') . " ===\n";
    }
}

// Run the test
$test = new SimpleCalendarTest();
$test->runTests();