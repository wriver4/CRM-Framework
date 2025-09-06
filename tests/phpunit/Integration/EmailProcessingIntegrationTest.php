<?php

use PHPUnit\Framework\TestCase;

/**
 * Integration tests for Email Processing System
 * Tests the interaction between EmailFormProcessor, EmailAccountManager, and CrmSyncManager
 */
class EmailProcessingIntegrationTest extends TestCase
{
    private $database;
    private $processor;
    private $accountManager;
    private $syncManager;

    protected function setUp(): void
    {
        // Initialize database connection
        require_once __DIR__ . '/../../../config/system.php';
        require_once __DIR__ . '/../../../classes/Core/Database.php';
        
        $this->database = new Database();
        
        // Initialize processors
        require_once __DIR__ . '/../../../classes/Models/EmailFormProcessor.php';
        require_once __DIR__ . '/../../../classes/Models/EmailAccountManager.php';
        require_once __DIR__ . '/../../../classes/Models/CrmSyncManager.php';
        
        $this->processor = new EmailFormProcessor();
        $this->accountManager = new EmailAccountManager();
        $this->syncManager = new CrmSyncManager();
    }

    public function testDatabaseTablesExist()
    {
        $pdo = $this->database->dbcrm();
        
        // Check if email processing tables exist
        $tables = ['email_form_processing', 'crm_sync_queue', 'email_accounts_config'];
        
        foreach ($tables as $table) {
            $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            $result = $stmt->fetch();
            
            $this->assertNotFalse($result, "Table {$table} should exist");
        }
    }

