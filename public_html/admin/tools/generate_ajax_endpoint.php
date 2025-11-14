#!/usr/bin/env php
<?php
/**
 * AJAX Endpoint Generator
 * Generates complete AJAX endpoints with backend PHP and frontend JavaScript
 * 
 * Usage:
 *   php generate_ajax_endpoint.php --type=get --table=users --operations=validate,log
 *   php generate_ajax_endpoint.php --type=post --table=email_queue --operations=validate,save,log
 *   php generate_ajax_endpoint.php --type=delete --table=email_templates --operations=validate,log
 *   php generate_ajax_endpoint.php --type=search --table=leads --operations=validate
 */

// Include database schema tool for column validation
require_once __DIR__ . '/get_database_schema.php';
require_once __DIR__ . '/db_connection.php';

class AjaxEndpointGenerator {
    
    private $translation_keys = [];
    private $pdo = null;
    
    public function __construct() {
        try {
            $this->pdo = getDbConnection();
        } catch (Exception $e) {
            // PDO not available, will skip schema validation
        }
    }
    
    public function generate($type, $table, $operations, $return_format = 'json') {
        $method = 'generate' . ucfirst($type);
        
        if (!method_exists($this, $method)) {
            return [
                'error' => "Unknown endpoint type: $type",
                'valid_types' => ['get', 'post', 'delete', 'search']
            ];
        }
        
        $this->translation_keys = [];
        
        // Get table schema if available
        $schema = null;
        if ($this->pdo) {
            try {
                $validator = new DatabaseSchemaValidator($this->pdo);
                $schema = $validator->getTableSchema($table);
            } catch (Exception $e) {
                // Schema not available
            }
        }
        
        $result = $this->$method($table, $operations, $return_format, $schema);
        $result['translation_keys_needed'] = $this->translation_keys;
        
        return $result;
    }
    
