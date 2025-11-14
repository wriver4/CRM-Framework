<?php

namespace Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Database;

/**
 * Database Class Unit Tests
 * 
 * Tests the core Database singleton pattern and connection management.
 * This is CRITICAL infrastructure that all other classes depend on.
 * 
 * @group Core
 * @group Critical
 */
class DatabaseTest extends TestCase
{
    private $database;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Load the Database class
        require_once __DIR__ . '/../../../../classes/Core/Database.php';
        
        $this->database = new Database();
    }
    
    protected function tearDown(): void
    {
        $this->database = null;
        parent::tearDown();
    }
    
    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(Database::class, $this->database);
    }
    
    /** @test */
    public function it_has_dbcrm_method()
    {
        $this->assertTrue(
            method_exists($this->database, 'dbcrm'),
            'Database class must have dbcrm() method'
        );
    }
    
    /** @test */
    public function it_returns_pdo_instance_from_dbcrm()
    {
        $connection = $this->database->dbcrm();
        
        $this->assertInstanceOf(
            \PDO::class,
            $connection,
            'dbcrm() must return a PDO instance'
        );
    }
    
    /** @test */
    public function it_implements_singleton_pattern()
    {
        // Get connection twice
        $connection1 = $this->database->dbcrm();
        $connection2 = $this->database->dbcrm();
        
        // Should be the same instance
        $this->assertSame(
            $connection1,
            $connection2,
            'dbcrm() should return the same PDO instance (singleton pattern)'
        );
    }
    
    /** @test */
    public function it_has_correct_pdo_attributes()
    {
        $connection = $this->database->dbcrm();
        
        // Check error mode
        $this->assertEquals(
            \PDO::ERRMODE_EXCEPTION,
            $connection->getAttribute(\PDO::ATTR_ERRMODE),
            'PDO should use ERRMODE_EXCEPTION'
        );
        
        // Check fetch mode
        $this->assertEquals(
            \PDO::FETCH_ASSOC,
            $connection->getAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE),
            'PDO should use FETCH_ASSOC as default'
        );
        
        // Check emulate prepares
        $this->assertFalse(
            $connection->getAttribute(\PDO::ATTR_EMULATE_PREPARES),
            'PDO should not emulate prepared statements'
        );
    }
    
    /** @test */
    public function it_can_execute_simple_query()
    {
        $connection = $this->database->dbcrm();
        
        // Simple query to test connection
        $stmt = $connection->query('SELECT 1 as test');
        $result = $stmt->fetch();
        
        $this->assertEquals(
            1,
            $result['test'],
            'Should be able to execute simple SELECT query'
        );
    }
    
    /** @test */
    public function it_can_prepare_statements()
    {
        $connection = $this->database->dbcrm();
        
        // Test prepared statement
        $stmt = $connection->prepare('SELECT :value as test');
        $stmt->execute(['value' => 'test_value']);
        $result = $stmt->fetch();
        
        $this->assertEquals(
            'test_value',
            $result['test'],
            'Should be able to prepare and execute statements'
        );
    }
    
    /** @test */
    public function it_uses_utf8mb4_charset()
    {
        $connection = $this->database->dbcrm();
        
        // Query to check charset
        $stmt = $connection->query("SHOW VARIABLES LIKE 'character_set_connection'");
        $result = $stmt->fetch();
        
        $this->assertEquals(
            'utf8mb4',
            $result['Value'],
            'Connection should use utf8mb4 charset'
        );
    }
    
    /** @test */
    public function it_throws_exception_on_invalid_query()
    {
        $this->expectException(\PDOException::class);
        
        $connection = $this->database->dbcrm();
        $connection->query('SELECT * FROM nonexistent_table_xyz');
    }
    
    /** @test */
    public function it_can_handle_transactions()
    {
        $connection = $this->database->dbcrm();
        
        // Start transaction
        $this->assertTrue(
            $connection->beginTransaction(),
            'Should be able to begin transaction'
        );
        
        // Check if in transaction
        $this->assertTrue(
            $connection->inTransaction(),
            'Should be in transaction'
        );
        
        // Rollback
        $this->assertTrue(
            $connection->rollBack(),
            'Should be able to rollback transaction'
        );
        
        // Should not be in transaction anymore
        $this->assertFalse(
            $connection->inTransaction(),
            'Should not be in transaction after rollback'
        );
    }
    
    /** @test */
    public function it_maintains_connection_across_multiple_calls()
    {
        // Execute multiple queries
        $connection = $this->database->dbcrm();
        
        $result1 = $connection->query('SELECT 1 as test')->fetch();
        $result2 = $connection->query('SELECT 2 as test')->fetch();
        $result3 = $connection->query('SELECT 3 as test')->fetch();
        
        $this->assertEquals(1, $result1['test']);
        $this->assertEquals(2, $result2['test']);
        $this->assertEquals(3, $result3['test']);
    }
    
    /** @test */
    public function it_can_get_last_insert_id()
    {
        $connection = $this->database->dbcrm();
        
        // This test assumes we're using test database
        // We'll just verify the method exists and returns a value
        $lastId = $connection->lastInsertId();
        
        $this->assertTrue(
            is_string($lastId) || is_int($lastId),
            'lastInsertId() should return string or int'
        );
    }
    
    /** @test */
    public function it_properly_escapes_special_characters()
    {
        $connection = $this->database->dbcrm();
        
        // Test with special characters
        $testString = "Test's \"quoted\" string with \\ backslash";
        
        $stmt = $connection->prepare('SELECT :test as result');
        $stmt->execute(['test' => $testString]);
        $result = $stmt->fetch();
        
        $this->assertEquals(
            $testString,
            $result['result'],
            'Should properly handle special characters in prepared statements'
        );
    }
    
    /** @test */
    public function it_handles_null_values_correctly()
    {
        $connection = $this->database->dbcrm();
        
        $stmt = $connection->prepare('SELECT :value as test');
        $stmt->execute(['value' => null]);
        $result = $stmt->fetch();
        
        $this->assertNull(
            $result['test'],
            'Should handle NULL values correctly'
        );
    }
    
    /** @test */
    public function it_handles_boolean_values()
    {
        $connection = $this->database->dbcrm();
        
        // Test true
        $stmt = $connection->prepare('SELECT :value as test');
        $stmt->execute(['value' => true]);
        $result = $stmt->fetch();
        $this->assertEquals(1, $result['test'], 'TRUE should be stored as 1');
        
        // Test false
        $stmt->execute(['value' => false]);
        $result = $stmt->fetch();
        $this->assertEquals(0, $result['test'], 'FALSE should be stored as 0');
    }
    
    /** @test */
    public function it_handles_numeric_values()
    {
        $connection = $this->database->dbcrm();
        
        // Test integer
        $stmt = $connection->prepare('SELECT :value as test');
        $stmt->execute(['value' => 42]);
        $result = $stmt->fetch();
        $this->assertEquals(42, $result['test']);
        
        // Test float
        $stmt->execute(['value' => 3.14159]);
        $result = $stmt->fetch();
        $this->assertEquals(3.14159, (float)$result['test']);
    }
    
    /** @test */
    public function it_can_fetch_multiple_rows()
    {
        $connection = $this->database->dbcrm();
        
        // Create a query that returns multiple rows
        $stmt = $connection->query('
            SELECT 1 as id, "first" as name
            UNION ALL
            SELECT 2 as id, "second" as name
            UNION ALL
            SELECT 3 as id, "third" as name
        ');
        
        $results = $stmt->fetchAll();
        
        $this->assertCount(3, $results, 'Should fetch 3 rows');
        $this->assertEquals(1, $results[0]['id']);
        $this->assertEquals('second', $results[1]['name']);
        $this->assertEquals(3, $results[2]['id']);
    }
    
    /** @test */
    public function it_returns_correct_row_count()
    {
        $connection = $this->database->dbcrm();
        
        $stmt = $connection->query('
            SELECT 1 as test
            UNION ALL
            SELECT 2 as test
        ');
        
        // Fetch all to get row count
        $results = $stmt->fetchAll();
        
        $this->assertCount(
            2,
            $results,
            'Should return correct row count'
        );
    }
}