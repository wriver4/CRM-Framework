<?php
/**
 * Test Migration Script
 * 
 * Tests the notes migration logic with sample data
 * before running on the full database
 */

require_once dirname(__DIR__) . '/config/system.php';
require_once 'migrate_notes.php';

class MigrationTester
{
    private $testCases = [];
    
    public function __construct()
    {
        $this->setupTestCases();
    }

    /**
     * Setup test cases with various note formats
     */
    private function setupTestCases()
    {
        $this->testCases = [
            [
                'id' => 1,
                'created_at' => '2024-01-01 00:00:00',
                'notes' => '2/3/25 I emailed, texted, called Sophie Frankel asked if theres any updates from the consultant you hired for comparisons?
2/3/25 I also emailed Heather Kline, Family Office for any updates?
2/4/25 Sophie Frankel texted, she hasnt received any updates. Shell check today & let me know what she finds out.
2/21/25 She texted, yes, thats what the board decided.'
            ],
            [
                'id' => 2,
                'created_at' => '2024-12-01 00:00:00',
                'notes' => '12/20/25  Randy created a proposal & emailed it to David.
12/18/25  Randy flew to CA met w/Jason @ Davids house.
1/15/25   I called Randy, he was going to call David today for an update.
1/28/25  David wants to go forward.'
            ],
            [
                'id' => 3,
                'created_at' => '2025-01-01 00:00:00', 
                'notes' => 'Initial contact made through website form.
3/1/25 First follow-up call completed.
No date entry here should use created date.
3/15/25 Second follow-up, very interested.
Final note without date.'
            ],
            [
                'id' => 4,
                'created_at' => '2025-01-01 00:00:00',
                'notes' => '1/10/25 10:30 AM Called customer, no answer.
1/11/25 2:15 PM Customer returned call.
01/15/2025 Site visit scheduled.
1-20-25 Site visit completed.'
            ]
        ];
    }

    /**
     * Run all tests
     */
    public function runTests()
    {
        echo "=== Migration Test Suite ===\n\n";
        
        $totalTests = count($this->testCases);
        $passedTests = 0;
        
        foreach ($this->testCases as $index => $testCase) {
            echo "Test Case " . ($index + 1) . ":\n";
            echo "Lead ID: {$testCase['id']}\n";
            echo "Created: {$testCase['created_at']}\n";
            echo "Original Notes Length: " . strlen($testCase['notes']) . " chars\n";
            echo "---\n";
            
            if ($this->testNotesParsing($testCase)) {
                $passedTests++;
                echo "✓ PASSED\n";
            } else {
                echo "✗ FAILED\n";
            }
            
            echo "\n" . str_repeat("-", 60) . "\n\n";
        }
        
        echo "=== Test Results ===\n";
        echo "Passed: {$passedTests}/{$totalTests}\n";
        echo "Success Rate: " . round(($passedTests / $totalTests) * 100, 1) . "%\n";
        
        return $passedTests === $totalTests;
    }

    /**
     * Test notes parsing for a single case
     */
    private function testNotesParsing($testCase)
    {
        $migration = new TestNotesMigration();
        
        try {
            $entries = $migration->testParseNotes($testCase['notes'], $testCase['created_at']);
            
            echo "Parsed into " . count($entries) . " entries:\n";
            
            foreach ($entries as $i => $entry) {
                echo "  " . ($i + 1) . ". Date: {$entry['date']}\n";
                echo "     Text: " . substr($entry['text'], 0, 80) . (strlen($entry['text']) > 80 ? '...' : '') . "\n";
                echo "     Length: " . strlen($entry['text']) . " chars\n\n";
            }
            
            // Validate entries
            return $this->validateEntries($entries);
            
        } catch (Exception $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Validate parsed entries
     */
    private function validateEntries($entries)
    {
        foreach ($entries as $entry) {
            // Check date format
            if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $entry['date'])) {
                echo "Invalid date format: {$entry['date']}\n";
                return false;
            }
            
            // Check text content
            if (empty(trim($entry['text']))) {
                echo "Empty text content found\n";
                return false;
            }
        }
        
        return true;
    }
}

/**
 * Test version of NotesMigration that exposes protected methods
 */
class TestNotesMigration extends NotesMigration
{
    public function testParseNotes($notesText, $leadCreatedAt)
    {
        // Use reflection to access private method
        $reflection = new ReflectionClass($this);
        $method = $reflection->getMethod('parseNotes');
        $method->setAccessible(true);
        
        return $method->invoke($this, $notesText, $leadCreatedAt);
    }
}

// Run tests
if (php_sapi_name() === 'cli') {
    $tester = new MigrationTester();
    $success = $tester->runTests();
    
    exit($success ? 0 : 1);
}
?>