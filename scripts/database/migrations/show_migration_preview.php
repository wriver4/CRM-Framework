<?php
/**
 * Migration Preview Script
 * 
 * Shows what the migration will change without making any changes.
 */

require_once dirname(__DIR__) . '/config/system.php';
require_once __DIR__ . '/stage_remapping.php';

echo "=== Stage System Migration Preview ===\n\n";

try {
    $db = new Database();
    
    // Get current stage distribution
    echo "CURRENT STAGE DISTRIBUTION:\n";
    echo "==========================\n";
    
    $sql = "SELECT stage, COUNT(*) as count FROM leads GROUP BY stage ORDER BY CAST(stage AS UNSIGNED)";
    $stmt = $db->dbcrm()->query($sql);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalLeads = 0;
    foreach ($results as $row) {
        echo sprintf("Stage %s: %d leads\n", str_pad($row['stage'], 3), $row['count']);
        $totalLeads += $row['count'];
    }
    echo sprintf("TOTAL: %d leads\n\n", $totalLeads);
    
    // Show planned changes
    echo "PLANNED STAGE CHANGES:\n";
    echo "=====================\n";
    
    $mapping = StageRemapping::getOldToNewMapping();
    $stageNames = [];
    foreach (StageRemapping::getNewStageMapping() as $newStage => $data) {
        $stageNames[$data['old_stage']] = $data['name'];
    }
    
    foreach ($mapping as $oldStage => $newStage) {
        $name = $stageNames[$oldStage] ?? 'Unknown';
        echo sprintf("%s -> %s (%s)\n", 
            str_pad($oldStage, 3), 
            str_pad($newStage, 3), 
            $name
        );
    }
    
    echo "\nKEY IMPROVEMENTS:\n";
    echo "================\n";
    echo "✅ 10-unit increments allow adding 9 stages between any two existing stages\n";
    echo "✅ Closed Won (130) and Closed Lost (140) moved before Contracting (150)\n";
    echo "✅ Lead dropdown shows: 10,20,30,40,50,140 (Lead through Prospect + Closed Lost)\n";
    echo "✅ Trigger stages identified: 40 (Referral), 50 (Prospect), 140 (Closed Lost)\n";
    echo "✅ Module filtering updated for proper lead distribution\n";
    
    echo "\nMODULE STAGE FILTERS:\n";
    echo "====================\n";
    $filters = StageRemapping::getModuleStageFilters();
    foreach ($filters as $module => $stages) {
        echo sprintf("%-12s: %s\n", ucfirst($module), implode(', ', $stages));
    }
    
    echo "\nNEXT STEPS:\n";
    echo "==========\n";
    echo "1. Review this preview carefully\n";
    echo "2. Run: php scripts/migrate_stage_numbering.php --dry-run\n";
    echo "3. If satisfied, run: php scripts/migrate_stage_numbering.php\n";
    echo "4. Test the system thoroughly after migration\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nNote: This preview requires database access.\n";
    echo "The stage mapping configuration is still valid and ready for migration.\n";
}