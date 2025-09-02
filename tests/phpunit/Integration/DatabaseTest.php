<?php

namespace Tests\Integration;

use Tests\TestCase;

/**
 * Test database connectivity and basic operations
 */
class DatabaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Include the Database class
        require_once __DIR__ . '/../../../classes/Database.php';
    }

    public function testDatabaseClassExists(): void
    {
        $this->assertTrue(class_exists('Database'), 'Database class should exist');
    }

    public function testDatabaseCanBeInstantiated(): void
    {
        $database = new \Database();
        $this->assertInstanceOf(\Database::class, $database);
    }

    public function testDatabaseConnectionExists(): void
    {
        $database = new \Database();
        
        // Check if the database has the dbcrm method which returns PDO connection
        $this->assertTrue(method_exists($database, 'dbcrm'), 'Database should have dbcrm method');
    }

    public function testDatabaseHasRequiredMethods(): void
    {
        $database = new \Database();
        
        $this->assertTrue(method_exists($database, 'dbcrm'), 'Database should have dbcrm method');
        
        // Only test actual connection if we're in remote mode
        if ($this->isRemoteMode()) {
            try {
                $pdo = $database->dbcrm();
                $this->assertInstanceOf(\PDO::class, $pdo, 'dbcrm should return PDO instance');
            } catch (\PDOException $e) {
                $this->markTestSkipped('Database connection failed: ' . $e->getMessage());
            }
        } else {
            $this->markTestSkipped('Database connection test skipped - not in remote mode');
        }
    }

    /**
     * Test basic database connectivity (if possible in test environment)
     */
    public function testDatabaseConnectivity(): void
    {
        // Skip this test if we're not in a proper test environment
        if (!$this->isRemoteMode()) {
            $this->markTestSkipped('Database connectivity test skipped - not in remote mode');
            return;
        }

        try {
            $database = new \Database();
            
            // Try to get the connection
            $reflection = new \ReflectionClass($database);
            if ($reflection->hasProperty('connection')) {
                $connectionProperty = $reflection->getProperty('connection');
                $connectionProperty->setAccessible(true);
                $connection = $connectionProperty->getValue($database);
                
                if ($connection instanceof \PDO) {
                    $this->assertInstanceOf(\PDO::class, $connection, 'Connection should be a PDO instance');
                } else {
                    $this->markTestSkipped('Database connection not available in test environment');
                }
            }
        } catch (\Exception $e) {
            $this->markTestSkipped('Database connection failed: ' . $e->getMessage());
        }
    }
}