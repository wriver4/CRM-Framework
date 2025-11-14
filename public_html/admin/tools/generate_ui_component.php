#!/usr/bin/env php
<?php
/**
 * UI Component Generator
 * Generates framework-compliant UI components (tables, forms, filters, headers, alerts, buttons)
 * 
 * Usage:
 *   php generate_ui_component.php --type=data_table --columns=id,name,email,status --prefix=user
 *   php generate_ui_component.php --type=form --fields=name:text,email:email,status:select --prefix=user
 *   php generate_ui_component.php --type=filters --filters=status,date_range --prefix=email_queue
 *   php generate_ui_component.php --type=page_header --title="Email Queue" --prefix=email_queue
 *   php generate_ui_component.php --type=action_buttons --actions=create,export --prefix=email_template
 */

class UIComponentGenerator {
    
    private $translation_keys = [];
    
    public function generate($type, $options, $prefix) {
        $method = 'generate' . ucfirst(str_replace('_', '', ucwords($type, '_')));
        
        if (!method_exists($this, $method)) {
            return [
                'error' => "Unknown component type: $type",
                'valid_types' => ['page_header', 'data_table', 'form', 'filters', 'action_buttons', 'alert']
            ];
        }
        
        $this->translation_keys = [];
        $result = $this->$method($options, $prefix);
        $result['translation_keys_needed'] = $this->translation_keys;
        
        return $result;
    }
    
    private function generatePageHeader($options, $prefix) {
        $title = $options['title'] ?? 'Page Title';
        $subtitle = $options['subtitle'] ?? '';
        $breadcrumbs = $options['breadcrumbs'] ?? [];
        
        $title_key = "{$prefix}_title";
        $this->translation_keys[] = $title_key;
        
        $html = "<!-- Page Header -->\n";
        $html .= "<div class=\"page-header\">\n";
        
        if (!empty($breadcrumbs)) {
            $html .= "    <nav aria-label=\"breadcrumb\">\n";
            $html .= "        <ol class=\"breadcrumb\">\n";
            foreach ($breadcrumbs as $crumb) {
                $crumb_key = "{$prefix}_{$crumb}_breadcrumb";
                $this->translation_keys[] = $crumb_key;
                $html .= "            <li class=\"breadcrumb-item\"><a href=\"#\"><?php echo \$lang['{$crumb_key}']; ?></a></li>\n";
            }
            $html .= "            <li class=\"breadcrumb-item active\" aria-current=\"page\"><?php echo \$lang['{$title_key}']; ?></li>\n";
            $html .= "        </ol>\n";
            $html .= "    </nav>\n";
        }
        
        $html .= "    <h1><?php echo \$lang['{$title_key}']; ?></h1>\n";
        
        if ($subtitle) {
            $subtitle_key = "{$prefix}_subtitle";
            $this->translation_keys[] = $subtitle_key;
            $html .= "    <p class=\"text-muted\"><?php echo \$lang['{$subtitle_key}']; ?></p>\n";
        }
        
        $html .= "</div>\n";
        
        return [
            'html' => $html,
            'php' => '',
            'javascript' => '',
            'css' => ''
        ];
    }
    
