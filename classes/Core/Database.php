<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */
/** Warning - Warning
 * This File will show errors that are not errors following $this->
 * In VSCode not sure about other editors
 */

/**
 * Database Connection Class
 * 
 * SCHEMA OVERVIEW:
 * 
 * Core Entities:
 * - users: User accounts (id, username, full_name, email, role_id, state_id)
 * - leads: Lead management (id, contact_id, service_id, source_id, structure_id, notes)
 * - contacts: Contact info (id, full_name, email, phone, address, city, state, zip)
 * - notes: Linked notes (id, lead_id, contact_id, user_id, note_text, created_at)
 * - communications: Communication history (id, lead_id, contact_id, user_id, type, content, created_at)
 * - sales: Sales pipeline (id, lead_id, amount, status, close_date)
 * 
 * Security:
 * - roles: User roles (id, role_name, description, permissions)
 * - permissions: System permissions (id, permission_name, description, module)  
 * - roles_permissions: Role-permission mapping (role_id, permission_id) - BRIDGE TABLE
 * - user_sessions: Session management (id, user_id, session_token, expires_at)
 * 
 * System:
 * - audit: Activity logging (id, user_id, action, table_name, record_id, old_values, new_values, ip_address, created_at)
 * - internal_errors: Error tracking (id, error_message, file_path, line_number, user_id, created_at)
 * - php_error_log: PHP error tracking (id, error_type, message, file, line, created_at)
 * 
 * Lookup Tables:
 * - lead_sources: Lead source options (id, source_name, description)
 * - lead_services: Available services (id, service_name, description, active)
 * - lead_structures: Structure types (id, structure_name, description)
 * - contact_types: Contact classifications (id, type_name, description)
 * - system_states: Active/inactive states (id, state_name, description)
 * 
 * Bridge Tables:
 * - roles_permissions: roles ↔ permissions (many-to-many)
 * - lead_contacts: leads ↔ contacts (if multiple contacts per lead)
 * - user_permissions: direct user permissions override (user_id, permission_id)
 * 
 * Key Foreign Key Relationships:
 * - users.role_id → roles.id
 * - users.state_id → system_states.id
 * - leads.contact_id → contacts.id
 * - leads.service_id → lead_services.id
 * - leads.source_id → lead_sources.id
 * - leads.structure_id → lead_structures.id
 * - notes.lead_id → leads.id
 * - notes.contact_id → contacts.id
 * - notes.user_id → users.id
 * - audit.user_id → users.id
 * - roles_permissions.role_id → roles.id
 * - roles_permissions.permission_id → permissions.id
 */

class Database
{
    protected $sqlLogger;
    protected $crm_host;
    protected $crm_database;
    protected $crm_username;
    protected $crm_password;
    protected $character_set;
    protected $options;

    public function __construct()
    {
        // Check if we're in test environment and use test database credentials
        if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'testing') {
            $this->crm_host = $_ENV['DB_HOST'] ?? 'localhost';
            $this->crm_database = $_ENV['DB_NAME'] ?? 'democrm_test';
            $this->crm_username = $_ENV['DB_USER'] ?? 'democrm_test';
            $this->crm_password = $_ENV['DB_PASS'] ?? 'TestDB_2025_Secure!';
        } else {
            // Production database connection information
            $this->crm_host = 'localhost';
            $this->crm_database = 'democrm_democrm';
            $this->crm_username = 'democrm_democrm';
            $this->crm_password = 'b3J2sy5T4JNm60';
        }

