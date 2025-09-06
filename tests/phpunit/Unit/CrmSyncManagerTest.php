<?php

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for CrmSyncManager class
 */
class CrmSyncManagerTest extends TestCase
{
    private $syncManager;

    protected function setUp(): void
    {
        require_once __DIR__ . '/../../../classes/Models/CrmSyncManager.php';
        $this->syncManager = new CrmSyncManager();
    }

    public function testValidateSyncData()
    {
        $validData = [
            'lead_id' => 1,
            'sync_action' => 'create',
            'external_system' => 'hubspot',
            'sync_data' => json_encode(['name' => 'John Doe', 'email' => 'john@example.com'])
        ];

        $result = $this->syncManager->validateSyncData($validData);
        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);

        $invalidData = [
            'lead_id' => 'not_a_number',
            'sync_action' => 'invalid_action',
            'external_system' => 'invalid_system',
            'sync_data' => 'invalid_json'
        ];

        $result = $this->syncManager->validateSyncData($invalidData);
        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    public function testValidateSyncAction()
    {
        $validActions = ['create', 'update', 'note_add'];
        
        foreach ($validActions as $action) {
            $this->assertTrue($this->syncManager->isValidSyncAction($action));
        }

        $invalidActions = ['delete', 'invalid', '', null];
        
        foreach ($invalidActions as $action) {
            $this->assertFalse($this->syncManager->isValidSyncAction($action));
        }
    }

    public function testValidateExternalSystem()
    {
        $validSystems = ['hubspot', 'salesforce', 'mailchimp', 'custom'];
        
        foreach ($validSystems as $system) {
            $this->assertTrue($this->syncManager->isValidExternalSystem($system));
        }

        $invalidSystems = ['invalid', 'pipedrive', '', null];
        
        foreach ($invalidSystems as $system) {
            $this->assertFalse($this->syncManager->isValidExternalSystem($system));
        }
    }

    public function testValidateSyncStatus()
    {
        $validStatuses = ['pending', 'in_progress', 'completed', 'failed'];
        
        foreach ($validStatuses as $status) {
            $this->assertTrue($this->syncManager->isValidSyncStatus($status));
        }

        $invalidStatuses = ['invalid', 'cancelled', '', null];
        
        foreach ($invalidStatuses as $status) {
            $this->assertFalse($this->syncManager->isValidSyncStatus($status));
        }
    }

    public function testGenerateSyncQueueData()
    {
        $inputData = [
            'lead_id' => 1,
            'sync_action' => 'create',
            'external_system' => 'hubspot',
            'sync_data' => ['name' => 'John Doe', 'email' => 'john@example.com']
        ];

        $queueData = $this->syncManager->generateSyncQueueData($inputData);

        $this->assertIsArray($queueData);
        $this->assertEquals(1, $queueData['lead_id']);
        $this->assertEquals('create', $queueData['sync_action']);
        $this->assertEquals('hubspot', $queueData['external_system']);
        $this->assertEquals('pending', $queueData['sync_status']);
        $this->assertEquals(0, $queueData['retry_count']);
        $this->assertEquals(3, $queueData['max_retries']);
        $this->assertArrayHasKey('sync_data', $queueData);
        $this->assertArrayHasKey('created_at', $queueData);
    }

    public function testCalculateNextRetryTime()
    {
        $retryCount = 1;
        $nextRetry = $this->syncManager->calculateNextRetryTime($retryCount);
        
        $this->assertIsString($nextRetry);
        $this->assertNotEmpty($nextRetry);
        
        // Should be a valid datetime
        $this->assertNotFalse(strtotime($nextRetry));
        
        // Should be in the future
        $this->assertGreaterThan(time(), strtotime($nextRetry));
    }

