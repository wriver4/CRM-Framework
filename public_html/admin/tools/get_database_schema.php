<?php
/**
 * Database Schema Tool
 * 
 * Returns actual database schema for validation
 * Prevents issues with non-existent columns
 * 
 * Usage:
 *   php get_database_schema.php [table_name]
 *   php get_database_schema.php --json [table_name]
 *   php get_database_schema.php --validate "SELECT * FROM table"
 */

// Include minimal database connection
require_once __DIR__ . '/db_connection.php';

class DatabaseSchemaValidator {
    private $pdo;
    private $schema_cache = [];
    
    public function __construct() {
        $this->pdo = getDbConnection();
    }
    
    /**
     * Get all tables in the database
     */
    public function getAllTables() {
        $stmt = $this->pdo->query("SHOW TABLES");
        $tables = [];
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        return $tables;
    }
    
    /**
     * Get detailed schema for a specific table
     */
    public function getTableSchema($table_name) {
        if (isset($this->schema_cache[$table_name])) {
            return $this->schema_cache[$table_name];
        }
        
        // Get column information
        $stmt = $this->pdo->query("DESCRIBE `$table_name`");
        $columns = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $columns[] = [
                'name' => $row['Field'],
                'type' => $row['Type'],
                'nullable' => $row['Null'] === 'YES',
                'key' => $row['Key'],
                'default' => $row['Default'],
                'extra' => $row['Extra']
            ];
        }
        
        // Get indexes
        $stmt = $this->pdo->query("SHOW INDEX FROM `$table_name`");
        $indexes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $indexes[] = [
                'name' => $row['Key_name'],
                'column' => $row['Column_name'],
                'unique' => $row['Non_unique'] == 0
            ];
        }
        
        // Get foreign keys
        $stmt = $this->pdo->query("
            SELECT 
                CONSTRAINT_NAME,
                COLUMN_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = '$table_name'
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        $foreign_keys = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $foreign_keys[] = [
                'constraint' => $row['CONSTRAINT_NAME'],
                'column' => $row['COLUMN_NAME'],
                'references_table' => $row['REFERENCED_TABLE_NAME'],
                'references_column' => $row['REFERENCED_COLUMN_NAME']
            ];
        }
        
        $schema = [
            'table' => $table_name,
            'columns' => $columns,
            'indexes' => $indexes,
            'foreign_keys' => $foreign_keys
        ];
        
        $this->schema_cache[$table_name] = $schema;
        return $schema;
    }
    
    /**
     * Get all schemas for all tables
     */
    public function getAllSchemas() {
        $tables = $this->getAllTables();
        $schemas = [];
        foreach ($tables as $table) {
            $schemas[$table] = $this->getTableSchema($table);
        }
        return $schemas;
    }
    
    /**
     * Get just column names for a table
     */
    public function getTableColumns($table_name) {
        $schema = $this->getTableSchema($table_name);
        return array_column($schema['columns'], 'name');
    }
    
    /**
     * Validate if columns exist in a table
     */
    public function validateColumns($table_name, $columns) {
        $valid_columns = $this->getTableColumns($table_name);
        $invalid = [];
        
        foreach ($columns as $col) {
            if (!in_array($col, $valid_columns)) {
                $suggestion = $this->findSimilarColumn($col, $valid_columns);
                $invalid[] = [
                    'column' => $col,
                    'table' => $table_name,
                    'suggestion' => $suggestion
                ];
            }
        }
        
        return [
            'valid' => empty($invalid),
            'invalid_columns' => $invalid
        ];
    }
    
    /**
     * Extract column names from SQL query
     */
    public function extractColumnsFromSQL($sql, $table_alias = null) {
        $columns = [];
        
        // Remove comments
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        
        // Extract SELECT columns
        if (preg_match('/SELECT\s+(.*?)\s+FROM/is', $sql, $matches)) {
            $select_part = $matches[1];
            
            // Skip SELECT *
            if (trim($select_part) === '*') {
                return [];
            }
            
            // Parse individual columns
            $parts = preg_split('/,(?![^()]*\))/', $select_part);
            foreach ($parts as $part) {
                $part = trim($part);
                
                // Extract column name (handle aliases)
                if (preg_match('/(?:^|\.)([\w]+)(?:\s+as\s+|\s+)?\w*$/i', $part, $col_match)) {
                    $col_name = $col_match[1];
                    if ($col_name !== '*') {
                        $columns[] = $col_name;
                    }
                }
            }
        }
        
        // Extract WHERE columns
        if (preg_match_all('/(?:WHERE|AND|OR|ON)\s+(?:\w+\.)?(\w+)\s*[=<>!]/i', $sql, $matches)) {
            $columns = array_merge($columns, $matches[1]);
        }
        
        // Extract ORDER BY columns
        if (preg_match_all('/ORDER\s+BY\s+(?:\w+\.)?(\w+)/i', $sql, $matches)) {
            $columns = array_merge($columns, $matches[1]);
        }
        
        // Extract GROUP BY columns
        if (preg_match_all('/GROUP\s+BY\s+(?:\w+\.)?(\w+)/i', $sql, $matches)) {
            $columns = array_merge($columns, $matches[1]);
        }
        
        return array_unique($columns);
    }
    
    /**
     * Validate SQL query against database schema
     */
    public function validateQuery($sql, $primary_table) {
        $columns = $this->extractColumnsFromSQL($sql);
        
        if (empty($columns)) {
            return [
                'valid' => true,
                'message' => 'No specific columns to validate (SELECT * or no columns detected)'
            ];
        }
        
        $result = $this->validateColumns($primary_table, $columns);
        
        if ($result['valid']) {
            return [
                'valid' => true,
                'message' => 'All columns exist in table',
                'columns_checked' => $columns
            ];
        } else {
            return [
                'valid' => false,
                'message' => 'Invalid columns detected',
                'invalid_columns' => $result['invalid_columns'],
                'columns_checked' => $columns
            ];
        }
    }
    
    /**
     * Find similar column name (for suggestions)
     */
    private function findSimilarColumn($needle, $haystack) {
        $needle = strtolower($needle);
        $best_match = null;
        $best_score = 0;
        
        foreach ($haystack as $column) {
            $column_lower = strtolower($column);
            
            // Exact match
            if ($needle === $column_lower) {
                return $column;
            }
            
            // Calculate similarity
            similar_text($needle, $column_lower, $percent);
            
            if ($percent > $best_score) {
                $best_score = $percent;
                $best_match = $column;
            }
            
            // Check if needle is contained in column or vice versa
            if (strpos($column_lower, $needle) !== false || strpos($needle, $column_lower) !== false) {
                if ($percent > 50) {
                    return $column;
                }
            }
        }
        
        return $best_score > 60 ? $best_match : null;
    }
    
    /**
     * Display schema in human-readable format
     */
    public function displaySchema($table_name) {
        $schema = $this->getTableSchema($table_name);
        
        echo "\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "  TABLE: {$schema['table']}\n";
        echo "═══════════════════════════════════════════════════════════════\n\n";
        
        echo "COLUMNS:\n";
        echo str_repeat("─", 63) . "\n";
        printf("%-25s %-20s %-8s %s\n", "Name", "Type", "Nullable", "Key");
        echo str_repeat("─", 63) . "\n";
        
        foreach ($schema['columns'] as $col) {
            printf(
                "%-25s %-20s %-8s %s\n",
                $col['name'],
                $col['type'],
                $col['nullable'] ? 'YES' : 'NO',
                $col['key']
            );
        }
        
        if (!empty($schema['foreign_keys'])) {
            echo "\n\nFOREIGN KEYS:\n";
            echo str_repeat("─", 63) . "\n";
            foreach ($schema['foreign_keys'] as $fk) {
                echo "  {$fk['column']} → {$fk['references_table']}.{$fk['references_column']}\n";
            }
        }
        
        echo "\n";
    }
    
    /**
     * Display all tables
     */
    public function displayAllTables() {
        $tables = $this->getAllTables();
        
        echo "\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "  DATABASE TABLES (" . count($tables) . " total)\n";
        echo "═══════════════════════════════════════════════════════════════\n\n";
        
        foreach ($tables as $table) {
            $schema = $this->getTableSchema($table);
            $col_count = count($schema['columns']);
            echo "  • {$table} ({$col_count} columns)\n";
        }
        
        echo "\n";
    }
}

