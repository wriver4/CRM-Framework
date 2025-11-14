<?php
/**
 * Naming Convention Suggester
 * 
 * Suggests framework-compliant names for translation keys, variables, CSS classes, and files
 * Based on actual patterns from Contacts and Leads modules
 * 
 * Usage:
 *   php suggest_naming.php --type translation_key --context "button for creating template" --module email_template
 *   php suggest_naming.php --type variable --context "database results for leads"
 *   php suggest_naming.php --type css_class --context "primary save button"
 *   php suggest_naming.php --type file --context "list page for opportunities"
 *   php suggest_naming.php --type db_column --context "personal street address"
 *   php suggest_naming.php --json --type translation_key --text "First Name" --module contact
 */

class NamingConventionSuggester {
    
    /**
     * Translation key patterns based on actual framework usage
     * Pattern: {module}_{element} (NOT {module}_{page}_{element}_{type})
     */
    private $translation_patterns = [
        'module_prefix' => '{module}_',
        'actions' => [
            'new' => '{module}_new',           // e.g., lead_new, contact_new
            'view' => '{module}_view',         // e.g., lead_view, contact_view
            'edit' => '{module}_edit',         // e.g., lead_edit, contact_edit
            'delete' => '{module}_delete',     // e.g., lead_delete, contact_delete
        ],
        'sections' => [
            'information' => '{module}_contact_information',
            'address' => '{module}_property_address',
            'details' => '{module}_details',
        ],
        'fields' => [
            // Direct field mapping - no suffix
            'pattern' => '{module}_{field_name}',
            'examples' => [
                'lead_first_name',
                'lead_family_name',
                'lead_cell_phone',
                'lead_email',
                'contact_type',
                'contact_call_order'
            ]
        ],
        'common_fields' => [
            // Generic fields without module prefix
            'first_name' => 'First Name',
            'family_name' => 'Family Name',
            'fullname' => 'Full Name',
            'cell_phone' => 'Cell Phone',
            'business_phone' => 'Business Phone',
            'alt_phone' => 'Alternate Phone',
            'personal_email' => 'Personal Email',
            'business_email' => 'Business Email',
            'alt_email' => 'Alternate Email',
            'street_address_1' => 'Street Address',
            'street_address_2' => 'Street Address 2',
            'city' => 'City',
            'state' => 'State',
            'postcode' => 'Zip Code',
            'country' => 'Country',
        ],
        'no_suffixes' => true, // Framework doesn't use _label, _button, _title suffixes
    ];
    
    /**
     * CSS class patterns (Bootstrap 5)
     */
    private $css_patterns = [
        'buttons' => [
            'primary' => 'btn btn-primary',           // Save, Submit, Create
            'success' => 'btn btn-success',           // Final submit, Complete
            'secondary' => 'btn btn-secondary',       // Back, Cancel
            'info' => 'btn btn-info',                 // View related items
            'danger' => 'btn btn-danger',             // Delete
            'outline_primary' => 'btn btn-outline-primary',
            'outline_secondary' => 'btn btn-outline-secondary',
            'outline_info' => 'btn btn-outline-info',
            'small' => 'btn btn-sm',                  // Compact buttons
        ],
        'forms' => [
            'group' => 'form-group pb-2',             // Form group with padding
            'label' => 'pb-1',                        // Label padding
            'label_with_top' => 'pb-1 pt-1',         // Label with top/bottom padding
            'required' => 'required',                 // Required field indicator
            'input' => 'form-control',                // Text, email, tel inputs
            'select' => 'form-select',                // Select dropdowns
            'checkbox' => 'form-check',               // Checkbox container
            'checkbox_input' => 'form-check-input',   // Checkbox input
            'checkbox_label' => 'form-check-label',   // Checkbox label
        ],
        'layout' => [
            'row' => 'row',
            'row_aligned' => 'row align-items-start',
            'col' => 'col',
            'col_half' => 'col-6',
            'card' => 'card',
            'card_header' => 'card-header',
            'card_body' => 'card-body',
            'alert' => 'alert alert-{type} alert-dismissible fade show',
            'accordion' => 'accordion',
            'accordion_item' => 'accordion-item',
            'accordion_header' => 'accordion-header',
            'accordion_button' => 'accordion-button',
            'accordion_collapse' => 'accordion-collapse',
        ],
        'tables' => [
            'table' => 'table table-striped table-hover',
            'responsive' => 'table-responsive',
        ],
    ];
    
