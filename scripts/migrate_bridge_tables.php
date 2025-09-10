<?php
/**
 * Bridge Table Migration Script
 * Runs the SQL migration and tests the bridge table integration
 */

require_once dirname(__DIR__) . '/config/system.php';

echo "=== Bridge Table Migration Script ===\n\n";

try {
    // Initialize database connection
    $db = new Database();
    $pdo = $db->dbcrm();
    
    echo "1. Reading migration SQL file...\n";
    $migration_sql = file_get_contents(dirname(__DIR__) . '/sql/migrations/2024_09_09_migrate_to_bridge_tables.sql');
    
    if (!$migration_sql) {
        throw new Exception("Could not read migration SQL file");
    }
    
    echo "2. Executing migration SQL...\n";
    
    // Split SQL into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $migration_sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^\s*--/', $stmt);
        }
    );
    
    $executed = 0;
    foreach ($statements as $sql) {
        if (trim($sql)) {
            try {
                $pdo->exec($sql);
                $executed++;
            } catch (PDOException $e) {
                // Skip if already exists or other non-critical errors
                if (strpos($e->getMessage(), 'already exists') === false && 
                    strpos($e->getMessage(), 'Duplicate entry') === false) {
                    echo "Warning: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    echo "   Executed {$executed} SQL statements\n\n";
    
    echo "3. Testing bridge table integration...\n";
    
    // Test bridge table models
    $structureInfo = new LeadStructureInfo();
    $documents = new LeadDocuments();
    $referrals = new LeadReferrals();
    $prospects = new Prospects();
    $contracting = new LeadContracting();
    $bridgeManager = new LeadBridgeManager();
    
    echo "   ✓ All bridge table models loaded successfully\n";
    
    // Test getting statistics
    $stats = $bridgeManager->getBridgeTableStats();
    echo "   Bridge table statistics:\n";
    foreach ($stats as $table => $info) {
        echo "     - {$info['label']}: {$info['count']} records\n";
    }
    
    echo "\n4. Testing data integrity...\n";
    $integrity_issues = $bridgeManager->validateDataIntegrity();
    if (empty($integrity_issues)) {
        echo "   ✓ No data integrity issues found\n";
    } else {
        echo "   ⚠ Data integrity issues found:\n";
        foreach ($integrity_issues as $issue) {
            echo "     - {$issue}\n";
        }
    }
    
    echo "\n5. Testing lead retrieval with bridge data...\n";
    $leads = new Leads();
    
    // Get a sample lead to test
    $sample_leads = $pdo->query("SELECT id FROM leads LIMIT 1")->fetchAll();
    if (!empty($sample_leads)) {
        $lead_id = $sample_leads[0]['id'];
        $complete_lead = $bridgeManager->getCompleteLeadData($lead_id);
        
        if ($complete_lead) {
            echo "   ✓ Successfully retrieved complete lead data for lead ID {$lead_id}\n";
            echo "     - Structure info: " . (isset($complete_lead['structure_info']) ? 'Present' : 'Not found') . "\n";
            echo "     - Documents: " . count($complete_lead['documents']['all'] ?? []) . " found\n";
            echo "     - Referral info: " . (isset($complete_lead['referral']) ? 'Present' : 'Not found') . "\n";
            echo "     - Prospect info: " . (isset($complete_lead['prospect']) ? 'Present' : 'Not found') . "\n";
            echo "     - Contracting info: " . (isset($complete_lead['contracting']) ? 'Present' : 'Not found') . "\n";
        } else {
            echo "   ⚠ Could not retrieve complete lead data\n";
        }
    } else {
        echo "   ⚠ No leads found in database to test\n";
    }
    
    echo "\n=== Migration completed successfully! ===\n";
    echo "\nNext steps:\n";
    echo "1. Test the lead forms to ensure they work with bridge tables\n";
    echo "2. Update any remaining view files that access old columns directly\n";
    echo "3. Test stage-specific modules (referrals, prospects, contracting)\n";
    echo "4. Consider dropping old columns after thorough testing\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}