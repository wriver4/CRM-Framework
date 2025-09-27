<?php

namespace Tests\Helpers;

use Tests\Fixtures\LeadsTestData;

/**
 * Helper utilities for Leads module testing
 * 
 * Provides common functionality for:
 * - Test data creation and cleanup
 * - Database state management
 * - Assertion helpers
 * - Mock object creation
 */
class LeadsTestHelper
{
    private static $createdLeadIds = [];
    private static $leads = null;

    /**
     * Get Leads instance
     */
    public static function getLeadsInstance(): \Leads
    {
        if (self::$leads === null) {
            self::$leads = new \Leads();
        }
        return self::$leads;
    }

    /**
     * Create a test lead and track it for cleanup
     */
    public static function createTestLead(array $data = null): int
    {
        $leads = self::getLeadsInstance();
        $leadData = $data ?? LeadsTestData::getValidLeadData();
        
        $leadId = $leads->create_lead($leadData);
        
        if ($leadId) {
            self::$createdLeadIds[] = $leadId;
        }
        
        return $leadId;
    }

    /**
     * Create multiple test leads
     */
    public static function createMultipleTestLeads(int $count = 3): array
    {
        $leadIds = [];
        $testData = LeadsTestData::getMultipleValidLeads();
        
        for ($i = 0; $i < min($count, count($testData)); $i++) {
            $leadId = self::createTestLead($testData[$i]);
            if ($leadId) {
                $leadIds[] = $leadId;
            }
        }
        
        return $leadIds;
    }

    /**
     * Create test leads with specific stages
     */
    public static function createTestLeadsWithStages(array $stages): array
    {
        $leadIds = [];
        $baseData = LeadsTestData::getValidLeadData();
        
        foreach ($stages as $index => $stage) {
            $leadData = array_merge($baseData, [
                'stage' => $stage,
                'email' => "stage{$stage}_{$index}@example.com",
                'first_name' => "Stage{$stage}",
                'family_name' => "Test{$index}"
            ]);
            
            $leadId = self::createTestLead($leadData);
            if ($leadId) {
                $leadIds[] = $leadId;
            }
        }
        
        return $leadIds;
    }

    /**
     * Create international test leads
     */
    public static function createInternationalTestLeads(): array
    {
        $leadIds = [];
        $internationalData = LeadsTestData::getInternationalLeads();
        
        foreach ($internationalData as $leadData) {
            $leadId = self::createTestLead($leadData);
            if ($leadId) {
                $leadIds[] = $leadId;
            }
        }
        
        return $leadIds;
    }

    /**
     * Update a test lead
     */
    public static function updateTestLead(int $leadId, array $updateData): bool
    {
        $leads = self::getLeadsInstance();
        return $leads->update_lead($leadId, $updateData);
    }

    /**
     * Get a test lead by ID
     */
    public static function getTestLead(int $leadId): ?array
    {
        $leads = self::getLeadsInstance();
        $result = $leads->get_lead_by_id($leadId);
        
        return $result && !empty($result[0]) ? $result[0] : null;
    }

    /**
     * Delete a specific test lead
     */
    public static function deleteTestLead(int $leadId): bool
    {
        $leads = self::getLeadsInstance();
        $result = $leads->delete_lead($leadId);
        
        // Remove from tracking array
        $key = array_search($leadId, self::$createdLeadIds);
        if ($key !== false) {
            unset(self::$createdLeadIds[$key]);
        }
        
        return $result;
    }

    /**
     * Clean up all created test leads
     */
    public static function cleanupTestLeads(): void
    {
        $leads = self::getLeadsInstance();
        
        foreach (self::$createdLeadIds as $leadId) {
            try {
                $leads->delete_lead($leadId);
            } catch (\Exception $e) {
                // Ignore cleanup errors
                error_log("Failed to cleanup test lead {$leadId}: " . $e->getMessage());
            }
        }
        
        self::$createdLeadIds = [];
    }

    /**
     * Get count of created test leads
     */
    public static function getCreatedLeadCount(): int
    {
        return count(self::$createdLeadIds);
    }

    /**
     * Get all created lead IDs
     */
    public static function getCreatedLeadIds(): array
    {
        return self::$createdLeadIds;
    }

    /**
     * Assert lead data matches expected values
     */
    public static function assertLeadDataMatches(\PHPUnit\Framework\TestCase $testCase, array $expected, array $actual): void
    {
        foreach ($expected as $key => $expectedValue) {
            $testCase->assertArrayHasKey($key, $actual, "Lead should have key: {$key}");
            $testCase->assertEquals($expectedValue, $actual[$key], "Lead {$key} should match expected value");
        }
    }

    /**
     * Assert lead exists in database
     */
    public static function assertLeadExists(\PHPUnit\Framework\TestCase $testCase, int $leadId): void
    {
        $lead = self::getTestLead($leadId);
        $testCase->assertNotNull($lead, "Lead {$leadId} should exist in database");
    }

    /**
     * Assert lead does not exist in database
     */
    public static function assertLeadNotExists(\PHPUnit\Framework\TestCase $testCase, int $leadId): void
    {
        $lead = self::getTestLead($leadId);
        $testCase->assertNull($lead, "Lead {$leadId} should not exist in database");
    }