    private function generateGet($table, $operations, $return_format, $schema) {
        $table_singular = rtrim($table, 's');
        
        // Backend PHP
        $php = "<?php\n";
        $php .= "/**\n";
        $php .= " * AJAX Endpoint - Get {$table}\n";
        $php .= " * Returns data from {$table} table\n";
        $php .= " */\n\n";
        $php .= "// Authentication check\n";
        $php .= "\$not->loggedin();\n\n";
        $php .= "// Set JSON header\n";
        $php .= "header('Content-Type: application/json');\n\n";
        $php .= "try {\n";
        $php .= "    \$database = new Database();\n";
        $php .= "    \$pdo = \$database->dbcrm();\n\n";
        
        if (in_array('validate', $operations)) {
            $php .= "    // Validate input\n";
            $php .= "    \$id = filter_var(\$_GET['id'] ?? null, FILTER_VALIDATE_INT);\n";
            $php .= "    if (\$id === false || \$id <= 0) {\n";
            
            $error_key = "{$table_singular}_invalid_id_error";
            $this->translation_keys[] = $error_key;
            
            $php .= "        echo json_encode([\n";
            $php .= "            'success' => false,\n";
            $php .= "            'error' => \$lang['{$error_key}']\n";
            $php .= "        ]);\n";
            $php .= "        exit;\n";
            $php .= "    }\n\n";
        } else {
            $php .= "    \$id = (int)(\$_GET['id'] ?? 0);\n\n";
        }
        
        $php .= "    // Fetch data\n";
        $php .= "    \$stmt = \$pdo->prepare(\"SELECT * FROM {$table} WHERE id = ?\");\n";
        $php .= "    \$stmt->execute([\$id]);\n";
        $php .= "    \$data = \$stmt->fetch(PDO::FETCH_ASSOC);\n\n";
        $php .= "    if (!\$data) {\n";
        
        $not_found_key = "{$table_singular}_not_found_error";
        $this->translation_keys[] = $not_found_key;
        
        $php .= "        echo json_encode([\n";
        $php .= "            'success' => false,\n";
        $php .= "            'error' => \$lang['{$not_found_key}']\n";
        $php .= "        ]);\n";
        $php .= "        exit;\n";
        $php .= "    }\n\n";
        
        if (in_array('log', $operations)) {
            $php .= "    // Log access\n";
            $php .= "    \$audit->log('view', '{$table}', \$id);\n\n";
        }
        
        $php .= "    // Return success\n";
        $php .= "    echo json_encode([\n";
        $php .= "        'success' => true,\n";
        $php .= "        'data' => \$data\n";
        $php .= "    ]);\n\n";
        $php .= "} catch (PDOException \$e) {\n";
        $php .= "    error_log('Database error in get {$table}: ' . \$e->getMessage());\n";
        
        $db_error_key = "database_error";
        $this->translation_keys[] = $db_error_key;
        
        $php .= "    echo json_encode([\n";
        $php .= "        'success' => false,\n";
        $php .= "        'error' => \$lang['{$db_error_key}']\n";
        $php .= "    ]);\n";
        $php .= "} catch (Exception \$e) {\n";
        $php .= "    error_log('Error in get {$table}: ' . \$e->getMessage());\n";
        
        $general_error_key = "general_error";
        $this->translation_keys[] = $general_error_key;
        
        $php .= "    echo json_encode([\n";
        $php .= "        'success' => false,\n";
        $php .= "        'error' => \$lang['{$general_error_key}']\n";
        $php .= "    ]);\n";
        $php .= "}\n";
        
        // Frontend JavaScript
        $js = "// Fetch {$table_singular} data via AJAX\n";
        $js .= "function get" . ucfirst($table_singular) . "(id) {\n";
        $js .= "    return fetch(`ajax_get.php?id=\${id}`)\n";
        $js .= "        .then(response => response.json())\n";
        $js .= "        .then(data => {\n";
        $js .= "            if (data.success) {\n";
        $js .= "                return data.data;\n";
        $js .= "            } else {\n";
        $js .= "                throw new Error(data.error);\n";
        $js .= "            }\n";
        $js .= "        })\n";
        $js .= "        .catch(error => {\n";
        $js .= "            console.error('Error fetching {$table_singular}:', error);\n";
        $js .= "            alert(error.message);\n";
        $js .= "            throw error;\n";
        $js .= "        });\n";
        $js .= "}\n\n";
        $js .= "// Usage example:\n";
        $js .= "// get" . ucfirst($table_singular) . "(123).then(data => console.log(data));\n";
        
        return [
            'backend_code' => $php,
            'frontend_code' => $js,
            'filename_backend' => 'ajax_get.php',
            'filename_frontend' => 'ajax-get.js'
        ];
    }
    
