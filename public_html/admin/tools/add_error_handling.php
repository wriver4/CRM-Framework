#!/usr/bin/env php
<?php
/**
 * Error Handling Wrapper
 * Adds framework-compliant error handling to code snippets
 * 
 * Usage:
 *   php add_error_handling.php --file=path/to/file.php --types=database,validation --context=page
 *   php add_error_handling.php --code="$stmt->execute();" --types=database --context=ajax
 */

class ErrorHandlingWrapper {
    
    private $translation_keys = [];
    
    public function addErrorHandling($code, $error_types, $context) {
        $this->translation_keys = [];
        
        $wrapped_code = $code;
        
        // Add try-catch wrapper if database errors requested
        if (in_array('database', $error_types)) {
            $wrapped_code = $this->wrapDatabaseErrors($wrapped_code, $context);
        }
        
        // Add validation error handling
        if (in_array('validation', $error_types)) {
            $wrapped_code = $this->addValidationHandling($wrapped_code, $context);
        }
        
        // Add API error handling
        if (in_array('api', $error_types)) {
            $wrapped_code = $this->addApiErrorHandling($wrapped_code, $context);
        }
        
        return [
            'code_with_error_handling' => $wrapped_code,
            'translation_keys_needed' => array_unique($this->translation_keys),
            'logging_added' => in_array('database', $error_types) || in_array('api', $error_types)
        ];
    }
    
    private function wrapDatabaseErrors($code, $context) {
        $this->translation_keys[] = 'database_error';
        
        $wrapped = "try {\n";
        $wrapped .= $this->indentCode($code, 1);
        $wrapped .= "\n} catch (PDOException \$e) {\n";
        $wrapped .= "    // Log the error\n";
        $wrapped .= "    error_log('Database error: ' . \$e->getMessage());\n";
        $wrapped .= "    error_log('Stack trace: ' . \$e->getTraceAsString());\n\n";
        
        if ($context === 'ajax' || $context === 'api') {
            $wrapped .= "    // Return JSON error response\n";
            $wrapped .= "    header('Content-Type: application/json');\n";
            $wrapped .= "    echo json_encode([\n";
            $wrapped .= "        'success' => false,\n";
            $wrapped .= "        'error' => \$lang['database_error']\n";
            $wrapped .= "    ]);\n";
            $wrapped .= "    exit;\n";
        } else {
            $wrapped .= "    // Set error message for user\n";
            $wrapped .= "    \$_SESSION['error_message'] = \$lang['database_error'];\n";
            $wrapped .= "    \$_SESSION['error_type'] = 'danger';\n";
            $wrapped .= "    // Redirect or display error\n";
            $wrapped .= "    // header('Location: list.php');\n";
            $wrapped .= "    // exit;\n";
        }
        
        $wrapped .= "}";
        
        return $wrapped;
    }
    
    private function addValidationHandling($code, $context) {
        $this->translation_keys[] = 'validation_error';
        $this->translation_keys[] = 'field_required_error';
        
        $validation = "// Validation error handling\n";
        $validation .= "\$errors = [];\n\n";
        $validation .= "// Example validation\n";
        $validation .= "if (empty(\$_POST['field_name'])) {\n";
        $validation .= "    \$errors[] = \$lang['field_required_error'];\n";
        $validation .= "}\n\n";
        $validation .= "if (!empty(\$errors)) {\n";
        
        if ($context === 'ajax' || $context === 'api') {
            $validation .= "    header('Content-Type: application/json');\n";
            $validation .= "    echo json_encode([\n";
            $validation .= "        'success' => false,\n";
            $validation .= "        'errors' => \$errors\n";
            $validation .= "    ]);\n";
            $validation .= "    exit;\n";
        } else {
            $validation .= "    \$_SESSION['validation_errors'] = \$errors;\n";
            $validation .= "    \$_SESSION['form_data'] = \$_POST;\n";
            $validation .= "    header('Location: ' . \$_SERVER['PHP_SELF']);\n";
            $validation .= "    exit;\n";
        }
        
        $validation .= "}\n\n";
        $validation .= $code;
        
        return $validation;
    }
    
