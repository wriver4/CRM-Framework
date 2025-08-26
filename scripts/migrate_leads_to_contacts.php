<?php
/**
 * Lead to Contact Migration Script
 * This script migrates existing leads to create corresponding contacts
 * and establishes the relationship between them.
 */

require_once dirname(__DIR__) . '/config/system.php';
require_once dirname(__DIR__) . '/classes/LeadsEnhanced.php';
require_once dirname(__DIR__) . '/classes/ContactsEnhanced.php';

// Set execution time limit for large migrations
set_time_limit(0);
ini_set('memory_limit', '512M');

class LeadContactMigration
{
    private $leadsEnhanced;
    private $contactsEnhanced;
    private $batchSize;
    private $logFile;

    public function __construct($batchSize = 100)
    {
        $this->leadsEnhanced = new LeadsEnhanced();
        $this->contactsEnhanced = new ContactsEnhanced();
        $this->batchSize = $batchSize;
        $this->logFile = dirname(__DIR__) . '/logs/lead_contact_migration.log';
        
        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    /**
     * Log message to file and console
     */
    private function log($message)
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;
        
        echo $logMessage;
        file_put_contents($this->logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }

    /**
     * Check if migration is needed
     */
    public function checkMigrationStatus()
    {
        try {
            $db = new Database();
            $dbcrm = $db->dbcrm();
            
            // Check if contact_id column exists in leads table
            $sql = "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE table_name = 'leads' 
                    AND column_name = 'contact_id' 
                    AND table_schema = DATABASE()";
            $stmt = $dbcrm->query($sql);
            $result = $stmt->fetch();
            
            if ($result['count'] == 0) {
                $this->log("ERROR: contact_id column not found in leads table. Please run the database migration first.");
                return false;
            }
            
            // Count leads without contact_id
            $sql = "SELECT COUNT(*) as count FROM leads WHERE contact_id IS NULL";
            $stmt = $dbcrm->query($sql);
            $result = $stmt->fetch();
            
            $this->log("Found {$result['count']} leads without contact_id that need migration.");
            
            return $result['count'] > 0;
            
        } catch (Exception $e) {
            $this->log("ERROR checking migration status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Run the migration
     */
    public function runMigration($dryRun = false)
    {
        $this->log("Starting lead-to-contact migration" . ($dryRun ? " (DRY RUN)" : ""));
        
        if (!$this->checkMigrationStatus()) {
            return false;
        }
        
        $totalProcessed = 0;
        $totalCreated = 0;
        $totalLinked = 0;
        $totalErrors = 0;
        $startTime = time();
        
        try {
            do {
                $result = $this->leadsEnhanced->migrate_leads_to_contacts($this->batchSize);
                
                $totalProcessed += $result['processed'];
                $totalCreated += $result['created_contacts'];
                $totalLinked += $result['linked_contacts'];
                $totalErrors += count($result['errors']);
                
                $this->log("Batch completed: {$result['processed']} processed, {$result['created_contacts']} contacts created, {$result['linked_contacts']} linked");
                
                if (!empty($result['errors'])) {
                    foreach ($result['errors'] as $error) {
                        $this->log("ERROR: {$error}");
                    }
                }
                
                // Continue until no more leads are processed
            } while ($result['processed'] > 0);
            
            $duration = time() - $startTime;
            
            $this->log("Migration completed in {$duration} seconds");
            $this->log("Total processed: {$totalProcessed}");
            $this->log("Total contacts created: {$totalCreated}");
            $this->log("Total leads linked: {$totalLinked}");
            $this->log("Total errors: {$totalErrors}");
            
            return true;
            
        } catch (Exception $e) {
            $this->log("FATAL ERROR during migration: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate migration results
     */
    public function validateMigration()
    {
        $this->log("Validating migration results...");
        
        try {
            $db = new Database();
            $dbcrm = $db->dbcrm();
            
            // Count leads without contact_id
            $sql = "SELECT COUNT(*) as count FROM leads WHERE contact_id IS NULL";
            $stmt = $dbcrm->query($sql);
            $result = $stmt->fetch();
            $leadsWithoutContact = $result['count'];
            
            // Count leads with invalid contact_id
            $sql = "SELECT COUNT(*) as count FROM leads l 
                    LEFT JOIN contacts c ON l.contact_id = c.id 
                    WHERE l.contact_id IS NOT NULL AND c.id IS NULL";
            $stmt = $dbcrm->query($sql);
            $result = $stmt->fetch();
            $leadsWithInvalidContact = $result['count'];
            
            // Count total leads and contacts
            $sql = "SELECT COUNT(*) as count FROM leads";
            $stmt = $dbcrm->query($sql);
            $result = $stmt->fetch();
            $totalLeads = $result['count'];
            
            $sql = "SELECT COUNT(*) as count FROM contacts";
            $stmt = $dbcrm->query($sql);
            $result = $stmt->fetch();
            $totalContacts = $result['count'];
            
            $this->log("Validation Results:");
            $this->log("- Total leads: {$totalLeads}");
            $this->log("- Total contacts: {$totalContacts}");
            $this->log("- Leads without contact_id: {$leadsWithoutContact}");
            $this->log("- Leads with invalid contact_id: {$leadsWithInvalidContact}");
            
            if ($leadsWithoutContact == 0 && $leadsWithInvalidContact == 0) {
                $this->log("✓ Migration validation PASSED");
                return true;
            } else {
                $this->log("✗ Migration validation FAILED");
                return false;
            }
            
        } catch (Exception $e) {
            $this->log("ERROR during validation: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate migration report
     */
    public function generateReport()
    {
        $this->log("Generating migration report...");
        
        try {
            $db = new Database();
            $dbcrm = $db->dbcrm();
            
            // Get sample of migrated data
            $sql = "SELECT 
                        l.id as lead_id,
                        l.lead_number,
                        l.first_name,
                        l.last_name,
                        l.email,
                        l.contact_id,
                        c.fullname as contact_name,
                        c.personal_email as contact_email
                    FROM leads l
                    INNER JOIN contacts c ON l.contact_id = c.id
                    ORDER BY l.id DESC
                    LIMIT 10";
            
            $stmt = $dbcrm->query($sql);
            $samples = $stmt->fetchAll();
            
            $this->log("Sample of migrated lead-contact pairs:");
            foreach ($samples as $sample) {
                $this->log("  Lead #{$sample['lead_number']} ({$sample['first_name']} {$sample['last_name']}) -> Contact #{$sample['contact_id']} ({$sample['contact_name']})");
            }
            
            // Check for potential duplicates
            $sql = "SELECT personal_email, COUNT(*) as count 
                    FROM contacts 
                    WHERE personal_email IS NOT NULL AND personal_email != ''
                    GROUP BY personal_email 
                    HAVING COUNT(*) > 1
                    LIMIT 10";
            
            $stmt = $dbcrm->query($sql);
            $duplicates = $stmt->fetchAll();
            
            if (!empty($duplicates)) {
                $this->log("Potential duplicate contacts found (by email):");
                foreach ($duplicates as $duplicate) {
                    $this->log("  Email: {$duplicate['personal_email']} ({$duplicate['count']} contacts)");
                }
            } else {
                $this->log("No duplicate contacts found by email");
            }
            
        } catch (Exception $e) {
            $this->log("ERROR generating report: " . $e->getMessage());
        }
    }

    /**
     * Rollback migration (for testing purposes)
     */
    public function rollbackMigration()
    {
        $this->log("WARNING: Rolling back migration - this will remove contact_id from all leads");
        
        try {
            $db = new Database();
            $dbcrm = $db->dbcrm();
            
            // Remove contact_id from leads
            $sql = "UPDATE leads SET contact_id = NULL";
            $stmt = $dbcrm->prepare($sql);
            $result = $stmt->execute();
            
            if ($result) {
                $this->log("Rollback completed - all contact_id values removed from leads");
                return true;
            } else {
                $this->log("ERROR: Rollback failed");
                return false;
            }
            
        } catch (Exception $e) {
            $this->log("ERROR during rollback: " . $e->getMessage());
            return false;
        }
    }
}

// Command line interface
if (php_sapi_name() === 'cli') {
    $migration = new LeadContactMigration();
    
    $command = $argv[1] ?? 'help';
    
    switch ($command) {
        case 'check':
            $migration->checkMigrationStatus();
            break;
            
        case 'migrate':
            $dryRun = isset($argv[2]) && $argv[2] === '--dry-run';
            $migration->runMigration($dryRun);
            break;
            
        case 'validate':
            $migration->validateMigration();
            break;
            
        case 'report':
            $migration->generateReport();
            break;
            
        case 'rollback':
            echo "Are you sure you want to rollback the migration? This will remove all contact_id values from leads. (y/N): ";
            $handle = fopen("php://stdin", "r");
            $line = fgets($handle);
            if (trim($line) === 'y' || trim($line) === 'Y') {
                $migration->rollbackMigration();
            } else {
                echo "Rollback cancelled.\n";
            }
            fclose($handle);
            break;
            
        case 'help':
        default:
            echo "Lead-Contact Migration Script\n";
            echo "Usage: php migrate_leads_to_contacts.php [command]\n\n";
            echo "Commands:\n";
            echo "  check     - Check migration status\n";
            echo "  migrate   - Run the migration\n";
            echo "  validate  - Validate migration results\n";
            echo "  report    - Generate migration report\n";
            echo "  rollback  - Rollback migration (removes contact_id from leads)\n";
            echo "  help      - Show this help message\n\n";
            echo "Options:\n";
            echo "  --dry-run - Run migration in dry-run mode (with migrate command)\n";
            break;
    }
} else {
    // Web interface (basic)
    echo "<h1>Lead-Contact Migration</h1>";
    echo "<p>This script should be run from the command line for best results.</p>";
    echo "<p>Use: <code>php migrate_leads_to_contacts.php help</code></p>";
}
?>