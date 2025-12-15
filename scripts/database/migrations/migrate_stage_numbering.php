<?php
/**
 * Stage Numbering Migration Script
 * 
 * This script migrates the existing stage numbers to the new numbering system
 * with 10-unit increments and moves Closed Won/Lost before Contracting.
 * 
 * USAGE: php migrate_stage_numbering.php [--dry-run] [--force]
 * 
 * --dry-run: Show what would be changed without making changes
 * --force: Skip confirmation prompts
 */

require_once dirname(__DIR__) . '/config/system.php';
require_once __DIR__ . '/stage_remapping.php';

class StageMigration {
    private $db;
    private $dryRun = false;
    private $force = false;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function run($args = []) {
        $this->parseArgs($args);
        
        echo "=== Stage Numbering Migration ===\n";
        echo "This will update stage numbers to use 10-unit increments\n";
        echo "and move Closed Won/Lost before Contracting.\n\n";
        
        if ($this->dryRun) {
            echo "DRY RUN MODE - No changes will be made\n\n";
        }
        
        // Get current stage distribution
        $this->showCurrentStageDistribution();
        
        // Show planned changes
        $this->showPlannedChanges();
        
        if (!$this->dryRun && !$this->force) {
            echo "Do you want to proceed with the migration? (y/N): ";
            $response = trim(fgets(STDIN));
            if (strtolower($response) !== 'y') {
                echo "Migration cancelled.\n";
                return;
            }
        }
        
        // Perform migration
        $this->performMigration();
        
        echo "\nMigration completed successfully!\n";
    }
    
    private function parseArgs($args) {
        foreach ($args as $arg) {
            switch ($arg) {
                case '--dry-run':
                    $this->dryRun = true;
                    break;
                case '--force':
                    $this->force = true;
                    break;
            }
        }
    }
    
    private function showCurrentStageDistribution() {
        echo "Current stage distribution:\n";
        
        $sql = "SELECT stage, COUNT(*) as count FROM leads GROUP BY stage ORDER BY stage";
        $stmt = $this->db->dbcrm()->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($results as $row) {
            echo sprintf("  Stage %s: %d leads\n", $row['stage'], $row['count']);
        }
        echo "\n";
    }
    
    private function showPlannedChanges() {
        echo "Planned stage number changes:\n";
        
        $mapping = StageRemapping::getOldToNewMapping();
        $stageNames = [];
        foreach (StageRemapping::getNewStageMapping() as $newStage => $data) {
            $stageNames[$data['old_stage']] = $data['name'];
        }
        
        foreach ($mapping as $oldStage => $newStage) {
            $name = $stageNames[$oldStage] ?? 'Unknown';
            echo sprintf("  %d (%s) -> %d\n", $oldStage, $name, $newStage);
        }
        echo "\n";
    }
    
    private function performMigration() {
        if (!$this->dryRun) {
            echo "Starting migration...\n";
            
            // Start transaction
            $this->db->dbcrm()->beginTransaction();
            
            try {
                $this->migrateLeadsTable();
                $this->migrateLeadsExtrasTable();
                $this->migrateBackupTables();
                
                // Commit transaction
                $this->db->dbcrm()->commit();
                echo "Database migration completed successfully.\n";
                
            } catch (Exception $e) {
                // Rollback on error
                $this->db->dbcrm()->rollback();
                throw new Exception("Migration failed: " . $e->getMessage());
            }
        } else {
            echo "DRY RUN: Would migrate the following tables:\n";
            echo "  - leads\n";
            echo "  - leads_extras\n";
            echo "  - leads_backup_* (if they exist)\n";
        }
    }
    
    private function migrateLeadsTable() {
        echo "Migrating leads table...\n";
        
        $mapping = StageRemapping::getOldToNewMapping();
        
        foreach ($mapping as $oldStage => $newStage) {
            $sql = "UPDATE leads SET stage = :new_stage WHERE stage = :old_stage";
            $stmt = $this->db->dbcrm()->prepare($sql);
            $stmt->bindValue(':new_stage', $newStage, PDO::PARAM_INT);
            $stmt->bindValue(':old_stage', $oldStage, PDO::PARAM_INT);
            
            if (!$this->dryRun) {
                $stmt->execute();
                $affected = $stmt->rowCount();
                echo "  Updated $affected leads from stage $oldStage to $newStage\n";
            }
        }
    }
    
    private function migrateLeadsExtrasTable() {
        // Check if leads_extras table exists and has stage column
        $sql = "SHOW TABLES LIKE 'leads_extras'";
        $stmt = $this->db->dbcrm()->query($sql);
        if ($stmt->rowCount() == 0) {
            echo "leads_extras table not found, skipping...\n";
            return;
        }
        
        $sql = "SHOW COLUMNS FROM leads_extras LIKE 'stage'";
        $stmt = $this->db->dbcrm()->query($sql);
        if ($stmt->rowCount() == 0) {
            echo "leads_extras table has no stage column, skipping...\n";
            return;
        }
        
        echo "Migrating leads_extras table...\n";
        
        $mapping = StageRemapping::getOldToNewMapping();
        
        foreach ($mapping as $oldStage => $newStage) {
            $sql = "UPDATE leads_extras SET stage = :new_stage WHERE stage = :old_stage";
            $stmt = $this->db->dbcrm()->prepare($sql);
            $stmt->bindValue(':new_stage', $newStage, PDO::PARAM_INT);
            $stmt->bindValue(':old_stage', $oldStage, PDO::PARAM_INT);
            
            if (!$this->dryRun) {
                $stmt->execute();
                $affected = $stmt->rowCount();
                if ($affected > 0) {
                    echo "  Updated $affected records in leads_extras from stage $oldStage to $newStage\n";
                }
            }
        }
    }
    
    private function migrateBackupTables() {
        // Find backup tables
        $sql = "SHOW TABLES LIKE 'leads_backup_%'";
        $stmt = $this->db->dbcrm()->query($sql);
        $backupTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($backupTables as $table) {
            // Check if table has stage column
            $sql = "SHOW COLUMNS FROM `$table` LIKE 'stage'";
            $stmt = $this->db->dbcrm()->query($sql);
            if ($stmt->rowCount() == 0) {
                continue;
            }
            
            echo "Migrating backup table $table...\n";
            
            $mapping = StageRemapping::getOldToNewMapping();
            
            foreach ($mapping as $oldStage => $newStage) {
                $sql = "UPDATE `$table` SET stage = :new_stage WHERE stage = :old_stage";
                $stmt = $this->db->dbcrm()->prepare($sql);
                $stmt->bindValue(':new_stage', $newStage, PDO::PARAM_INT);
                $stmt->bindValue(':old_stage', $oldStage, PDO::PARAM_INT);
                
                if (!$this->dryRun) {
                    $stmt->execute();
                    $affected = $stmt->rowCount();
                    if ($affected > 0) {
                        echo "  Updated $affected records in $table from stage $oldStage to $newStage\n";
                    }
                }
            }
        }
    }
}

// Run migration if called directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    try {
        $migration = new StageMigration();
        $migration->run(array_slice($argv, 1));
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        exit(1);
    }
}