    /**
     * Variable naming patterns
     */
    private $variable_patterns = [
        'database_results' => [
            'multiple' => '$results',              // Multiple rows
            'single' => '$result',                 // Single row
            'row' => '$row',                       // Row in loop
        ],
        'class_instances' => [
            'pattern' => '${class_name_plural}',   // e.g., $contacts, $leads, $users
            'examples' => ['$contacts', '$leads', '$users', '$helpers'],
        ],
        'lists' => [
            'pattern' => '${descriptive_plural}',  // e.g., $property_contacts, $lead_contacts
        ],
        'field_extraction' => [
            'pattern' => '${column_name}',         // Direct column mapping
            'example' => '$first_name = $result[\'first_name\']',
        ],
    ];
    
    /**
     * Database column naming patterns
     */
    private $db_column_patterns = [
        'prefixes' => [
            'personal' => 'p_',                    // Personal address: p_street_1, p_city, p_state
            'business' => 'b_',                    // Business address: b_street_1, b_city, b_state
            'mailing' => 'm_',                     // Mailing address: m_street_1, m_city, m_state
            'form' => 'form_',                     // Form fields: form_street_1, form_city, form_state
        ],
        'common_columns' => [
            'id', 'contact_id', 'lead_id', 'user_id',
            'first_name', 'family_name', 'full_name',
            'cell_phone', 'business_phone', 'alt_phone',
            'personal_email', 'business_email', 'alt_email',
            'street_1', 'street_2', 'city', 'state', 'postcode', 'country',
            'timezone', 'status', 'created_at', 'updated_at',
        ],
        'address_fields' => [
            'street_1', 'street_2', 'city', 'state', 'postcode', 'country'
        ],
    ];
    
    /**
     * File structure conventions
     */
    private $file_patterns = [
        'crud' => [
            'list' => 'list.php',
            'view' => 'view.php',
            'new' => 'new.php',
            'edit' => 'edit.php',
        ],
        'api' => [
            'get' => 'get.php',
            'post' => 'post.php',
            'delete' => 'delete.php',
        ],
        'pattern' => '{module}/{action}.php',
    ];
    
    /**
     * Suggest translation key name
     */
    public function suggestTranslationKey($text, $module = '', $context = '') {
        $suggestions = [];
        
        // Clean the text
        $text = trim($text);
        $text = strip_tags($text);
        
        // Convert to snake_case
        $base_key = $this->toSnakeCase($text);
        
        // Check if it's a common field (no module prefix needed)
        if (isset($this->translation_patterns['common_fields'][$base_key])) {
            $suggestions[] = [
                'name' => $base_key,
                'pattern' => 'common_field',
                'explanation' => 'Common field - no module prefix needed',
                'example_value' => $this->translation_patterns['common_fields'][$base_key],
            ];
        }
        
        // Check if it's an action
        foreach ($this->translation_patterns['actions'] as $action => $pattern) {
            if (stripos($text, $action) !== false || stripos($context, $action) !== false) {
                $key = $module ? str_replace('{module}', $module, $pattern) : $pattern;
                $suggestions[] = [
                    'name' => $key,
                    'pattern' => 'module_action',
                    'explanation' => "Action pattern: {module}_{action}",
                    'example' => 'lead_new, contact_edit',
                ];
            }
        }
        
        // Module-prefixed field
        if ($module) {
            $key = $module . '_' . $base_key;
            $suggestions[] = [
                'name' => $key,
                'pattern' => 'module_field',
                'explanation' => "Field pattern: {module}_{field_name} (NO suffix)",
                'example' => 'lead_first_name, contact_type',
            ];
        }
        
        // Generic suggestion
        if (empty($suggestions)) {
            $key = $module ? $module . '_' . $base_key : $base_key;
            $suggestions[] = [
                'name' => $key,
                'pattern' => 'generic',
                'explanation' => 'Generic pattern: {module}_{element}',
                'note' => 'Framework uses simple {module}_{element} pattern, NOT {module}_{page}_{element}_{type}',
            ];
        }
        
        return [
            'suggested_name' => $suggestions[0]['name'],
            'alternatives' => array_slice($suggestions, 1),
            'all_suggestions' => $suggestions,
            'important_note' => 'Framework does NOT use suffixes like _label, _button, _title',
        ];
    }
    