    public function testRetryLogic()
    {
        // Test retry count validation
        $this->assertTrue($this->syncManager->canRetry(0, 3));
        $this->assertTrue($this->syncManager->canRetry(2, 3));
        $this->assertFalse($this->syncManager->canRetry(3, 3));
        $this->assertFalse($this->syncManager->canRetry(5, 3));

        // Test retry delay calculation
        $delay1 = $this->syncManager->getRetryDelay(1);
        $delay2 = $this->syncManager->getRetryDelay(2);
        $delay3 = $this->syncManager->getRetryDelay(3);

        $this->assertIsInt($delay1);
        $this->assertIsInt($delay2);
        $this->assertIsInt($delay3);
        
        // Delays should increase with retry count
        $this->assertLessThan($delay2, $delay1);
        $this->assertLessThan($delay3, $delay2);
    }

    public function testSyncDataSerialization()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '555-1234',
            'company' => 'Test Company'
        ];

        $serialized = $this->syncManager->serializeSyncData($data);
        $this->assertIsString($serialized);
        $this->assertJson($serialized);

        $unserialized = $this->syncManager->unserializeSyncData($serialized);
        $this->assertIsArray($unserialized);
        $this->assertEquals($data, $unserialized);
    }

    public function testErrorHandling()
    {
        // Test with invalid JSON
        $invalidJson = 'invalid json string';
        $result = $this->syncManager->unserializeSyncData($invalidJson);
        $this->assertNull($result);

        // Test with empty data
        $result = $this->syncManager->serializeSyncData([]);
        $this->assertEquals('[]', $result);

        // Test with null data
        $result = $this->syncManager->serializeSyncData(null);
        $this->assertEquals('null', $result);
    }

    public function testSyncStatusTransitions()
    {
        // Test valid status transitions
        $this->assertTrue($this->syncManager->isValidStatusTransition('pending', 'in_progress'));
        $this->assertTrue($this->syncManager->isValidStatusTransition('in_progress', 'completed'));
        $this->assertTrue($this->syncManager->isValidStatusTransition('in_progress', 'failed'));
        $this->assertTrue($this->syncManager->isValidStatusTransition('failed', 'pending')); // Retry

        // Test invalid status transitions
        $this->assertFalse($this->syncManager->isValidStatusTransition('completed', 'pending'));
        $this->assertFalse($this->syncManager->isValidStatusTransition('completed', 'in_progress'));
    }

    public function testExternalIdValidation()
    {
        $validIds = ['12345', 'abc-123-def', 'contact_001'];
        
        foreach ($validIds as $id) {
            $this->assertTrue($this->syncManager->isValidExternalId($id));
        }

        $invalidIds = ['', null, str_repeat('a', 256)]; // Too long
        
        foreach ($invalidIds as $id) {
            $this->assertFalse($this->syncManager->isValidExternalId($id));
        }
    }

    public function testSyncPriority()
    {
        // Test priority assignment based on action
        $this->assertEquals(1, $this->syncManager->getSyncPriority('create'));
        $this->assertEquals(2, $this->syncManager->getSyncPriority('update'));
        $this->assertEquals(3, $this->syncManager->getSyncPriority('note_add'));
        $this->assertEquals(999, $this->syncManager->getSyncPriority('invalid'));
    }

    public function testBatchProcessingValidation()
    {
        $validBatchSize = 50;
        $this->assertTrue($this->syncManager->isValidBatchSize($validBatchSize));

        $invalidBatchSizes = [0, -1, 1001];
        
        foreach ($invalidBatchSizes as $size) {
            $this->assertFalse($this->syncManager->isValidBatchSize($size));
        }
    }

    public function testSyncMetrics()
    {
        $metrics = $this->syncManager->initializeSyncMetrics();
        
        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('total_processed', $metrics);
        $this->assertArrayHasKey('successful', $metrics);
        $this->assertArrayHasKey('failed', $metrics);
        $this->assertArrayHasKey('start_time', $metrics);
        
        $this->assertEquals(0, $metrics['total_processed']);
        $this->assertEquals(0, $metrics['successful']);
        $this->assertEquals(0, $metrics['failed']);
    }
}