<?php
/**
 * Notes Migration Script
 * 
 * This script parses the notes field from the leads table,
 * extracts dated entries, and inserts them into the notes table
 * for better organization and display in the edit forms.
 */

require_once dirname(__DIR__) . '/config/system.php';

class NotesMigration
{
    private $pdo;
    private $log = [];
    private $stats = [
        'leads_processed' => 0,
        'notes_created' => 0,
        'parsing_errors' => 0,
        'database_errors' => 0
    ];

    public function __construct()
    {
        $database = new Database();
        $this->pdo = $database->dbcrm();
        
        $this->log("=== Notes Migration Started ===");
        $this->log("Start time: " . date('Y-m-d H:i:s'));
    }

    /**
     * Main migration method
     */
    public function migrate($dry_run = true)
    {
        $this->log("Running in " . ($dry_run ? "DRY RUN" : "LIVE") . " mode");
        
        try {
            if (!$dry_run) {
                $this->pdo->beginTransaction();
                $this->ensureTablesExist();
            } else {
                $this->log("DRY RUN: Would ensure tables exist");
            }

            $leads = $this->getLeadsWithNotes();
            $this->log("Found " . count($leads) . " leads with notes to process");

            foreach ($leads as $lead) {
                $this->processLead($lead, $dry_run);
            }

            if (!$dry_run) {
                $this->pdo->commit();
                $this->log("Transaction committed successfully");
            }

        } catch (Exception $e) {
            if (!$dry_run) {
                $this->pdo->rollBack();
            }
            $this->log("ERROR: " . $e->getMessage());
            $this->stats['database_errors']++;
        }

        $this->printStats();
        return $this->log;
    }

    /**
     * Ensure required tables exist
     */
    private function ensureTablesExist()
    {
        $this->log("Ensuring leads_notes relationship table exists...");
        
        $sql = "CREATE TABLE IF NOT EXISTS `leads_notes` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `lead_id` int(11) NOT NULL,
                  `note_id` int(11) NOT NULL,
                  `date_linked` datetime DEFAULT current_timestamp(),
                  PRIMARY KEY (`id`),
                  KEY `idx_lead_id` (`lead_id`),
                  KEY `idx_note_id` (`note_id`),
                  KEY `idx_date_linked` (`date_linked`)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci";
        
        $this->pdo->exec($sql);
        $this->log("leads_notes table ready");
    }

    /**
     * Get all leads that have notes content
     */
    private function getLeadsWithNotes()
    {
        $sql = "SELECT id, notes, created_at FROM leads 
                WHERE notes IS NOT NULL 
                AND TRIM(notes) != '' 
                ORDER BY id";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Process a single lead's notes
     */
    private function processLead($lead, $dry_run)
    {
        $this->log("Processing lead ID: {$lead['id']}");
        $this->stats['leads_processed']++;

        $noteEntries = $this->parseNotes($lead['notes'], $lead['created_at']);
        
        if (empty($noteEntries)) {
            $this->log("  No parseable notes found");
            return;
        }

        $this->log("  Found " . count($noteEntries) . " note entries");

        foreach ($noteEntries as $entry) {
            if (!$dry_run) {
                $this->insertNote($lead['id'], $entry);
            } else {
                $this->log("  DRY RUN: Would insert note dated {$entry['date']} with " . strlen($entry['text']) . " characters");
            }
        }
    }

    /**
     * Parse notes text into dated entries
     */
    private function parseNotes($notesText, $leadCreatedAt)
    {
        $entries = [];
        $undatedLines = [];
        $isFirstEntry = true;
        
        // Split by potential date patterns
        $lines = explode("\n", $notesText);
        $currentEntry = null;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Check if line starts with a date
            $dateMatch = $this->extractDate($line);
            
            if ($dateMatch) {
                // Handle any accumulated undated lines
                if (!empty($undatedLines)) {
                    if ($isFirstEntry) {
                        // First undated content gets its own entry with lead creation date
                        $entries[] = [
                            'date' => $leadCreatedAt,
                            'text' => implode("\n", $undatedLines)
                        ];
                        $isFirstEntry = false;
                    } else {
                        // Combine with last dated entry
                        if (!empty($entries)) {
                            $lastIndex = count($entries) - 1;
                            $entries[$lastIndex]['text'] .= "\n" . implode("\n", $undatedLines);
                        }
                    }
                    $undatedLines = [];
                }
                
                // Save previous dated entry if exists
                if ($currentEntry) {
                    $entries[] = $currentEntry;
                }
                
                // Start new dated entry
                $currentEntry = [
                    'date' => $dateMatch['date'],
                    'text' => $dateMatch['remaining_text']
                ];
                $isFirstEntry = false;
                
            } else {
                // Undated line
                if ($currentEntry) {
                    // We're in a dated entry - add to current entry
                    $currentEntry['text'] .= "\n" . $line;
                } else {
                    // Accumulate undated lines
                    $undatedLines[] = $line;
                }
            }
        }
        
        // Handle final entries
        if ($currentEntry) {
            $entries[] = $currentEntry;
        }
        
        // Handle any remaining undated lines
        if (!empty($undatedLines)) {
            if ($isFirstEntry) {
                // All content was undated - use lead creation date
                $entries[] = [
                    'date' => $leadCreatedAt,
                    'text' => implode("\n", $undatedLines)
                ];
            } else {
                // Combine with last dated entry
                if (!empty($entries)) {
                    $lastIndex = count($entries) - 1;
                    $entries[$lastIndex]['text'] .= "\n" . implode("\n", $undatedLines);
                }
            }
        }

        return $this->cleanAndSortEntries($entries);
    }

