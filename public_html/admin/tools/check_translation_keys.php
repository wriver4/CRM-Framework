<?php
/**
 * Translation Keys Checker
 * 
 * Validates translation keys exist in language files
 * Prevents missing translation errors
 * 
 * Usage:
 *   php check_translation_keys.php key1 key2 key3
 *   php check_translation_keys.php --file path/to/file.php
 *   php check_translation_keys.php --json key1 key2
 *   php check_translation_keys.php --suggest "Create Template"
 */

class TranslationKeyChecker {
    private $languages = ['en', 'es'];
    private $lang_files = [];
    private $translations = [];
    
    public function __construct($base_path = null) {
        if ($base_path === null) {
            $base_path = __DIR__ . '/../../admin/languages';
        }
        
        foreach ($this->languages as $lang) {
            $file = "$base_path/$lang.php";
            if (file_exists($file)) {
                $this->lang_files[$lang] = $file;
                $this->loadTranslations($lang);
            }
        }
    }
    
    /**
     * Load translations from language file
     */
    private function loadTranslations($language) {
        $lang = [];
        include($this->lang_files[$language]);
        $this->translations[$language] = $lang;
    }
    
    /**
     * Check if translation keys exist
     */
    public function checkKeys($keys) {
        $results = [
            'valid' => true,
            'missing_keys' => [],
            'existing_keys' => []
        ];
        
        foreach ($keys as $key) {
            $missing_in = [];
            
            foreach ($this->languages as $lang) {
                if (!isset($this->translations[$lang][$key])) {
                    $missing_in[] = $lang;
                }
            }
            
            if (!empty($missing_in)) {
                $results['valid'] = false;
                $results['missing_keys'][$key] = $missing_in;
            } else {
                $results['existing_keys'][$key] = array_map(
                    fn($lang) => $this->translations[$lang][$key],
                    $this->languages
                );
            }
        }
        
        return $results;
    }
    
    /**
     * Find similar existing keys (for suggestions)
     */
    public function findSimilarKeys($search_key, $limit = 5) {
        $search_lower = strtolower($search_key);
        $matches = [];
        
        // Get all keys from English (primary language)
        $all_keys = array_keys($this->translations['en']);
        
        foreach ($all_keys as $key) {
            $key_lower = strtolower($key);
            
            // Calculate similarity
            similar_text($search_lower, $key_lower, $percent);
            
            // Check for partial matches
            if (strpos($key_lower, $search_lower) !== false || 
                strpos($search_lower, $key_lower) !== false) {
                $percent += 20; // Boost score for partial matches
            }
            
            if ($percent > 30) {
                $matches[$key] = $percent;
            }
        }
        
        // Sort by similarity
        arsort($matches);
        
        return array_slice(array_keys($matches), 0, $limit);
    }
    
    /**
     * Suggest translation key name based on text
     */
    public function suggestKeyName($text, $module = '', $type = '') {
        // Clean the text
        $text = trim($text);
        $text = strip_tags($text);
        
        // Convert to snake_case
        $key = strtolower($text);
        $key = preg_replace('/[^a-z0-9]+/', '_', $key);
        $key = trim($key, '_');
        
        // Add module prefix if provided
        if ($module) {
            $key = $module . '_' . $key;
        }
        
        // Add type suffix if provided
        if ($type) {
            $key = $key . '_' . $type;
        }
        
        // Check if key already exists
        if (isset($this->translations['en'][$key])) {
            // Try adding a number
            $counter = 2;
            while (isset($this->translations['en']["{$key}_{$counter}"])) {
                $counter++;
            }
            $key = "{$key}_{$counter}";
        }
        
        return $key;
    }
    
    /**
     * Extract translation keys from PHP file
     */
    public function extractKeysFromFile($file_path) {
        if (!file_exists($file_path)) {
            return ['error' => 'File not found'];
        }
        
        $content = file_get_contents($file_path);
        $keys = [];
        
        // Pattern: $lang['key'] or $lang["key"]
        preg_match_all('/\$lang\[[\'"]([^\'"]+)[\'"]\]/', $content, $matches);
        
        if (!empty($matches[1])) {
            $keys = array_unique($matches[1]);
        }
        
        return $keys;
    }
    
