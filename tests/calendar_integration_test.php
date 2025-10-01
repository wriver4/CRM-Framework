<?php
/**
 * Calendar Integration Test Suite
 * 
 * Comprehensive testing for Calendar/FullCalendar integration
 * Uses Enhanced Testing Framework patterns
 * Tests database operations, API endpoints, and model functionality
 * 
 * @author CRM Framework
 * @version 1.0
 */

// Include system configuration and classes
require_once __DIR__ . '/../config/system.php';
require_once CLASSES . 'Models/CalendarEvent.php';
require_once CLASSES . 'Core/Sessions.php';
require_once CLASSES . 'Core/Security.php';
require_once CLASSES . 'Core/Database.php';

class CalendarIntegrationTest
{
    private $calendar;
    private $database;
    private $lang;
    private $test_user_id = 1; // Assuming user ID 1 exists
    private $test_lead_id = null;
    private $test_contact_id = null;
    private $created_event_ids = [];
    private $test_results = [];
    private $start_time;
    
    public function __construct()
    {
        $this->start_time = microtime(true);
        $this->calendar = new CalendarEvent();
        $this->database = new Database();
        $this->lang = include DOCPUBLIC . '/admin/languages/en.php';
        
        echo "=== Calendar Integration Test Suite ===\n";
        echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";
    }
    
    public function runAllTests()
    {
        try {
            // Setup test data
            $this->setupTestData();
            
            // Database schema tests
            $this->testDatabaseSchema();
            
            // Model functionality tests
            $this->testEventCreation();
            $this->testEventRetrieval();
            $this->testEventUpdate();
            $this->testEventDeletion();
            $this->testEventTypes();
            $this->testPriorities();
            $this->testEventStats();
            $this->testTodaysEvents();
            $this->testNextActionIntegration();
            
            // API endpoint tests
            $this->testAPIEndpoints();
            
            // Security tests
            $this->testSecurityValidation();
            
            // Performance tests
            $this->testPerformance();
            
            // Cleanup
            $this->cleanup();
            
            // Generate report
            $this->generateReport();
            
        } catch (Exception $e) {
            echo "FATAL ERROR: " . $e->getMessage() . "\n";
            echo "Stack trace: " . $e->getTraceAsString() . "\n";
            $this->cleanup();
        }
    }
    
    private function setupTestData()
    {
        echo "Setting up test data...\n";
        
        // Create test lead if needed
        $query = "SELECT id FROM leads LIMIT 1";
        $stmt = $this->database->connection->prepare($query);
        $stmt->execute();
        $lead = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($lead) {
            $this->test_lead_id = $lead['id'];
        } else {
            // Create a test lead
            $query = "INSERT INTO leads (company_name, status, created_by) VALUES ('Test Company', 1, :user_id)";
            $stmt = $this->database->connection->prepare($query);
            $stmt->bindValue(':user_id', $this->test_user_id, PDO::PARAM_INT);
            $stmt->execute();
            $this->test_lead_id = $this->database->connection->lastInsertId();
        }
        
        // Create test contact if needed
        $query = "SELECT id FROM contacts LIMIT 1";
        $stmt = $this->database->connection->prepare($query);
        $stmt->execute();
        $contact = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($contact) {
            $this->test_contact_id = $contact['id'];
        } else {
            // Create a test contact
            $query = "INSERT INTO contacts (first_name, family_name, full_name, lead_id) VALUES ('Test', 'Contact', 'Test Contact', :lead_id)";
            $stmt = $this->database->connection->prepare($query);
            $stmt->bindValue(':lead_id', $this->test_lead_id, PDO::PARAM_INT);
            $stmt->execute();
            $this->test_contact_id = $this->database->connection->lastInsertId();
        }
        
        $stmt = null;
        echo "Test data setup complete.\n\n";
    }
    
