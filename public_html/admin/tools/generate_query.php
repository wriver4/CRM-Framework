<?php
/**
 * Query Generator Tool
 * 
 * Generates framework-compliant SQL queries with proper joins and audit fields
 * Validates against actual database schema
 * 
 * Usage:
 *   php generate_query.php --type list --table email_templates
 *   php generate_query.php --type get --table email_templates --id
 *   php generate_query.php --type insert --table email_templates --fields "name,subject,body"
 *   php generate_query.php --type update --table email_templates --fields "name,subject" --id
 *   php generate_query.php --type delete --table email_templates --id --soft
 */

require_once(__DIR__ . '/get_database_schema.php');

class QueryGenerator {
    private $schema_validator;
    
    // Common join patterns
    private $common_joins = [
        'users' => [
            'created_by' => 'LEFT JOIN users u ON t.created_by = u.id',
            'updated_by' => 'LEFT JOIN users u2 ON t.updated_by = u2.id',
            'user_id' => 'LEFT JOIN users u ON t.user_id = u.id'
        ],
        'companies' => [
            'company_id' => 'LEFT JOIN companies c ON t.company_id = c.id'
        ],
        'email_templates' => [
            'template_id' => 'LEFT JOIN email_templates et ON t.template_id = et.id'
        ]
    ];
    
    // Audit fields
    private $audit_fields = [
        'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at'
    ];
    
    public function __construct() {
        $this->schema_validator = new DatabaseSchemaValidator();
    }
    
    /**
     * Generate SELECT query for listing
     */
    public function generateListQuery($table, $options = []) {
        $schema = $this->schema_validator->getTableSchema($table);
        $columns = array_column($schema['columns'], 'name');
        
        // Build SELECT clause
        $select_parts = ["t.*"];
        
        // Add joins if needed
        $joins = [];
        $auto_joins = $options['auto_joins'] ?? true;
        
        if ($auto_joins) {
            // Auto-detect join opportunities
            foreach ($columns as $col) {
                if ($col === 'created_by' && in_array('created_by', $columns)) {
                    $joins[] = "LEFT JOIN users u ON t.created_by = u.id";
                    $select_parts[] = "u.name as created_by_name";
                }
                if ($col === 'updated_by' && in_array('updated_by', $columns)) {
                    $joins[] = "LEFT JOIN users u2 ON t.updated_by = u2.id";
                    $select_parts[] = "u2.name as updated_by_name";
                }
                if ($col === 'company_id' && in_array('company_id', $columns)) {
                    $joins[] = "LEFT JOIN companies c ON t.company_id = c.id";
                    $select_parts[] = "c.name as company_name";
                }
            }
        }
        
        // Manual joins
        if (!empty($options['joins'])) {
            foreach ($options['joins'] as $join) {
                $joins[] = $join;
            }
        }
        
        // Build WHERE clause
        $where_parts = [];
        
        // Soft delete check
        if (in_array('deleted_at', $columns) && ($options['include_deleted'] ?? false) === false) {
            $where_parts[] = "t.deleted_at IS NULL";
        }
        
        // Custom filters
        if (!empty($options['filters'])) {
            foreach ($options['filters'] as $field => $condition) {
                $where_parts[] = "t.$field $condition";
            }
        }
        
        $where_clause = !empty($where_parts) ? "WHERE " . implode(" AND ", $where_parts) : "";
        
        // Build ORDER BY
        $order_by = $options['order_by'] ?? 't.id DESC';
        
        // Build LIMIT
        $limit = "";
        if (isset($options['pagination']) && $options['pagination']) {
            $page = $options['page'] ?? 1;
            $per_page = $options['per_page'] ?? 20;
            $offset = ($page - 1) * $per_page;
            $limit = "LIMIT $offset, $per_page";
        }
        
        // Assemble query
        $select = "SELECT " . implode(", ", $select_parts);
        $from = "FROM `$table` t";
        $join_clause = !empty($joins) ? implode("\n    ", $joins) : "";
        
        $query = trim("$select\n$from\n" . ($join_clause ? "    $join_clause\n" : "") . "$where_clause\nORDER BY $order_by\n$limit");
        
        // Generate count query
        $count_query = "SELECT COUNT(*) as total\nFROM `$table` t\n" . ($join_clause ? "    $join_clause\n" : "") . $where_clause;
        
        return [
            'query' => $query,
            'count_query' => $count_query,
            'type' => 'list',
            'table' => $table,
            'validation' => $this->validateQuery($query, $table)
        ];
    }
    
