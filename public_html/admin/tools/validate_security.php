#!/usr/bin/env php
<?php
/**
 * Security Validator
 * Checks PHP files for security vulnerabilities (SQL injection, XSS, CSRF, input validation)
 * 
 * Usage:
 *   php validate_security.php path/to/file.php
 *   php validate_security.php path/to/file.php --checks=sql_injection,xss
 *   php validate_security.php path/to/file.php --json
 */

class SecurityValidator {
    
    private $vulnerabilities = [];
    private $file_content = '';
    private $lines = [];
    
    private $checks = [
        'sql_injection' => 'checkSqlInjection',
        'xss' => 'checkXss',
        'csrf' => 'checkCsrf',
        'input_validation' => 'checkInputValidation',
        'authentication' => 'checkAuthentication',
        'file_upload' => 'checkFileUpload',
        'session_security' => 'checkSessionSecurity'
    ];
    
    public function validate($file_path, $checks = []) {
        if (!file_exists($file_path)) {
            return ['error' => "File not found: $file_path"];
        }
        
        $this->file_content = file_get_contents($file_path);
        $this->lines = explode("\n", $this->file_content);
        $this->vulnerabilities = [];
        
        // If no specific checks requested, run all
        if (empty($checks)) {
            $checks = array_keys($this->checks);
        }
        
        // Run requested checks
        foreach ($checks as $check) {
            if (isset($this->checks[$check])) {
                $method = $this->checks[$check];
                $this->$method();
            }
        }
        
        // Calculate security score
        $score = $this->calculateScore();
        
        return [
            'file' => $file_path,
            'vulnerabilities' => $this->vulnerabilities,
            'total_issues' => count($this->vulnerabilities),
            'critical' => count(array_filter($this->vulnerabilities, fn($v) => $v['severity'] === 'critical')),
            'high' => count(array_filter($this->vulnerabilities, fn($v) => $v['severity'] === 'high')),
            'medium' => count(array_filter($this->vulnerabilities, fn($v) => $v['severity'] === 'medium')),
            'low' => count(array_filter($this->vulnerabilities, fn($v) => $v['severity'] === 'low')),
            'score' => $score,
            'compliant' => $score >= 80 && count(array_filter($this->vulnerabilities, fn($v) => $v['severity'] === 'critical')) === 0
        ];
    }
    
    private function checkSqlInjection() {
        foreach ($this->lines as $line_num => $line) {
            $line_num++; // 1-based line numbers
            
            // Check for string concatenation in SQL queries
            if (preg_match('/\$\w+\s*=\s*["\'].*?(SELECT|INSERT|UPDATE|DELETE|FROM|WHERE).*?["\'].*?\.\s*\$/', $line, $matches)) {
                $this->addVulnerability(
                    'sql_injection',
                    'critical',
                    $line_num,
                    trim($line),
                    'SQL query uses string concatenation with variables - HIGH RISK for SQL injection',
                    'Use prepared statements with PDO placeholders',
                    '$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?"); $stmt->execute([$id]);'
                );
            }
            
            // Check for direct variable interpolation in queries
            if (preg_match('/(query|prepare|exec)\s*\(["\'].*?\$\w+.*?["\']\)/', $line)) {
                $this->addVulnerability(
                    'sql_injection',
                    'critical',
                    $line_num,
                    trim($line),
                    'SQL query contains direct variable interpolation - SQL injection risk',
                    'Use prepared statements with placeholders',
                    '$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?"); $stmt->execute([$email]);'
                );
            }
            
            // Check for $_GET/$_POST used directly in queries
            if (preg_match('/\$_(GET|POST|REQUEST)\[.*?\]/', $line) && preg_match('/(SELECT|INSERT|UPDATE|DELETE|WHERE)/i', $line)) {
                $this->addVulnerability(
                    'sql_injection',
                    'critical',
                    $line_num,
                    trim($line),
                    'User input from $_GET/$_POST used directly in SQL query',
                    'Validate and sanitize input, use prepared statements',
                    '$id = (int)$_GET["id"]; $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?"); $stmt->execute([$id]);'
                );
            }
        }
    }
    