    private function testDatabaseSchema()
    {
        echo "Testing database schema...\n";
        
        $tables_to_check = [
            'calendar_events',
            'calendar_event_attendees',
            'calendar_event_reminders',
            'calendar_user_settings',
            'calendar_event_types',
            'calendar_priorities'
        ];
        
        foreach ($tables_to_check as $table) {
            $query = "SHOW TABLES LIKE '$table'";
            $stmt = $this->database->connection->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            
            if ($result) {
                $this->recordTest("Database Schema - $table exists", true, "Table $table found");
            } else {
                $this->recordTest("Database Schema - $table exists", false, "Table $table not found");
            }
        }
        
        // Test foreign key constraints
        $query = "SELECT COUNT(*) as count FROM calendar_event_types";
        $stmt = $this->database->connection->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->recordTest("Event types populated", $result['count'] > 0, "Found {$result['count']} event types");
        
        $query = "SELECT COUNT(*) as count FROM calendar_priorities";
        $stmt = $this->database->connection->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->recordTest("Priorities populated", $result['count'] == 10, "Found {$result['count']} priorities (expected 10)");
        
        $stmt = null;
        echo "Database schema tests completed.\n\n";
    }
    
    private function testEventCreation()
    {
        echo "Testing event creation...\n";
        
        $test_data = [
            'title' => 'Test Event - Phone Call',
            'description' => 'Test event description',
            'event_type' => 1, // Phone call
            'start_datetime' => date('Y-m-d H:i:s', strtotime('+1 hour')),
            'end_datetime' => date('Y-m-d H:i:s', strtotime('+1.5 hours')),
            'status' => 1,
            'priority' => 7,
            'location' => 'Test Location',
            'notes' => 'Test notes',
            'lead_id' => $this->test_lead_id,
            'contact_id' => $this->test_contact_id,
            'reminder_minutes' => 15,
            'timezone' => 'UTC'
        ];
        
        try {
            $event_id = $this->calendar->createEvent($test_data, $this->test_user_id);
            
            if ($event_id) {
                $this->created_event_ids[] = $event_id;
                $this->recordTest("Event creation", true, "Event created with ID: $event_id");
                
                // Verify event was created correctly
                $event = $this->calendar->getEventById($event_id);
                
                $this->recordTest("Event data integrity - title", $event['title'] === $test_data['title'], "Title matches");
                $this->recordTest("Event data integrity - type", $event['event_type'] == $test_data['event_type'], "Event type matches");
                $this->recordTest("Event data integrity - priority", $event['priority'] == $test_data['priority'], "Priority matches");
                $this->recordTest("Event data integrity - lead", $event['lead_id'] == $test_data['lead_id'], "Lead ID matches");
                
            } else {
                $this->recordTest("Event creation", false, "Failed to create event");
            }
            
        } catch (Exception $e) {
            $this->recordTest("Event creation", false, "Exception: " . $e->getMessage());
        }
        
        echo "Event creation tests completed.\n\n";
    }
    
    private function testEventRetrieval()
    {
        echo "Testing event retrieval...\n";
        
        try {
            // Test getting events for user
            $events = $this->calendar->getEventsForUser($this->test_user_id);
            $this->recordTest("Get events for user", is_array($events), "Retrieved " . count($events) . " events");
            
            // Test getting events for calendar (FullCalendar format)
            $calendar_events = $this->calendar->getEventsForCalendar($this->test_user_id);
            $this->recordTest("Get events for calendar", is_array($calendar_events), "Retrieved " . count($calendar_events) . " calendar events");
            
            // Verify FullCalendar format
            if (!empty($calendar_events)) {
                $first_event = $calendar_events[0];
                $required_fields = ['id', 'title', 'start', 'backgroundColor', 'extendedProps'];
                $has_all_fields = true;
                
                foreach ($required_fields as $field) {
                    if (!isset($first_event[$field])) {
                        $has_all_fields = false;
                        break;
                    }
                }
                
                $this->recordTest("FullCalendar format validation", $has_all_fields, "Event has required FullCalendar fields");
            }
            
            // Test date range filtering
            $start_date = date('Y-m-d H:i:s');
            $end_date = date('Y-m-d H:i:s', strtotime('+1 week'));
            $filtered_events = $this->calendar->getEventsForUser($this->test_user_id, $start_date, $end_date);
            
            $this->recordTest("Date range filtering", is_array($filtered_events), "Retrieved " . count($filtered_events) . " events in date range");
            
        } catch (Exception $e) {
            $this->recordTest("Event retrieval", false, "Exception: " . $e->getMessage());
        }
        
        echo "Event retrieval tests completed.\n\n";
    }
    