    /**
     * Generate SELECT query for single item
     */
    public function generateGetQuery($table, $options = []) {
        $schema = $this->schema_validator->getTableSchema($table);
        $columns = array_column($schema['columns'], 'name');
        
        $select_parts = ["t.*"];
        $joins = [];
        
        // Auto-detect joins
        if ($options['auto_joins'] ?? true) {
            foreach ($columns as $col) {
                if ($col === 'created_by') {
                    $joins[] = "LEFT JOIN users u ON t.created_by = u.id";
                    $select_parts[] = "u.name as created_by_name";
                }
                if ($col === 'company_id') {
                    $joins[] = "LEFT JOIN companies c ON t.company_id = c.id";
                    $select_parts[] = "c.name as company_name";
                }
            }
        }
        
        $where = "WHERE t.id = ?";
        
        // Soft delete check
        if (in_array('deleted_at', $columns) && ($options['include_deleted'] ?? false) === false) {
            $where .= " AND t.deleted_at IS NULL";
        }
        
        $select = "SELECT " . implode(", ", $select_parts);
        $from = "FROM `$table` t";
        $join_clause = !empty($joins) ? implode("\n    ", $joins) : "";
        
        $query = trim("$select\n$from\n" . ($join_clause ? "    $join_clause\n" : "") . "$where");
        
        return [
            'query' => $query,
            'prepared' => true,
            'parameters' => ['id'],
            'type' => 'get',
            'table' => $table,
            'validation' => $this->validateQuery($query, $table)
        ];
    }
    
    /**
     * Generate INSERT query
     */
    public function generateInsertQuery($table, $fields, $options = []) {
        $schema = $this->schema_validator->getTableSchema($table);
        $columns = array_column($schema['columns'], 'name');
        
        // Parse fields
        $field_list = is_array($fields) ? $fields : explode(',', $fields);
        $field_list = array_map('trim', $field_list);
        
        // Add audit fields if they exist
        if (in_array('created_at', $columns) && !in_array('created_at', $field_list)) {
            $field_list[] = 'created_at';
        }
        if (in_array('created_by', $columns) && !in_array('created_by', $field_list)) {
            $field_list[] = 'created_by';
        }
        
        // Validate fields exist
        $invalid = array_diff($field_list, $columns);
        if (!empty($invalid)) {
            return [
                'error' => 'Invalid fields: ' . implode(', ', $invalid),
                'valid_fields' => $columns
            ];
        }
        
        // Build query
        $fields_str = implode(', ', $field_list);
        $placeholders = implode(', ', array_fill(0, count($field_list), '?'));
        
        $query = "INSERT INTO `$table` ($fields_str)\nVALUES ($placeholders)";
        
        // Generate parameter info
        $params = [];
        foreach ($field_list as $field) {
            if ($field === 'created_at' || $field === 'updated_at') {
                $params[$field] = 'NOW() or date string';
            } elseif ($field === 'created_by' || $field === 'updated_by') {
                $params[$field] = '$_SESSION[\'user_id\'] or user ID';
            } else {
                $params[$field] = 'value';
            }
        }
        
        return [
            'query' => $query,
            'prepared' => true,
            'fields' => $field_list,
            'parameters' => $params,
            'type' => 'insert',
            'table' => $table
        ];
    }
    