    private function generatePost($table, $operations, $return_format, $schema) {
        $table_singular = rtrim($table, 's');
        
        // Determine fields from schema
        $fields = [];
        if ($schema && isset($schema['columns'])) {
            foreach ($schema['columns'] as $col_name => $col_info) {
                if (!in_array($col_name, ['id', 'created_at', 'updated_at'])) {
                    $fields[] = $col_name;
                }
            }
        } else {
            $fields = ['name', 'status']; // Default fields
        }
        
        // Backend PHP
        $php = "<?php\n";
        $php .= "/**\n";
        $php .= " * AJAX Endpoint - Save {$table_singular}\n";
        $php .= " * Creates or updates {$table_singular} record\n";
        $php .= " */\n\n";
        $php .= "// Authentication check\n";
        $php .= "\$not->loggedin();\n\n";
        $php .= "// CSRF check\n";
        $php .= "\$nonce->check();\n\n";
        $php .= "// Set JSON header\n";
        $php .= "header('Content-Type: application/json');\n\n";
        $php .= "try {\n";
        $php .= "    \$database = new Database();\n";
        $php .= "    \$pdo = \$database->dbcrm();\n\n";
        
        if (in_array('validate', $operations)) {
            $php .= "    // Validate input\n";
            $php .= "    \$errors = [];\n\n";
            
            foreach (array_slice($fields, 0, 3) as $field) { // Validate first 3 fields as example
                $required_key = "{$table_singular}_{$field}_required_error";
                $this->translation_keys[] = $required_key;
                
                $php .= "    if (empty(\$_POST['{$field}'])) {\n";
                $php .= "        \$errors[] = \$lang['{$required_key}'];\n";
                $php .= "    }\n\n";
            }
            
            $php .= "    if (!empty(\$errors)) {\n";
            $php .= "        echo json_encode([\n";
            $php .= "            'success' => false,\n";
            $php .= "            'errors' => \$errors\n";
            $php .= "        ]);\n";
            $php .= "        exit;\n";
            $php .= "    }\n\n";
        }
        
        $php .= "    // Prepare data\n";
        $php .= "    \$id = (int)(\$_POST['id'] ?? 0);\n";
        foreach ($fields as $field) {
            $php .= "    \${$field} = trim(\$_POST['{$field}'] ?? '');\n";
        }
        $php .= "\n";
        
        $php .= "    if (\$id > 0) {\n";
        $php .= "        // Update existing record\n";
        $php .= "        \$sql = \"UPDATE {$table} SET \";\n";
        $set_parts = [];
        foreach ($fields as $field) {
            $set_parts[] = "{$field} = ?";
        }
        $php .= "        \$sql .= \"" . implode(', ', $set_parts) . "\";\n";
        $php .= "        \$sql .= \" WHERE id = ?\";\n";
        $php .= "        \$params = [" . implode(', ', array_map(fn($f) => "\${$f}", $fields)) . ", \$id];\n";
        $php .= "        \$stmt = \$pdo->prepare(\$sql);\n";
        $php .= "        \$stmt->execute(\$params);\n";
        
        if (in_array('log', $operations)) {
            $php .= "        \$audit->log('update', '{$table}', \$id);\n";
        }
        
        $updated_key = "{$table_singular}_updated_message";
        $this->translation_keys[] = $updated_key;
        
        $php .= "        \$message = \$lang['{$updated_key}'];\n";
        $php .= "    } else {\n";
        $php .= "        // Insert new record\n";
        $php .= "        \$sql = \"INSERT INTO {$table} (\";\n";
        $php .= "        \$sql .= \"" . implode(', ', $fields) . "\";\n";
        $php .= "        \$sql .= \") VALUES (\";\n";
        $php .= "        \$sql .= \"" . implode(', ', array_fill(0, count($fields), '?')) . "\";\n";
        $php .= "        \$sql .= \")\";\n";
        $php .= "        \$params = [" . implode(', ', array_map(fn($f) => "\${$f}", $fields)) . "];\n";
        $php .= "        \$stmt = \$pdo->prepare(\$sql);\n";
        $php .= "        \$stmt->execute(\$params);\n";
        $php .= "        \$id = \$pdo->lastInsertId();\n";
        
        if (in_array('log', $operations)) {
            $php .= "        \$audit->log('create', '{$table}', \$id);\n";
        }
        
        $created_key = "{$table_singular}_created_message";
        $this->translation_keys[] = $created_key;
        
        $php .= "        \$message = \$lang['{$created_key}'];\n";
        $php .= "    }\n\n";
        $php .= "    // Return success\n";
        $php .= "    echo json_encode([\n";
        $php .= "        'success' => true,\n";
        $php .= "        'message' => \$message,\n";
        $php .= "        'id' => \$id\n";
        $php .= "    ]);\n\n";
        $php .= "} catch (PDOException \$e) {\n";
        $php .= "    error_log('Database error in save {$table_singular}: ' . \$e->getMessage());\n";
        $php .= "    echo json_encode([\n";
        $php .= "        'success' => false,\n";
        $php .= "        'error' => \$lang['database_error']\n";
        $php .= "    ]);\n";
        $php .= "} catch (Exception \$e) {\n";
        $php .= "    error_log('Error in save {$table_singular}: ' . \$e->getMessage());\n";
        $php .= "    echo json_encode([\n";
        $php .= "        'success' => false,\n";
        $php .= "        'error' => \$lang['general_error']\n";
        $php .= "    ]);\n";
        $php .= "}\n";
        
        // Frontend JavaScript
        $js = "// Save {$table_singular} via AJAX\n";
        $js .= "function save" . ucfirst($table_singular) . "(formData) {\n";
        $js .= "    return fetch('ajax_post.php', {\n";
        $js .= "        method: 'POST',\n";
        $js .= "        body: formData\n";
        $js .= "    })\n";
        $js .= "        .then(response => response.json())\n";
        $js .= "        .then(data => {\n";
        $js .= "            if (data.success) {\n";
        $js .= "                alert(data.message);\n";
        $js .= "                return data;\n";
        $js .= "            } else {\n";
        $js .= "                if (data.errors) {\n";
        $js .= "                    alert(data.errors.join('\\n'));\n";
        $js .= "                } else {\n";
        $js .= "                    alert(data.error);\n";
        $js .= "                }\n";
        $js .= "                throw new Error(data.error || 'Validation failed');\n";
        $js .= "            }\n";
        $js .= "        })\n";
        $js .= "        .catch(error => {\n";
        $js .= "            console.error('Error saving {$table_singular}:', error);\n";
        $js .= "            throw error;\n";
        $js .= "        });\n";
        $js .= "}\n\n";
        $js .= "// Usage example:\n";
        $js .= "// const formData = new FormData(document.getElementById('myForm'));\n";
        $js .= "// save" . ucfirst($table_singular) . "(formData).then(data => window.location = 'list.php');\n";
        
        return [
            'backend_code' => $php,
            'frontend_code' => $js,
            'filename_backend' => 'ajax_post.php',
            'filename_frontend' => 'ajax-post.js',
            'validation_rules' => array_combine($fields, array_fill(0, count($fields), 'required'))
        ];
    }
    
