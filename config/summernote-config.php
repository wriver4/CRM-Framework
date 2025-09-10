<?php
/**
 * Summernote Configuration
 * 
 * Central configuration file for Summernote WYSIWYG editor integration
 * Customize toolbar presets, page-specific settings, and plugin configurations
 */

return [
    // Global settings applied to all editors
    'global' => [
        'height' => 200,
        'minHeight' => 100,
        'maxHeight' => 500,
        'focus' => false,
        'tabsize' => 2,
        'useLocalAssets' => false, // Set to true to use local assets instead of CDN
    ],
    
    // Custom toolbar presets
    'toolbars' => [
        'minimal' => [
            ['style', ['bold', 'italic', 'underline']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['misc', ['undo', 'redo']]
        ],
        
        'basic' => [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link']],
            ['misc', ['undo', 'redo']]
        ],
        
        'standard' => [
            ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize', 'color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['insert', ['link', 'picture', 'table', 'hr']],
            ['misc', ['fullscreen', 'undo', 'redo']]
        ],
        
        'advanced' => [
            ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize', 'fontname', 'color']],
            ['para', ['ul', 'ol', 'paragraph', 'height']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video', 'hr']],
            ['view', ['fullscreen', 'codeview']],
            ['misc', ['undo', 'redo']]
        ],
        
        'email' => [
            ['style', ['style', 'bold', 'italic', 'underline']],
            ['font', ['fontsize', 'fontname', 'color']],
            ['para', ['ul', 'ol', 'paragraph', 'height']],
            ['table', ['table']],
            ['insert', ['link', 'picture']],
            ['view', ['fullscreen', 'codeview']],
            ['misc', ['undo', 'redo']]
        ],
        
        'notes' => [
            ['style', ['bold', 'italic', 'underline']],
            ['font', ['color']],
            ['para', ['ul', 'ol']],
            ['insert', ['link']],
            ['misc', ['undo', 'redo']]
        ]
    ],
    
    // Page-specific configurations
    'pages' => [
        'leads' => [
            'new' => [
                'structure_additional' => [
                    'toolbar' => 'minimal',
                    'height' => 80,
                    'placeholder' => 'Additional structure details...'
                ],
                'notes' => [
                    'toolbar' => 'notes',
                    'height' => 120,
                    'placeholder' => 'Initial notes about this lead...'
                ]
            ],
            
            'edit' => [
                'note_text' => [
                    'toolbar' => 'minimal',
                    'height' => 80,
                    'placeholder' => 'Questions Asked'
                ],
                'next_action_notes' => [
                    'toolbar' => 'minimal',
                    'height' => 80,
                    'placeholder' => 'What you promised...'
                ],
                'project_description' => [
                    'toolbar' => 'standard',
                    'height' => 150,
                    'placeholder' => 'Describe the project details...'
                ],
                'notes' => [
                    'toolbar' => 'notes',
                    'height' => 120,
                    'placeholder' => 'Add notes about this lead...'
                ]
            ],
            
            'compare_notes' => [
                'notes' => [
                    'toolbar' => 'basic',
                    'height' => 200,
                    'placeholder' => 'Lead notes...'
                ],
                'lead_lost_notes' => [
                    'toolbar' => 'basic',
                    'height' => 200,
                    'placeholder' => 'Lead lost notes...'
                ]
            ]
        ],
        
        'contacts' => [
            'new' => [
                'notes' => [
                    'toolbar' => 'notes',
                    'height' => 120,
                    'placeholder' => 'Contact notes...'
                ]
            ],
            
            'edit' => [
                'notes' => [
                    'toolbar' => 'notes',
                    'height' => 120,
                    'placeholder' => 'Contact notes...'
                ]
            ]
        ],
        
        'users' => [
            'new' => [
                'bio' => [
                    'toolbar' => 'basic',
                    'height' => 100,
                    'placeholder' => 'User biography or notes...'
                ]
            ],
            
            'edit' => [
                'bio' => [
                    'toolbar' => 'basic',
                    'height' => 100,
                    'placeholder' => 'User biography or notes...'
                ]
            ]
        ],
        
        'systems' => [
            'edit' => [
                'floatingTextarea' => [
                    'toolbar' => 'advanced',
                    'height' => 300,
                    'placeholder' => 'System description...'
                ]
            ]
        ],
        
        'admin' => [
            'email' => [
                'template_content' => [
                    'toolbar' => 'email',
                    'height' => 400,
                    'placeholder' => 'Design your email template...'
                ]
            ]
        ]
    ],
    
    // Available plugins from awesome-summernote
    'plugins' => [
        'emoji' => [
            'name' => 'summernote-ext-emoji',
            'url' => 'https://cdn.jsdelivr.net/npm/summernote-ext-emoji@latest/dist/summernote-ext-emoji.min.js',
            'css' => 'https://cdn.jsdelivr.net/npm/summernote-ext-emoji@latest/dist/summernote-ext-emoji.min.css',
            'description' => 'Add emoji support to the editor'
        ],
        
        'mention' => [
            'name' => 'summernote-ext-mention',
            'url' => 'https://cdn.jsdelivr.net/npm/summernote-ext-mention@latest/dist/summernote-ext-mention.min.js',
            'description' => 'Add @mention functionality'
        ],
        
        'template' => [
            'name' => 'summernote-ext-template',
            'url' => 'https://cdn.jsdelivr.net/npm/summernote-ext-template@latest/dist/summernote-ext-template.min.js',
            'description' => 'Template management for email templates'
        ],
        
        'print' => [
            'name' => 'summernote-ext-print',
            'url' => 'https://cdn.jsdelivr.net/npm/summernote-ext-print@latest/dist/summernote-ext-print.min.js',
            'description' => 'Add print functionality'
        ],
        
        'specialchars' => [
            'name' => 'summernote-ext-specialchars',
            'url' => 'https://cdn.jsdelivr.net/npm/summernote-ext-specialchars@latest/dist/summernote-ext-specialchars.min.js',
            'description' => 'Insert special characters'
        ],
        
        'highlight' => [
            'name' => 'summernote-ext-highlight',
            'url' => 'https://cdn.jsdelivr.net/npm/summernote-ext-highlight@latest/dist/summernote-ext-highlight.min.js',
            'description' => 'Code syntax highlighting'
        ],
        
        'rtl' => [
            'name' => 'summernote-ext-rtl',
            'url' => 'https://cdn.jsdelivr.net/npm/summernote-ext-rtl@latest/dist/summernote-ext-rtl.min.js',
            'description' => 'Right-to-left text support'
        ]
    ],
    
    // Email template variables
    'email_variables' => [
        'customer' => [
            ['name' => 'Customer Name', 'value' => '{{customer_name}}'],
            ['name' => 'Customer Email', 'value' => '{{customer_email}}'],
            ['name' => 'Customer Phone', 'value' => '{{customer_phone}}'],
            ['name' => 'Customer Address', 'value' => '{{customer_address}}']
        ],
        
        'lead' => [
            ['name' => 'Lead Number', 'value' => '{{lead_number}}'],
            ['name' => 'Lead Status', 'value' => '{{lead_status}}'],
            ['name' => 'Lead Source', 'value' => '{{lead_source}}'],
            ['name' => 'Project Name', 'value' => '{{project_name}}'],
            ['name' => 'Project Description', 'value' => '{{project_description}}']
        ],
        
        'company' => [
            ['name' => 'Company Name', 'value' => '{{company_name}}'],
            ['name' => 'Company Address', 'value' => '{{company_address}}'],
            ['name' => 'Company Phone', 'value' => '{{company_phone}}'],
            ['name' => 'Company Email', 'value' => '{{company_email}}']
        ],
        
        'user' => [
            ['name' => 'User Name', 'value' => '{{user_name}}'],
            ['name' => 'User Email', 'value' => '{{user_email}}'],
            ['name' => 'User Title', 'value' => '{{user_title}}'],
            ['name' => 'User Phone', 'value' => '{{user_phone}}']
        ],
        
        'system' => [
            ['name' => 'Current Date', 'value' => '{{current_date}}'],
            ['name' => 'Current Time', 'value' => '{{current_time}}'],
            ['name' => 'System URL', 'value' => '{{system_url}}'],
            ['name' => 'Login URL', 'value' => '{{login_url}}']
        ]
    ],
    
    // Font configurations
    'fonts' => [
        'names' => [
            'Arial', 'Arial Black', 'Comic Sans MS', 'Courier New',
            'Helvetica Neue', 'Helvetica', 'Impact', 'Lucida Grande',
            'Tahoma', 'Times New Roman', 'Verdana', 'Georgia',
            'Palatino', 'Garamond', 'Bookman', 'Trebuchet MS'
        ],
        
        'sizes' => ['8', '9', '10', '11', '12', '14', '16', '18', '20', '24', '28', '32', '36', '48', '64', '72']
    ],
    
    // Color palette
    'colors' => [
        // Basic colors
        ['#000000', '#424242', '#636363', '#9C9C94', '#CEC6CE', '#EFEFEF', '#F7F3F7', '#FFFFFF'],
        
        // Primary colors
        ['#FF0000', '#FF9C00', '#FFFF00', '#00FF00', '#00FFFF', '#0000FF', '#9C00FF', '#FF00FF'],
        
        // Light colors
        ['#F7C6CE', '#FFE7CE', '#FFEFC6', '#D6EFD6', '#CEDEE7', '#CEE7F7', '#D6D6E7', '#E7D6DE'],
        
        // Medium colors
        ['#E79C9C', '#FFC69C', '#FFE79C', '#B5D6A5', '#A5C6CE', '#9CC6EF', '#B5A5D6', '#D6A5BD'],
        
        // Bright colors
        ['#E76363', '#F7AD6B', '#FFD663', '#94BD7B', '#73A5AD', '#6BADDE', '#8C7BC6', '#C67BA5'],
        
        // Dark colors
        ['#CE0000', '#E79439', '#EFC631', '#6BA54A', '#4A7B8C', '#3984C6', '#634AA5', '#A54A7B'],
        
        // Darker colors
        ['#9C0000', '#B56308', '#BD9400', '#397B21', '#104A5A', '#085294', '#311873', '#731842'],
        
        // Darkest colors
        ['#630000', '#7B3900', '#846300', '#295218', '#083139', '#003163', '#21104A', '#4A1031']
    ],
    
    // Security settings
    'security' => [
        'allowedTags' => [
            'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'strike',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'ul', 'ol', 'li',
            'a', 'img',
            'table', 'thead', 'tbody', 'tr', 'th', 'td',
            'blockquote', 'pre', 'code',
            'div', 'span'
        ],
        
        'allowedAttributes' => [
            'href', 'src', 'alt', 'title', 'class', 'style',
            'width', 'height', 'border', 'cellpadding', 'cellspacing'
        ],
        
        'stripTags' => ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button']
    ]
];