    private function generateDataTable($options, $prefix) {
        $columns = $options['columns'] ?? ['id', 'name', 'status'];
        $actions = $options['actions'] ?? ['view', 'edit', 'delete'];
        $table_id = $options['table_id'] ?? 'dataTable';
        $ajax_source = $options['ajax_source'] ?? '';
        
        // Generate column headers
        $html = "<!-- Data Table -->\n";
        $html .= "<div class=\"table-responsive\">\n";
        $html .= "    <table id=\"{$table_id}\" class=\"table table-striped table-hover\">\n";
        $html .= "        <thead>\n";
        $html .= "            <tr>\n";
        
        foreach ($columns as $col) {
            $col_key = "{$prefix}_{$col}_column";
            $this->translation_keys[] = $col_key;
            $html .= "                <th><?php echo \$lang['{$col_key}']; ?></th>\n";
        }
        
        if (!empty($actions)) {
            $actions_key = "{$prefix}_actions_column";
            $this->translation_keys[] = $actions_key;
            $html .= "                <th><?php echo \$lang['{$actions_key}']; ?></th>\n";
        }
        
        $html .= "            </tr>\n";
        $html .= "        </thead>\n";
        $html .= "        <tbody>\n";
        
        // Generate sample row (PHP loop)
        $php = "<?php if (!empty(\$items)): ?>\n";
        $php .= "    <?php foreach (\$items as \$item): ?>\n";
        $php .= "        <tr>\n";
        
        foreach ($columns as $col) {
            $php .= "            <td><?php echo htmlspecialchars(\$item['{$col}'] ?? ''); ?></td>\n";
        }
        
        if (!empty($actions)) {
            $php .= "            <td>\n";
            foreach ($actions as $action) {
                $action_key = "{$prefix}_{$action}_button";
                $this->translation_keys[] = $action_key;
                
                $btn_class = $action === 'delete' ? 'btn-danger' : ($action === 'edit' ? 'btn-primary' : 'btn-secondary');
                $icon = $action === 'delete' ? 'trash' : ($action === 'edit' ? 'pencil' : 'eye');
                
                $php .= "                <a href=\"{$action}.php?id=<?php echo \$item['id']; ?>\" class=\"btn btn-sm {$btn_class}\">\n";
                $php .= "                    <i class=\"bi bi-{$icon}\"></i> <?php echo \$lang['{$action_key}']; ?>\n";
                $php .= "                </a>\n";
            }
            $php .= "            </td>\n";
        }
        
        $php .= "        </tr>\n";
        $php .= "    <?php endforeach; ?>\n";
        $php .= "<?php else: ?>\n";
        $php .= "    <tr>\n";
        $php .= "        <td colspan=\"" . (count($columns) + (empty($actions) ? 0 : 1)) . "\" class=\"text-center\">\n";
        
        $no_data_key = "{$prefix}_no_data_message";
        $this->translation_keys[] = $no_data_key;
        $php .= "            <?php echo \$lang['{$no_data_key}']; ?>\n";
        $php .= "        </td>\n";
        $php .= "    </tr>\n";
        $php .= "<?php endif; ?>\n";
        
        $html .= "            <!-- PHP loop will go here -->\n";
        $html .= "        </tbody>\n";
        $html .= "    </table>\n";
        $html .= "</div>\n";
        
        // Generate DataTables JavaScript if ajax source provided
        $javascript = '';
        if ($ajax_source) {
            $javascript = "// DataTable initialization\n";
            $javascript .= "$(document).ready(function() {\n";
            $javascript .= "    $('#{$table_id}').DataTable({\n";
            $javascript .= "        ajax: '{$ajax_source}',\n";
            $javascript .= "        columns: [\n";
            foreach ($columns as $col) {
                $javascript .= "            { data: '{$col}' },\n";
            }
            if (!empty($actions)) {
                $javascript .= "            { data: null, render: function(data, type, row) {\n";
                $javascript .= "                return '<a href=\"view.php?id=' + row.id + '\" class=\"btn btn-sm btn-secondary\">View</a>';\n";
                $javascript .= "            }}\n";
            }
            $javascript .= "        ],\n";
            $javascript .= "        order: [[0, 'desc']],\n";
            $javascript .= "        pageLength: 25\n";
            $javascript .= "    });\n";
            $javascript .= "});\n";
        }
        
        return [
            'html' => $html,
            'php' => $php,
            'javascript' => $javascript,
            'css' => ''
        ];
    }
    