    private function generateDelete($table, $operations, $return_format, $schema) {
        $table_singular = rtrim($table, 's');
        
        // Backend PHP
        $php = "<?php\n";
        $php .= "/**\n";
        $php .= " * AJAX Endpoint - Delete {$table_singular}\n";
        $php .= " * Deletes a {$table_singular} record\n";
        $php .= " */\n\n";
        $php .= "// Authentication check\n";
        $php .= "\$not->loggedin();\n\n";
        $php .= "// CSRF check\n";
        $php .= "\$nonce->check();\n\n";
        $php .= "// Set JSON header\n";
        $php .= "header('Content-Type: application/json');\n\n";
        $php .= "try {\n";
        $php .= "    \$database = new Database();\n";
        $php .= "    \$pdo = \$database->dbcrm();\n\n";
        
        if (in_array('validate', $operations)) {
            $php .= "    // Validate input\n";
            $php .= "    \$id = filter_var(\$_POST['id'] ?? null, FILTER_VALIDATE_INT);\n";
            $php .= "    if (\$id === false || \$id <= 0) {\n";
            
            $error_key = "{$table_singular}_invalid_id_error";
            $this->translation_keys[] = $error_key;
            
            $php .= "        echo json_encode([\n";
            $php .= "            'success' => false,\n";
            $php .= "            'error' => \$lang['{$error_key}']\n";
            $php .= "        ]);\n";
            $php .= "        exit;\n";
            $php .= "    }\n\n";
        } else {
            $php .= "    \$id = (int)(\$_POST['id'] ?? 0);\n\n";
        }
        
        $php .= "    // Check if record exists\n";
        $php .= "    \$stmt = \$pdo->prepare(\"SELECT id FROM {$table} WHERE id = ?\");\n";
        $php .= "    \$stmt->execute([\$id]);\n";
        $php .= "    if (!\$stmt->fetch()) {\n";
        
        $not_found_key = "{$table_singular}_not_found_error";
        $this->translation_keys[] = $not_found_key;
        
        $php .= "        echo json_encode([\n";
        $php .= "            'success' => false,\n";
        $php .= "            'error' => \$lang['{$not_found_key}']\n";
        $php .= "        ]);\n";
        $php .= "        exit;\n";
        $php .= "    }\n\n";
        
        if (in_array('log', $operations)) {
            $php .= "    // Log before deletion\n";
            $php .= "    \$audit->log('delete', '{$table}', \$id);\n\n";
        }
        
        $php .= "    // Delete record\n";
        $php .= "    \$stmt = \$pdo->prepare(\"DELETE FROM {$table} WHERE id = ?\");\n";
        $php .= "    \$stmt->execute([\$id]);\n\n";
        
        $deleted_key = "{$table_singular}_deleted_message";
        $this->translation_keys[] = $deleted_key;
        
        $php .= "    // Return success\n";
        $php .= "    echo json_encode([\n";
        $php .= "        'success' => true,\n";
        $php .= "        'message' => \$lang['{$deleted_key}']\n";
        $php .= "    ]);\n\n";
        $php .= "} catch (PDOException \$e) {\n";
        $php .= "    error_log('Database error in delete {$table_singular}: ' . \$e->getMessage());\n";
        $php .= "    echo json_encode([\n";
        $php .= "        'success' => false,\n";
        $php .= "        'error' => \$lang['database_error']\n";
        $php .= "    ]);\n";
        $php .= "} catch (Exception \$e) {\n";
        $php .= "    error_log('Error in delete {$table_singular}: ' . \$e->getMessage());\n";
        $php .= "    echo json_encode([\n";
        $php .= "        'success' => false,\n";
        $php .= "        'error' => \$lang['general_error']\n";
        $php .= "    ]);\n";
        $php .= "}\n";
        
        // Frontend JavaScript
        $js = "// Delete {$table_singular} via AJAX\n";
        $js .= "function delete" . ucfirst($table_singular) . "(id) {\n";
        $js .= "    if (!confirm('Are you sure you want to delete this {$table_singular}?')) {\n";
        $js .= "        return Promise.reject('Cancelled');\n";
        $js .= "    }\n\n";
        $js .= "    const formData = new FormData();\n";
        $js .= "    formData.append('id', id);\n";
        $js .= "    // Add CSRF token from page\n";
        $js .= "    const csrfToken = document.querySelector('input[name=\"csrf_token\"]');\n";
        $js .= "    if (csrfToken) {\n";
        $js .= "        formData.append('csrf_token', csrfToken.value);\n";
        $js .= "    }\n\n";
        $js .= "    return fetch('ajax_delete.php', {\n";
        $js .= "        method: 'POST',\n";
        $js .= "        body: formData\n";
        $js .= "    })\n";
        $js .= "        .then(response => response.json())\n";
        $js .= "        .then(data => {\n";
        $js .= "            if (data.success) {\n";
        $js .= "                alert(data.message);\n";
        $js .= "                return data;\n";
        $js .= "            } else {\n";
        $js .= "                throw new Error(data.error);\n";
        $js .= "            }\n";
        $js .= "        })\n";
        $js .= "        .catch(error => {\n";
        $js .= "            if (error !== 'Cancelled') {\n";
        $js .= "                console.error('Error deleting {$table_singular}:', error);\n";
        $js .= "                alert(error.message);\n";
        $js .= "            }\n";
        $js .= "            throw error;\n";
        $js .= "        });\n";
        $js .= "}\n\n";
        $js .= "// Usage example:\n";
        $js .= "// delete" . ucfirst($table_singular) . "(123).then(() => location.reload());\n";
        
        return [
            'backend_code' => $php,
            'frontend_code' => $js,
            'filename_backend' => 'ajax_delete.php',
            'filename_frontend' => 'ajax-delete.js'
        ];
    }
    
