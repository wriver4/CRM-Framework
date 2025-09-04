<?php
/**
 * Migration script to rename fullname column to full_name in contacts table
 * Run this script to safely migrate the database schema
 */

require_once '../../config/system.php';
// Database class is loaded via autoloader in system.php

class FullnameMigration
{
    private $db;
    private $pdo;
    
    public function __construct()
    {
        $this->db = new Database();
        $this->pdo = $this->db->dbcrm();
    }
    
    public function run()
    {
        echo "Starting fullname to full_name migration...\n";
        
        try {
            // Step 1: Check if migration is needed
            if (!$this->needsMigration()) {
                echo "Migration not needed - full_name column already exists and fullname doesn't exist.\n";
                return true;
            }
            
            // Step 2: Create backup
            echo "Creating backup table...\n";
            $this->createBackup();
            
            // Step 3: Add new column
            echo "Adding full_name column...\n";
            $this->addFullNameColumn();
            
            // Step 4: Copy data
            echo "Copying data from fullname to full_name...\n";
            $this->copyData();
            
            // Step 5: Verify data integrity
            echo "Verifying data integrity...\n";
            if (!$this->verifyDataIntegrity()) {
                throw new Exception("Data integrity check failed!");
            }
            
            // Step 6: Drop old column
            echo "Dropping old fullname column...\n";
            $this->dropOldColumn();
            
            echo "Migration completed successfully!\n";
            $this->showStats();
            
            return true;
            
        } catch (Exception $e) {
            echo "Migration failed: " . $e->getMessage() . "\n";
            echo "Database has been left in a safe state. Manual intervention may be required.\n";
            return false;
        }
    }
    
    private function needsMigration()
    {
        // Check if fullname column exists and full_name doesn't
        $stmt = $this->pdo->query("SHOW COLUMNS FROM contacts LIKE 'fullname'");
        $fullnameExists = $stmt->rowCount() > 0;
        
        $stmt = $this->pdo->query("SHOW COLUMNS FROM contacts LIKE 'full_name'");
        $fullNameExists = $stmt->rowCount() > 0;
        
        return $fullnameExists && !$fullNameExists;
    }
    
    private function createBackup()
    {
        $sql = "CREATE TABLE contacts_backup_before_fullname_rename AS SELECT * FROM contacts";
        $this->pdo->exec($sql);
        echo "Backup table 'contacts_backup_before_fullname_rename' created.\n";
    }
    
    private function addFullNameColumn()
    {
        $sql = "ALTER TABLE contacts ADD COLUMN full_name varchar(200) NOT NULL DEFAULT ''";
        $this->pdo->exec($sql);
    }
    
    private function copyData()
    {
        $sql = "UPDATE contacts SET full_name = fullname";
        $affectedRows = $this->pdo->exec($sql);
        echo "Copied data for $affectedRows rows.\n";
    }
    
    private function verifyDataIntegrity()
    {
        $sql = "SELECT COUNT(*) as total, 
                       SUM(CASE WHEN fullname = full_name THEN 1 ELSE 0 END) as matching
                FROM contacts";
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total'] != $result['matching']) {
            echo "Data integrity check failed: {$result['matching']} matching out of {$result['total']} total rows.\n";
            return false;
        }
        
        echo "Data integrity verified: all {$result['total']} rows match.\n";
        return true;
    }
    
    private function dropOldColumn()
    {
        $sql = "ALTER TABLE contacts DROP COLUMN fullname";
        $this->pdo->exec($sql);
    }
    
    private function showStats()
    {
        $sql = "SELECT COUNT(*) as total_contacts, 
                       COUNT(full_name) as full_name_count,
                       COUNT(CASE WHEN full_name = '' THEN 1 END) as empty_full_names
                FROM contacts";
        $stmt = $this->pdo->query($sql);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "\nMigration Statistics:\n";
        echo "- Total contacts: {$stats['total_contacts']}\n";
        echo "- Contacts with full_name: {$stats['full_name_count']}\n";
        echo "- Empty full_name values: {$stats['empty_full_names']}\n";
    }
}

// Run the migration
if (php_sapi_name() === 'cli') {
    $migration = new FullnameMigration();
    $success = $migration->run();
    exit($success ? 0 : 1);
} else {
    echo "This script must be run from the command line.\n";
    exit(1);
}