    private function addApiErrorHandling($code, $context) {
        $this->translation_keys[] = 'api_error';
        $this->translation_keys[] = 'general_error';
        
        $wrapped = "try {\n";
        $wrapped .= $this->indentCode($code, 1);
        $wrapped .= "\n} catch (Exception \$e) {\n";
        $wrapped .= "    // Log the error\n";
        $wrapped .= "    error_log('API error: ' . \$e->getMessage());\n\n";
        
        if ($context === 'ajax' || $context === 'api') {
            $wrapped .= "    // Return JSON error response\n";
            $wrapped .= "    header('Content-Type: application/json');\n";
            $wrapped .= "    http_response_code(500);\n";
            $wrapped .= "    echo json_encode([\n";
            $wrapped .= "        'success' => false,\n";
            $wrapped .= "        'error' => \$lang['general_error']\n";
            $wrapped .= "    ]);\n";
            $wrapped .= "    exit;\n";
        } else {
            $wrapped .= "    // Set error message\n";
            $wrapped .= "    \$_SESSION['error_message'] = \$lang['general_error'];\n";
            $wrapped .= "    \$_SESSION['error_type'] = 'danger';\n";
        }
        
        $wrapped .= "}";
        
        return $wrapped;
    }
    
    private function indentCode($code, $levels = 1) {
        $indent = str_repeat('    ', $levels);
        $lines = explode("\n", $code);
        return implode("\n" . $indent, $lines);
    }
    
    public function wrapFile($file_path, $error_types, $context) {
        if (!file_exists($file_path)) {
            return ['error' => "File not found: $file_path"];
        }
        
        $code = file_get_contents($file_path);
        
        // Extract the main code block (between <?php and end)
        $pattern = '/<\?php\s*(.*)/s';
        if (preg_match($pattern, $code, $matches)) {
            $main_code = $matches[1];
            
            $result = $this->addErrorHandling($main_code, $error_types, $context);
            
            // Reconstruct the file
            $result['code_with_error_handling'] = "<?php\n" . $result['code_with_error_handling'];
            
            return $result;
        }
        
        return $this->addErrorHandling($code, $error_types, $context);
    }
    
    public function generateErrorDisplayComponent($context = 'page') {
        $this->translation_keys[] = 'close_button';
        
        if ($context === 'ajax' || $context === 'api') {
            return [
                'component' => 'JSON response (see generated code)',
                'type' => 'json'
            ];
        }
        
        $html = "<!-- Error/Success Message Display -->\n";
        $html .= "<?php if (isset(\$_SESSION['error_message'])): ?>\n";
        $html .= "    <div class=\"alert alert-<?php echo \$_SESSION['error_type'] ?? 'danger'; ?> alert-dismissible fade show\" role=\"alert\">\n";
        $html .= "        <?php echo htmlspecialchars(\$_SESSION['error_message']); ?>\n";
        $html .= "        <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"<?php echo \$lang['close_button']; ?>\"></button>\n";
        $html .= "    </div>\n";
        $html .= "    <?php\n";
        $html .= "    unset(\$_SESSION['error_message']);\n";
        $html .= "    unset(\$_SESSION['error_type']);\n";
        $html .= "    ?>\n";
        $html .= "<?php endif; ?>\n\n";
        
        $html .= "<?php if (isset(\$_SESSION['validation_errors'])): ?>\n";
        $html .= "    <div class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\">\n";
        $html .= "        <strong><?php echo \$lang['validation_error']; ?></strong>\n";
        $html .= "        <ul class=\"mb-0 mt-2\">\n";
        $html .= "            <?php foreach (\$_SESSION['validation_errors'] as \$error): ?>\n";
        $html .= "                <li><?php echo htmlspecialchars(\$error); ?></li>\n";
        $html .= "            <?php endforeach; ?>\n";
        $html .= "        </ul>\n";
        $html .= "        <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"<?php echo \$lang['close_button']; ?>\"></button>\n";
        $html .= "    </div>\n";
        $html .= "    <?php\n";
        $html .= "    unset(\$_SESSION['validation_errors']);\n";
        $html .= "    ?>\n";
        $html .= "<?php endif; ?>\n";
        
        return [
            'component' => $html,
            'type' => 'html'
        ];
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
        echo "  CODE WITH ERROR HANDLING\n";
        echo "═══════════════════════════════════════════════════════════════\n\n";
        
        echo "CODE:\n";
        echo "───────────────────────────────────────────────────────────────\n";
        echo $result['code_with_error_handling'] . "\n\n";
        
        if (!empty($result['translation_keys_needed'])) {
            echo "TRANSLATION KEYS NEEDED:\n";
            echo "───────────────────────────────────────────────────────────────\n";
            foreach ($result['translation_keys_needed'] as $key) {
                echo "  • {$key}\n";
            }
            echo "\n";
        }
        
        echo "LOGGING ADDED: " . ($result['logging_added'] ? '✓ Yes' : '✗ No') . "\n\n";
        
        if (isset($result['component'])) {
            echo "ERROR DISPLAY COMPONENT:\n";
            echo "───────────────────────────────────────────────────────────────\n";
            echo $result['component'] . "\n\n";
        }
    }
}