    private function generateForm($options, $prefix) {
        $fields = $options['fields'] ?? [];
        $action = $options['action'] ?? 'post.php';
        $method = $options['method'] ?? 'POST';
        $form_id = $options['form_id'] ?? 'mainForm';
        
        $html = "<!-- Form -->\n";
        $html .= "<form id=\"{$form_id}\" method=\"{$method}\" action=\"{$action}\" class=\"needs-validation\" novalidate>\n";
        $html .= "    <?php echo \$nonce->field(); ?>\n\n";
        
        foreach ($fields as $field_def) {
            $parts = explode(':', $field_def);
            $field_name = $parts[0];
            $field_type = $parts[1] ?? 'text';
            $required = isset($parts[2]) && $parts[2] === 'required';
            
            $label_key = "{$prefix}_{$field_name}_label";
            $placeholder_key = "{$prefix}_{$field_name}_placeholder";
            $this->translation_keys[] = $label_key;
            $this->translation_keys[] = $placeholder_key;
            
            $html .= "    <div class=\"mb-3\">\n";
            $html .= "        <label for=\"{$field_name}\" class=\"form-label\">\n";
            $html .= "            <?php echo \$lang['{$label_key}']; ?>\n";
            if ($required) {
                $html .= "            <span class=\"text-danger\">*</span>\n";
            }
            $html .= "        </label>\n";
            
            if ($field_type === 'textarea') {
                $html .= "        <textarea id=\"{$field_name}\" name=\"{$field_name}\" class=\"form-control\" ";
                $html .= "placeholder=\"<?php echo \$lang['{$placeholder_key}']; ?>\" ";
                if ($required) $html .= "required ";
                $html .= "rows=\"4\"><?php echo htmlspecialchars(\$data['{$field_name}'] ?? ''); ?></textarea>\n";
            } elseif ($field_type === 'select') {
                $options_var = "{$field_name}_options";
                $html .= "        <select id=\"{$field_name}\" name=\"{$field_name}\" class=\"form-select\" ";
                if ($required) $html .= "required";
                $html .= ">\n";
                $html .= "            <option value=\"\"><?php echo \$lang['{$placeholder_key}']; ?></option>\n";
                $html .= "            <?php foreach (\${$options_var} as \$key => \$value): ?>\n";
                $html .= "                <option value=\"<?php echo \$key; ?>\" <?php echo (isset(\$data['{$field_name}']) && \$data['{$field_name}'] == \$key) ? 'selected' : ''; ?>>\n";
                $html .= "                    <?php echo htmlspecialchars(\$value); ?>\n";
                $html .= "                </option>\n";
                $html .= "            <?php endforeach; ?>\n";
                $html .= "        </select>\n";
            } else {
                $html .= "        <input type=\"{$field_type}\" id=\"{$field_name}\" name=\"{$field_name}\" class=\"form-control\" ";
                $html .= "placeholder=\"<?php echo \$lang['{$placeholder_key}']; ?>\" ";
                $html .= "value=\"<?php echo htmlspecialchars(\$data['{$field_name}'] ?? ''); ?>\" ";
                if ($required) $html .= "required ";
                $html .= "/>\n";
            }
            
            if ($required) {
                $error_key = "{$prefix}_{$field_name}_required_error";
                $this->translation_keys[] = $error_key;
                $html .= "        <div class=\"invalid-feedback\">\n";
                $html .= "            <?php echo \$lang['{$error_key}']; ?>\n";
                $html .= "        </div>\n";
            }
            
            $html .= "    </div>\n\n";
        }
        
        $submit_key = "{$prefix}_submit_button";
        $cancel_key = "{$prefix}_cancel_button";
        $this->translation_keys[] = $submit_key;
        $this->translation_keys[] = $cancel_key;
        
        $html .= "    <div class=\"mb-3\">\n";
        $html .= "        <button type=\"submit\" class=\"btn btn-primary\">\n";
        $html .= "            <?php echo \$lang['{$submit_key}']; ?>\n";
        $html .= "        </button>\n";
        $html .= "        <a href=\"list.php\" class=\"btn btn-secondary\">\n";
        $html .= "            <?php echo \$lang['{$cancel_key}']; ?>\n";
        $html .= "        </a>\n";
        $html .= "    </div>\n";
        $html .= "</form>\n";
        
        $javascript = "// Form validation\n";
        $javascript .= "(function() {\n";
        $javascript .= "    'use strict';\n";
        $javascript .= "    var form = document.getElementById('{$form_id}');\n";
        $javascript .= "    form.addEventListener('submit', function(event) {\n";
        $javascript .= "        if (!form.checkValidity()) {\n";
        $javascript .= "            event.preventDefault();\n";
        $javascript .= "            event.stopPropagation();\n";
        $javascript .= "        }\n";
        $javascript .= "        form.classList.add('was-validated');\n";
        $javascript .= "    }, false);\n";
        $javascript .= "})();\n";
        
        return [
            'html' => $html,
            'php' => '',
            'javascript' => $javascript,
            'css' => ''
        ];
    }
    