    private function testEventUpdate()
    {
        echo "Testing event update...\n";
        
        if (empty($this->created_event_ids)) {
            $this->recordTest("Event update", false, "No events to update");
            return;
        }
        
        $event_id = $this->created_event_ids[0];
        
        $update_data = [
            'title' => 'Updated Test Event',
            'description' => 'Updated description',
            'event_type' => 2, // Email
            'start_datetime' => date('Y-m-d H:i:s', strtotime('+2 hours')),
            'end_datetime' => date('Y-m-d H:i:s', strtotime('+2.5 hours')),
            'status' => 2, // Completed
            'priority' => 9,
            'location' => 'Updated Location',
            'notes' => 'Updated notes'
        ];
        
        try {
            $result = $this->calendar->updateEvent($event_id, $update_data, $this->test_user_id);
            $this->recordTest("Event update", $result, "Event update result: " . ($result ? 'success' : 'failed'));
            
            if ($result) {
                // Verify update
                $updated_event = $this->calendar->getEventById($event_id);
                $this->recordTest("Update verification - title", $updated_event['title'] === $update_data['title'], "Title updated correctly");
                $this->recordTest("Update verification - type", $updated_event['event_type'] == $update_data['event_type'], "Event type updated correctly");
                $this->recordTest("Update verification - priority", $updated_event['priority'] == $update_data['priority'], "Priority updated correctly");
            }
            
        } catch (Exception $e) {
            $this->recordTest("Event update", false, "Exception: " . $e->getMessage());
        }
        
        echo "Event update tests completed.\n\n";
    }
    
    private function testEventDeletion()
    {
        echo "Testing event deletion...\n";
        
        if (empty($this->created_event_ids)) {
            $this->recordTest("Event deletion", false, "No events to delete");
            return;
        }
        
        $event_id = array_pop($this->created_event_ids); // Remove last event from tracking
        
        try {
            $result = $this->calendar->deleteEvent($event_id, $this->test_user_id);
            $this->recordTest("Event deletion", $result, "Event deletion result: " . ($result ? 'success' : 'failed'));
            
            if ($result) {
                // Verify deletion
                $deleted_event = $this->calendar->getEventById($event_id);
                $this->recordTest("Deletion verification", $deleted_event === false, "Event no longer exists");
            }
            
        } catch (Exception $e) {
            $this->recordTest("Event deletion", false, "Exception: " . $e->getMessage());
        }
        
        echo "Event deletion tests completed.\n\n";
    }
    
    private function testEventTypes()
    {
        echo "Testing event types...\n";
        
        try {
            $types = $this->calendar->getEventTypes($this->lang);
            $this->recordTest("Get event types", is_array($types) && count($types) > 0, "Retrieved " . count($types) . " event types");
            
            // Verify required fields
            if (!empty($types)) {
                $first_type = $types[0];
                $required_fields = ['id', 'name', 'color', 'icon'];
                $has_all_fields = true;
                
                foreach ($required_fields as $field) {
                    if (!isset($first_type[$field])) {
                        $has_all_fields = false;
                        break;
                    }
                }
                
                $this->recordTest("Event type structure", $has_all_fields, "Event type has required fields");
            }
            
        } catch (Exception $e) {
            $this->recordTest("Event types", false, "Exception: " . $e->getMessage());
        }
        
        echo "Event types tests completed.\n\n";
    }
    
    private function testPriorities()
    {
        echo "Testing priorities...\n";
        
        try {
            $priorities = $this->calendar->getPriorities($this->lang);
            $this->recordTest("Get priorities", is_array($priorities) && count($priorities) == 10, "Retrieved " . count($priorities) . " priorities (expected 10)");
            
            // Verify 1-10 range
            if (!empty($priorities)) {
                $priority_ids = array_column($priorities, 'id');
                $expected_ids = range(1, 10);
                $has_correct_range = empty(array_diff($expected_ids, $priority_ids));
                
                $this->recordTest("Priority range 1-10", $has_correct_range, "Priorities cover 1-10 range");
            }
            
        } catch (Exception $e) {
            $this->recordTest("Priorities", false, "Exception: " . $e->getMessage());
        }
        
        echo "Priorities tests completed.\n\n";
    }
    
