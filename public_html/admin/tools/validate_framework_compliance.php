<?php
/**
 * Framework Compliance Validator
 * 
 * Validates PHP files against DemoCRM framework rules
 * Checks for authentication, translations, templates, etc.
 * 
 * Usage:
 *   php validate_framework_compliance.php path/to/file.php
 *   php validate_framework_compliance.php --json path/to/file.php
 *   php validate_framework_compliance.php --rules auth,translations path/to/file.php
 *   php validate_framework_compliance.php --directory path/to/dir
 */

require_once(__DIR__ . '/check_translation_keys.php');

class FrameworkComplianceValidator {
    private $translation_checker;
    private $issues = [];
    
    // Rule definitions
    private $rules = [
        'auth' => [
            'name' => 'Authentication Check',
            'description' => 'Page must have $not->loggedin() check',
            'severity' => 'error'
        ],
        'translations' => [
            'name' => 'Translation Keys',
            'description' => 'All user-facing text must use $lang[] keys',
            'severity' => 'error'
        ],
        'templates' => [
            'name' => 'Template Sequence',
            'description' => 'Must use proper template sequence (HEADER → NAV → LISTOPEN → LISTCLOSE → FOOTER)',
            'severity' => 'warning'
        ],
        'routing' => [
            'name' => 'Routing Variables',
            'description' => 'Must set routing variables (TITLE, BODY, ACTIVE)',
            'severity' => 'warning'
        ],
        'security' => [
            'name' => 'Security Checks',
            'description' => 'Check for SQL injection, XSS vulnerabilities',
            'severity' => 'error'
        ]
    ];
    
    public function __construct() {
        $this->translation_checker = new TranslationKeyChecker();
    }
    
    /**
     * Validate a file against all or specific rules
     */
    public function validateFile($file_path, $rules_to_check = null) {
        if (!file_exists($file_path)) {
            return [
                'error' => 'File not found',
                'file' => $file_path
            ];
        }
        
        $content = file_get_contents($file_path);
        $this->issues = [];
        
        // Determine which rules to check
        if ($rules_to_check === null) {
            $rules_to_check = array_keys($this->rules);
        }
        
        $results = [
            'file' => $file_path,
            'compliant' => true,
            'rules_checked' => [],
            'issues' => []
        ];
        
        // Run each rule check
        foreach ($rules_to_check as $rule) {
            if (!isset($this->rules[$rule])) {
                continue;
            }
            
            $method = 'check_' . $rule;
            if (method_exists($this, $method)) {
                $rule_result = $this->$method($content, $file_path);
                $results['rules_checked'][] = $rule;
                
                if (!$rule_result['passed']) {
                    $results['compliant'] = false;
                    $results['issues'] = array_merge($results['issues'], $rule_result['issues']);
                }
            }
        }
        
        // Calculate compliance score
        $total_issues = count($results['issues']);
        $error_count = count(array_filter($results['issues'], fn($i) => $i['severity'] === 'error'));
        $warning_count = count(array_filter($results['issues'], fn($i) => $i['severity'] === 'warning'));
        
        $results['summary'] = [
            'total_issues' => $total_issues,
            'errors' => $error_count,
            'warnings' => $warning_count,
            'score' => $this->calculateScore($error_count, $warning_count)
        ];
        
        return $results;
    }
    
    /**
     * Check authentication
     */
    private function check_auth($content, $file_path) {
        $issues = [];
        $passed = true;
        
        // Check for $not->loggedin()
        if (!preg_match('/\$not\s*->\s*loggedin\s*\(\s*\)/', $content)) {
            $passed = false;
            $issues[] = [
                'rule' => 'auth',
                'severity' => 'error',
                'message' => 'Missing authentication check: $not->loggedin()',
                'line' => $this->findLineNumber($content, '<?php', 1),
                'suggestion' => 'Add $not->loggedin(); at the top of the file after includes'
            ];
        } else {
            // Check if it's early enough in the file (within first 50 lines)
            $lines = explode("\n", $content);
            $found_line = 0;
            foreach ($lines as $num => $line) {
                if (preg_match('/\$not\s*->\s*loggedin\s*\(\s*\)/', $line)) {
                    $found_line = $num + 1;
                    break;
                }
            }
            
            if ($found_line > 50) {
                $issues[] = [
                    'rule' => 'auth',
                    'severity' => 'warning',
                    'message' => 'Authentication check found but appears late in file',
                    'line' => $found_line,
                    'suggestion' => 'Move $not->loggedin() closer to the top of the file'
                ];
            }
        }
        
        return ['passed' => $passed, 'issues' => $issues];
    }
    