    /**
     * Suggest CSS class
     */
    public function suggestCssClass($context) {
        $context_lower = strtolower($context);
        $suggestions = [];
        
        // Button patterns
        if (stripos($context, 'button') !== false || stripos($context, 'btn') !== false) {
            if (stripos($context, 'save') !== false || stripos($context, 'submit') !== false || stripos($context, 'create') !== false) {
                $suggestions[] = [
                    'name' => 'btn btn-primary',
                    'usage' => 'Primary action buttons (Save, Submit, Create)',
                ];
            }
            if (stripos($context, 'complete') !== false || stripos($context, 'final') !== false) {
                $suggestions[] = [
                    'name' => 'btn btn-success',
                    'usage' => 'Success/completion buttons',
                ];
            }
            if (stripos($context, 'back') !== false || stripos($context, 'cancel') !== false) {
                $suggestions[] = [
                    'name' => 'btn btn-secondary',
                    'usage' => 'Secondary actions (Back, Cancel)',
                ];
            }
            if (stripos($context, 'delete') !== false || stripos($context, 'remove') !== false) {
                $suggestions[] = [
                    'name' => 'btn btn-danger',
                    'usage' => 'Destructive actions (Delete)',
                ];
            }
            if (stripos($context, 'view') !== false || stripos($context, 'info') !== false) {
                $suggestions[] = [
                    'name' => 'btn btn-info',
                    'usage' => 'Informational actions (View)',
                ];
            }
            if (stripos($context, 'outline') !== false || stripos($context, 'secondary') !== false) {
                $suggestions[] = [
                    'name' => 'btn btn-outline-primary',
                    'usage' => 'Outline variant for navigation/secondary actions',
                ];
            }
            if (stripos($context, 'small') !== false || stripos($context, 'compact') !== false) {
                $suggestions[] = [
                    'name' => 'btn btn-sm',
                    'usage' => 'Small/compact buttons',
                ];
            }
        }
        
        // Form patterns
        if (stripos($context, 'input') !== false || stripos($context, 'text') !== false || stripos($context, 'email') !== false) {
            $suggestions[] = [
                'name' => 'form-control',
                'usage' => 'Text inputs, email, tel, textarea',
            ];
        }
        if (stripos($context, 'select') !== false || stripos($context, 'dropdown') !== false) {
            $suggestions[] = [
                'name' => 'form-select',
                'usage' => 'Select dropdowns',
            ];
        }
        if (stripos($context, 'checkbox') !== false) {
            $suggestions[] = [
                'name' => 'form-check',
                'usage' => 'Checkbox container',
            ];
            $suggestions[] = [
                'name' => 'form-check-input',
                'usage' => 'Checkbox input element',
            ];
            $suggestions[] = [
                'name' => 'form-check-label',
                'usage' => 'Checkbox label',
            ];
        }
        if (stripos($context, 'form group') !== false || stripos($context, 'form-group') !== false) {
            $suggestions[] = [
                'name' => 'form-group pb-2',
                'usage' => 'Form group with bottom padding',
            ];
        }
        if (stripos($context, 'label') !== false) {
            $suggestions[] = [
                'name' => 'pb-1',
                'usage' => 'Label with bottom padding',
            ];
            $suggestions[] = [
                'name' => 'pb-1 pt-1',
                'usage' => 'Label with top and bottom padding',
            ];
        }
        if (stripos($context, 'required') !== false) {
            $suggestions[] = [
                'name' => 'required',
                'usage' => 'Required field indicator',
            ];
        }
        
        // Layout patterns
        if (stripos($context, 'row') !== false) {
            $suggestions[] = [
                'name' => 'row',
                'usage' => 'Bootstrap row',
            ];
            $suggestions[] = [
                'name' => 'row align-items-start',
                'usage' => 'Row with top alignment',
            ];
        }
        if (stripos($context, 'column') !== false || stripos($context, 'col') !== false) {
            $suggestions[] = [
                'name' => 'col',
                'usage' => 'Auto-width column',
            ];
            $suggestions[] = [
                'name' => 'col-6',
                'usage' => 'Half-width column',
            ];
        }
        if (stripos($context, 'card') !== false) {
            $suggestions[] = [
                'name' => 'card',
                'usage' => 'Card container',
            ];
            $suggestions[] = [
                'name' => 'card-header',
                'usage' => 'Card header',
            ];
            $suggestions[] = [
                'name' => 'card-body',
                'usage' => 'Card body',
            ];
        }
        if (stripos($context, 'alert') !== false) {
            $suggestions[] = [
                'name' => 'alert alert-success alert-dismissible fade show',
                'usage' => 'Success alert (replace success with: danger, warning, info)',
            ];
        }
        if (stripos($context, 'accordion') !== false) {
            $suggestions[] = [
                'name' => 'accordion',
                'usage' => 'Accordion container',
            ];
            $suggestions[] = [
                'name' => 'accordion-item',
                'usage' => 'Accordion item',
            ];
        }
        if (stripos($context, 'table') !== false) {
            $suggestions[] = [
                'name' => 'table table-striped table-hover',
                'usage' => 'Standard data table',
            ];
        }
        
        if (empty($suggestions)) {
            return [
                'error' => 'Could not determine CSS class from context',
                'tip' => 'Try being more specific: "primary button", "text input", "data table", etc.',
            ];
        }
        
        return [
            'suggested_name' => $suggestions[0]['name'],
            'alternatives' => array_slice($suggestions, 1),
            'all_suggestions' => $suggestions,
            'framework' => 'Bootstrap 5',
        ];
    }
    