    /**
     * Generate UPDATE query
     */
    public function generateUpdateQuery($table, $fields, $options = []) {
        $schema = $this->schema_validator->getTableSchema($table);
        $columns = array_column($schema['columns'], 'name');
        
        // Parse fields
        $field_list = is_array($fields) ? $fields : explode(',', $fields);
        $field_list = array_map('trim', $field_list);
        
        // Add audit fields if they exist
        if (in_array('updated_at', $columns) && !in_array('updated_at', $field_list)) {
            $field_list[] = 'updated_at';
        }
        if (in_array('updated_by', $columns) && !in_array('updated_by', $field_list)) {
            $field_list[] = 'updated_by';
        }
        
        // Validate fields exist
        $invalid = array_diff($field_list, $columns);
        if (!empty($invalid)) {
            return [
                'error' => 'Invalid fields: ' . implode(', ', $invalid),
                'valid_fields' => $columns
            ];
        }
        
        // Build SET clause
        $set_parts = array_map(fn($f) => "$f = ?", $field_list);
        $set_clause = implode(", ", $set_parts);
        
        $query = "UPDATE `$table`\nSET $set_clause\nWHERE id = ?";
        
        // Generate parameter info
        $params = [];
        foreach ($field_list as $field) {
            if ($field === 'updated_at') {
                $params[$field] = 'NOW() or date string';
            } elseif ($field === 'updated_by') {
                $params[$field] = '$_SESSION[\'user_id\'] or user ID';
            } else {
                $params[$field] = 'value';
            }
        }
        $params['id'] = 'record ID';
        
        return [
            'query' => $query,
            'prepared' => true,
            'fields' => $field_list,
            'parameters' => $params,
            'type' => 'update',
            'table' => $table
        ];
    }
    
    /**
     * Generate DELETE query
     */
    public function generateDeleteQuery($table, $options = []) {
        $schema = $this->schema_validator->getTableSchema($table);
        $columns = array_column($schema['columns'], 'name');
        
        $soft_delete = $options['soft'] ?? in_array('deleted_at', $columns);
        
        if ($soft_delete && in_array('deleted_at', $columns)) {
            // Soft delete
            $set_parts = ['deleted_at = NOW()'];
            
            if (in_array('updated_by', $columns)) {
                $set_parts[] = 'updated_by = ?';
                $params = ['updated_by' => '$_SESSION[\'user_id\']', 'id' => 'record ID'];
            } else {
                $params = ['id' => 'record ID'];
            }
            
            $query = "UPDATE `$table`\nSET " . implode(', ', $set_parts) . "\nWHERE id = ?";
            
            return [
                'query' => $query,
                'prepared' => true,
                'parameters' => $params,
                'type' => 'soft_delete',
                'table' => $table,
                'note' => 'This is a soft delete (sets deleted_at timestamp)'
            ];
        } else {
            // Hard delete
            $query = "DELETE FROM `$table`\nWHERE id = ?";
            
            return [
                'query' => $query,
                'prepared' => true,
                'parameters' => ['id' => 'record ID'],
                'type' => 'hard_delete',
                'table' => $table,
                'warning' => 'This permanently deletes the record!'
            ];
        }
    }
    
    /**
     * Validate generated query
     */
    private function validateQuery($query, $table) {
        return $this->schema_validator->validateQuery($query, $table);
    }
    
    /**
     * Display query result
     */
    public function displayQuery($result) {
        echo "\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "  GENERATED QUERY\n";
        echo "═══════════════════════════════════════════════════════════════\n\n";
        
        if (isset($result['error'])) {
            echo "✗ Error: {$result['error']}\n\n";
            if (isset($result['valid_fields'])) {
                echo "Valid fields for this table:\n";
                echo "  " . implode(', ', $result['valid_fields']) . "\n\n";
            }
            return;
        }
        
        echo "Type: {$result['type']}\n";
        echo "Table: {$result['table']}\n";
        
        if (isset($result['note'])) {
            echo "Note: {$result['note']}\n";
        }
        if (isset($result['warning'])) {
            echo "⚠ Warning: {$result['warning']}\n";
        }
        
        echo "\nQuery:\n";
        echo str_repeat("─", 63) . "\n";
        echo $result['query'] . "\n";
        echo str_repeat("─", 63) . "\n";
        
        if (isset($result['count_query'])) {
            echo "\nCount Query:\n";
            echo str_repeat("─", 63) . "\n";
            echo $result['count_query'] . "\n";
            echo str_repeat("─", 63) . "\n";
        }
        
        if (!empty($result['parameters'])) {
            echo "\nParameters:\n";
            foreach ($result['parameters'] as $param => $desc) {
                if (is_numeric($param)) {
                    echo "  • $desc\n";
                } else {
                    echo "  • $param: $desc\n";
                }
            }
        }
        
        if (isset($result['validation'])) {
            echo "\nValidation:\n";
            if ($result['validation']['valid']) {
                echo "  ✓ {$result['validation']['message']}\n";
            } else {
                echo "  ✗ {$result['validation']['message']}\n";
                if (!empty($result['validation']['invalid_columns'])) {
                    foreach ($result['validation']['invalid_columns'] as $invalid) {
                        echo "    • {$invalid['column']}";
                        if ($invalid['suggestion']) {
                            echo " → Did you mean: {$invalid['suggestion']}?";
                        }
                        echo "\n";
                    }
                }
            }
        }
        
        echo "\n";
    }
}