    private function testEventStats()
    {
        echo "Testing event statistics...\n";
        
        try {
            $stats = $this->calendar->getEventStats($this->test_user_id);
            $this->recordTest("Get event stats", is_array($stats), "Retrieved event statistics");
            
            // Verify required fields
            $required_fields = ['total_events', 'phone_calls', 'emails', 'meetings', 'high_priority', 'completed'];
            $has_all_fields = true;
            
            foreach ($required_fields as $field) {
                if (!isset($stats[$field])) {
                    $has_all_fields = false;
                    break;
                }
            }
            
            $this->recordTest("Stats structure", $has_all_fields, "Stats have required fields");
            
        } catch (Exception $e) {
            $this->recordTest("Event stats", false, "Exception: " . $e->getMessage());
        }
        
        echo "Event statistics tests completed.\n\n";
    }
    
    private function testTodaysEvents()
    {
        echo "Testing today's events...\n";
        
        try {
            $events = $this->calendar->getTodaysEvents($this->test_user_id, 5);
            $this->recordTest("Get today's events", is_array($events), "Retrieved " . count($events) . " today's events");
            
            // Verify events are for today
            $today = date('Y-m-d');
            $all_today = true;
            
            foreach ($events as $event) {
                $event_date = date('Y-m-d', strtotime($event['start_datetime']));
                if ($event_date !== $today) {
                    $all_today = false;
                    break;
                }
            }
            
            $this->recordTest("Today's events date filter", $all_today || empty($events), "All events are for today");
            
        } catch (Exception $e) {
            $this->recordTest("Today's events", false, "Exception: " . $e->getMessage());
        }
        
        echo "Today's events tests completed.\n\n";
    }
    
    private function testNextActionIntegration()
    {
        echo "Testing Next Action integration...\n";
        
        $next_action_data = [
            'next_action' => 1, // Phone call
            'next_action_notes' => 'Follow up on proposal',
            'next_action_date' => date('Y-m-d', strtotime('+1 day')),
            'next_action_time' => '14:30'
        ];
        
        try {
            $event_id = $this->calendar->createEventFromNextAction($this->test_lead_id, $next_action_data, $this->test_user_id);
            
            if ($event_id) {
                $this->created_event_ids[] = $event_id;
                $this->recordTest("Next Action integration", true, "Event created from Next Action with ID: $event_id");
                
                // Verify event details
                $event = $this->calendar->getEventById($event_id);
                $this->recordTest("Next Action - event type", $event['event_type'] == 1, "Event type matches Next Action type");
                $this->recordTest("Next Action - lead association", $event['lead_id'] == $this->test_lead_id, "Lead ID matches");
                $this->recordTest("Next Action - notes", strpos($event['notes'], 'Follow up on proposal') !== false, "Notes transferred correctly");
                
            } else {
                $this->recordTest("Next Action integration", false, "Failed to create event from Next Action");
            }
            
        } catch (Exception $e) {
            $this->recordTest("Next Action integration", false, "Exception: " . $e->getMessage());
        }
        
        echo "Next Action integration tests completed.\n\n";
    }
    
    private function testAPIEndpoints()
    {
        echo "Testing API endpoints...\n";
        
        // Note: This would require setting up a proper HTTP client and session
        // For now, we'll test the basic API structure
        
        $api_file = DOCPUBLIC . '/calendar/api.php';
        $this->recordTest("API file exists", file_exists($api_file), "API file found at: $api_file");
        
        // Test API file syntax
        $api_content = file_get_contents($api_file);
        $this->recordTest("API file readable", !empty($api_content), "API file content loaded");
        
        // Check for required functions
        $required_functions = ['handleGetRequest', 'handlePostRequest', 'handlePutRequest', 'handleDeleteRequest'];
        $all_functions_present = true;
        
        foreach ($required_functions as $function) {
            if (strpos($api_content, "function $function") === false) {
                $all_functions_present = false;
                break;
            }
        }
        
        $this->recordTest("API functions present", $all_functions_present, "All required API functions found");
        
        echo "API endpoints tests completed.\n\n";
    }
    
