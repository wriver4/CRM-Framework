<?php
/**
 * Migration script to fix contacts.lead_id data type
 * Converts from varchar(10) to int(11) to match leads.id and other tables
 */

require_once __DIR__ . '/../classes/Database.php';

class ContactsLeadIdMigration extends Database {
    
    public function runMigration() {
        echo "Starting contacts.lead_id data type migration...\n\n";
        
        try {
            // Step 1: Check current data integrity
            echo "Step 1: Checking for non-numeric lead_id values...\n";
            $this->checkNonNumericLeadIds();
            
            // Step 2: Check for orphaned references
            echo "\nStep 2: Checking for orphaned lead_id references...\n";
            $this->checkOrphanedReferences();
            
            // Step 3: Create backup
            echo "\nStep 3: Creating backup table...\n";
            $this->createBackup();
            
            // Step 4: Perform the migration
            echo "\nStep 4: Converting lead_id column to int(11)...\n";
            $this->convertLeadIdColumn();
            
            // Step 5: Clean up orphaned references
            echo "\nStep 5: Cleaning up orphaned references...\n";
            $this->cleanupOrphanedReferences();
            
            // Step 6: Add foreign key constraint
            echo "\nStep 6: Adding foreign key constraint...\n";
            $this->addForeignKeyConstraint();
            
            // Step 7: Verify the changes
            echo "\nStep 7: Verifying migration...\n";
            $this->verifyMigration();
            
            echo "\n✅ Migration completed successfully!\n";
            
        } catch (Exception $e) {
            echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
            echo "You can restore from backup table: contacts_backup_before_lead_id_fix\n";
            throw $e;
        }
    }
    
    private function checkNonNumericLeadIds() {
        $sql = "SELECT id, lead_id, first_name, family_name 
                FROM contacts 
                WHERE lead_id NOT REGEXP '^[0-9]+$' OR lead_id = '' OR lead_id IS NULL";
        
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        if (count($results) > 0) {
            echo "Found " . count($results) . " contacts with non-numeric lead_id values:\n";
            foreach ($results as $row) {
                echo "  - Contact ID {$row['id']}: '{$row['lead_id']}' ({$row['first_name']} {$row['family_name']})\n";
            }
        } else {
            echo "✅ All lead_id values are numeric.\n";
        }
    }
    
    private function checkOrphanedReferences() {
        $sql = "SELECT c.id, c.lead_id, c.first_name, c.family_name
                FROM contacts c
                LEFT JOIN leads l ON CAST(c.lead_id AS UNSIGNED) = l.id
                WHERE l.id IS NULL AND c.lead_id != '' AND c.lead_id IS NOT NULL";
        
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        if (count($results) > 0) {
            echo "⚠️  Found " . count($results) . " contacts with orphaned lead_id references:\n";
            foreach ($results as $row) {
                echo "  - Contact ID {$row['id']}: lead_id '{$row['lead_id']}' ({$row['first_name']} {$row['family_name']})\n";
            }
            echo "These will be set to NULL during migration.\n";
        } else {
            echo "✅ All lead_id values reference valid leads.\n";
        }
    }
    
    private function createBackup() {
        $sql = "CREATE TABLE contacts_backup_before_lead_id_fix AS SELECT * FROM contacts";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute();
        
        // Verify backup was created
        $countSql = "SELECT COUNT(*) as count FROM contacts_backup_before_lead_id_fix";
        $countStmt = $this->dbcrm()->prepare($countSql);
        $countStmt->execute();
        $count = $countStmt->fetch()['count'];
        
        echo "✅ Backup created with {$count} records.\n";
    }
    
    private function convertLeadIdColumn() {
        $sql = "ALTER TABLE contacts MODIFY COLUMN lead_id int(11) DEFAULT NULL";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute();
        echo "✅ Column converted to int(11).\n";
    }
    
    private function cleanupOrphanedReferences() {
        $sql = "UPDATE contacts SET lead_id = NULL WHERE lead_id NOT IN (SELECT id FROM leads)";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute();
        $affected = $stmt->rowCount();
        
        if ($affected > 0) {
            echo "⚠️  Set {$affected} orphaned lead_id references to NULL.\n";
        } else {
            echo "✅ No orphaned references to clean up.\n";
        }
    }
    
    private function addForeignKeyConstraint() {
        try {
            $sql = "ALTER TABLE contacts 
                    ADD CONSTRAINT fk_contacts_lead_id 
                    FOREIGN KEY (lead_id) REFERENCES leads(id) 
                    ON DELETE SET NULL 
                    ON UPDATE CASCADE";
            $stmt = $this->dbcrm()->prepare($sql);
            $stmt->execute();
            echo "✅ Foreign key constraint added.\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
                echo "ℹ️  Foreign key constraint already exists.\n";
            } else {
                throw $e;
            }
        }
    }
    
    private function verifyMigration() {
        // Check column structure
        $sql = "DESCRIBE contacts";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute();
        $columns = $stmt->fetchAll();
        
        $leadIdColumn = null;
        foreach ($columns as $column) {
            if ($column['Field'] === 'lead_id') {
                $leadIdColumn = $column;
                break;
            }
        }
        
        if ($leadIdColumn && strpos($leadIdColumn['Type'], 'int(11)') !== false) {
            echo "✅ Column type verified: {$leadIdColumn['Type']}\n";
        } else {
            throw new Exception("Column type verification failed");
        }
        
        // Check sample data
        $sql = "SELECT id, lead_id, first_name, family_name FROM contacts LIMIT 5";
        $stmt = $this->dbcrm()->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        echo "✅ Sample data after migration:\n";
        foreach ($results as $row) {
            $leadId = $row['lead_id'] ?? 'NULL';
            echo "  - Contact ID {$row['id']}: lead_id {$leadId} ({$row['first_name']} {$row['family_name']})\n";
        }
    }
}

// Run the migration
try {
    $migration = new ContactsLeadIdMigration();
    $migration->runMigration();
} catch (Exception $e) {
    echo "\nMigration failed with error: " . $e->getMessage() . "\n";
    exit(1);
}
?>