    private function generateSearch($table, $operations, $return_format, $schema) {
        $table_singular = rtrim($table, 's');
        
        // Determine searchable fields from schema
        $search_fields = [];
        if ($schema && isset($schema['columns'])) {
            foreach ($schema['columns'] as $col_name => $col_info) {
                if (strpos($col_info['type'], 'varchar') !== false || strpos($col_info['type'], 'text') !== false) {
                    $search_fields[] = $col_name;
                }
            }
        } else {
            $search_fields = ['name', 'email']; // Default fields
        }
        
        // Backend PHP
        $php = "<?php\n";
        $php .= "/**\n";
        $php .= " * AJAX Endpoint - Search {$table}\n";
        $php .= " * Searches {$table} table\n";
        $php .= " */\n\n";
        $php .= "// Authentication check\n";
        $php .= "\$not->loggedin();\n\n";
        $php .= "// Set JSON header\n";
        $php .= "header('Content-Type: application/json');\n\n";
        $php .= "try {\n";
        $php .= "    \$database = new Database();\n";
        $php .= "    \$pdo = \$database->dbcrm();\n\n";
        $php .= "    // Get search term\n";
        $php .= "    \$search = trim(\$_GET['q'] ?? '');\n";
        $php .= "    \$limit = min((int)(\$_GET['limit'] ?? 10), 100);\n\n";
        $php .= "    if (empty(\$search)) {\n";
        $php .= "        echo json_encode(['success' => true, 'data' => []]);\n";
        $php .= "        exit;\n";
        $php .= "    }\n\n";
        $php .= "    // Build search query\n";
        $php .= "    \$sql = \"SELECT * FROM {$table} WHERE \";\n";
        $where_parts = [];
        foreach (array_slice($search_fields, 0, 3) as $field) {
            $where_parts[] = "{$field} LIKE ?";
        }
        $php .= "    \$sql .= \"" . implode(' OR ', $where_parts) . "\";\n";
        $php .= "    \$sql .= \" LIMIT ?\";\n\n";
        $php .= "    \$search_param = '%' . \$search . '%';\n";
        $php .= "    \$params = array_fill(0, " . count(array_slice($search_fields, 0, 3)) . ", \$search_param);\n";
        $php .= "    \$params[] = \$limit;\n\n";
        $php .= "    \$stmt = \$pdo->prepare(\$sql);\n";
        $php .= "    \$stmt->execute(\$params);\n";
        $php .= "    \$results = \$stmt->fetchAll(PDO::FETCH_ASSOC);\n\n";
        $php .= "    // Return results\n";
        $php .= "    echo json_encode([\n";
        $php .= "        'success' => true,\n";
        $php .= "        'data' => \$results,\n";
        $php .= "        'count' => count(\$results)\n";
        $php .= "    ]);\n\n";
        $php .= "} catch (PDOException \$e) {\n";
        $php .= "    error_log('Database error in search {$table}: ' . \$e->getMessage());\n";
        $php .= "    echo json_encode([\n";
        $php .= "        'success' => false,\n";
        $php .= "        'error' => \$lang['database_error']\n";
        $php .= "    ]);\n";
        $php .= "} catch (Exception \$e) {\n";
        $php .= "    error_log('Error in search {$table}: ' . \$e->getMessage());\n";
        $php .= "    echo json_encode([\n";
        $php .= "        'success' => false,\n";
        $php .= "        'error' => \$lang['general_error']\n";
        $php .= "    ]);\n";
        $php .= "}\n";
        
        // Frontend JavaScript
        $js = "// Search {$table} via AJAX\n";
        $js .= "function search" . ucfirst($table) . "(query, limit = 10) {\n";
        $js .= "    return fetch(`ajax_search.php?q=\${encodeURIComponent(query)}&limit=\${limit}`)\n";
        $js .= "        .then(response => response.json())\n";
        $js .= "        .then(data => {\n";
        $js .= "            if (data.success) {\n";
        $js .= "                return data.data;\n";
        $js .= "            } else {\n";
        $js .= "                throw new Error(data.error);\n";
        $js .= "            }\n";
        $js .= "        })\n";
        $js .= "        .catch(error => {\n";
        $js .= "            console.error('Error searching {$table}:', error);\n";
        $js .= "            throw error;\n";
        $js .= "        });\n";
        $js .= "}\n\n";
        $js .= "// Usage example with autocomplete:\n";
        $js .= "// const searchInput = document.getElementById('search');\n";
        $js .= "// searchInput.addEventListener('input', (e) => {\n";
        $js .= "//     search" . ucfirst($table) . "(e.target.value).then(results => {\n";
        $js .= "//         // Display results in dropdown\n";
        $js .= "//         console.log(results);\n";
        $js .= "//     });\n";
        $js .= "// });\n";
        
        return [
            'backend_code' => $php,
            'frontend_code' => $js,
            'filename_backend' => 'ajax_search.php',
            'filename_frontend' => 'ajax-search.js'
        ];
    }
    
