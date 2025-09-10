<?php

/**
 * Summernote WYSIWYG Editor Manager
 * 
 * Provides flexible Summernote integration with per-page and per-textarea customization
 * Supports future plugin integration for email templates and advanced features
 * 
 * Features:
 * - Per-page toolbar customization
 * - Individual textarea configuration
 * - Plugin system ready
 * - CDN and local asset support
 * - Bootstrap 5 integration
 * - Security-aware configuration
 */
class SummernoteManager
{
    private static $instance = null;
    private $configurations = [];
    private $globalConfig = [];
    private $assetsLoaded = false;
    private $useLocalAssets = false;
    private $configData = [];
    
    // Default toolbar configurations
    private $toolbarPresets = [
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
        ]
    ];
    
    private function __construct()
    {
        $this->loadConfiguration();
        $this->setGlobalDefaults();
    }
    
    /**
     * Load configuration from config file
     */
    private function loadConfiguration(): void
    {
        $configFile = dirname(__DIR__, 2) . '/config/summernote-config.php';
        if (file_exists($configFile)) {
            $config = require $configFile;
            
            // Load custom toolbar presets
            if (isset($config['toolbars'])) {
                $this->toolbarPresets = array_merge($this->toolbarPresets, $config['toolbars']);
            }
            
            // Store full config for later use
            $this->configData = $config;
        }
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Set global default configuration
     */
    private function setGlobalDefaults(): void
    {
        $defaults = [
            'height' => 200,
            'minHeight' => 100,
            'maxHeight' => 500,
            'focus' => false,
            'toolbar' => $this->toolbarPresets['basic'],
            'placeholder' => 'Enter text here...',
            'tabsize' => 2,
            'fontNames' => [
                'Arial', 'Arial Black', 'Comic Sans MS', 'Courier New',
                'Helvetica Neue', 'Helvetica', 'Impact', 'Lucida Grande',
                'Tahoma', 'Times New Roman', 'Verdana'
            ],
            'fontSizes' => ['8', '9', '10', '11', '12', '14', '16', '18', '20', '24', '36', '48'],
            'colors' => [
                ['#000000', '#424242', '#636363', '#9C9C94', '#CEC6CE', '#EFEFEF', '#F7F3F7', '#FFFFFF'],
                ['#FF0000', '#FF9C00', '#FFFF00', '#00FF00', '#00FFFF', '#0000FF', '#9C00FF', '#FF00FF'],
                ['#F7C6CE', '#FFE7CE', '#FFEFC6', '#D6EFD6', '#CEDEE7', '#CEE7F7', '#D6D6E7', '#E7D6DE'],
                ['#E79C9C', '#FFC69C', '#FFE79C', '#B5D6A5', '#A5C6CE', '#9CC6EF', '#B5A5D6', '#D6A5BD'],
                ['#E76363', '#F7AD6B', '#FFD663', '#94BD7B', '#73A5AD', '#6BADDE', '#8C7BC6', '#C67BA5'],
                ['#CE0000', '#E79439', '#EFC631', '#6BA54A', '#4A7B8C', '#3984C6', '#634AA5', '#A54A7B'],
                ['#9C0000', '#B56308', '#BD9400', '#397B21', '#104A5A', '#085294', '#311873', '#731842'],
                ['#630000', '#7B3900', '#846300', '#295218', '#083139', '#003163', '#21104A', '#4A1031']
            ],
            'callbacks' => []
        ];
        
        // Merge with config file settings
        if (isset($this->configData['global'])) {
            $defaults = array_merge($defaults, $this->configData['global']);
        }
        
        // Override fonts and colors from config if available
        if (isset($this->configData['fonts']['names'])) {
            $defaults['fontNames'] = $this->configData['fonts']['names'];
        }
        if (isset($this->configData['fonts']['sizes'])) {
            $defaults['fontSizes'] = $this->configData['fonts']['sizes'];
        }
        if (isset($this->configData['colors'])) {
            $defaults['colors'] = $this->configData['colors'];
        }
        
        // Set useLocalAssets if configured
        if (isset($this->configData['global']['useLocalAssets'])) {
            $this->useLocalAssets = $this->configData['global']['useLocalAssets'];
        }
        
        $this->globalConfig = $defaults;
    }
    
    /**
     * Configure Summernote for a specific textarea
     */
    public function configure(string $selector, array $config = []): self
    {
        // Handle toolbar presets
        if (isset($config['toolbar']) && is_string($config['toolbar'])) {
            if (isset($this->toolbarPresets[$config['toolbar']])) {
                $config['toolbar'] = $this->toolbarPresets[$config['toolbar']];
            }
        }
        
        // Merge with global defaults
        $finalConfig = array_merge($this->globalConfig, $config);
        
        $this->configurations[$selector] = $finalConfig;
        return $this;
    }
    
    /**
     * Configure multiple textareas with different settings
     */
    public function configureMultiple(array $configs): self
    {
        foreach ($configs as $selector => $config) {
            $this->configure($selector, $config);
        }
        return $this;
    }
    
    /**
     * Set page-wide configuration that applies to all textareas on the page
     */
    public function setPageDefaults(array $config): self
    {
        $this->globalConfig = array_merge($this->globalConfig, $config);
        return $this;
    }
    
    /**
     * Enable local assets instead of CDN
     */
    public function useLocalAssets(bool $useLocal = true): self
    {
        $this->useLocalAssets = $useLocal;
        return $this;
    }
    
    /**
     * Enable CDN with fallback to local assets (recommended)
     */
    public function useCdnWithFallback(): self
    {
        $this->useLocalAssets = false;
        return $this;
    }
    
    /**
     * Force local assets only (no CDN)
     */
    public function forceLocalAssets(): self
    {
        $this->useLocalAssets = true;
        return $this;
    }
    
    /**
     * Get CSS includes for Summernote with CDN fallback
     */
    public function getCssIncludes(): string
    {
        if ($this->useLocalAssets) {
            return '<link href="/assets/css/summernote/summernote-bs5.min.css" rel="stylesheet">';
        } else {
            // CDN with fallback to local assets
            return '<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet" onerror="this.onerror=null;this.href=\'/assets/css/summernote/summernote-bs5.min.css\';">';
        }
    }
    
    /**
     * Get JavaScript includes for Summernote with CDN fallback
     */
    public function getJsIncludes(): string
    {
        $includes = '';
        
        // jQuery is required - check if it's already loaded
        $includes .= '<script>if (typeof jQuery === "undefined") { document.write(\'<script src="https://code.jquery.com/jquery-3.6.0.min.js"><\/script>\'); }</script>' . "\n";
        
        if ($this->useLocalAssets) {
            $includes .= '<script src="/assets/js/summernote/summernote-bs5.min.js"></script>';
        } else {
            // CDN with fallback to local assets
            $includes .= '<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js" onerror="this.onerror=null;this.src=\'/assets/js/summernote/summernote-bs5.min.js\';"></script>';
        }
        
        // Add CDN fallback detection script
        if (!$this->useLocalAssets) {
            $includes .= $this->getCdnFallbackScript();
        }
        
        return $includes;
    }
    
    /**
     * Generate CDN fallback detection script
     */
    private function getCdnFallbackScript(): string
    {
        return '
<script>
// Advanced CDN fallback detection for Summernote
(function() {
    // Check if Summernote loaded from CDN
    setTimeout(function() {
        if (typeof $.fn.summernote === "undefined") {
            console.warn("Summernote CDN failed, loading local fallback...");
            
            // Load local Summernote
            var script = document.createElement("script");
            script.src = "/assets/js/summernote/summernote-bs5.min.js";
            script.onload = function() {
                console.log("Summernote local fallback loaded successfully");
            };
            script.onerror = function() {
                console.error("Both CDN and local Summernote failed to load");
            };
            document.head.appendChild(script);
        }
    }, 1000);
})();
</script>';
    }
    
    /**
     * Generate initialization JavaScript for all configured textareas
     */
    public function getInitializationScript(): string
    {
        if (empty($this->configurations)) {
            return '';
        }
        
        $script = '<script>$(document).ready(function() {' . "\n";
        
        foreach ($this->configurations as $selector => $config) {
            $script .= $this->generateSingleInit($selector, $config);
        }
        
        $script .= '});</script>';
        
        return $script;
    }
    
    /**
     * Generate initialization script for a single textarea
     */
    private function generateSingleInit(string $selector, array $config): string
    {
        $jsConfig = $this->configToJavaScript($config);
        
        return "    $('{$selector}').summernote({$jsConfig});\n";
    }
    
    /**
     * Convert PHP config array to JavaScript object
     */
    private function configToJavaScript(array $config): string
    {
        $jsConfig = [];
        
        foreach ($config as $key => $value) {
            if ($key === 'callbacks' && is_array($value)) {
                // Handle callbacks specially - they should not be quoted
                foreach ($value as $callbackName => $callbackFunction) {
                    $jsConfig[] = "'{$callbackName}': {$callbackFunction}";
                }
            } else {
                $jsConfig[] = "'{$key}': " . $this->valueToJavaScript($value);
            }
        }
        
        return '{' . implode(', ', $jsConfig) . '}';
    }
    
    /**
     * Convert PHP value to JavaScript representation
     */
    private function valueToJavaScript($value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        } elseif (is_numeric($value)) {
            return (string)$value;
        } elseif (is_string($value)) {
            return "'" . addslashes($value) . "'";
        } elseif (is_array($value)) {
            if ($this->isAssociativeArray($value)) {
                // Object notation
                $items = [];
                foreach ($value as $k => $v) {
                    $items[] = "'{$k}': " . $this->valueToJavaScript($v);
                }
                return '{' . implode(', ', $items) . '}';
            } else {
                // Array notation
                $items = array_map([$this, 'valueToJavaScript'], $value);
                return '[' . implode(', ', $items) . ']';
            }
        } else {
            return 'null';
        }
    }
    
    /**
     * Check if array is associative
     */
    private function isAssociativeArray(array $array): bool
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }
    
    /**
     * Add a custom toolbar preset
     */
    public function addToolbarPreset(string $name, array $toolbar): self
    {
        $this->toolbarPresets[$name] = $toolbar;
        return $this;
    }
    
    /**
     * Get available toolbar presets
     */
    public function getToolbarPresets(): array
    {
        return array_keys($this->toolbarPresets);
    }
    
    /**
     * Quick setup for common scenarios
     */
    public function quickSetup(string $scenario, array $selectors = ['textarea']): self
    {
        $configs = [];
        
        switch ($scenario) {
            case 'notes':
                foreach ($selectors as $selector) {
                    $configs[$selector] = [
                        'toolbar' => 'basic',
                        'height' => 150,
                        'placeholder' => 'Enter your notes here...'
                    ];
                }
                break;
                
            case 'email':
                foreach ($selectors as $selector) {
                    $configs[$selector] = [
                        'toolbar' => 'email',
                        'height' => 300,
                        'placeholder' => 'Compose your email...'
                    ];
                }
                break;
                
            case 'description':
                foreach ($selectors as $selector) {
                    $configs[$selector] = [
                        'toolbar' => 'standard',
                        'height' => 200,
                        'placeholder' => 'Enter description...'
                    ];
                }
                break;
                
            case 'minimal':
                foreach ($selectors as $selector) {
                    $configs[$selector] = [
                        'toolbar' => 'minimal',
                        'height' => 100,
                        'placeholder' => 'Enter text...'
                    ];
                }
                break;
        }
        
        return $this->configureMultiple($configs);
    }
    
    /**
     * Render complete Summernote integration for a page
     */
    public function render(): string
    {
        $output = '';
        
        // CSS includes (should be in head)
        $output .= "<!-- Summernote CSS -->\n";
        $output .= $this->getCssIncludes() . "\n";
        
        // JavaScript includes (should be before closing body)
        $output .= "<!-- Summernote JavaScript -->\n";
        $output .= $this->getJsIncludes() . "\n";
        
        // Initialization script
        $output .= "<!-- Summernote Initialization -->\n";
        $output .= $this->getInitializationScript() . "\n";
        
        return $output;
    }
    
    /**
     * Reset all configurations (useful for testing)
     */
    public function reset(): self
    {
        $this->configurations = [];
        $this->setGlobalDefaults();
        return $this;
    }
}