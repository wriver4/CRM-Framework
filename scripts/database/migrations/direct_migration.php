<?php
/**
 * Direct Database Migration Script
 * 
 * This script directly connects to the database and performs the stage migration
 * without requiring the full system initialization.
 */

require_once __DIR__ . '/stage_remapping.php';

// Database configuration from system
$host = 'localhost';
$dbname = 'democrm_democrm';
$username = 'democrm_democrm';
$password = 'b3J2sy5T4JNm60';

try {
    echo "=== Direct Stage Migration ===\n";
    echo "Connecting to database...\n";
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected successfully!\n\n";
    
    // Show current stage distribution
    echo "Current stage distribution:\n";
    $stmt = $pdo->query("SELECT stage, COUNT(*) as count FROM leads GROUP BY stage ORDER BY CAST(stage AS UNSIGNED)");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalLeads = 0;
    foreach ($results as $row) {
        echo sprintf("  Stage %s: %d leads\n", $row['stage'], $row['count']);
        $totalLeads += $row['count'];
    }
    echo sprintf("  TOTAL: %d leads\n\n", $totalLeads);
    
    // Show planned changes
    echo "Planned stage changes:\n";
    $mapping = StageRemapping::getOldToNewMapping();
    $stageNames = [];
    foreach (StageRemapping::getNewStageMapping() as $newStage => $data) {
        $stageNames[$data['old_stage']] = $data['name'];
    }
    
    foreach ($mapping as $oldStage => $newStage) {
        $name = $stageNames[$oldStage] ?? 'Unknown';
        echo sprintf("  %d -> %d (%s)\n", $oldStage, $newStage, $name);
    }
    
    echo "\nStarting migration...\n";
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Migrate leads table
        echo "Migrating leads table...\n";
        
        foreach ($mapping as $oldStage => $newStage) {
            $sql = "UPDATE leads SET stage = :new_stage WHERE stage = :old_stage";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':new_stage', $newStage, PDO::PARAM_INT);
            $stmt->bindValue(':old_stage', $oldStage, PDO::PARAM_INT);
            $stmt->execute();
            
            $affected = $stmt->rowCount();
            if ($affected > 0) {
                echo "  Updated $affected leads from stage $oldStage to $newStage\n";
            }
        }
        
        // Check if leads_extras table exists and migrate if needed
        $stmt = $pdo->query("SHOW TABLES LIKE 'leads_extras'");
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->query("SHOW COLUMNS FROM leads_extras LIKE 'stage'");
            if ($stmt->rowCount() > 0) {
                echo "Migrating leads_extras table...\n";
                
                foreach ($mapping as $oldStage => $newStage) {
                    $sql = "UPDATE leads_extras SET stage = :new_stage WHERE stage = :old_stage";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':new_stage', $newStage, PDO::PARAM_INT);
                    $stmt->bindValue(':old_stage', $oldStage, PDO::PARAM_INT);
                    $stmt->execute();
                    
                    $affected = $stmt->rowCount();
                    if ($affected > 0) {
                        echo "  Updated $affected records in leads_extras from stage $oldStage to $newStage\n";
                    }
                }
            }
        }
        
        // Migrate backup tables if they exist
        $stmt = $pdo->query("SHOW TABLES LIKE 'leads_backup_%'");
        $backupTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($backupTables as $table) {
            $stmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE 'stage'");
            if ($stmt->rowCount() > 0) {
                echo "Migrating backup table $table...\n";
                
                foreach ($mapping as $oldStage => $newStage) {
                    $sql = "UPDATE `$table` SET stage = :new_stage WHERE stage = :old_stage";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':new_stage', $newStage, PDO::PARAM_INT);
                    $stmt->bindValue(':old_stage', $oldStage, PDO::PARAM_INT);
                    $stmt->execute();
                    
                    $affected = $stmt->rowCount();
                    if ($affected > 0) {
                        echo "  Updated $affected records in $table from stage $oldStage to $newStage\n";
                    }
                }
            }
        }
        
        // Commit transaction
        $pdo->commit();
        echo "\nMigration completed successfully!\n";
        
        // Show new stage distribution
        echo "\nNew stage distribution:\n";
        $stmt = $pdo->query("SELECT stage, COUNT(*) as count FROM leads GROUP BY stage ORDER BY CAST(stage AS UNSIGNED)");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($results as $row) {
            $stageName = '';
            foreach (StageRemapping::getNewStageMapping() as $stageNum => $data) {
                if ($stageNum == $row['stage']) {
                    $stageName = $data['name'];
                    break;
                }
            }
            echo sprintf("  Stage %s: %d leads (%s)\n", $row['stage'], $row['count'], $stageName);
        }
        
        echo "\n✅ Migration completed successfully!\n";
        echo "\nKey changes:\n";
        echo "- Closed Won moved to stage 130 (before Contracting)\n";
        echo "- Closed Lost moved to stage 140 (before Contracting)\n";
        echo "- Contracting moved to stage 150 (after Won/Lost)\n";
        echo "- All stages now use 10-unit increments\n";
        echo "- Lead dropdown will show: 10,20,30,40,50,140\n";
        
    } catch (Exception $e) {
        // Rollback on error
        $pdo->rollback();
        throw new Exception("Migration failed: " . $e->getMessage());
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "\nIf you're getting database connection errors, please update the database credentials at the top of this script.\n";
    exit(1);
}