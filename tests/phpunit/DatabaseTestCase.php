<?php

namespace Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Database Test Case
 * 
 * Provides database-specific testing utilities including:
 * - Automatic test database setup/teardown
 * - Transaction-based test isolation
 * - Database snapshots and restoration
 * - Test data seeding
 */
abstract class DatabaseTestCase extends TestCase
{
    protected static $db;
    protected static $pdo;
    protected $useTransactions = true;
    protected $seedData = true;
    
    /**
     * Set up test database before any tests run
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        
        // Initialize TEST database connection
        self::$db = new \TestDatabase();
        self::$pdo = self::$db->testdbcrm();
        
        echo "\n✅ Test database: " . self::$db->getCurrentDatabase() . "\n";
        echo "   Test mode: " . self::$db->getTestMode() . "\n";
    }
    
    /**
     * Set up before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        if ($this->useTransactions) {
            self::$pdo->beginTransaction();
        }
        
        if ($this->seedData) {
            $this->seedTestData();
        }
    }
    
    /**
     * Tear down after each test
     */
    protected function tearDown(): void
    {
        if ($this->useTransactions && self::$pdo->inTransaction()) {
            self::$pdo->rollBack();
        }
        
        parent::tearDown();
    }
    
    /**
     * Seed test data (override in child classes)
     */
    protected function seedTestData(): void
    {
        // Override in child classes to seed specific test data
    }
    
    /**
     * Get database connection
     */
    protected function getDb(): \Database
    {
        return self::$db;
    }
    
    /**
     * Get PDO connection
     */
    protected function getPdo(): \PDO
    {
        return self::$pdo;
    }
    
    /**
     * Execute raw SQL query
     */
    protected function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    /**
     * Insert test data and return inserted ID
     */
    protected function insert(string $table, array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":$col", $columns);
        
        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
        
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($data);
        
        return (int) self::$pdo->lastInsertId();
    }
    
    /**
     * Update test data
     */
    protected function update(string $table, array $data, array $where): int
    {
        $setClauses = array_map(fn($col) => "$col = :$col", array_keys($data));
        $whereClauses = array_map(fn($col) => "$col = :where_$col", array_keys($where));
        
        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s",
            $table,
            implode(', ', $setClauses),
            implode(' AND ', $whereClauses)
        );
        
        $params = array_merge(
            $data,
            array_combine(
                array_map(fn($k) => "where_$k", array_keys($where)),
                array_values($where)
            )
        );
        
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    }
    
    /**
     * Delete test data
     */
    protected function delete(string $table, array $where): int
    {
        $whereClauses = array_map(fn($col) => "$col = :$col", array_keys($where));
        
        $sql = sprintf(
            "DELETE FROM %s WHERE %s",
            $table,
            implode(' AND ', $whereClauses)
        );
        
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($where);
        
        return $stmt->rowCount();
    }
    
    /**
     * Fetch single row
     */
    protected function fetchOne(string $table, array $where): ?array
    {
        $whereClauses = array_map(fn($col) => "$col = :$col", array_keys($where));
        
        $sql = sprintf(
            "SELECT * FROM %s WHERE %s LIMIT 1",
            $table,
            implode(' AND ', $whereClauses)
        );
        
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($where);
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }
    
    /**
     * Fetch multiple rows
     */
    protected function fetchAll(string $table, array $where = []): array
    {
        if (empty($where)) {
            $sql = "SELECT * FROM $table";
            $stmt = self::$pdo->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        
        $whereClauses = array_map(fn($col) => "$col = :$col", array_keys($where));
        
        $sql = sprintf(
            "SELECT * FROM %s WHERE %s",
            $table,
            implode(' AND ', $whereClauses)
        );
        
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($where);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Count rows
     */
    protected function count(string $table, array $where = []): int
    {
        if (empty($where)) {
            $sql = "SELECT COUNT(*) FROM $table";
            $stmt = self::$pdo->query($sql);
            return (int) $stmt->fetchColumn();
        }
        
        $whereClauses = array_map(fn($col) => "$col = :$col", array_keys($where));
        
        $sql = sprintf(
            "SELECT COUNT(*) FROM %s WHERE %s",
            $table,
            implode(' AND ', $whereClauses)
        );
        
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($where);
        
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Truncate table
     */
    protected function truncate(string $table): void
    {
        self::$pdo->exec("TRUNCATE TABLE $table");
    }
    
    /**
     * Assert database has record
     */
    protected function assertDatabaseHas(string $table, array $data): void
    {
        $result = $this->fetchOne($table, $data);
        $this->assertNotNull($result, "Failed asserting that table '$table' contains matching record");
    }
    
    /**
     * Assert database missing record
     */
    protected function assertDatabaseMissing(string $table, array $data): void
    {
        $result = $this->fetchOne($table, $data);
        $this->assertNull($result, "Failed asserting that table '$table' does not contain matching record");
    }
    
    /**
     * Assert database count
     */
    protected function assertDatabaseCount(string $table, int $expected, array $where = []): void
    {
        $actual = $this->count($table, $where);
        $this->assertEquals($expected, $actual, "Failed asserting that table '$table' has $expected records");
    }
    
    /**
     * Create database snapshot
     */
    protected function createSnapshot(string $name): void
    {
        $config = TEST_CONFIG;
        if (!$config['database']['snapshots']['enabled']) {
            return;
        }
        
        $snapshotDir = $config['database']['snapshots']['directory'];
        if (!is_dir($snapshotDir)) {
            mkdir($snapshotDir, 0755, true);
        }
        
        $snapshotFile = "$snapshotDir/$name.sql";
        $dbName = self::$db->getCurrentDatabase();
        
        $command = sprintf(
            'mysqldump -u%s -p%s %s > %s',
            escapeshellarg($this->getDb()->crm_username),
            escapeshellarg($this->getDb()->crm_password),
            escapeshellarg($dbName),
            escapeshellarg($snapshotFile)
        );
        
        exec($command);
    }
    
    /**
     * Restore database snapshot
     */
    protected function restoreSnapshot(string $name): void
    {
        $config = TEST_CONFIG;
        if (!$config['database']['snapshots']['enabled']) {
            return;
        }
        
        $snapshotFile = $config['database']['snapshots']['directory'] . "/$name.sql";
        if (!file_exists($snapshotFile)) {
            throw new \RuntimeException("Snapshot '$name' not found");
        }
        
        $dbName = self::$db->getCurrentDatabase();
        
        $command = sprintf(
            'mysql -u%s -p%s %s < %s',
            escapeshellarg($this->getDb()->crm_username),
            escapeshellarg($this->getDb()->crm_password),
            escapeshellarg($dbName),
            escapeshellarg($snapshotFile)
        );
        
        exec($command);
    }
}