    private function checkXss() {
        foreach ($this->lines as $line_num => $line) {
            $line_num++;
            
            // Check for unescaped echo of variables
            if (preg_match('/echo\s+\$\w+/', $line) && !preg_match('/htmlspecialchars|htmlentities/', $line)) {
                $this->addVulnerability(
                    'xss',
                    'high',
                    $line_num,
                    trim($line),
                    'Variable echoed without HTML escaping - XSS vulnerability',
                    'Use htmlspecialchars() to escape output',
                    'echo htmlspecialchars($variable, ENT_QUOTES, \'UTF-8\');'
                );
            }
            
            // Check for unescaped $_GET/$_POST output
            if (preg_match('/echo\s+\$_(GET|POST|REQUEST)\[/', $line) && !preg_match('/htmlspecialchars|htmlentities/', $line)) {
                $this->addVulnerability(
                    'xss',
                    'critical',
                    $line_num,
                    trim($line),
                    'User input echoed directly without escaping - CRITICAL XSS vulnerability',
                    'Always escape user input before output',
                    'echo htmlspecialchars($_GET["name"], ENT_QUOTES, \'UTF-8\');'
                );
            }
            
            // Check for <?= without escaping
            if (preg_match('/<\?=\s*\$\w+/', $line) && !preg_match('/htmlspecialchars|htmlentities/', $line)) {
                $this->addVulnerability(
                    'xss',
                    'high',
                    $line_num,
                    trim($line),
                    'Short echo tag without HTML escaping - XSS risk',
                    'Use htmlspecialchars() for output',
                    '<?= htmlspecialchars($variable, ENT_QUOTES, \'UTF-8\') ?>'
                );
            }
            
            // Check for innerHTML/outerHTML in JavaScript
            if (preg_match('/\.innerHTML\s*=|\.outerHTML\s*=/', $line)) {
                $this->addVulnerability(
                    'xss',
                    'medium',
                    $line_num,
                    trim($line),
                    'Direct innerHTML assignment can lead to XSS',
                    'Use textContent or properly sanitize HTML',
                    'element.textContent = userInput; // or use DOMPurify for HTML'
                );
            }
        }
    }
    
    private function checkCsrf() {
        $has_post_form = preg_match('/<form[^>]*method=["\']post["\']/i', $this->file_content);
        $has_csrf_check = preg_match('/\$not->csrf_check\(\)|csrf_token|nonce->check/', $this->file_content);
        $has_csrf_field = preg_match('/nonce->field\(\)|csrf_field/', $this->file_content);
        
        if ($has_post_form && !$has_csrf_field) {
            $this->addVulnerability(
                'csrf',
                'high',
                0,
                'Form detected',
                'POST form found without CSRF token field',
                'Add CSRF token to form',
                '<?php echo $nonce->field(); ?>'
            );
        }
        
        // Check if file handles POST data
        $handles_post = preg_match('/\$_POST/', $this->file_content);
        if ($handles_post && !$has_csrf_check) {
            $this->addVulnerability(
                'csrf',
                'high',
                0,
                'POST handler',
                'File processes POST data without CSRF validation',
                'Add CSRF check before processing POST data',
                'if ($_SERVER["REQUEST_METHOD"] === "POST") { $nonce->check(); /* process data */ }'
            );
        }
    }
    
    private function checkInputValidation() {
        foreach ($this->lines as $line_num => $line) {
            $line_num++;
            
            // Check for direct use of $_GET/$_POST without validation
            if (preg_match('/\$\w+\s*=\s*\$_(GET|POST|REQUEST)\[/', $line)) {
                // Check if there's validation on the same line or nearby
                $has_validation = preg_match('/(int|intval|filter_var|trim|htmlspecialchars|isset|empty|strlen)/', $line);
                
                if (!$has_validation) {
                    $this->addVulnerability(
                        'input_validation',
                        'medium',
                        $line_num,
                        trim($line),
                        'User input assigned without validation or sanitization',
                        'Validate and sanitize all user input',
                        '$id = filter_var($_GET["id"], FILTER_VALIDATE_INT); if ($id === false) { /* error */ }'
                    );
                }
            }
            
            // Check for missing email validation
            if (preg_match('/email/i', $line) && preg_match('/\$_(GET|POST)/', $line) && !preg_match('/filter_var.*FILTER_VALIDATE_EMAIL/', $line)) {
                $this->addVulnerability(
                    'input_validation',
                    'medium',
                    $line_num,
                    trim($line),
                    'Email input without proper validation',
                    'Use filter_var with FILTER_VALIDATE_EMAIL',
                    '$email = filter_var($_POST["email"], FILTER_VALIDATE_EMAIL);'
                );
            }
            
            // Check for file upload without validation
            if (preg_match('/\$_FILES/', $line)) {
                $this->addVulnerability(
                    'file_upload',
                    'high',
                    $line_num,
                    trim($line),
                    'File upload detected - ensure proper validation',
                    'Validate file type, size, and use move_uploaded_file()',
                    'Check file extension whitelist, MIME type, and size limits'
                );
            }
        }
    }
    