    /**
     * Suggest variable name
     */
    public function suggestVariableName($context) {
        $context_lower = strtolower($context);
        $suggestions = [];
        
        // Database results
        if (stripos($context, 'database') !== false || stripos($context, 'query') !== false || stripos($context, 'results') !== false) {
            if (stripos($context, 'multiple') !== false || stripos($context, 'list') !== false || stripos($context, 'all') !== false) {
                $suggestions[] = [
                    'name' => '$results',
                    'usage' => 'Multiple database rows',
                    'example' => '$results = $stmt->fetchAll();',
                ];
            } else {
                $suggestions[] = [
                    'name' => '$result',
                    'usage' => 'Single database row',
                    'example' => '$result = $stmt->fetch();',
                ];
            }
            $suggestions[] = [
                'name' => '$row',
                'usage' => 'Row variable in foreach loop',
                'example' => 'foreach ($results as $row)',
            ];
        }
        
        // Class instances (plural pattern)
        if (stripos($context, 'class') !== false || stripos($context, 'instance') !== false) {
            $suggestions[] = [
                'name' => '${class_name_plural}',
                'usage' => 'Class instances use plural names',
                'examples' => ['$contacts', '$leads', '$users', '$helpers'],
                'pattern' => 'Framework uses plural for class instances',
            ];
        }
        
        // Lists
        if (stripos($context, 'list') !== false || stripos($context, 'array') !== false) {
            $suggestions[] = [
                'name' => '${descriptive_plural}',
                'usage' => 'Descriptive plural for lists',
                'examples' => ['$property_contacts', '$lead_contacts', '$user_roles'],
            ];
        }
        
        // Field extraction
        if (stripos($context, 'field') !== false || stripos($context, 'column') !== false) {
            $suggestions[] = [
                'name' => '${column_name}',
                'usage' => 'Direct column name mapping',
                'example' => '$first_name = $result[\'first_name\']',
                'pattern' => 'Use exact column name as variable name',
            ];
        }
        
        if (empty($suggestions)) {
            return [
                'error' => 'Could not determine variable name from context',
                'tip' => 'Try: "database results", "class instance for contacts", "list of leads"',
            ];
        }
        
        return [
            'suggested_name' => $suggestions[0]['name'],
            'alternatives' => array_slice($suggestions, 1),
            'all_suggestions' => $suggestions,
        ];
    }
    
