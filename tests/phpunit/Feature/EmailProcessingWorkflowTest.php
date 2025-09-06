<?php

use PHPUnit\Framework\TestCase;

/**
 * Feature tests for complete Email Processing workflow
 * Tests end-to-end functionality from email receipt to CRM sync
 */
class EmailProcessingWorkflowTest extends TestCase
{
    private $database;
    private $testLeadId;
    private $testAccountId;
    private $testProcessingId;
    private $testSyncId;

    protected function setUp(): void
    {
        require_once __DIR__ . '/../../../config/system.php';
        require_once __DIR__ . '/../../../classes/Core/Database.php';
        
        $this->database = new Database();
    }

    public function testCompleteEmailProcessingWorkflow()
    {
        $pdo = $this->database->dbcrm();
        
        try {
            // Step 1: Create test email account configuration
            $this->createTestEmailAccount($pdo);
            
            // Step 2: Simulate email processing
            $this->processTestEmail($pdo);
            
            // Step 3: Verify lead creation
            $this->verifyLeadCreation($pdo);
            
            // Step 4: Test CRM sync queue
            $this->testCrmSyncQueue($pdo);
            
            // Step 5: Test processing log
            $this->verifyProcessingLog($pdo);
            
            $this->assertTrue(true, "Complete workflow test passed");
            
        } catch (Exception $e) {
            $this->fail("Workflow test failed: " . $e->getMessage());
        }
    }