        $this->character_set = 'utf8mb4';
        $this->options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        // Initialize SQL logger (lazy loading to avoid circular dependency)
        $this->sqlLogger = null;
    }

    /**
     * Get SQL logger instance (lazy loading to avoid circular dependency)
     */
    protected function getSqlLogger()
    {
        if ($this->sqlLogger === null && class_exists('SqlErrorLogger')) {
            // Only create if we're not already in SqlErrorLogger to avoid circular dependency
            if (get_class($this) !== 'SqlErrorLogger') {
                $this->sqlLogger = new SqlErrorLogger();
            }
        }
        return $this->sqlLogger;
    }

    public function dbcrm()
    {
        static $DBCRM = null;
        if (is_null($DBCRM)) {
            $dsn = 'mysql:host=' . $this->crm_host . ';dbname=' . $this->crm_database . ';charset=' . $this->character_set;
            try {
                $pdo = new \PDO($dsn, $this->crm_username, $this->crm_password, $this->options);
                //echo "Connected successfully";
            } catch (\PDOException $e) {
                throw new \PDOException($e->getMessage(), (int)$e->getCode());
                echo "Connection failed: " . $e->getMessage();
                exit;
            }
            $DBCRM = $pdo;
        }
        return $DBCRM;
    }

    /**
     * Execute prepared statement with comprehensive logging
     */
    protected function executeWithLogging($stmt, $sql, $parameters = [], $context = [])
    {
        $startTime = microtime(true);
        
        try {
            // Log parameter mismatch if detected
            $this->validateParameters($sql, $parameters);
            
            $result = $stmt->execute();
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            // Log successful execution (only if debug mode is enabled)
            $logger = $this->getSqlLogger();
            if ($logger) {
                $logger->logSqlExecution($sql, $parameters, $executionTime, true);
            }
            
            return $result;
            
        } catch (PDOException $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            // Log the SQL error with full context
            $errorContext = array_merge($context, [
                'sql' => $sql,
                'parameters' => $parameters,
                'execution_time_ms' => $executionTime,
                'pdo_error_code' => $e->getCode(),
                'pdo_error_info' => $e->errorInfo ?? null
            ]);
            
            $logger = $this->getSqlLogger();
            if ($logger) {
                $logger->logSqlError($e->getMessage(), $errorContext);
                // Log failed execution
                $logger->logSqlExecution($sql, $parameters, $executionTime, false);
            }
            
            // Re-throw the exception
            throw $e;
        }
    }

    /**
     * Prepare and execute SQL with logging
     */
    protected function prepareAndExecute($sql, $parameters = [], $context = [])
    {
        try {
            $stmt = $this->dbcrm()->prepare($sql);
            
            // Bind parameters
            foreach ($parameters as $key => $value) {
                $paramName = is_numeric($key) ? $key + 1 : $key;
                if (!is_numeric($key) && !str_starts_with($key, ':')) {
                    $paramName = ':' . $key;
                }
                $stmt->bindValue($paramName, $value);
            }
            
            return $this->executeWithLogging($stmt, $sql, $parameters, $context);
            
        } catch (PDOException $e) {
            // Additional logging for prepare failures
            $this->sqlLogger->logSqlError("SQL Prepare failed: " . $e->getMessage(), [
                'sql' => $sql,
                'parameters' => $parameters,
                'context' => $context
            ]);
            throw $e;
        }
    }

    /**
     * Validate SQL parameters against query
     */
    private function validateParameters($sql, $parameters)
    {
        // Extract named parameters from SQL
        preg_match_all('/:(\w+)/', $sql, $matches);
        $expectedParams = $matches[1] ?? [];
        
        if (!empty($expectedParams)) {
            $providedKeys = [];
            foreach (array_keys($parameters) as $key) {
                if (is_string($key)) {
                    $providedKeys[] = str_starts_with($key, ':') ? substr($key, 1) : $key;
                }
            }
            
            $missing = array_diff($expectedParams, $providedKeys);
            $extra = array_diff($providedKeys, $expectedParams);
            
            if (!empty($missing) || !empty($extra)) {
                $logger = $this->getSqlLogger();
                if ($logger) {
                    $logger->logParameterMismatch($sql, $parameters, $expectedParams);
                }
            }
        }
    }

    /**
     * Log form submission error
     */
    protected function logFormError($formName, $error, $formData = [])
    {
        $logger = $this->getSqlLogger();
        if ($logger) {
            $logger->logFormError($formName, $error, $formData);
        }
    }
}