    private function testSecurityValidation()
    {
        echo "Testing security validation...\n";
        
        try {
            // Test unauthorized user access (should fail)
            $unauthorized_user_id = 99999; // Non-existent user
            
            try {
                $this->calendar->createEvent([
                    'title' => 'Unauthorized Test',
                    'event_type' => 1,
                    'start_datetime' => date('Y-m-d H:i:s')
                ], $unauthorized_user_id);
                
                $this->recordTest("Security - unauthorized user", false, "Unauthorized user was able to create event");
            } catch (Exception $e) {
                $this->recordTest("Security - unauthorized user", true, "Unauthorized user blocked: " . $e->getMessage());
            }
            
            // Test event ownership validation
            if (!empty($this->created_event_ids)) {
                $event_id = $this->created_event_ids[0];
                
                try {
                    $this->calendar->updateEvent($event_id, ['title' => 'Unauthorized Update'], $unauthorized_user_id);
                    $this->recordTest("Security - event ownership", false, "Unauthorized user was able to update event");
                } catch (Exception $e) {
                    $this->recordTest("Security - event ownership", true, "Event ownership protected: " . $e->getMessage());
                }
            }
            
        } catch (Exception $e) {
            $this->recordTest("Security validation", false, "Exception: " . $e->getMessage());
        }
        
        echo "Security validation tests completed.\n\n";
    }
    
    private function testPerformance()
    {
        echo "Testing performance...\n";
        
        $start_time = microtime(true);
        
        // Test bulk event retrieval
        $events = $this->calendar->getEventsForUser($this->test_user_id);
        $retrieval_time = microtime(true) - $start_time;
        
        $this->recordTest("Performance - event retrieval", $retrieval_time < 1.0, "Event retrieval took {$retrieval_time}s (should be < 1s)");
        
        // Test calendar format conversion
        $start_time = microtime(true);
        $calendar_events = $this->calendar->getEventsForCalendar($this->test_user_id);
        $conversion_time = microtime(true) - $start_time;
        
        $this->recordTest("Performance - calendar conversion", $conversion_time < 1.0, "Calendar conversion took {$conversion_time}s (should be < 1s)");
        
        // Test stats calculation
        $start_time = microtime(true);
        $stats = $this->calendar->getEventStats($this->test_user_id);
        $stats_time = microtime(true) - $start_time;
        
        $this->recordTest("Performance - stats calculation", $stats_time < 0.5, "Stats calculation took {$stats_time}s (should be < 0.5s)");
        
        echo "Performance tests completed.\n\n";
    }
    
    private function cleanup()
    {
        echo "Cleaning up test data...\n";
        
        // Delete created events
        foreach ($this->created_event_ids as $event_id) {
            try {
                $this->calendar->deleteEvent($event_id, $this->test_user_id);
            } catch (Exception $e) {
                echo "Warning: Could not delete event $event_id: " . $e->getMessage() . "\n";
            }
        }
        
        echo "Cleanup completed.\n\n";
    }
    
    private function recordTest($test_name, $passed, $details = '')
    {
        $this->test_results[] = [
            'name' => $test_name,
            'passed' => $passed,
            'details' => $details,
            'timestamp' => microtime(true)
        ];
        
        $status = $passed ? 'PASS' : 'FAIL';
        $color = $passed ? "\033[32m" : "\033[31m"; // Green for pass, red for fail
        $reset = "\033[0m";
        
        echo "{$color}[$status]{$reset} $test_name";
        if ($details) {
            echo " - $details";
        }
        echo "\n";
    }
    
    private function generateReport()
    {
        $total_time = microtime(true) - $this->start_time;
        $total_tests = count($this->test_results);
        $passed_tests = count(array_filter($this->test_results, function($test) { return $test['passed']; }));
        $failed_tests = $total_tests - $passed_tests;
        
        echo "\n=== TEST REPORT ===\n";
        echo "Total Tests: $total_tests\n";
        echo "Passed: $passed_tests\n";
        echo "Failed: $failed_tests\n";
        echo "Success Rate: " . round(($passed_tests / $total_tests) * 100, 2) . "%\n";
        echo "Total Time: " . round($total_time, 3) . "s\n";
        echo "Completed at: " . date('Y-m-d H:i:s') . "\n";
        
        if ($failed_tests > 0) {
            echo "\n=== FAILED TESTS ===\n";
            foreach ($this->test_results as $test) {
                if (!$test['passed']) {
                    echo "- {$test['name']}: {$test['details']}\n";
                }
            }
        }
        
        echo "\n=== Calendar Integration Test Suite Complete ===\n";
    }
}

// Run tests if called directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    $test_suite = new CalendarIntegrationTest();
    $test_suite->runAllTests();
}