// CLI Interface - only run if this file is executed directly
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    $validator = new DatabaseSchemaValidator();
    
    $args = array_slice($argv, 1);
    $json_output = false;
    $validate_query = false;
    
    // Parse flags
    if (in_array('--json', $args)) {
        $json_output = true;
        $args = array_values(array_diff($args, ['--json']));
    }
    
    if (in_array('--validate', $args)) {
        $validate_query = true;
        $key = array_search('--validate', $args);
        unset($args[$key]);
        $args = array_values($args);
    }
    
    // Handle commands
    if ($validate_query && !empty($args)) {
        // Validate SQL query
        $sql = $args[0];
        $table = $args[1] ?? null;
        
        if (!$table) {
            // Try to extract table from SQL
            if (preg_match('/FROM\s+`?(\w+)`?/i', $sql, $matches)) {
                $table = $matches[1];
            } else {
                echo "Error: Could not determine table name. Please specify: --validate \"SQL\" table_name\n";
                exit(1);
            }
        }
        
        $result = $validator->validateQuery($sql, $table);
        
        if ($json_output) {
            echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
        } else {
            echo "\n";
            echo "Query Validation Result:\n";
            echo str_repeat("─", 63) . "\n";
            echo "Status: " . ($result['valid'] ? "✓ VALID" : "✗ INVALID") . "\n";
            echo "Message: {$result['message']}\n";
            
            if (!$result['valid'] && isset($result['invalid_columns'])) {
                echo "\nInvalid Columns:\n";
                foreach ($result['invalid_columns'] as $invalid) {
                    echo "  ✗ {$invalid['column']}";
                    if ($invalid['suggestion']) {
                        echo " → Did you mean: {$invalid['suggestion']}?";
                    }
                    echo "\n";
                }
            }
            echo "\n";
        }
        
    } elseif (empty($args)) {
        // Show all tables
        if ($json_output) {
            $schemas = $validator->getAllSchemas();
            echo json_encode($schemas, JSON_PRETTY_PRINT) . "\n";
        } else {
            $validator->displayAllTables();
        }
        
    } else {
        // Show specific table
        $table_name = $args[0];
        
        if ($json_output) {
            $schema = $validator->getTableSchema($table_name);
            echo json_encode($schema, JSON_PRETTY_PRINT) . "\n";
        } else {
            $validator->displaySchema($table_name);
        }
    }
}