    /**
     * Assert stage change notification should be triggered
     */
    public static function assertStageChangeNotification(\PHPUnit\Framework\TestCase $testCase, int $oldStage, int $newStage, bool $shouldTrigger): void
    {
        $triggerStages = [40, 50, 140]; // Referral, Prospect, Closed Lost
        
        $actualShouldTrigger = ($oldStage != $newStage && in_array($newStage, $triggerStages));
        
        $testCase->assertEquals($shouldTrigger, $actualShouldTrigger, 
            "Stage change from {$oldStage} to {$newStage} notification trigger expectation");
    }

    /**
     * Assert phone number formatting
     */
    public static function assertPhoneFormatting(\PHPUnit\Framework\TestCase $testCase, string $input, string $country, string $expected): void
    {
        $helpers = new \Helpers();
        $actual = $helpers->format_phone_display($input, $country);
        
        $testCase->assertEquals($expected, $actual, 
            "Phone {$input} in country {$country} should format to {$expected}");
    }

    /**
     * Create mock language array
     */
    public static function getMockLanguageArray(string $language = 'en'): array
    {
        $languageData = LeadsTestData::getLanguageTestData();
        return $languageData[$language] ?? $languageData['en'];
    }

    /**
     * Create mock HTTP response
     */
    public static function createMockHttpResponse(int $statusCode = 200, string $body = '', array $headers = []): array
    {
        return [
            'status_code' => $statusCode,
            'body' => $body,
            'headers' => $headers
        ];
    }

    /**
     * Generate unique email for testing
     */
    public static function generateUniqueEmail(string $prefix = 'test'): string
    {
        return $prefix . '_' . uniqid() . '@example.com';
    }

    /**
     * Generate unique phone number for testing
     */
    public static function generateUniquePhone(): string
    {
        return '555-' . rand(100, 999) . '-' . rand(1000, 9999);
    }

    /**
     * Wait for database operations to complete (if needed)
     */
    public static function waitForDatabase(float $seconds = 0.1): void
    {
        usleep($seconds * 1000000);
    }

    /**
     * Check if running in CI environment
     */
    public static function isCI(): bool
    {
        return !empty($_ENV['CI']) || !empty($_ENV['CONTINUOUS_INTEGRATION']);
    }

    /**
     * Skip test if not in appropriate environment
     */
    public static function skipIfNotAppropriateEnvironment(\PHPUnit\Framework\TestCase $testCase, string $reason = 'Test requires specific environment'): void
    {
        if (self::isCI() && !self::isDatabaseAvailable()) {
            $testCase->markTestSkipped($reason);
        }
    }

    /**
     * Check if database is available for testing
     */
    public static function isDatabaseAvailable(): bool
    {
        try {
            $leads = self::getLeadsInstance();
            // Try a simple database operation
            $leads->get_lead_stage_array();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get test database connection info
     */
    public static function getTestDatabaseInfo(): array
    {
        return [
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'database' => $_ENV['DB_NAME'] ?? 'test_democrm',
            'username' => $_ENV['DB_USER'] ?? 'test_user',
            'password' => $_ENV['DB_PASS'] ?? 'test_pass'
        ];
    }

    /**
     * Create test user for lead assignments
     */
    public static function createTestUser(): int
    {
        // This would create a test user if needed
        // For now, return a default user ID
        return 1;
    }

    /**
     * Measure execution time of a function
     */
    public static function measureExecutionTime(callable $function): array
    {
        $startTime = microtime(true);
        $result = $function();
        $endTime = microtime(true);
        
        return [
            'result' => $result,
            'execution_time' => $endTime - $startTime
        ];
    }

    /**
     * Generate performance test data
     */
    public static function generatePerformanceTestData(int $count): array
    {
        return LeadsTestData::getPerformanceTestData($count);
    }

    /**
     * Validate lead data structure
     */
    public static function validateLeadDataStructure(\PHPUnit\Framework\TestCase $testCase, array $leadData): void
    {
        $requiredFields = ['id', 'first_name', 'family_name', 'email', 'stage', 'created_at', 'updated_at'];
        
        foreach ($requiredFields as $field) {
            $testCase->assertArrayHasKey($field, $leadData, "Lead data should have {$field} field");
        }
        
        // Validate data types
        $testCase->assertIsNumeric($leadData['id'], 'Lead ID should be numeric');
        $testCase->assertIsString($leadData['first_name'], 'First name should be string');
        $testCase->assertIsString($leadData['family_name'], 'Family name should be string');
        $testCase->assertIsString($leadData['email'], 'Email should be string');
        $testCase->assertIsNumeric($leadData['stage'], 'Stage should be numeric');
    }

    /**
     * Reset test environment
     */
    public static function resetTestEnvironment(): void
    {
        self::cleanupTestLeads();
        self::$leads = null;
    }

    /**
     * Get test statistics
     */
    public static function getTestStatistics(): array
    {
        return [
            'created_leads' => count(self::$createdLeadIds),
            'database_available' => self::isDatabaseAvailable(),
            'is_ci' => self::isCI(),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ];
    }
}