    /**
     * Check translations
     */
    private function check_translations($content, $file_path) {
        $issues = [];
        $passed = true;
        
        // Use translation checker to find hardcoded strings
        $validation = $this->translation_checker->validateFile($file_path);
        
        if (!$validation['compliant']) {
            $passed = false;
            
            // Add issues for missing keys
            if (!empty($validation['missing_keys'])) {
                foreach ($validation['missing_keys'] as $key => $langs) {
                    $issues[] = [
                        'rule' => 'translations',
                        'severity' => 'error',
                        'message' => "Translation key '$key' is missing in: " . implode(', ', $langs),
                        'line' => $this->findLineNumber($content, $key),
                        'suggestion' => "Add '$key' to language files: " . implode(', ', array_map(fn($l) => "$l.php", $langs))
                    ];
                }
            }
            
            // Add issues for hardcoded strings
            if (!empty($validation['suggestions'])) {
                foreach (array_slice($validation['suggestions'], 0, 5) as $suggestion) {
                    $issues[] = [
                        'rule' => 'translations',
                        'severity' => 'error',
                        'message' => "Hardcoded string found: \"{$suggestion['text']}\"",
                        'line' => $this->findLineNumber($content, $suggestion['text']),
                        'suggestion' => "Replace with \$lang['{$suggestion['suggested_key']}'] and add to language files"
                    ];
                }
                
                if ($validation['hardcoded_strings'] > 5) {
                    $remaining = $validation['hardcoded_strings'] - 5;
                    $issues[] = [
                        'rule' => 'translations',
                        'severity' => 'warning',
                        'message' => "... and $remaining more hardcoded strings",
                        'line' => 0,
                        'suggestion' => "Run with --file flag for full list"
                    ];
                }
            }
        }
        
        return ['passed' => $passed, 'issues' => $issues];
    }
    
    /**
     * Check template sequence
     */
    private function check_templates($content, $file_path) {
        $issues = [];
        $passed = true;
        
        $required_templates = ['HEADER', 'NAV', 'LISTOPEN', 'LISTCLOSE', 'FOOTER'];
        $found_templates = [];
        
        // Find all template displays
        preg_match_all('/\$ui\s*->\s*display\s*\(\s*[\'"](\w+)[\'"]\s*\)/', $content, $matches);
        
        if (!empty($matches[1])) {
            $found_templates = $matches[1];
        }
        
        // Check for required templates
        foreach ($required_templates as $template) {
            if (!in_array($template, $found_templates)) {
                $passed = false;
                $issues[] = [
                    'rule' => 'templates',
                    'severity' => 'warning',
                    'message' => "Missing template display: \$ui->display('$template')",
                    'line' => 0,
                    'suggestion' => "Add \$ui->display('$template') in the appropriate location"
                ];
            }
        }
        
        // Check sequence order
        if (count($found_templates) >= 2) {
            $expected_order = ['HEADER', 'NAV', 'LISTOPEN'];
            $found_order = array_intersect($expected_order, $found_templates);
            
            if ($found_order !== array_values(array_intersect($found_templates, $expected_order))) {
                $issues[] = [
                    'rule' => 'templates',
                    'severity' => 'warning',
                    'message' => 'Template display order may be incorrect',
                    'line' => 0,
                    'suggestion' => 'Expected order: HEADER → NAV → LISTOPEN → content → LISTCLOSE → FOOTER'
                ];
            }
        }
        
        return ['passed' => $passed, 'issues' => $issues];
    }
    
    /**
     * Check routing variables
     */
    private function check_routing($content, $file_path) {
        $issues = [];
        $passed = true;
        
        // Check for $ui->assign('_page', ...)
        if (!preg_match('/\$ui\s*->\s*assign\s*\(\s*[\'"]_page[\'"]\s*,/', $content)) {
            $passed = false;
            $issues[] = [
                'rule' => 'routing',
                'severity' => 'warning',
                'message' => 'Missing routing configuration: $ui->assign(\'_page\', ...)',
                'line' => 0,
                'suggestion' => 'Add routing configuration with TITLE, BODY, and ACTIVE keys'
            ];
        } else {
            // Check for required keys
            $required_keys = ['TITLE', 'BODY', 'ACTIVE'];
            foreach ($required_keys as $key) {
                if (!preg_match("/['\"]" . $key . "['\"]\s*=>/", $content)) {
                    $issues[] = [
                        'rule' => 'routing',
                        'severity' => 'warning',
                        'message' => "Routing configuration missing '$key' key",
                        'line' => 0,
                        'suggestion' => "Add '$key' to the _page array"
                    ];
                }
            }
        }
        
        return ['passed' => $passed, 'issues' => $issues];
    }
    