    /**
     * Extract date from line beginning
     */
    private function extractDate($line)
    {
        // Date patterns to match
        $patterns = [
            // M/D/YY format (most common)
            '/^(\d{1,2}\/\d{1,2}\/\d{2,4})\s*(.*)$/',
            // M/D/YY with time
            '/^(\d{1,2}\/\d{1,2}\/\d{2,4}\s+\d{1,2}:\d{2}(?::\d{2})?(?:\s*[AaPp][Mm])?)\s*(.*)$/',
            // Alternative formats
            '/^(\d{1,2}-\d{1,2}-\d{2,4})\s*(.*)$/',
            '/^(\d{1,2}\.\d{1,2}\.\d{2,4})\s*(.*)$/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $line, $matches)) {
                $dateStr = $matches[1];
                $remainingText = trim($matches[2]);
                
                try {
                    $parsedDate = $this->parseDate($dateStr);
                    return [
                        'date' => $parsedDate,
                        'remaining_text' => $remainingText
                    ];
                } catch (Exception $e) {
                    $this->log("  Date parsing error for '{$dateStr}': " . $e->getMessage());
                    $this->stats['parsing_errors']++;
                }
            }
        }

        return null;
    }

    /**
     * Parse various date formats into MySQL datetime
     */
    private function parseDate($dateStr)
    {
        // Clean the date string
        $dateStr = trim($dateStr);
        
        // Handle M/D/YY format specifically
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{2,4})/', $dateStr, $matches)) {
            $month = (int)$matches[1];
            $day = (int)$matches[2];
            $year = (int)$matches[3];
            
            // Convert 2-digit year to 4-digit
            if ($year < 100) {
                $year += ($year < 50) ? 2000 : 1900;
            }
            
            // Extract time if present
            $time = '00:00:00';
            if (preg_match('/\d{1,2}:\d{2}/', $dateStr, $timeMatch)) {
                $time = $timeMatch[0] . ':00';
            }
            
            return sprintf('%04d-%02d-%02d %s', $year, $month, $day, $time);
        }
        
        // Fallback to strtotime
        $timestamp = strtotime($dateStr);
        if ($timestamp === false) {
            throw new Exception("Unable to parse date: $dateStr");
        }
        
        return date('Y-m-d H:i:s', $timestamp);
    }

    /**
     * Clean and sort entries by date
     */
    private function cleanAndSortEntries($entries)
    {
        // Remove empty entries
        $entries = array_filter($entries, function($entry) {
            return !empty(trim($entry['text']));
        });

        // Sort by date
        usort($entries, function($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        return $entries;
    }

    /**
     * Insert note into database and link to lead
     */
    private function insertNote($leadId, $entry)
    {
        try {
            // Insert the note
            $sql = "INSERT INTO notes (source, note_text, date_created, user_id, form_source) 
                    VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $this->pdo->prepare($sql);
            $success = $stmt->execute([
                4, // Internal Note
                trim($entry['text']),
                $entry['date'],
                null, // No specific user for migration
                'leads'
            ]);

            if ($success) {
                // Get the newly inserted note ID
                $noteId = $this->pdo->lastInsertId();
                
                // Link the note to the lead
                $linkSql = "INSERT INTO leads_notes (lead_id, note_id, date_linked) 
                           VALUES (?, ?, ?)";
                           
                $linkStmt = $this->pdo->prepare($linkSql);
                $linkSuccess = $linkStmt->execute([
                    $leadId,
                    $noteId,
                    $entry['date']
                ]);
                
                if ($linkSuccess) {
                    $this->stats['notes_created']++;
                    $this->log("  Inserted and linked note dated: {$entry['date']}");
                } else {
                    $this->stats['database_errors']++;
                    $this->log("  Failed to link note {$noteId} to lead {$leadId}");
                }
            } else {
                $this->stats['database_errors']++;
                $this->log("  Database insert failed for lead {$leadId}");
            }

        } catch (PDOException $e) {
            $this->stats['database_errors']++;
            $this->log("  Database error for lead {$leadId}: " . $e->getMessage());
        }
    }

    /**
     * Add message to log
     */
    private function log($message)
    {
        $this->log[] = "[" . date('H:i:s') . "] " . $message;
        echo $message . "\n";
    }

    /**
     * Print migration statistics
     */
    private function printStats()
    {
        $this->log("\n=== Migration Statistics ===");
        foreach ($this->stats as $key => $value) {
            $this->log(ucwords(str_replace('_', ' ', $key)) . ": " . $value);
        }
        $this->log("=== Migration Completed ===");
    }

    /**
     * Get migration log
     */
    public function getLog()
    {
        return $this->log;
    }
}

// Command line execution
if (php_sapi_name() === 'cli') {
    $migration = new NotesMigration();
    
    // Check for dry-run flag
    $dryRun = in_array('--dry-run', $argv) || in_array('-d', $argv);
    $live = in_array('--live', $argv) || in_array('-l', $argv);
    
    if (!$dryRun && !$live) {
        echo "Usage: php migrate_notes.php [--dry-run|-d] [--live|-l]\n";
        echo "  --dry-run, -d  : Run in dry-run mode (no database changes)\n";
        echo "  --live, -l     : Run live migration (makes database changes)\n";
        exit(1);
    }
    
    $migration->migrate(!$live);
}
?>