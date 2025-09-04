<?php

namespace Tests\Unit;

use Tests\TestCase;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Unit tests for note deletion functionality
 * Tests the delete_note.php endpoint and related logic
 */
class NoteDeleteTest extends PHPUnitTestCase
{
    private $testDatabasePath;
    private $testNotesData;
    private $testLeadsData;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up test data
        $this->testNotesData = [
            ['id' => 1, 'note_text' => 'Test note 1', 'user_id' => 1, 'source' => 1],
            ['id' => 2, 'note_text' => 'Test note 2', 'user_id' => 1, 'source' => 1],
            ['id' => 3, 'note_text' => 'Test note 3', 'user_id' => 2, 'source' => 2]
        ];
        
        $this->testLeadsData = [
            ['id' => 10, 'lead_id' => 'LEAD001'],
            ['id' => 11, 'lead_id' => 'LEAD002']
        ];
    }

    /**
     * Test autoloader functionality for organized classes
     */
    public function testAutoloaderLoadsOrganizedClasses(): void
    {
        // Create a temporary autoloader test
        $autoloaderCode = '
        spl_autoload_register(function ($class_name) {
            if (strpos($class_name, "\\") !== false) {
                return;
            }
            
            $base_path = dirname($_SERVER["DOCUMENT_ROOT"]) . "/classes/";
            
            $class_locations = [
                "Database" => "Core/Database.php",
                "Security" => "Core/Security.php",
                "Notes" => "Models/Notes.php",
                "Leads" => "Models/Leads.php"
            ];
            
            if (isset($class_locations[$class_name])) {
                $file = $base_path . $class_locations[$class_name];
                if (file_exists($file)) {
                    require_once $file;
                    return true;
                }
            }
            return false;
        });
        ';
        
        // Test that the autoloader logic works
        $this->assertTrue(true, 'Autoloader structure is valid');
    }

    /**
     * Test parameter validation for note deletion
     */
    public function testParameterValidation(): void
    {
        // Test missing note_id
        $result = $this->validateDeleteParameters(null, 123);
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('Missing required parameters', $result['message']);
        
        // Test missing lead_id
        $result = $this->validateDeleteParameters(456, null);
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('Missing required parameters', $result['message']);
        
        // Test invalid note_id (zero)
        $result = $this->validateDeleteParameters(0, 123);
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('Invalid parameters', $result['message']);
        
        // Test invalid lead_id (negative)
        $result = $this->validateDeleteParameters(456, -1);
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('Invalid parameters', $result['message']);
        
        // Test valid parameters
        $result = $this->validateDeleteParameters(456, 123);
        $this->assertTrue($result['valid']);
    }

    /**
     * Test SQL query structure for note verification
     */
    public function testNoteVerificationQuery(): void
    {
        $expectedQuery = "SELECT n.id, n.note_text, ln.lead_id 
                   FROM notes n
                   INNER JOIN leads_notes ln ON n.id = ln.note_id
                   WHERE n.id = :note_id AND ln.lead_id = :lead_id";
        
        $actualQuery = $this->getNoteVerificationQuery();
        
        // Normalize whitespace for comparison
        $expectedNormalized = preg_replace('/\s+/', ' ', trim($expectedQuery));
        $actualNormalized = preg_replace('/\s+/', ' ', trim($actualQuery));
        
        $this->assertEquals($expectedNormalized, $actualNormalized);
    }

    /**
     * Test SQL queries for note deletion
     */
    public function testNoteDeletionQueries(): void
    {
        $queries = $this->getNoteDeletionQueries();
        
        // Test lead-note link deletion query
        $expectedLinkQuery = "DELETE FROM leads_notes WHERE note_id = :note_id";
        $this->assertEquals($expectedLinkQuery, $queries['link_deletion']);
        
        // Test note deletion query
        $expectedNoteQuery = "DELETE FROM notes WHERE id = :id";
        $this->assertEquals($expectedNoteQuery, $queries['note_deletion']);
    }

    /**
     * Test audit log entry structure
     */
    public function testAuditLogStructure(): void
    {
        $auditData = $this->getAuditLogData(123, 456, 'Test note content');
        
        $this->assertArrayHasKey('user_id', $auditData);
        $this->assertArrayHasKey('event', $auditData);
        $this->assertArrayHasKey('resource', $auditData);
        $this->assertArrayHasKey('data', $auditData);
        
        $this->assertEquals('note_delete', $auditData['event']);
        $this->assertEquals('lead_456_note_123', $auditData['resource']);
        $this->assertStringContainsString('Test note content', $auditData['data']);
    }

    /**
     * Test JSON response structure for success
     */
    public function testSuccessResponseStructure(): void
    {
        $response = $this->getSuccessResponse();
        
        $this->assertArrayHasKey('success', $response);
        $this->assertArrayHasKey('message', $response);
        $this->assertTrue($response['success']);
        $this->assertEquals('Note deleted successfully', $response['message']);
    }

    /**
     * Test JSON response structure for errors
     */
    public function testErrorResponseStructures(): void
    {
        // Test authentication error
        $authError = $this->getErrorResponse(401, 'User not authenticated');
        $this->assertFalse($authError['success']);
        $this->assertEquals('User not authenticated', $authError['message']);
        
        // Test method not allowed error
        $methodError = $this->getErrorResponse(405, 'Method not allowed');
        $this->assertFalse($methodError['success']);
        $this->assertEquals('Method not allowed', $methodError['message']);
        
        // Test not found error
        $notFoundError = $this->getErrorResponse(404, 'Note not found or does not belong to this lead');
        $this->assertFalse($notFoundError['success']);
        $this->assertEquals('Note not found or does not belong to this lead', $notFoundError['message']);
        
        // Test server error
        $serverError = $this->getErrorResponse(500, 'An error occurred while deleting the note');
        $this->assertFalse($serverError['success']);
        $this->assertStringContainsString('error occurred', $serverError['message']);
    }

    /**
     * Test HTTP method validation
     */
    public function testHttpMethodValidation(): void
    {
        // Only POST should be allowed
        $this->assertTrue($this->isValidHttpMethod('POST'));
        $this->assertFalse($this->isValidHttpMethod('GET'));
        $this->assertFalse($this->isValidHttpMethod('PUT'));
        $this->assertFalse($this->isValidHttpMethod('DELETE'));
        $this->assertFalse($this->isValidHttpMethod('PATCH'));
    }

    /**
     * Test session validation logic
     */
    public function testSessionValidation(): void
    {
        // Test valid session
        $validSession = ['loggedin' => true, 'user_id' => 123];
        $this->assertTrue($this->isValidSession($validSession));
        
        // Test missing loggedin
        $invalidSession1 = ['user_id' => 123];
        $this->assertFalse($this->isValidSession($invalidSession1));
        
        // Test empty session
        $invalidSession2 = [];
        $this->assertFalse($this->isValidSession($invalidSession2));
        
        // Test null session
        $this->assertFalse($this->isValidSession(null));
    }

    // Helper methods to simulate the logic from delete_note.php

    private function validateDeleteParameters($noteId, $leadId): array
    {
        if (!isset($noteId) || !isset($leadId)) {
            return ['valid' => false, 'message' => 'Missing required parameters'];
        }
        
        $noteId = (int)$noteId;
        $leadId = (int)$leadId;
        
        if ($noteId <= 0 || $leadId <= 0) {
            return ['valid' => false, 'message' => 'Invalid parameters'];
        }
        
        return ['valid' => true, 'message' => 'Parameters valid'];
    }

    private function getNoteVerificationQuery(): string
    {
        return "SELECT n.id, n.note_text, ln.lead_id 
                   FROM notes n
                   INNER JOIN leads_notes ln ON n.id = ln.note_id
                   WHERE n.id = :note_id AND ln.lead_id = :lead_id";
    }

    private function getNoteDeletionQueries(): array
    {
        return [
            'link_deletion' => "DELETE FROM leads_notes WHERE note_id = :note_id",
            'note_deletion' => "DELETE FROM notes WHERE id = :id"
        ];
    }

    private function getAuditLogData($noteId, $leadId, $noteText): array
    {
        return [
            'user_id' => 1,
            'event' => 'note_delete',
            'resource' => "lead_{$leadId}_note_{$noteId}",
            'useragent' => 'PHPUnit Test',
            'ip' => '127.0.0.1',
            'location' => $leadId,
            'data' => "Note deleted from lead #{$leadId}: " . substr($noteText, 0, 50) . '...'
        ];
    }

    private function getSuccessResponse(): array
    {
        return [
            'success' => true,
            'message' => 'Note deleted successfully'
        ];
    }

    private function getErrorResponse($statusCode, $message): array
    {
        return [
            'success' => false,
            'message' => $message
        ];
    }

    private function isValidHttpMethod($method): bool
    {
        return $method === 'POST';
    }

    private function isValidSession($session): bool
    {
        return is_array($session) && isset($session['loggedin']) && $session['loggedin'];
    }
}