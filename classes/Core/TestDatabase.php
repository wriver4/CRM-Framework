<?php

/**
 * Test Database Connection Class
 * 
 * Extends the main Database class to provide test-specific functionality
 * without adding any overhead to production code.
 * 
 * Usage in tests:
 *   $db = new TestDatabase();
 *   $pdo = $db->testdbcrm();
 */

class TestDatabase extends Database
{
    protected $testConfig = null;
    protected $testMode = null;

    public function __construct()
    {
        // Load test configuration
        $this->loadTestConfig();
        
        // Call parent constructor but override database config after
        parent::__construct();
        
        // Override with test database configuration
        $this->loadTestDatabaseConfig();
    }
    
    /**
     * Load test configuration from config/testing.php
     */
    protected function loadTestConfig()
    {
        $configFile = __DIR__ . '/../../config/testing.php';
        
        if (!file_exists($configFile)) {
            throw new \RuntimeException("Test configuration not found: $configFile");
        }
        
        $this->testConfig = require $configFile;
        
        if (!$this->testConfig['enabled']) {
            throw new \RuntimeException("Testing mode is not enabled in config/testing.php");
        }
    }
    
    /**
     * Load test database configuration based on test mode
     */
    protected function loadTestDatabaseConfig()
    {
        $mode = getenv('TESTING_MODE_TYPE') ?: $this->testConfig['mode'];
        
        // Determine which test database config to use
        if ($mode === 'ephemeral') {
            $dbConfig = $this->testConfig['database']['ephemeral'];
        } elseif ($mode === 'persistent') {
            $dbConfig = $this->testConfig['database']['persistent'];
        } else {
            // Auto mode: use persistent for integration/feature tests, ephemeral for unit tests
            $dbConfig = $this->testConfig['database']['persistent'];
        }
        
        $this->crm_host = $dbConfig['host'];
        $this->crm_database = $dbConfig['name'];
        $this->crm_username = $dbConfig['username'];
        $this->crm_password = $dbConfig['password'];
        $this->testMode = $mode;
    }
    
    /**
     * Get test database connection
     * Overrides parent dbcrm() to ensure we're using test database
     */
    public function testdbcrm()
    {
        return $this->dbcrm();
    }
    
    /**
     * Check if database is in test mode (always true for TestDatabase)
     */
    public function isTestMode()
    {
        return true;
    }
    
    /**
     * Get current database name (useful for debugging)
     */
    public function getCurrentDatabase()
    {
        return $this->crm_database;
    }
    
    /**
     * Get test configuration
     */
    public function getTestConfig()
    {
        return $this->testConfig;
    }
    
    /**
     * Get test mode (persistent, ephemeral, auto)
     */
    public function getTestMode()
    {
        return $this->testMode;
    }
    
    /**
     * Begin transaction for test isolation
     */
    public function beginTestTransaction()
    {
        $this->dbcrm()->beginTransaction();
    }
    
    /**
     * Rollback transaction for test cleanup
     */
    public function rollbackTestTransaction()
    {
        if ($this->dbcrm()->inTransaction()) {
            $this->dbcrm()->rollBack();
        }
    }
    
    /**
     * Commit transaction (use sparingly in tests)
     */
    public function commitTestTransaction()
    {
        if ($this->dbcrm()->inTransaction()) {
            $this->dbcrm()->commit();
        }
    }
    
    /**
     * Truncate a table (useful for test cleanup)
     */
    public function truncateTable($tableName)
    {
        $this->dbcrm()->exec("TRUNCATE TABLE `$tableName`");
    }
    
    /**
     * Get table row count
     */
    public function getTableCount($tableName)
    {
        $stmt = $this->dbcrm()->query("SELECT COUNT(*) FROM `$tableName`");
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Disable foreign key checks (useful for seeding)
     */
    public function disableForeignKeyChecks()
    {
        $this->dbcrm()->exec("SET FOREIGN_KEY_CHECKS = 0");
    }
    
    /**
     * Enable foreign key checks
     */
    public function enableForeignKeyChecks()
    {
        $this->dbcrm()->exec("SET FOREIGN_KEY_CHECKS = 1");
    }
}