    /**
     * Check security
     */
    private function check_security($content, $file_path) {
        $issues = [];
        $passed = true;
        
        // Check for potential SQL injection
        if (preg_match_all('/\$\w+\s*->\s*query\s*\(\s*[\'"].*?\$/', $content, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                $line = $this->getLineFromOffset($content, $match[1]);
                $issues[] = [
                    'rule' => 'security',
                    'severity' => 'error',
                    'message' => 'Potential SQL injection: Variable concatenation in query',
                    'line' => $line,
                    'suggestion' => 'Use prepared statements with placeholders instead'
                ];
                $passed = false;
            }
        }
        
        // Check for unescaped output
        if (preg_match_all('/echo\s+\$(?!lang\[)/', $content, $matches, PREG_OFFSET_CAPTURE)) {
            foreach (array_slice($matches[0], 0, 3) as $match) {
                $line = $this->getLineFromOffset($content, $match[1]);
                $issues[] = [
                    'rule' => 'security',
                    'severity' => 'warning',
                    'message' => 'Potential XSS: Unescaped variable output',
                    'line' => $line,
                    'suggestion' => 'Use htmlspecialchars() for user-generated content'
                ];
            }
        }
        
        // Check for $_GET/$_POST without validation
        if (preg_match_all('/\$_(GET|POST|REQUEST)\[/', $content, $matches, PREG_OFFSET_CAPTURE)) {
            $count = count($matches[0]);
            if ($count > 0) {
                $issues[] = [
                    'rule' => 'security',
                    'severity' => 'warning',
                    'message' => "Found $count direct superglobal access(es)",
                    'line' => 0,
                    'suggestion' => 'Ensure all user input is validated and sanitized'
                ];
            }
        }
        
        return ['passed' => $passed, 'issues' => $issues];
    }
    
    /**
     * Calculate compliance score
     */
    private function calculateScore($errors, $warnings) {
        $score = 100;
        $score -= ($errors * 15);  // Each error: -15 points
        $score -= ($warnings * 5);  // Each warning: -5 points
        return max(0, $score);
    }
    
    /**
     * Find line number for a string
     */
    private function findLineNumber($content, $search, $default = 0) {
        $lines = explode("\n", $content);
        foreach ($lines as $num => $line) {
            if (strpos($line, $search) !== false) {
                return $num + 1;
            }
        }
        return $default;
    }
    
    /**
     * Get line number from byte offset
     */
    private function getLineFromOffset($content, $offset) {
        return substr_count(substr($content, 0, $offset), "\n") + 1;
    }
    
