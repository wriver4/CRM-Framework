<?php
/**
 * Minimal Database Connection for CLI Tools
 * 
 * This file provides a lightweight database connection by loading
 * only the Database class without initializing the full framework.
 */

// Prevent full framework initialization by suppressing errors from missing services
error_reporting(E_ERROR | E_PARSE);

// Set up minimal environment for Database class
if (!defined('DOCROOT')) {
    define('DOCROOT', dirname(__DIR__, 3));
}

// Load only the Database class directly
$database_class_file = DOCROOT . '/classes/Core/Database.php';
if (!file_exists($database_class_file)) {
    die("Error: Database class not found at: $database_class_file\n");
}

require_once $database_class_file;

// Restore normal error reporting
error_reporting(E_ALL);

/**
 * Get a PDO database connection using the Database class
 * 
 * @return PDO Database connection
 * @throws PDOException if connection fails
 */
function getDbConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $database = new Database();
            $pdo = $database->dbcrm();
        } catch (Exception $e) {
            throw new PDOException("Database connection failed: " . $e->getMessage(), (int)$e->getCode());
        }
    }
    
    return $pdo;
}