// CLI Interface
if (php_sapi_name() === 'cli') {
    $generator = new QueryGenerator();
    
    $args = array_slice($argv, 1);
    $options = [];
    $json_output = false;
    
    // Parse arguments
    for ($i = 0; $i < count($args); $i++) {
        switch ($args[$i]) {
            case '--type':
                $options['type'] = $args[++$i] ?? null;
                break;
            case '--table':
                $options['table'] = $args[++$i] ?? null;
                break;
            case '--fields':
                $options['fields'] = $args[++$i] ?? null;
                break;
            case '--id':
                $options['id'] = true;
                break;
            case '--soft':
                $options['soft'] = true;
                break;
            case '--pagination':
                $options['pagination'] = true;
                break;
            case '--page':
                $options['page'] = (int)($args[++$i] ?? 1);
                break;
            case '--per-page':
                $options['per_page'] = (int)($args[++$i] ?? 20);
                break;
            case '--order':
                $options['order_by'] = $args[++$i] ?? null;
                break;
            case '--json':
                $json_output = true;
                break;
        }
    }
    
    // Validate required options
    if (empty($options['type']) || empty($options['table'])) {
        echo "Usage:\n";
        echo "  php generate_query.php --type TYPE --table TABLE [OPTIONS]\n\n";
        echo "Types:\n";
        echo "  list     Generate SELECT query for listing records\n";
        echo "  get      Generate SELECT query for single record\n";
        echo "  insert   Generate INSERT query\n";
        echo "  update   Generate UPDATE query\n";
        echo "  delete   Generate DELETE query\n\n";
        echo "Options:\n";
        echo "  --fields FIELDS    Comma-separated field names (for insert/update)\n";
        echo "  --id               Include ID in WHERE clause (for get/update/delete)\n";
        echo "  --soft             Use soft delete (for delete)\n";
        echo "  --pagination       Add pagination (for list)\n";
        echo "  --page N           Page number (default: 1)\n";
        echo "  --per-page N       Records per page (default: 20)\n";
        echo "  --order FIELD      Order by field (default: id DESC)\n";
        echo "  --json             Output as JSON\n\n";
        echo "Examples:\n";
        echo "  php generate_query.php --type list --table email_templates --pagination\n";
        echo "  php generate_query.php --type get --table email_templates --id\n";
        echo "  php generate_query.php --type insert --table email_templates --fields \"name,subject,body\"\n";
        echo "  php generate_query.php --type update --table email_templates --fields \"name,subject\" --id\n";
        echo "  php generate_query.php --type delete --table email_templates --id --soft\n";
        exit(0);
    }
    
    // Generate query based on type
    $result = null;
    switch ($options['type']) {
        case 'list':
            $result = $generator->generateListQuery($options['table'], $options);
            break;
        case 'get':
            $result = $generator->generateGetQuery($options['table'], $options);
            break;
        case 'insert':
            if (empty($options['fields'])) {
                echo "Error: --fields required for insert query\n";
                exit(1);
            }
            $result = $generator->generateInsertQuery($options['table'], $options['fields'], $options);
            break;
        case 'update':
            if (empty($options['fields'])) {
                echo "Error: --fields required for update query\n";
                exit(1);
            }
            $result = $generator->generateUpdateQuery($options['table'], $options['fields'], $options);
            break;
        case 'delete':
            $result = $generator->generateDeleteQuery($options['table'], $options);
            break;
        default:
            echo "Error: Invalid type. Use: list, get, insert, update, or delete\n";
            exit(1);
    }
    
    // Output result
    if ($json_output) {
        echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
    } else {
        $generator->displayQuery($result);
    }
}