    private function generateFilters($options, $prefix) {
        $filters = $options['filters'] ?? ['status', 'date_range'];
        $form_id = $options['form_id'] ?? 'filterForm';
        
        $html = "<!-- Filters -->\n";
        $html .= "<div class=\"card mb-3\">\n";
        $html .= "    <div class=\"card-header\">\n";
        
        $filters_title_key = "{$prefix}_filters_title";
        $this->translation_keys[] = $filters_title_key;
        $html .= "        <h5 class=\"mb-0\"><?php echo \$lang['{$filters_title_key}']; ?></h5>\n";
        $html .= "    </div>\n";
        $html .= "    <div class=\"card-body\">\n";
        $html .= "        <form id=\"{$form_id}\" method=\"GET\" action=\"\" class=\"row g-3\">\n";
        
        foreach ($filters as $filter) {
            $filter_label_key = "{$prefix}_{$filter}_filter_label";
            $this->translation_keys[] = $filter_label_key;
            
            if ($filter === 'date_range') {
                $html .= "            <div class=\"col-md-3\">\n";
                $html .= "                <label for=\"date_from\" class=\"form-label\"><?php echo \$lang['{$prefix}_date_from_label']; ?></label>\n";
                $html .= "                <input type=\"date\" id=\"date_from\" name=\"date_from\" class=\"form-control\" value=\"<?php echo htmlspecialchars(\$_GET['date_from'] ?? ''); ?>\">\n";
                $html .= "            </div>\n";
                $html .= "            <div class=\"col-md-3\">\n";
                $html .= "                <label for=\"date_to\" class=\"form-label\"><?php echo \$lang['{$prefix}_date_to_label']; ?></label>\n";
                $html .= "                <input type=\"date\" id=\"date_to\" name=\"date_to\" class=\"form-control\" value=\"<?php echo htmlspecialchars(\$_GET['date_to'] ?? ''); ?>\">\n";
                $html .= "            </div>\n";
                
                $this->translation_keys[] = "{$prefix}_date_from_label";
                $this->translation_keys[] = "{$prefix}_date_to_label";
            } else {
                $html .= "            <div class=\"col-md-3\">\n";
                $html .= "                <label for=\"{$filter}\" class=\"form-label\"><?php echo \$lang['{$filter_label_key}']; ?></label>\n";
                $html .= "                <select id=\"{$filter}\" name=\"{$filter}\" class=\"form-select\">\n";
                $html .= "                    <option value=\"\"><?php echo \$lang['{$prefix}_all_option']; ?></option>\n";
                $html .= "                    <?php foreach (\${$filter}_options as \$key => \$value): ?>\n";
                $html .= "                        <option value=\"<?php echo \$key; ?>\" <?php echo (isset(\$_GET['{$filter}']) && \$_GET['{$filter}'] == \$key) ? 'selected' : ''; ?>>\n";
                $html .= "                            <?php echo htmlspecialchars(\$value); ?>\n";
                $html .= "                        </option>\n";
                $html .= "                    <?php endforeach; ?>\n";
                $html .= "                </select>\n";
                $html .= "            </div>\n";
                
                $this->translation_keys[] = "{$prefix}_all_option";
            }
        }
        
        $apply_key = "{$prefix}_apply_filters_button";
        $reset_key = "{$prefix}_reset_filters_button";
        $this->translation_keys[] = $apply_key;
        $this->translation_keys[] = $reset_key;
        
        $html .= "            <div class=\"col-md-3 d-flex align-items-end\">\n";
        $html .= "                <button type=\"submit\" class=\"btn btn-primary me-2\">\n";
        $html .= "                    <?php echo \$lang['{$apply_key}']; ?>\n";
        $html .= "                </button>\n";
        $html .= "                <a href=\"?\" class=\"btn btn-secondary\">\n";
        $html .= "                    <?php echo \$lang['{$reset_key}']; ?>\n";
        $html .= "                </a>\n";
        $html .= "            </div>\n";
        $html .= "        </form>\n";
        $html .= "    </div>\n";
        $html .= "</div>\n";
        
        return [
            'html' => $html,
            'php' => '',
            'javascript' => '',
            'css' => ''
        ];
    }
    
