<?php
// config/database.php - Enhanced with CalDAV support
class Database {
    private $host = 'localhost';
    private $db_name = 'crm_system';
    private $username = 'your_username';
    private $password = 'your_password';
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                )
            );
        } catch(PDOException $exception) {
            error_log("Database connection error: " . $exception->getMessage());
            throw new Exception("Database connection failed");
        }

        return $this->conn;
    }

    public function createCalDAVTables() {
        $sql = [
            // Users table for authentication
            "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                display_name VARCHAR(255),
                timezone VARCHAR(50) DEFAULT 'UTC',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",

            // CalDAV calendars table
            "CREATE TABLE IF NOT EXISTS caldav_calendars (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id VARCHAR(255) NOT NULL,
                uri VARCHAR(255) NOT NULL,
                displayname VARCHAR(255) NOT NULL,
                description TEXT,
                color VARCHAR(7) DEFAULT '#3366CC',
                timezone VARCHAR(255) DEFAULT 'UTC',
                components VARCHAR(255) DEFAULT 'VEVENT,VTODO',
                ctag INT DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_calendar (user_id, uri),
                INDEX idx_user_id (user_id)
            )",

            // CalDAV properties table
            "CREATE TABLE IF NOT EXISTS caldav_properties (
                id INT AUTO_INCREMENT PRIMARY KEY,
                path VARCHAR(1024) NOT NULL,
                name VARCHAR(255) NOT NULL,
                value MEDIUMTEXT,
                valuetype INT DEFAULT 1,
                UNIQUE KEY unique_property (path(255), name),
                INDEX idx_path (path(255))
            )",

            // CalDAV locks table
            "CREATE TABLE IF NOT EXISTS caldav_locks (
                id INT AUTO_INCREMENT PRIMARY KEY,
                owner VARCHAR(100),
                timeout INT UNSIGNED,
                created_at INT,
                token VARCHAR(100) UNIQUE,
                scope TINYINT,
                depth TINYINT,
                uri VARCHAR(1000),
                INDEX idx_token (token),
                INDEX idx_uri (uri(100))
            )",

            // CalDAV sync log table
            "CREATE TABLE IF NOT EXISTS caldav_sync_log (
                id INT AUTO_INCREMENT PRIMARY KEY,
                task_id INT,
                action ENUM('create', 'update', 'delete') NOT NULL,
                caldav_uid VARCHAR(255),
                sync_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                sync_status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
                error_message TEXT,
                user_id VARCHAR(255),
                FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
                INDEX idx_caldav_uid (caldav_uid),
                INDEX idx_sync_status (sync_status),
                INDEX idx_user_id (user_id)
            )"
        ];

        foreach ($sql as $query) {
            $this->conn->exec($query);
        }

        // Add CalDAV columns to existing tasks table
        $this->addCalDAVColumnsToTasks();
        
        // Insert default data
        $this->insertDefaultData();
    }

    private function addCalDAVColumnsToTasks() {
        $alterQueries = [
            "ALTER TABLE tasks ADD COLUMN IF NOT EXISTS caldav_uid VARCHAR(255) UNIQUE",
            "ALTER TABLE tasks ADD COLUMN IF NOT EXISTS caldav_etag VARCHAR(255)",
            "ALTER TABLE tasks ADD COLUMN IF NOT EXISTS caldav_lastmod TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
            "ALTER TABLE tasks ADD COLUMN IF NOT EXISTS caldav_sequence INT DEFAULT 0",
            "ALTER TABLE tasks ADD COLUMN IF NOT EXISTS calendar_id INT DEFAULT 1",
            "ALTER TABLE tasks ADD INDEX IF NOT EXISTS idx_caldav_uid (caldav_uid)",
            "ALTER TABLE tasks ADD INDEX IF NOT EXISTS idx_calendar_id (calendar_id)"
        ];

        foreach ($alterQueries as $query) {
            try {
                $this->conn->exec($query);
            } catch (PDOException $e) {
                // Column might already exist, which is fine
                if (strpos($e->getMessage(), 'Duplicate column name') === false) {
                    error_log("Error adding CalDAV columns: " . $e->getMessage());
                }
            }
        }
    }

    private function insertDefaultData() {
        // Insert default admin user
        $stmt = $this->conn->prepare("
            INSERT IGNORE INTO users (username, password, email, display_name) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            'admin',
            password_hash('admin123', PASSWORD_DEFAULT),
            'admin@yourcrm.com',
            'CRM Administrator'
        ]);

        // Insert default calendar
        $stmt = $this->conn->prepare("
            INSERT IGNORE INTO caldav_calendars (user_id, uri, displayname, description, color) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            'admin',
            'crm-tasks',
            'CRM Tasks',
            'Tasks and events from CRM system',
            '#007bff'
        ]);
    }

    public function updateCalendarCTag($calendarId) {
        $stmt = $this->conn->prepare("
            UPDATE caldav_calendars 
            SET ctag = ctag + 1, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        return $stmt->execute([$calendarId]);
    }

    public function generateUID() {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        ) . '@yourcrm.com';
    }

    public function generateETag($data) {
        return '"' . md5($data . microtime(true)) . '"';
    }
}

// Helper function to get database connection
function getDB() {
    static $database = null;
    if ($database === null) {
        $database = new Database();
    }
    return $database->getConnection();
}

// Helper function to initialize CalDAV database
function initializeCalDAVDatabase() {
    $database = new Database();
    $db = $database->getConnection();
    $database->createCalDAVTables();
    return $database;
}
?>