    private function checkAuthentication() {
        $has_auth_check = preg_match('/\$not->loggedin\(\)|session.*user_id|is_logged_in/', $this->file_content);
        $is_admin_file = preg_match('/\/admin\//', $this->file_content) || strpos($this->file_content, 'admin') !== false;
        
        if ($is_admin_file && !$has_auth_check) {
            $this->addVulnerability(
                'authentication',
                'critical',
                0,
                'File location',
                'Admin file without authentication check',
                'Add authentication check at the top of the file',
                '$not->loggedin();'
            );
        }
    }
    
    private function checkFileUpload() {
        if (preg_match('/move_uploaded_file/', $this->file_content)) {
            $has_extension_check = preg_match('/pathinfo.*PATHINFO_EXTENSION|getExtension/', $this->file_content);
            $has_mime_check = preg_match('/mime_content_type|finfo_file/', $this->file_content);
            $has_size_check = preg_match('/\$_FILES\[.*?\]\[.size.\]/', $this->file_content);
            
            if (!$has_extension_check) {
                $this->addVulnerability(
                    'file_upload',
                    'critical',
                    0,
                    'File upload',
                    'File upload without extension validation - can upload malicious files',
                    'Validate file extension against whitelist',
                    '$allowed = ["jpg", "png", "pdf"]; $ext = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION); if (!in_array($ext, $allowed)) { die("Invalid file type"); }'
                );
            }
            
            if (!$has_mime_check) {
                $this->addVulnerability(
                    'file_upload',
                    'high',
                    0,
                    'File upload',
                    'File upload without MIME type validation',
                    'Validate MIME type',
                    '$finfo = finfo_open(FILEINFO_MIME_TYPE); $mime = finfo_file($finfo, $_FILES["file"]["tmp_name"]);'
                );
            }
            
            if (!$has_size_check) {
                $this->addVulnerability(
                    'file_upload',
                    'medium',
                    0,
                    'File upload',
                    'File upload without size validation',
                    'Check file size limits',
                    'if ($_FILES["file"]["size"] > 5000000) { die("File too large"); }'
                );
            }
        }
    }
    
    private function checkSessionSecurity() {
        if (preg_match('/session_start/', $this->file_content)) {
            $has_regenerate = preg_match('/session_regenerate_id/', $this->file_content);
            $has_httponly = preg_match('/session\.cookie_httponly|ini_set.*session\.cookie_httponly/', $this->file_content);
            
            if (!$has_httponly) {
                $this->addVulnerability(
                    'session_security',
                    'medium',
                    0,
                    'Session configuration',
                    'Session cookies should have HttpOnly flag',
                    'Set HttpOnly flag for session cookies',
                    'ini_set("session.cookie_httponly", 1);'
                );
            }
        }
    }
    
    private function addVulnerability($type, $severity, $line, $code, $issue, $fix, $example) {
        $this->vulnerabilities[] = [
            'type' => $type,
            'severity' => $severity,
            'line' => $line,
            'code' => $code,
            'issue' => $issue,
            'fix' => $fix,
            'example' => $example
        ];
    }
    
    private function calculateScore() {
        $total_issues = count($this->vulnerabilities);
        
        if ($total_issues === 0) {
            return 100;
        }
        
        // Weight by severity
        $penalty = 0;
        foreach ($this->vulnerabilities as $vuln) {
            switch ($vuln['severity']) {
                case 'critical':
                    $penalty += 25;
                    break;
                case 'high':
                    $penalty += 15;
                    break;
                case 'medium':
                    $penalty += 8;
                    break;
                case 'low':
                    $penalty += 3;
                    break;
            }
        }
        
        $score = max(0, 100 - $penalty);
        return $score;
    }
    