    private function generateActionButtons($options, $prefix) {
        $actions = $options['actions'] ?? ['create', 'export'];
        $align = $options['align'] ?? 'right';
        
        $align_class = $align === 'right' ? 'justify-content-end' : ($align === 'center' ? 'justify-content-center' : '');
        
        $html = "<!-- Action Buttons -->\n";
        $html .= "<div class=\"d-flex {$align_class} mb-3\">\n";
        
        foreach ($actions as $action) {
            $action_key = "{$prefix}_{$action}_button";
            $this->translation_keys[] = $action_key;
            
            $btn_class = $action === 'create' ? 'btn-primary' : 'btn-secondary';
            $icon = $action === 'create' ? 'plus-circle' : ($action === 'export' ? 'download' : 'gear');
            $href = $action === 'create' ? 'new.php' : "{$action}.php";
            
            $html .= "    <a href=\"{$href}\" class=\"btn {$btn_class} me-2\">\n";
            $html .= "        <i class=\"bi bi-{$icon}\"></i> <?php echo \$lang['{$action_key}']; ?>\n";
            $html .= "    </a>\n";
        }
        
        $html .= "</div>\n";
        
        return [
            'html' => $html,
            'php' => '',
            'javascript' => '',
            'css' => ''
        ];
    }
    
    private function generateAlert($options, $prefix) {
        $type = $options['alert_type'] ?? 'info'; // success, danger, warning, info
        $dismissible = $options['dismissible'] ?? true;
        $message_key = $options['message_key'] ?? "{$prefix}_alert_message";
        
        $this->translation_keys[] = $message_key;
        
        $html = "<!-- Alert -->\n";
        $html .= "<?php if (isset(\$_SESSION['{$prefix}_message'])): ?>\n";
        $html .= "    <div class=\"alert alert-<?php echo \$_SESSION['{$prefix}_message_type'] ?? '{$type}'; ?>";
        if ($dismissible) {
            $html .= " alert-dismissible fade show";
        }
        $html .= "\" role=\"alert\">\n";
        $html .= "        <?php echo htmlspecialchars(\$_SESSION['{$prefix}_message']); ?>\n";
        if ($dismissible) {
            $html .= "        <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>\n";
        }
        $html .= "    </div>\n";
        $html .= "    <?php\n";
        $html .= "    unset(\$_SESSION['{$prefix}_message']);\n";
        $html .= "    unset(\$_SESSION['{$prefix}_message_type']);\n";
        $html .= "    ?>\n";
        $html .= "<?php endif; ?>\n";
        
        return [
            'html' => $html,
            'php' => '',
            'javascript' => '',
            'css' => ''
        ];
    }
    
    public function display($result, $json = false) {
        if ($json) {
            echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
            return;
        }
        
        if (isset($result['error'])) {
            echo "\n❌ ERROR: {$result['error']}\n";
            if (isset($result['valid_types'])) {
                echo "\nValid types: " . implode(', ', $result['valid_types']) . "\n";
            }
            return;
        }
        
        echo "\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "  UI COMPONENT GENERATED\n";
        echo "═══════════════════════════════════════════════════════════════\n\n";
        
        if (!empty($result['html'])) {
            echo "HTML:\n";
            echo "───────────────────────────────────────────────────────────────\n";
            echo $result['html'] . "\n";
        }
        
        if (!empty($result['php'])) {
            echo "\nPHP:\n";
            echo "───────────────────────────────────────────────────────────────\n";
            echo $result['php'] . "\n";
        }
        
        if (!empty($result['javascript'])) {
            echo "\nJAVASCRIPT:\n";
            echo "───────────────────────────────────────────────────────────────\n";
            echo $result['javascript'] . "\n";
        }
        
        if (!empty($result['css'])) {
            echo "\nCSS:\n";
            echo "───────────────────────────────────────────────────────────────\n";
            echo $result['css'] . "\n";
        }
        
        if (!empty($result['translation_keys_needed'])) {
            echo "\nTRANSLATION KEYS NEEDED:\n";
            echo "───────────────────────────────────────────────────────────────\n";
            foreach ($result['translation_keys_needed'] as $key) {
                echo "  • {$key}\n";
            }
            echo "\n";
        }
    }
}