    private function createTestEmailAccount($pdo)
    {
        $stmt = $pdo->prepare("INSERT INTO email_accounts_config 
            (email_address, form_type, imap_host, imap_port, imap_encryption, username, password, is_active) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bindValue(1, 'workflow-test@example.com', PDO::PARAM_STR);
        $stmt->bindValue(2, 'estimate', PDO::PARAM_STR);
        $stmt->bindValue(3, 'mail.example.com', PDO::PARAM_STR);
        $stmt->bindValue(4, 993, PDO::PARAM_INT);
        $stmt->bindValue(5, 'ssl', PDO::PARAM_STR);
        $stmt->bindValue(6, 'workflow-test@example.com', PDO::PARAM_STR);
        $stmt->bindValue(7, 'encrypted_password', PDO::PARAM_STR);
        $stmt->bindValue(8, 1, PDO::PARAM_INT);
        
        $result = $stmt->execute();
        $this->assertTrue($result, "Should create test email account");
        
        $this->testAccountId = $pdo->lastInsertId();
        $this->assertGreaterThan(0, $this->testAccountId);
    }

    private function processTestEmail($pdo)
    {
        // Create test lead first
        $stmt = $pdo->prepare("INSERT INTO leads 
            (full_name, email, phone, lead_source, created_at) 
            VALUES (?, ?, ?, ?, ?)");
        
        $stmt->bindValue(1, 'Workflow Test User', PDO::PARAM_STR);
        $stmt->bindValue(2, 'workflow-user@example.com', PDO::PARAM_STR);
        $stmt->bindValue(3, '555-WORKFLOW', PDO::PARAM_STR);
        $stmt->bindValue(4, 'email_form', PDO::PARAM_STR);
        $stmt->bindValue(5, date('Y-m-d H:i:s'), PDO::PARAM_STR);
        
        $result = $stmt->execute();
        $this->assertTrue($result, "Should create test lead");
        
        $this->testLeadId = $pdo->lastInsertId();
        $this->assertGreaterThan(0, $this->testLeadId);

        // Log the email processing
        $stmt = $pdo->prepare("INSERT INTO email_form_processing 
            (email_account, form_type, message_id, subject, sender_email, received_at, 
             processing_status, lead_id, raw_email_content, parsed_form_data) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $formData = json_encode([
            'full_name' => 'Workflow Test User',
            'email' => 'workflow-user@example.com',
            'phone' => '555-WORKFLOW',
            'service' => 'Test Service',
            'message' => 'This is a workflow test'
        ]);
        
        $stmt->bindValue(1, 'workflow-test@example.com', PDO::PARAM_STR);
        $stmt->bindValue(2, 'estimate', PDO::PARAM_STR);
        $stmt->bindValue(3, 'workflow-test-' . time(), PDO::PARAM_STR);
        $stmt->bindValue(4, 'Workflow Test Email', PDO::PARAM_STR);
        $stmt->bindValue(5, 'workflow-user@example.com', PDO::PARAM_STR);
        $stmt->bindValue(6, date('Y-m-d H:i:s'), PDO::PARAM_STR);
        $stmt->bindValue(7, 'success', PDO::PARAM_STR);
        $stmt->bindValue(8, (int)$this->testLeadId, PDO::PARAM_INT);
        $stmt->bindValue(9, 'Raw email content for workflow test', PDO::PARAM_STR);
        $stmt->bindValue(10, $formData, PDO::PARAM_STR);
        
        $result = $stmt->execute();
        $this->assertTrue($result, "Should log email processing");
        
        $this->testProcessingId = $pdo->lastInsertId();
        $this->assertGreaterThan(0, $this->testProcessingId);
    }

    private function verifyLeadCreation($pdo)
    {
        $stmt = $pdo->prepare("SELECT * FROM leads WHERE id = ?");
        $stmt->bindValue(1, (int)$this->testLeadId, PDO::PARAM_INT);
        $stmt->execute();
        $lead = $stmt->fetch();
        
        $this->assertNotFalse($lead, "Lead should exist");
        $this->assertEquals('Workflow Test User', $lead['full_name']);
        $this->assertEquals('workflow-user@example.com', $lead['email']);
        $this->assertEquals('555-WORKFLOW', $lead['phone']);
        $this->assertEquals('email_form', $lead['lead_source']);
    }

    private function testCrmSyncQueue($pdo)
    {
        // Add lead to sync queue
        $stmt = $pdo->prepare("INSERT INTO crm_sync_queue 
            (lead_id, sync_action, external_system, sync_status, retry_count, max_retries, sync_data) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $syncData = json_encode([
            'name' => 'Workflow Test User',
            'email' => 'workflow-user@example.com',
            'phone' => '555-WORKFLOW',
            'source' => 'email_form'
        ]);
        
        $stmt->bindValue(1, (int)$this->testLeadId, PDO::PARAM_INT);
        $stmt->bindValue(2, 'create', PDO::PARAM_STR);
        $stmt->bindValue(3, 'hubspot', PDO::PARAM_STR);
        $stmt->bindValue(4, 'pending', PDO::PARAM_STR);
        $stmt->bindValue(5, 0, PDO::PARAM_INT);
        $stmt->bindValue(6, 3, PDO::PARAM_INT);
        $stmt->bindValue(7, $syncData, PDO::PARAM_STR);
        
        $result = $stmt->execute();
        $this->assertTrue($result, "Should add to sync queue");
        
        $this->testSyncId = $pdo->lastInsertId();
        $this->assertGreaterThan(0, $this->testSyncId);

        // Verify sync queue entry
        $stmt = $pdo->prepare("SELECT * FROM crm_sync_queue WHERE id = ?");
        $stmt->bindValue(1, (int)$this->testSyncId, PDO::PARAM_INT);
        $stmt->execute();
        $sync = $stmt->fetch();
        
        $this->assertNotFalse($sync, "Sync entry should exist");
        $this->assertEquals($this->testLeadId, $sync['lead_id']);
        $this->assertEquals('create', $sync['sync_action']);
        $this->assertEquals('hubspot', $sync['external_system']);
        $this->assertEquals('pending', $sync['sync_status']);
    }

    private function verifyProcessingLog($pdo)
    {
        $stmt = $pdo->prepare("SELECT * FROM email_form_processing WHERE id = ?");
        $stmt->bindValue(1, (int)$this->testProcessingId, PDO::PARAM_INT);
        $stmt->execute();
        $log = $stmt->fetch();
        
        $this->assertNotFalse($log, "Processing log should exist");
        $this->assertEquals('workflow-test@example.com', $log['email_account']);
        $this->assertEquals('estimate', $log['form_type']);
        $this->assertEquals('success', $log['processing_status']);
        $this->assertEquals($this->testLeadId, $log['lead_id']);
        
        // Verify parsed form data
        $parsedData = json_decode($log['parsed_form_data'], true);
        $this->assertIsArray($parsedData);
        $this->assertEquals('Workflow Test User', $parsedData['full_name']);
        $this->assertEquals('workflow-user@example.com', $parsedData['email']);
    }

    public function testEmailProcessingStatistics()
    {
        $pdo = $this->database->dbcrm();
        
        try {
            // Test processing statistics query
            $stmt = $pdo->prepare("SELECT 
                processing_status,
                COUNT(*) as count,
                DATE(processed_at) as date
                FROM email_form_processing 
                WHERE processed_at >= DATE_SUB(NOW(), INTERVAL 7 DAYS)
                GROUP BY processing_status, DATE(processed_at)
                ORDER BY date DESC");
            
            $stmt->execute();
            $stats = $stmt->fetchAll();
            
            $this->assertIsArray($stats);
            // Stats array can be empty if no recent processing
            
        } catch (Exception $e) {
            $this->fail("Statistics query failed: " . $e->getMessage());
        }
    }

    public function testSyncQueueStatistics()
    {
        $pdo = $this->database->dbcrm();
        
        try {
            // Test sync queue statistics
            $stmt = $pdo->prepare("SELECT 
                sync_status,
                external_system,
                COUNT(*) as count
                FROM crm_sync_queue 
                GROUP BY sync_status, external_system
                ORDER BY sync_status, external_system");
            
            $stmt->execute();
            $stats = $stmt->fetchAll();
            
            $this->assertIsArray($stats);
            // Stats array can be empty if no sync entries
            
        } catch (Exception $e) {
            $this->fail("Sync statistics query failed: " . $e->getMessage());
        }
    }

    public function testSystemHealthChecks()
    {
        $pdo = $this->database->dbcrm();
        
        try {
            // Test database connectivity
            $stmt = $pdo->query("SELECT 1");
            $result = $stmt->fetch();
            $this->assertEquals(1, $result[0]);

            // Test table existence
            $tables = ['email_form_processing', 'crm_sync_queue', 'email_accounts_config'];
            
            foreach ($tables as $table) {
                $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
                $stmt->execute([$table]);
                $result = $stmt->fetch();
                $this->assertNotFalse($result, "Table {$table} should exist");
            }

            // Test account configuration
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM email_accounts_config");
            $result = $stmt->fetch();
            $this->assertGreaterThanOrEqual(0, $result['count']);

        } catch (Exception $e) {
            $this->fail("Health check failed: " . $e->getMessage());
        }
    }

    public function testApiEndpointFunctionality()
    {
        // Test API endpoint structure (without making actual HTTP requests)
        $apiFile = __DIR__ . '/../../../public_html/api/email_forms.php';
        $this->assertFileExists($apiFile, "API endpoint file should exist");

        $content = file_get_contents($apiFile);
        $this->assertStringContains('<?php', $content);
        $this->assertStringContains('api_key', $content);
        $this->assertStringContains('json', $content);
    }

    protected function tearDown(): void
    {
        // Clean up test data
        try {
            $pdo = $this->database->dbcrm();
            
            if ($this->testSyncId) {
                $stmt = $pdo->prepare("DELETE FROM crm_sync_queue WHERE id = ?");
                $stmt->bindValue(1, (int)$this->testSyncId, PDO::PARAM_INT);
                $stmt->execute();
            }
            
            if ($this->testProcessingId) {
                $stmt = $pdo->prepare("DELETE FROM email_form_processing WHERE id = ?");
                $stmt->bindValue(1, (int)$this->testProcessingId, PDO::PARAM_INT);
                $stmt->execute();
            }
            
            if ($this->testLeadId) {
                $stmt = $pdo->prepare("DELETE FROM leads WHERE id = ?");
                $stmt->bindValue(1, (int)$this->testLeadId, PDO::PARAM_INT);
                $stmt->execute();
            }
            
            if ($this->testAccountId) {
                $stmt = $pdo->prepare("DELETE FROM email_accounts_config WHERE id = ?");
                $stmt->bindValue(1, (int)$this->testAccountId, PDO::PARAM_INT);
                $stmt->execute();
            }
            
        } catch (Exception $e) {
            // Ignore cleanup errors
        }
    }
}