    public function display($result, $json = false) {
        if ($json) {
            echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
            return;
        }
        
        if (isset($result['error'])) {
            echo "\n❌ ERROR: {$result['error']}\n";
            return;
        }
        
        echo "\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "  SECURITY VALIDATION REPORT\n";
        echo "═══════════════════════════════════════════════════════════════\n\n";
        
        echo "File: {$result['file']}\n\n";
        
        echo "SUMMARY:\n";
        echo "───────────────────────────────────────────────────────────────\n";
        echo sprintf("Total Issues:     %d\n", $result['total_issues']);
        echo sprintf("Critical:         %d\n", $result['critical']);
        echo sprintf("High:             %d\n", $result['high']);
        echo sprintf("Medium:           %d\n", $result['medium']);
        echo sprintf("Low:              %d\n", $result['low']);
        echo sprintf("Security Score:   %d/100\n", $result['score']);
        echo sprintf("Compliant:        %s\n\n", $result['compliant'] ? '✓ YES' : '✗ NO');
        
        if (empty($result['vulnerabilities'])) {
            echo "✓ No security issues found!\n\n";
            return;
        }
        
        // Group by severity
        $by_severity = [
            'critical' => [],
            'high' => [],
            'medium' => [],
            'low' => []
        ];
        
        foreach ($result['vulnerabilities'] as $vuln) {
            $by_severity[$vuln['severity']][] = $vuln;
        }
        
        foreach (['critical', 'high', 'medium', 'low'] as $severity) {
            if (empty($by_severity[$severity])) {
                continue;
            }
            
            $label = strtoupper($severity);
            $icon = $severity === 'critical' ? '🔴' : ($severity === 'high' ? '🟠' : ($severity === 'medium' ? '🟡' : '🔵'));
            
            echo "{$icon} {$label} SEVERITY ISSUES:\n";
            echo "───────────────────────────────────────────────────────────────\n";
            
            foreach ($by_severity[$severity] as $vuln) {
                if ($vuln['line'] > 0) {
                    echo "Line {$vuln['line']}: ";
                }
                echo "[{$vuln['type']}]\n";
                echo "  Issue: {$vuln['issue']}\n";
                if (!empty($vuln['code']) && $vuln['code'] !== 'File location' && $vuln['code'] !== 'Form detected' && $vuln['code'] !== 'POST handler') {
                    echo "  Code:  {$vuln['code']}\n";
                }
                echo "  Fix:   {$vuln['fix']}\n";
                echo "  Example: {$vuln['example']}\n\n";
            }
        }
    }
}

// CLI Interface
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    $options = getopt('', ['checks::', 'json', 'help']);
    $args = array_slice($argv, 1);
    
    // Remove known options to get file path
    $file_path = null;
    foreach ($args as $arg) {
        if (!str_starts_with($arg, '--')) {
            $file_path = $arg;
            break;
        }
    }
    
    if (isset($options['help']) || empty($file_path)) {
        echo <<<HELP

Security Validator
═══════════════════════════════════════════════════════════════

Checks PHP files for security vulnerabilities including SQL injection,
XSS, CSRF, input validation, authentication, and file upload issues.

USAGE:
  php validate_security.php FILE_PATH [OPTIONS]

ARGUMENTS:
  FILE_PATH            Path to PHP file to validate

OPTIONS:
  --checks=LIST        Comma-separated list of checks to run:
                       - sql_injection
                       - xss
                       - csrf
                       - input_validation
                       - authentication
                       - file_upload
                       - session_security
                       (default: all checks)
  
  --json               Output in JSON format
  --help               Show this help message

EXAMPLES:
  # Run all security checks
  php validate_security.php admin/users/edit.php
  
  # Run specific checks only
  php validate_security.php admin/users/post.php --checks=sql_injection,xss
  
  # JSON output
  php validate_security.php admin/users/list.php --json

SEVERITY LEVELS:
  🔴 CRITICAL - Immediate security risk, must fix
  🟠 HIGH     - Serious vulnerability, fix soon
  🟡 MEDIUM   - Potential issue, should fix
  🔵 LOW      - Minor concern, consider fixing

SECURITY SCORE:
  90-100  - Excellent security
  80-89   - Good security
  70-79   - Fair security (needs improvement)
  < 70    - Poor security (urgent fixes needed)


HELP;
        exit(0);
    }
    
    $checks = [];
    if (isset($options['checks'])) {
        $checks = explode(',', $options['checks']);
    }
    
    $validator = new SecurityValidator();
    $result = $validator->validate($file_path, $checks);
    $validator->display($result, isset($options['json']));
    
    // Exit with error code if critical issues found
    exit($result['critical'] > 0 ? 1 : 0);
}