    public function display($result, $json = false) {
        if ($json) {
            echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
            return;
        }
        
        if (isset($result['error'])) {
            echo "\n❌ ERROR: {$result['error']}\n";
            if (isset($result['valid_types'])) {
                echo "\nValid types: " . implode(', ', $result['valid_types']) . "\n";
            }
            return;
        }
        
        echo "\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "  AJAX ENDPOINT GENERATED\n";
        echo "═══════════════════════════════════════════════════════════════\n\n";
        
        if (!empty($result['filename_backend'])) {
            echo "BACKEND FILE: {$result['filename_backend']}\n";
            echo "───────────────────────────────────────────────────────────────\n";
            echo $result['backend_code'] . "\n\n";
        }
        
        if (!empty($result['filename_frontend'])) {
            echo "FRONTEND FILE: {$result['filename_frontend']}\n";
            echo "───────────────────────────────────────────────────────────────\n";
            echo $result['frontend_code'] . "\n\n";
        }
        
        if (!empty($result['validation_rules'])) {
            echo "VALIDATION RULES:\n";
            echo "───────────────────────────────────────────────────────────────\n";
            foreach ($result['validation_rules'] as $field => $rule) {
                echo "  {$field}: {$rule}\n";
            }
            echo "\n";
        }
        
        if (!empty($result['translation_keys_needed'])) {
            echo "TRANSLATION KEYS NEEDED:\n";
            echo "───────────────────────────────────────────────────────────────\n";
            foreach (array_unique($result['translation_keys_needed']) as $key) {
                echo "  • {$key}\n";
            }
            echo "\n";
        }
    }
}