    /**
     * Suggest database column name
     */
    public function suggestDbColumn($context) {
        $context_lower = strtolower($context);
        $suggestions = [];
        
        // Address fields with prefixes
        if (stripos($context, 'address') !== false || stripos($context, 'street') !== false || 
            stripos($context, 'city') !== false || stripos($context, 'state') !== false) {
            
            $field = '';
            if (stripos($context, 'street') !== false) $field = 'street_1';
            elseif (stripos($context, 'city') !== false) $field = 'city';
            elseif (stripos($context, 'state') !== false) $field = 'state';
            elseif (stripos($context, 'zip') !== false || stripos($context, 'postal') !== false) $field = 'postcode';
            elseif (stripos($context, 'country') !== false) $field = 'country';
            
            if ($field) {
                if (stripos($context, 'personal') !== false) {
                    $suggestions[] = [
                        'name' => 'p_' . $field,
                        'prefix' => 'p_',
                        'usage' => 'Personal address fields',
                        'examples' => ['p_street_1', 'p_city', 'p_state', 'p_postcode', 'p_country'],
                    ];
                }
                if (stripos($context, 'business') !== false) {
                    $suggestions[] = [
                        'name' => 'b_' . $field,
                        'prefix' => 'b_',
                        'usage' => 'Business address fields',
                        'examples' => ['b_street_1', 'b_city', 'b_state', 'b_postcode', 'b_country'],
                    ];
                }
                if (stripos($context, 'mailing') !== false) {
                    $suggestions[] = [
                        'name' => 'm_' . $field,
                        'prefix' => 'm_',
                        'usage' => 'Mailing address fields',
                        'examples' => ['m_street_1', 'm_city', 'm_state', 'm_postcode', 'm_country'],
                    ];
                }
                if (stripos($context, 'form') !== false) {
                    $suggestions[] = [
                        'name' => 'form_' . $field,
                        'prefix' => 'form_',
                        'usage' => 'Form data fields',
                        'examples' => ['form_street_1', 'form_city', 'form_state', 'form_postcode', 'form_country'],
                    ];
                }
            }
        }
        
        // Common columns
        foreach ($this->db_column_patterns['common_columns'] as $col) {
            if (stripos($context, str_replace('_', ' ', $col)) !== false) {
                $suggestions[] = [
                    'name' => $col,
                    'usage' => 'Standard column name',
                    'pattern' => 'snake_case',
                ];
            }
        }
        
        // Generic snake_case suggestion
        if (empty($suggestions)) {
            $name = $this->toSnakeCase($context);
            $suggestions[] = [
                'name' => $name,
                'usage' => 'Generic column name',
                'pattern' => 'snake_case',
                'note' => 'Consider adding prefix if this is an address field (p_, b_, m_, form_)',
            ];
        }
        
        return [
            'suggested_name' => $suggestions[0]['name'],
            'alternatives' => array_slice($suggestions, 1),
            'all_suggestions' => $suggestions,
            'prefix_patterns' => [
                'p_' => 'Personal address',
                'b_' => 'Business address',
                'm_' => 'Mailing address',
                'form_' => 'Form data',
            ],
        ];
    }
    
    /**
     * Suggest file name
     */
    public function suggestFileName($context, $module = '') {
        $context_lower = strtolower($context);
        $suggestions = [];
        
        // CRUD files
        foreach ($this->file_patterns['crud'] as $action => $file) {
            if (stripos($context, $action) !== false) {
                $path = $module ? "$module/$file" : $file;
                $suggestions[] = [
                    'name' => $file,
                    'full_path' => $path,
                    'type' => 'CRUD',
                    'usage' => ucfirst($action) . ' page',
                ];
            }
        }
        
        // API files
        foreach ($this->file_patterns['api'] as $action => $file) {
            if (stripos($context, $action) !== false || stripos($context, 'api') !== false || stripos($context, 'ajax') !== false) {
                $path = $module ? "$module/$file" : $file;
                $suggestions[] = [
                    'name' => $file,
                    'full_path' => $path,
                    'type' => 'API',
                    'usage' => ucfirst($action) . ' API endpoint',
                ];
            }
        }
        
        if (empty($suggestions)) {
            return [
                'error' => 'Could not determine file name from context',
                'tip' => 'Try: "list page", "edit form", "get api", "post endpoint"',
                'standard_files' => array_merge(
                    $this->file_patterns['crud'],
                    $this->file_patterns['api']
                ),
            ];
        }
        
        return [
            'suggested_name' => $suggestions[0]['name'],
            'alternatives' => array_slice($suggestions, 1),
            'all_suggestions' => $suggestions,
            'pattern' => '{module}/{action}.php',
        ];
    }
    