// CLI Interface
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    $options = getopt('', ['type:', 'columns::', 'fields::', 'filters::', 'actions::', 'prefix:', 'title::', 'subtitle::', 'ajax::', 'json', 'help']);
    
    if (isset($options['help']) || empty($options['type']) || empty($options['prefix'])) {
        echo <<<HELP

UI Component Generator
═══════════════════════════════════════════════════════════════

Generates framework-compliant UI components with proper Bootstrap 5 
styling and translation key integration.

USAGE:
  php generate_ui_component.php --type=TYPE --prefix=PREFIX [OPTIONS] [--json]

REQUIRED:
  --type=TYPE          Component type:
                       - page_header
                       - data_table
                       - form
                       - filters
                       - action_buttons
                       - alert
  
  --prefix=PREFIX      Translation key prefix (e.g., email_queue, user)

OPTIONS:
  --columns=LIST       Comma-separated column names (for data_table)
                       Example: id,name,email,status
  
  --fields=LIST        Comma-separated field definitions (for form)
                       Format: name:type[:required]
                       Example: name:text:required,email:email:required,bio:textarea
  
  --filters=LIST       Comma-separated filter names (for filters)
                       Example: status,date_range,priority
  
  --actions=LIST       Comma-separated action names (for action_buttons)
                       Example: create,export,import
  
  --title=TEXT         Page title (for page_header)
  --subtitle=TEXT      Page subtitle (for page_header)
  --ajax=URL           AJAX data source URL (for data_table)
  
  --json               Output in JSON format
  --help               Show this help message

EXAMPLES:
  # Generate a data table
  php generate_ui_component.php --type=data_table --columns=id,name,email,status --prefix=user
  
  # Generate a form
  php generate_ui_component.php --type=form --fields=name:text:required,email:email:required,status:select --prefix=user
  
  # Generate filters
  php generate_ui_component.php --type=filters --filters=status,date_range --prefix=email_queue
  
  # Generate page header
  php generate_ui_component.php --type=page_header --title="Email Queue" --prefix=email_queue
  
  # Generate action buttons
  php generate_ui_component.php --type=action_buttons --actions=create,export --prefix=email_template
  
  # Generate alert
  php generate_ui_component.php --type=alert --prefix=email_queue
  
  # JSON output
  php generate_ui_component.php --type=data_table --columns=id,name,status --prefix=user --json


HELP;
        exit(0);
    }
    
    $component_options = [];
    
    if (isset($options['columns'])) {
        $component_options['columns'] = explode(',', $options['columns']);
    }
    
    if (isset($options['fields'])) {
        $component_options['fields'] = explode(',', $options['fields']);
    }
    
    if (isset($options['filters'])) {
        $component_options['filters'] = explode(',', $options['filters']);
    }
    
    if (isset($options['actions'])) {
        $component_options['actions'] = explode(',', $options['actions']);
    }
    
    if (isset($options['title'])) {
        $component_options['title'] = $options['title'];
    }
    
    if (isset($options['subtitle'])) {
        $component_options['subtitle'] = $options['subtitle'];
    }
    
    if (isset($options['ajax'])) {
        $component_options['ajax_source'] = $options['ajax'];
    }
    
    $generator = new UIComponentGenerator();
    $result = $generator->generate($options['type'], $component_options, $options['prefix']);
    $generator->display($result, isset($options['json']));
}