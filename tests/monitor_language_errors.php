<?php
/**
 * Language Error Monitor
 * Monitors PHP error log for language-related issues and provides quick fixes
 */

class LanguageErrorMonitor {
    private $rootPath;
    private $errorLogPath;
    private $languageTest;
    
    public function __construct($rootPath) {
        $this->rootPath = rtrim($rootPath, '/');
        $this->errorLogPath = $this->rootPath . '/logs/php_errors.log';
    }
    
    /**
     * Parse error log for language-related errors
     */
    public function parseErrorLog() {
        if (!file_exists($this->errorLogPath)) {
            echo "❌ Error log not found: {$this->errorLogPath}\n";
            return [];
        }
        
        $errors = [];
        $lines = file($this->errorLogPath, FILE_IGNORE_NEW_LINES);
        
        foreach ($lines as $lineNum => $line) {
            // Look for "Undefined array key" errors related to language
            if (strpos($line, 'Undefined array key') !== false && strpos($line, '$lang') !== false) {
                // Extract key name and file info
                preg_match('/Undefined array key "([^"]+)"/', $line, $keyMatches);
                preg_match('/at ([^:]+):(\d+)/', $line, $fileMatches);
                preg_match('/\[([^\]]+)\]/', $line, $dateMatches);
                
                if (!empty($keyMatches[1]) && !empty($fileMatches[1])) {
                    $errors[] = [
                        'line_number' => $lineNum + 1,
                        'timestamp' => $dateMatches[1] ?? 'Unknown',
                        'key' => $keyMatches[1],
                        'file' => $fileMatches[1],
                        'line' => $fileMatches[2] ?? 'Unknown',
                        'raw_line' => $line
                    ];
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Monitor for new language errors
     */
    public function monitor() {
        echo "=== LANGUAGE ERROR MONITOR ===\n";
        echo "Scanning error log for language-related issues...\n\n";
        
        $errors = $this->parseErrorLog();
        
        if (empty($errors)) {
            echo "✅ No language-related errors found in the log!\n";
            return true;
        }
        
        echo "Found " . count($errors) . " language-related errors:\n\n";
        
        // Group errors by key and file
        $groupedErrors = [];
        foreach ($errors as $error) {
            $key = $error['key'] . '@' . basename($error['file']);
            if (!isset($groupedErrors[$key])) {
                $groupedErrors[$key] = [
                    'key' => $error['key'],
                    'file' => $error['file'],
                    'line' => $error['line'],
                    'count' => 0,
                    'first_seen' => $error['timestamp'],
                    'last_seen' => $error['timestamp']
                ];
            }
            $groupedErrors[$key]['count']++;
            $groupedErrors[$key]['last_seen'] = $error['timestamp'];
        }
        
        // Display grouped errors
        foreach ($groupedErrors as $group) {
            echo "❌ Missing key: '{$group['key']}'\n";
            echo "   File: " . basename($group['file']) . ":{$group['line']}\n";
            echo "   Occurrences: {$group['count']}\n";
            echo "   First seen: {$group['first_seen']}\n";
            echo "   Last seen: {$group['last_seen']}\n\n";
        }
        
        return false;
    }
    
    /**
     * Quick fix for detected errors
     */
    public function quickFix() {
        echo "=== QUICK FIX MODE ===\n";
        
        $errors = $this->parseErrorLog();
        if (empty($errors)) {
            echo "✅ No errors to fix!\n";
            return;
        }
        
        // Extract unique missing keys
        $missingKeys = [];
        foreach ($errors as $error) {
            $missingKeys[$error['key']] = $error['file'];
        }
        
        echo "Attempting to fix " . count($missingKeys) . " missing language keys...\n\n";
        
        foreach ($missingKeys as $key => $file) {
            echo "Fixing key: '{$key}' (used in " . basename($file) . ")\n";
            
            // Add to English
            $this->addLanguageKey('en', $key, $this->suggestTranslation($key, 'en'));
            
            // Add to Spanish  
            $this->addLanguageKey('es', $key, $this->suggestTranslation($key, 'es'));
        }
        
        echo "\n✅ Quick fixes applied! Please test the application.\n";
    }
    
    /**
     * Add a language key to a specific language file
     */
    private function addLanguageKey($langCode, $key, $value) {
        $langFile = $this->rootPath . "/public_html/admin/languages/{$langCode}.php";
        
        if (!file_exists($langFile)) {
            echo "❌ Language file not found: {$langFile}\n";
            return false;
        }
        
        // Read current file
        $content = file_get_contents($langFile);
        
        // Check if key already exists
        if (strpos($content, "'{$key}'") !== false) {
            echo "   ⚠️  Key '{$key}' already exists in {$langCode}.php\n";
            return true;
        }
        
        // Find the end of the array
        $insertPosition = strrpos($content, '];');
        if ($insertPosition === false) {
            echo "❌ Could not find array end in {$langCode}.php\n";
            return false;
        }
        
        // Insert new entry
        $newEntry = "  '{$key}' => '{$value}',\n";
        $newContent = substr($content, 0, $insertPosition) . $newEntry . substr($content, $insertPosition);
        
        // Write back to file
        if (file_put_contents($langFile, $newContent)) {
            echo "   ✅ Added '{$key}' => '{$value}' to {$langCode}.php\n";
            return true;
        } else {
            echo "   ❌ Failed to update {$langCode}.php\n";
            return false;
        }
    }
    
    /**
     * Suggest translation for a key
     */
    private function suggestTranslation($key, $langCode) {
        $translations = [
            'en' => [
                'address' => 'Address',
                'save' => 'Save',
                'cancel' => 'Cancel',
                'delete' => 'Delete',
                'edit' => 'Edit',
                'add' => 'Add',
                'remove' => 'Remove',
                'confirm' => 'Confirm',
                'warning' => 'Warning',
                'error' => 'Error',
                'success' => 'Success',
                'required' => 'Required',
                'optional' => 'Optional',
                'loading' => 'Loading',
                'search' => 'Search',
                'filter' => 'Filter'
            ],
            'es' => [
                'address' => 'Dirección',
                'save' => 'Guardar',
                'cancel' => 'Cancelar',
                'delete' => 'Eliminar',
                'edit' => 'Editar',
                'add' => 'Agregar',
                'remove' => 'Remover',
                'confirm' => 'Confirmar',
                'warning' => 'Advertencia',
                'error' => 'Error',
                'success' => 'Éxito',
                'required' => 'Requerido',
                'optional' => 'Opcional',
                'loading' => 'Cargando',
                'search' => 'Buscar',
                'filter' => 'Filtrar'
            ]
        ];
        
        if (isset($translations[$langCode][$key])) {
            return $translations[$langCode][$key];
        }
        
        // Fallback: capitalize and replace underscores
        return ucwords(str_replace('_', ' ', $key));
    }
    
    /**
     * Run continuous monitoring
     */
    public function runMonitoring() {
        echo "=== CONTINUOUS LANGUAGE MONITORING ===\n";
        echo "Monitoring error log for new language issues...\n";
        echo "Press Ctrl+C to stop monitoring.\n\n";
        
        $lastCheck = time();
        
        while (true) {
            $hasErrors = !$this->monitor();
            
            if ($hasErrors) {
                echo "\n🔧 Would you like to apply quick fixes? (y/n): ";
                $handle = fopen("php://stdin", "r");
                $input = trim(fgets($handle));
                fclose($handle);
                
                if (strtolower($input) === 'y') {
                    $this->quickFix();
                }
            }
            
            echo "\n⏰ Next check in 30 seconds...\n";
            sleep(30);
        }
    }
}

// Command line interface
if (isset($argv[1])) {
    $rootPath = '/run/user/1000/gvfs/sftp:host=159.203.116.150,port=222/home/democrm';
    $monitor = new LanguageErrorMonitor($rootPath);
    
    switch ($argv[1]) {
        case 'monitor':
            $monitor->monitor();
            break;
        case 'fix':
            $monitor->quickFix();
            break;
        case 'watch':
            $monitor->runMonitoring();
            break;
        default:
            echo "Usage: php monitor_language_errors.php [monitor|fix|watch]\n";
            echo "  monitor - Check for language errors once\n";
            echo "  fix     - Apply quick fixes for detected errors\n";
            echo "  watch   - Continuously monitor for new errors\n";
    }
} else {
    echo "Usage: php monitor_language_errors.php [monitor|fix|watch]\n";
}