// CLI Interface
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    $options = getopt('', ['type:', 'table:', 'operations::', 'format::', 'json', 'help']);
    
    if (isset($options['help']) || empty($options['type']) || empty($options['table'])) {
        echo <<<HELP

AJAX Endpoint Generator
═══════════════════════════════════════════════════════════════

Generates complete AJAX endpoints with backend PHP and frontend JavaScript.

USAGE:
  php generate_ajax_endpoint.php --type=TYPE --table=TABLE [OPTIONS]

REQUIRED:
  --type=TYPE          Endpoint type:
                       - get (fetch single record)
                       - post (create/update record)
                       - delete (delete record)
                       - search (search records)
  
  --table=TABLE        Database table name

OPTIONS:
  --operations=LIST    Comma-separated operations to include:
                       - validate (input validation)
                       - save (database save)
                       - log (audit logging)
                       Default: validate,log
  
  --format=FORMAT      Return format: json or html (default: json)
  --json               Output in JSON format
  --help               Show this help message

EXAMPLES:
  # Generate GET endpoint
  php generate_ajax_endpoint.php --type=get --table=users --operations=validate,log
  
  # Generate POST endpoint
  php generate_ajax_endpoint.php --type=post --table=email_queue --operations=validate,save,log
  
  # Generate DELETE endpoint
  php generate_ajax_endpoint.php --type=delete --table=email_templates --operations=validate,log
  
  # Generate SEARCH endpoint
  php generate_ajax_endpoint.php --type=search --table=leads --operations=validate
  
  # JSON output
  php generate_ajax_endpoint.php --type=get --table=users --json


HELP;
        exit(0);
    }
    
    $operations = [];
    if (isset($options['operations'])) {
        $operations = explode(',', $options['operations']);
    } else {
        $operations = ['validate', 'log'];
    }
    
    $format = $options['format'] ?? 'json';
    
    $generator = new AjaxEndpointGenerator();
    $result = $generator->generate($options['type'], $options['table'], $operations, $format);
    $generator->display($result, isset($options['json']));
}