// CLI Interface
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    $options = getopt('', ['file::', 'code::', 'types:', 'context:', 'component', 'json', 'help']);
    
    if (isset($options['help']) || (empty($options['file']) && empty($options['code']) && !isset($options['component']))) {
        echo <<<HELP

Error Handling Wrapper
═══════════════════════════════════════════════════════════════

Adds framework-compliant error handling to PHP code.

USAGE:
  php add_error_handling.php --file=FILE --types=TYPES --context=CONTEXT
  php add_error_handling.php --code="CODE" --types=TYPES --context=CONTEXT
  php add_error_handling.php --component --context=CONTEXT

OPTIONS:
  --file=FILE          Path to PHP file to wrap with error handling
  --code=CODE          Code snippet to wrap (alternative to --file)
  --component          Generate error display component only
  
  --types=LIST         Comma-separated error types to handle:
                       - database (PDO exceptions)
                       - validation (form validation)
                       - api (general exceptions)
  
  --context=CONTEXT    Context for error handling:
                       - page (regular page with redirects)
                       - ajax (AJAX endpoint with JSON)
                       - api (API endpoint with JSON)
  
  --json               Output in JSON format
  --help               Show this help message

EXAMPLES:
  # Wrap a file with database error handling
  php add_error_handling.php --file=admin/users/post.php --types=database,validation --context=page
  
  # Wrap code snippet for AJAX endpoint
  php add_error_handling.php --code="\$stmt->execute();" --types=database --context=ajax
  
  # Generate error display component
  php add_error_handling.php --component --context=page
  
  # Multiple error types for API
  php add_error_handling.php --file=api/endpoint.php --types=database,api --context=api
  
  # JSON output
  php add_error_handling.php --code="\$stmt->execute();" --types=database --context=ajax --json

ERROR TYPES:
  database    - Wraps code in try-catch for PDOException
                Logs errors and shows user-friendly messages
  
  validation  - Adds validation error collection and display
                Stores errors in session or returns JSON
  
  api         - Wraps code in try-catch for general exceptions
                Returns proper HTTP status codes

CONTEXTS:
  page        - Regular page: uses session messages and redirects
  ajax        - AJAX endpoint: returns JSON responses
  api         - API endpoint: returns JSON with HTTP status codes


HELP;
        exit(0);
    }
    
    $wrapper = new ErrorHandlingWrapper();
    
    // Generate error display component
    if (isset($options['component'])) {
        $context = $options['context'] ?? 'page';
        $result = $wrapper->generateErrorDisplayComponent($context);
        $result['translation_keys_needed'] = $wrapper->translation_keys;
        $wrapper->display($result, isset($options['json']));
        exit(0);
    }
    
    $error_types = explode(',', $options['types'] ?? 'database');
    $context = $options['context'] ?? 'page';
    
    if (!empty($options['file'])) {
        $result = $wrapper->wrapFile($options['file'], $error_types, $context);
    } else {
        $code = $options['code'] ?? '';
        $result = $wrapper->addErrorHandling($code, $error_types, $context);
    }
    
    $wrapper->display($result, isset($options['json']));
}