    /**
     * Extract hardcoded strings from PHP file
     */
    public function extractHardcodedStrings($file_path) {
        if (!file_exists($file_path)) {
            return ['error' => 'File not found'];
        }
        
        $content = file_get_contents($file_path);
        $strings = [];
        
        // Remove PHP comments
        $content = preg_replace('/\/\*.*?\*\//s', '', $content);
        $content = preg_replace('/\/\/.*$/m', '', $content);
        
        // Find strings in HTML/PHP that are not in $lang[]
        // Pattern: Look for quoted strings in echo, HTML content, etc.
        preg_match_all('/(?:echo|print|\?>)[^<]*?[\'"]([^\'"]{3,})[\'"]/', $content, $matches);
        
        if (!empty($matches[1])) {
            foreach ($matches[1] as $str) {
                // Skip if it looks like a variable, URL, or code
                if (preg_match('/^[\$\w]+$/', $str) || 
                    preg_match('/^https?:\/\//', $str) ||
                    preg_match('/[{}();]/', $str)) {
                    continue;
                }
                
                $strings[] = trim($str);
            }
        }
        
        // Find strings in HTML tags
        preg_match_all('/>([^<]{3,})</', $content, $matches);
        if (!empty($matches[1])) {
            foreach ($matches[1] as $str) {
                $str = trim($str);
                // Skip if it's PHP code or just whitespace
                if (empty($str) || preg_match('/<\?php|[\${}]/', $str)) {
                    continue;
                }
                $strings[] = $str;
            }
        }
        
        return array_unique($strings);
    }
    
    /**
     * Validate a PHP file for translation compliance
     */
    public function validateFile($file_path) {
        $keys = $this->extractKeysFromFile($file_path);
        $hardcoded = $this->extractHardcodedStrings($file_path);
        
        $result = [
            'file' => $file_path,
            'keys_used' => count($keys),
            'hardcoded_strings' => count($hardcoded),
            'compliant' => count($hardcoded) === 0
        ];
        
        // Check if used keys exist
        if (!empty($keys)) {
            $check_result = $this->checkKeys($keys);
            $result['missing_keys'] = $check_result['missing_keys'];
            $result['valid_keys'] = count($check_result['existing_keys']);
            
            if (!$check_result['valid']) {
                $result['compliant'] = false;
            }
        }
        
        // Add suggestions for hardcoded strings
        if (!empty($hardcoded)) {
            $result['suggestions'] = [];
            foreach (array_slice($hardcoded, 0, 10) as $str) {
                $result['suggestions'][] = [
                    'text' => $str,
                    'suggested_key' => $this->suggestKeyName($str)
                ];
            }
        }
        
        return $result;
    }
    
    /**
     * Get all translation keys
     */
    public function getAllKeys($language = 'en') {
        return array_keys($this->translations[$language] ?? []);
    }
    
    /**
     * Search for keys by pattern
     */
    public function searchKeys($pattern, $language = 'en') {
        $all_keys = $this->getAllKeys($language);
        $matches = [];
        
        foreach ($all_keys as $key) {
            if (preg_match("/$pattern/i", $key)) {
                $matches[$key] = $this->translations[$language][$key];
            }
        }
        
        return $matches;
    }
    
    /**
     * Display check results
     */
    public function displayCheckResults($results) {
        echo "\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "  TRANSLATION KEY CHECK RESULTS\n";
        echo "═══════════════════════════════════════════════════════════════\n\n";
        
        if ($results['valid']) {
            echo "✓ All keys exist in all languages\n\n";
            
            echo "Existing Keys:\n";
            echo str_repeat("─", 63) . "\n";
            foreach ($results['existing_keys'] as $key => $translations) {
                echo "  ✓ $key\n";
                foreach ($this->languages as $i => $lang) {
                    echo "    [$lang] {$translations[$i]}\n";
                }
            }
        } else {
            echo "✗ Some keys are missing\n\n";
            
            if (!empty($results['existing_keys'])) {
                echo "Valid Keys: " . count($results['existing_keys']) . "\n";
            }
            
            echo "\nMissing Keys:\n";
            echo str_repeat("─", 63) . "\n";
            foreach ($results['missing_keys'] as $key => $missing_langs) {
                echo "  ✗ $key\n";
                echo "    Missing in: " . implode(', ', $missing_langs) . "\n";
                
                // Show suggestions
                $similar = $this->findSimilarKeys($key, 3);
                if (!empty($similar)) {
                    echo "    Similar keys: " . implode(', ', $similar) . "\n";
                }
            }
        }
        
        echo "\n";
    }
    