    /**
     * Convert text to snake_case
     */
    private function toSnakeCase($text) {
        $text = trim($text);
        $text = strip_tags($text);
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '_', $text);
        $text = trim($text, '_');
        return $text;
    }
    
    /**
     * Display results
     */
    public function displayResults($results, $type) {
        echo "\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "  NAMING CONVENTION SUGGESTION - " . strtoupper(str_replace('_', ' ', $type)) . "\n";
        echo "═══════════════════════════════════════════════════════════════\n\n";
        
        if (isset($results['error'])) {
            echo "✗ Error: {$results['error']}\n";
            if (isset($results['tip'])) {
                echo "  Tip: {$results['tip']}\n";
            }
            echo "\n";
            return;
        }
        
        echo "✓ Suggested Name: {$results['suggested_name']}\n\n";
        
        if (!empty($results['all_suggestions'])) {
            echo "All Suggestions:\n";
            echo str_repeat("─", 63) . "\n";
            foreach ($results['all_suggestions'] as $i => $suggestion) {
                $num = $i + 1;
                echo "  $num. {$suggestion['name']}\n";
                if (isset($suggestion['usage'])) {
                    echo "     Usage: {$suggestion['usage']}\n";
                }
                if (isset($suggestion['pattern'])) {
                    echo "     Pattern: {$suggestion['pattern']}\n";
                }
                if (isset($suggestion['explanation'])) {
                    echo "     Explanation: {$suggestion['explanation']}\n";
                }
                if (isset($suggestion['example'])) {
                    echo "     Example: {$suggestion['example']}\n";
                }
                if (isset($suggestion['examples'])) {
                    echo "     Examples: " . implode(', ', $suggestion['examples']) . "\n";
                }
                if (isset($suggestion['note'])) {
                    echo "     Note: {$suggestion['note']}\n";
                }
                echo "\n";
            }
        }
        
        if (isset($results['important_note'])) {
            echo "⚠ Important: {$results['important_note']}\n\n";
        }
        
        if (isset($results['framework'])) {
            echo "Framework: {$results['framework']}\n\n";
        }
        
        if (isset($results['prefix_patterns'])) {
            echo "Prefix Patterns:\n";
            echo str_repeat("─", 63) . "\n";
            foreach ($results['prefix_patterns'] as $prefix => $desc) {
                echo "  • $prefix → $desc\n";
            }
            echo "\n";
        }
    }
}

// CLI Interface - only run if this file is executed directly
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    $suggester = new NamingConventionSuggester();
    
    $args = array_slice($argv, 1);
    $json_output = false;
    $type = '';
    $context = '';
    $module = '';
    $text = '';
    
    // Parse arguments
    for ($i = 0; $i < count($args); $i++) {
        switch ($args[$i]) {
            case '--json':
                $json_output = true;
                break;
            case '--type':
                $type = $args[++$i] ?? '';
                break;
            case '--context':
                $context = $args[++$i] ?? '';
                break;
            case '--module':
                $module = $args[++$i] ?? '';
                break;
            case '--text':
                $text = $args[++$i] ?? '';
                break;
            case '--help':
            case '-h':
                echo "Naming Convention Suggester\n\n";
                echo "Usage:\n";
                echo "  php suggest_naming.php --type TYPE --context CONTEXT [--module MODULE]\n\n";
                echo "Types:\n";
                echo "  translation_key  Suggest translation key names\n";
                echo "  variable         Suggest variable names\n";
                echo "  css_class        Suggest CSS class names\n";
                echo "  file             Suggest file names\n";
                echo "  db_column        Suggest database column names\n\n";
                echo "Options:\n";
                echo "  --json           Output as JSON\n";
                echo "  --text TEXT      Text to convert (for translation keys)\n";
                echo "  --module MODULE  Module name prefix\n\n";
                echo "Examples:\n";
                echo "  php suggest_naming.php --type translation_key --text \"First Name\" --module contact\n";
                echo "  php suggest_naming.php --type css_class --context \"primary save button\"\n";
                echo "  php suggest_naming.php --type variable --context \"database results for leads\"\n";
                echo "  php suggest_naming.php --type file --context \"list page\" --module opportunities\n";
                echo "  php suggest_naming.php --type db_column --context \"personal street address\"\n";
                exit(0);
        }
    }
    
    if (empty($type)) {
        echo "Error: --type is required\n";
        echo "Use --help for usage information\n";
        exit(1);
    }
    
    // Use text if provided, otherwise use context
    $input = $text ?: $context;
    
    if (empty($input)) {
        echo "Error: --context or --text is required\n";
        echo "Use --help for usage information\n";
        exit(1);
    }
    
    // Generate suggestions based on type
    $results = [];
    switch ($type) {
        case 'translation_key':
            $results = $suggester->suggestTranslationKey($input, $module, $context);
            break;
        case 'css_class':
            $results = $suggester->suggestCssClass($input);
            break;
        case 'variable':
            $results = $suggester->suggestVariableName($input);
            break;
        case 'db_column':
            $results = $suggester->suggestDbColumn($input);
            break;
        case 'file':
            $results = $suggester->suggestFileName($input, $module);
            break;
        default:
            echo "Error: Invalid type '$type'\n";
            echo "Valid types: translation_key, css_class, variable, db_column, file\n";
            exit(1);
    }
    
    if ($json_output) {
        echo json_encode($results, JSON_PRETTY_PRINT) . "\n";
    } else {
        $suggester->displayResults($results, $type);
    }
}