    public function testEmailAccountConfigCRUD()
    {
        $pdo = $this->database->dbcrm();
        
        // Test data
        $testAccount = [
            'email_address' => 'test@integration.com',
            'form_type' => 'estimate',
            'imap_host' => 'mail.test.com',
            'imap_port' => 993,
            'imap_encryption' => 'ssl',
            'username' => 'test@integration.com',
            'password' => 'encrypted_test_password',
            'is_active' => 1
        ];

        try {
            // CREATE
            $stmt = $pdo->prepare("INSERT INTO email_accounts_config 
                (email_address, form_type, imap_host, imap_port, imap_encryption, username, password, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->bindValue(1, $testAccount['email_address'], PDO::PARAM_STR);
            $stmt->bindValue(2, $testAccount['form_type'], PDO::PARAM_STR);
            $stmt->bindValue(3, $testAccount['imap_host'], PDO::PARAM_STR);
            $stmt->bindValue(4, $testAccount['imap_port'], PDO::PARAM_INT);
            $stmt->bindValue(5, $testAccount['imap_encryption'], PDO::PARAM_STR);
            $stmt->bindValue(6, $testAccount['username'], PDO::PARAM_STR);
            $stmt->bindValue(7, $testAccount['password'], PDO::PARAM_STR);
            $stmt->bindValue(8, $testAccount['is_active'], PDO::PARAM_INT);
            
            $result = $stmt->execute();
            $this->assertTrue($result, "Should be able to insert email account config");
            
            $accountId = $pdo->lastInsertId();
            $this->assertGreaterThan(0, $accountId);

            // READ
            $stmt = $pdo->prepare("SELECT * FROM email_accounts_config WHERE id = ?");
            $stmt->bindValue(1, (int)$accountId, PDO::PARAM_INT);
            $stmt->execute();
            $account = $stmt->fetch();
            
            $this->assertNotFalse($account);
            $this->assertEquals($testAccount['email_address'], $account['email_address']);
            $this->assertEquals($testAccount['form_type'], $account['form_type']);

            // UPDATE
            $stmt = $pdo->prepare("UPDATE email_accounts_config SET is_active = ? WHERE id = ?");
            $stmt->bindValue(1, 0, PDO::PARAM_INT);
            $stmt->bindValue(2, (int)$accountId, PDO::PARAM_INT);
            $result = $stmt->execute();
            $this->assertTrue($result);

            // DELETE
            $stmt = $pdo->prepare("DELETE FROM email_accounts_config WHERE id = ?");
            $stmt->bindValue(1, (int)$accountId, PDO::PARAM_INT);
            $result = $stmt->execute();
            $this->assertTrue($result);

        } catch (Exception $e) {
            $this->fail("Database operations failed: " . $e->getMessage());
        }
    }

    public function testEmailProcessingLogCRUD()
    {
        $pdo = $this->database->dbcrm();
        
        $testLog = [
            'email_account' => 'test@integration.com',
            'form_type' => 'estimate',
            'message_id' => 'test-message-123',
            'subject' => 'Test Email Processing',
            'sender_email' => 'sender@test.com',
            'received_at' => date('Y-m-d H:i:s'),
            'processing_status' => 'success',
            'lead_id' => null,
            'raw_email_content' => 'Test email content',
            'parsed_form_data' => json_encode(['name' => 'Test User']),
            'error_message' => null
        ];

        try {
            // CREATE
            $stmt = $pdo->prepare("INSERT INTO email_form_processing 
                (email_account, form_type, message_id, subject, sender_email, received_at, 
                 processing_status, lead_id, raw_email_content, parsed_form_data, error_message) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->bindValue(1, $testLog['email_account'], PDO::PARAM_STR);
            $stmt->bindValue(2, $testLog['form_type'], PDO::PARAM_STR);
            $stmt->bindValue(3, $testLog['message_id'], PDO::PARAM_STR);
            $stmt->bindValue(4, $testLog['subject'], PDO::PARAM_STR);
            $stmt->bindValue(5, $testLog['sender_email'], PDO::PARAM_STR);
            $stmt->bindValue(6, $testLog['received_at'], PDO::PARAM_STR);
            $stmt->bindValue(7, $testLog['processing_status'], PDO::PARAM_STR);
            $stmt->bindValue(8, $testLog['lead_id'], PDO::PARAM_INT);
            $stmt->bindValue(9, $testLog['raw_email_content'], PDO::PARAM_STR);
            $stmt->bindValue(10, $testLog['parsed_form_data'], PDO::PARAM_STR);
            $stmt->bindValue(11, $testLog['error_message'], PDO::PARAM_STR);
            
            $result = $stmt->execute();
            $this->assertTrue($result, "Should be able to insert processing log");
            
            $logId = $pdo->lastInsertId();
            $this->assertGreaterThan(0, $logId);

            // READ
            $stmt = $pdo->prepare("SELECT * FROM email_form_processing WHERE id = ?");
            $stmt->bindValue(1, (int)$logId, PDO::PARAM_INT);
            $stmt->execute();
            $log = $stmt->fetch();
            
            $this->assertNotFalse($log);
            $this->assertEquals($testLog['message_id'], $log['message_id']);
            $this->assertEquals($testLog['processing_status'], $log['processing_status']);

            // DELETE
            $stmt = $pdo->prepare("DELETE FROM email_form_processing WHERE id = ?");
            $stmt->bindValue(1, (int)$logId, PDO::PARAM_INT);
            $result = $stmt->execute();
            $this->assertTrue($result);

        } catch (Exception $e) {
            $this->fail("Processing log operations failed: " . $e->getMessage());
        }
    }

    public function testCrmSyncQueueCRUD()
    {
        $pdo = $this->database->dbcrm();
        
        // First, we need a test lead
        $testLead = [
            'full_name' => 'Integration Test Lead',
            'email' => 'integration@test.com',
            'phone' => '555-TEST',
            'lead_source' => 'integration_test'
        ];

        try {
            // Create test lead
            $stmt = $pdo->prepare("INSERT INTO leads (full_name, email, phone, lead_source) VALUES (?, ?, ?, ?)");
            $stmt->bindValue(1, $testLead['full_name'], PDO::PARAM_STR);
            $stmt->bindValue(2, $testLead['email'], PDO::PARAM_STR);
            $stmt->bindValue(3, $testLead['phone'], PDO::PARAM_STR);
            $stmt->bindValue(4, $testLead['lead_source'], PDO::PARAM_STR);
            $stmt->execute();
            
            $leadId = $pdo->lastInsertId();
            $this->assertGreaterThan(0, $leadId);

            // Test sync queue
            $testSync = [
                'lead_id' => $leadId,
                'sync_action' => 'create',
                'external_system' => 'hubspot',
                'sync_status' => 'pending',
                'retry_count' => 0,
                'max_retries' => 3,
                'sync_data' => json_encode(['name' => 'Integration Test Lead'])
            ];

            // CREATE sync entry
            $stmt = $pdo->prepare("INSERT INTO crm_sync_queue 
                (lead_id, sync_action, external_system, sync_status, retry_count, max_retries, sync_data) 
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->bindValue(1, (int)$testSync['lead_id'], PDO::PARAM_INT);
            $stmt->bindValue(2, $testSync['sync_action'], PDO::PARAM_STR);
            $stmt->bindValue(3, $testSync['external_system'], PDO::PARAM_STR);
            $stmt->bindValue(4, $testSync['sync_status'], PDO::PARAM_STR);
            $stmt->bindValue(5, $testSync['retry_count'], PDO::PARAM_INT);
            $stmt->bindValue(6, $testSync['max_retries'], PDO::PARAM_INT);
            $stmt->bindValue(7, $testSync['sync_data'], PDO::PARAM_STR);
            
            $result = $stmt->execute();
            $this->assertTrue($result, "Should be able to insert sync queue entry");
            
            $syncId = $pdo->lastInsertId();
            $this->assertGreaterThan(0, $syncId);

            // READ
            $stmt = $pdo->prepare("SELECT * FROM crm_sync_queue WHERE id = ?");
            $stmt->bindValue(1, (int)$syncId, PDO::PARAM_INT);
            $stmt->execute();
            $sync = $stmt->fetch();
            
            $this->assertNotFalse($sync);
            $this->assertEquals($testSync['sync_action'], $sync['sync_action']);
            $this->assertEquals($testSync['external_system'], $sync['external_system']);

            // UPDATE
            $stmt = $pdo->prepare("UPDATE crm_sync_queue SET sync_status = ? WHERE id = ?");
            $stmt->bindValue(1, 'completed', PDO::PARAM_STR);
            $stmt->bindValue(2, (int)$syncId, PDO::PARAM_INT);
            $result = $stmt->execute();
            $this->assertTrue($result);

            // Cleanup
            $stmt = $pdo->prepare("DELETE FROM crm_sync_queue WHERE id = ?");
            $stmt->bindValue(1, (int)$syncId, PDO::PARAM_INT);
            $stmt->execute();

            $stmt = $pdo->prepare("DELETE FROM leads WHERE id = ?");
            $stmt->bindValue(1, (int)$leadId, PDO::PARAM_INT);
            $stmt->execute();

        } catch (Exception $e) {
            $this->fail("Sync queue operations failed: " . $e->getMessage());
        }
    }

    public function testFullEmailProcessingWorkflow()
    {
        // This test simulates the complete email processing workflow
        $emailData = [
            'message_id' => 'integration-test-' . time(),
            'subject' => 'Integration Test Estimate Request',
            'sender_email' => 'integration@test.com',
            'received_at' => date('Y-m-d H:i:s'),
            'content' => "Name: Integration Test User\nEmail: integration@test.com\nPhone: 555-TEST\nService: Test Service\nMessage: This is an integration test"
        ];

        try {
            // Step 1: Parse form data
            $formData = $this->processor->parseFormData($emailData['content'], 'estimate');
            $this->assertIsArray($formData);
            $this->assertArrayHasKey('full_name', $formData);

            // Step 2: Validate form data
            $validation = $this->processor->validateFormData($formData);
            $this->assertTrue($validation['valid']);

            // Step 3: Check for duplicates
            $isDuplicate = $this->processor->isDuplicateEmail($emailData['message_id'], $emailData['sender_email']);
            $this->assertFalse($isDuplicate); // Should not be duplicate for new message

            // Step 4: Generate lead data
            $leadData = $this->processor->generateLeadData($formData, 'estimate');
            $this->assertIsArray($leadData);
            $this->assertArrayHasKey('full_name', $leadData);
            $this->assertArrayHasKey('email', $leadData);

            // This test validates the workflow structure without actually creating database records
            $this->assertTrue(true, "Full workflow validation completed successfully");

        } catch (Exception $e) {
            $this->fail("Full workflow test failed: " . $e->getMessage());
        }
    }

    public function testApiEndpointStructure()
    {
        // Test that the API endpoint file exists and has proper structure
        $apiFile = __DIR__ . '/../../../public_html/api/email_forms.php';
        $this->assertFileExists($apiFile, "API endpoint should exist");

        $content = file_get_contents($apiFile);
        $this->assertStringContains('<?php', $content);
        $this->assertStringContains('email_forms', $content);
    }

    public function testWebInterfaceFiles()
    {
        // Test that web interface files exist
        $webFiles = [
            'leads/email_import.php',
            'admin/email/processing_log.php',
            'admin/email/accounts_config.php',
            'admin/email/sync_queue.php',
            'admin/email/system_status.php'
        ];

        foreach ($webFiles as $file) {
            $fullPath = __DIR__ . '/../../../public_html/' . $file;
            $this->assertFileExists($fullPath, "Web interface file {$file} should exist");
        }
    }

    public function testCronScriptExists()
    {
        $cronScript = __DIR__ . '/../../../scripts/email_cron.php';
        $this->assertFileExists($cronScript, "Cron script should exist");

        $content = file_get_contents($cronScript);
        $this->assertStringContains('<?php', $content);
        $this->assertStringContains('email', $content);
    }

    protected function tearDown(): void
    {
        // Clean up any test data that might have been created
        try {
            $pdo = $this->database->dbcrm();
            
            // Clean up test records
            $stmt = $pdo->prepare("DELETE FROM email_form_processing WHERE sender_email LIKE '%integration%' OR sender_email LIKE '%test%'");
            $stmt->execute();
            
            $stmt = $pdo->prepare("DELETE FROM email_accounts_config WHERE email_address LIKE '%integration%' OR email_address LIKE '%test%'");
            $stmt->execute();
            
            $stmt = $pdo->prepare("DELETE FROM leads WHERE email LIKE '%integration%' OR email LIKE '%test%'");
            $stmt->execute();
            
        } catch (Exception $e) {
            // Ignore cleanup errors
        }
    }
}