    /**
     * Validate directory
     */
    public function validateDirectory($dir_path, $rules_to_check = null, $recursive = true) {
        $results = [];
        
        if (!is_dir($dir_path)) {
            return ['error' => 'Directory not found'];
        }
        
        $files = $recursive 
            ? new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir_path))
            : new DirectoryIterator($dir_path);
        
        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $file_path = $file->getPathname();
                $results[$file_path] = $this->validateFile($file_path, $rules_to_check);
            }
        }
        
        return $results;
    }
    
    /**
     * Display validation results
     */
    public function displayResults($results) {
        echo "\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "  FRAMEWORK COMPLIANCE VALIDATION\n";
        echo "═══════════════════════════════════════════════════════════════\n\n";
        
        if (isset($results['error'])) {
            echo "✗ Error: {$results['error']}\n\n";
            return;
        }
        
        echo "File: {$results['file']}\n";
        echo "Status: " . ($results['compliant'] ? "✓ COMPLIANT" : "✗ NOT COMPLIANT") . "\n";
        echo "Score: {$results['summary']['score']}/100\n\n";
        
        echo "Summary:\n";
        echo "  • Total Issues: {$results['summary']['total_issues']}\n";
        echo "  • Errors: {$results['summary']['errors']}\n";
        echo "  • Warnings: {$results['summary']['warnings']}\n";
        echo "  • Rules Checked: " . implode(', ', $results['rules_checked']) . "\n\n";
        
        if (!empty($results['issues'])) {
            echo "Issues Found:\n";
            echo str_repeat("─", 63) . "\n";
            
            // Group by severity
            $errors = array_filter($results['issues'], fn($i) => $i['severity'] === 'error');
            $warnings = array_filter($results['issues'], fn($i) => $i['severity'] === 'warning');
            
            if (!empty($errors)) {
                echo "\n✗ ERRORS:\n";
                foreach ($errors as $issue) {
                    $this->displayIssue($issue);
                }
            }
            
            if (!empty($warnings)) {
                echo "\n⚠ WARNINGS:\n";
                foreach ($warnings as $issue) {
                    $this->displayIssue($issue);
                }
            }
        } else {
            echo "✓ No issues found!\n";
        }
        
        echo "\n";
    }
    
    /**
     * Display single issue
     */
    private function displayIssue($issue) {
        $line_info = $issue['line'] > 0 ? " (line {$issue['line']})" : "";
        echo "  • [{$issue['rule']}] {$issue['message']}$line_info\n";
        if (!empty($issue['suggestion'])) {
            echo "    → {$issue['suggestion']}\n";
        }
    }
    
    /**
     * Display directory results summary
     */
    public function displayDirectoryResults($results) {
        echo "\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "  DIRECTORY COMPLIANCE VALIDATION\n";
        echo "═══════════════════════════════════════════════════════════════\n\n";
        
        $total_files = count($results);
        $compliant_files = count(array_filter($results, fn($r) => $r['compliant'] ?? false));
        $total_errors = array_sum(array_map(fn($r) => $r['summary']['errors'] ?? 0, $results));
        $total_warnings = array_sum(array_map(fn($r) => $r['summary']['warnings'] ?? 0, $results));
        
        echo "Summary:\n";
        echo "  • Total Files: $total_files\n";
        echo "  • Compliant: $compliant_files\n";
        echo "  • Non-Compliant: " . ($total_files - $compliant_files) . "\n";
        echo "  • Total Errors: $total_errors\n";
        echo "  • Total Warnings: $total_warnings\n\n";
        
        echo "Files:\n";
        echo str_repeat("─", 63) . "\n";
        
        foreach ($results as $file => $result) {
            $status = ($result['compliant'] ?? false) ? "✓" : "✗";
            $score = $result['summary']['score'] ?? 0;
            $basename = basename($file);
            echo "  $status $basename (Score: $score/100)\n";
            
            if (!($result['compliant'] ?? false)) {
                echo "      Errors: {$result['summary']['errors']}, Warnings: {$result['summary']['warnings']}\n";
            }
        }
        
        echo "\n";
    }
}

// CLI Interface
if (php_sapi_name() === 'cli') {
    $validator = new FrameworkComplianceValidator();
    
    $args = array_slice($argv, 1);
    $json_output = false;
    $check_directory = false;
    $rules_to_check = null;
    
    // Parse flags
    if (in_array('--json', $args)) {
        $json_output = true;
        $args = array_values(array_diff($args, ['--json']));
    }
    
    if (in_array('--directory', $args)) {
        $check_directory = true;
        $key = array_search('--directory', $args);
        unset($args[$key]);
        $args = array_values($args);
    }
    
    if (($key = array_search('--rules', $args)) !== false) {
        unset($args[$key]);
        if (isset($args[$key + 1])) {
            $rules_to_check = explode(',', $args[$key + 1]);
            unset($args[$key + 1]);
        }
        $args = array_values($args);
    }
    
    // Handle commands
    if (empty($args)) {
        echo "Usage:\n";
        echo "  php validate_framework_compliance.php path/to/file.php\n";
        echo "  php validate_framework_compliance.php --directory path/to/dir\n";
        echo "  php validate_framework_compliance.php --rules auth,translations file.php\n";
        echo "  php validate_framework_compliance.php --json file.php\n";
        echo "\nAvailable rules: auth, translations, templates, routing, security\n";
        exit(0);
    }
    
    $path = $args[0];
    
    if ($check_directory) {
        $results = $validator->validateDirectory($path, $rules_to_check);
        
        if ($json_output) {
            echo json_encode($results, JSON_PRETTY_PRINT) . "\n";
        } else {
            $validator->displayDirectoryResults($results);
        }
    } else {
        $results = $validator->validateFile($path, $rules_to_check);
        
        if ($json_output) {
            echo json_encode($results, JSON_PRETTY_PRINT) . "\n";
        } else {
            $validator->displayResults($results);
        }
    }
}