    /**
     * Display file validation results
     */
    public function displayFileResults($results) {
        echo "\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "  FILE TRANSLATION COMPLIANCE CHECK\n";
        echo "═══════════════════════════════════════════════════════════════\n\n";
        
        echo "File: {$results['file']}\n";
        echo "Status: " . ($results['compliant'] ? "✓ COMPLIANT" : "✗ NOT COMPLIANT") . "\n\n";
        
        echo "Statistics:\n";
        echo "  • Translation keys used: {$results['keys_used']}\n";
        echo "  • Hardcoded strings found: {$results['hardcoded_strings']}\n";
        
        if (isset($results['valid_keys'])) {
            echo "  • Valid keys: {$results['valid_keys']}\n";
        }
        
        if (!empty($results['missing_keys'])) {
            echo "\n✗ Missing Translation Keys:\n";
            echo str_repeat("─", 63) . "\n";
            foreach ($results['missing_keys'] as $key => $langs) {
                echo "  • $key (missing in: " . implode(', ', $langs) . ")\n";
            }
        }
        
        if (!empty($results['suggestions'])) {
            echo "\n⚠ Hardcoded Strings (should use translation keys):\n";
            echo str_repeat("─", 63) . "\n";
            foreach ($results['suggestions'] as $suggestion) {
                echo "  • \"{$suggestion['text']}\"\n";
                echo "    → Suggested key: {$suggestion['suggested_key']}\n";
            }
            
            if ($results['hardcoded_strings'] > 10) {
                $remaining = $results['hardcoded_strings'] - 10;
                echo "\n  ... and $remaining more\n";
            }
        }
        
        echo "\n";
    }
}

// CLI Interface - only run if this file is executed directly
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    $checker = new TranslationKeyChecker();
    
    $args = array_slice($argv, 1);
    $json_output = false;
    $check_file = false;
    $suggest_mode = false;
    
    // Parse flags
    if (in_array('--json', $args)) {
        $json_output = true;
        $args = array_values(array_diff($args, ['--json']));
    }
    
    if (in_array('--file', $args)) {
        $check_file = true;
        $key = array_search('--file', $args);
        unset($args[$key]);
        $args = array_values($args);
    }
    
    if (in_array('--suggest', $args)) {
        $suggest_mode = true;
        $key = array_search('--suggest', $args);
        unset($args[$key]);
        $args = array_values($args);
    }
    
    if (in_array('--search', $args)) {
        $search_mode = true;
        $key = array_search('--search', $args);
        unset($args[$key]);
        $args = array_values($args);
    }
    
    // Handle commands
    if (empty($args)) {
        echo "Usage:\n";
        echo "  php check_translation_keys.php key1 key2 key3\n";
        echo "  php check_translation_keys.php --file path/to/file.php\n";
        echo "  php check_translation_keys.php --suggest \"Create Template\" [module] [type]\n";
        echo "  php check_translation_keys.php --search pattern\n";
        echo "  php check_translation_keys.php --json key1 key2\n";
        exit(0);
    }
    
    if ($suggest_mode) {
        // Suggest key name
        $text = $args[0];
        $module = $args[1] ?? '';
        $type = $args[2] ?? '';
        
        $suggested = $checker->suggestKeyName($text, $module, $type);
        
        if ($json_output) {
            echo json_encode([
                'text' => $text,
                'suggested_key' => $suggested,
                'module' => $module,
                'type' => $type
            ], JSON_PRETTY_PRINT) . "\n";
        } else {
            echo "\nText: \"$text\"\n";
            echo "Suggested Key: $suggested\n\n";
        }
        
    } elseif (isset($search_mode)) {
        // Search for keys
        $pattern = $args[0];
        $results = $checker->searchKeys($pattern);
        
        if ($json_output) {
            echo json_encode($results, JSON_PRETTY_PRINT) . "\n";
        } else {
            echo "\nSearch Results for: \"$pattern\"\n";
            echo str_repeat("─", 63) . "\n";
            foreach ($results as $key => $value) {
                echo "  • $key\n    \"$value\"\n";
            }
            echo "\nTotal: " . count($results) . " keys found\n\n";
        }
        
    } elseif ($check_file) {
        // Validate file
        $file_path = $args[0];
        $results = $checker->validateFile($file_path);
        
        if ($json_output) {
            echo json_encode($results, JSON_PRETTY_PRINT) . "\n";
        } else {
            $checker->displayFileResults($results);
        }
        
    } else {
        // Check specific keys
        $results = $checker->checkKeys($args);
        
        if ($json_output) {
            echo json_encode($results, JSON_PRETTY_PRINT) . "\n";
        } else {
            $checker->displayCheckResults($results);
        }
    }
}