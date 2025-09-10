<?php

require_once __DIR__ . '/SummernoteManager.php';

/**
 * Editor Helper Class
 * 
 * Provides easy integration between Summernote and the existing CRM template system
 * Handles automatic detection of textareas and applies appropriate configurations
 */
class EditorHelper
{
    private static $instance = null;
    private $summernote;
    private $pageConfig = [];
    private $autoDetect = true;
    
    private function __construct()
    {
        $this->summernote = SummernoteManager::getInstance();
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Configure editor for current page based on context
     */
    public function configureForPage(string $dir, string $page, array $customConfig = []): self
    {
        $this->pageConfig = [
            'dir' => $dir,
            'page' => $page,
            'custom' => $customConfig
        ];
        
        // Apply page-specific configurations
        $this->applyPageSpecificConfig($dir, $page, $customConfig);
        
        return $this;
    }
    
    /**
     * Apply configuration based on page context
     */
    private function applyPageSpecificConfig(string $dir, string $page, array $customConfig): void
    {
        // Default configurations based on page type
        switch ($dir) {
            case 'leads':
                $this->configureLeadsPages($page, $customConfig);
                break;
                
            case 'contacts':
                $this->configureContactsPages($page, $customConfig);
                break;
                
            case 'users':
                $this->configureUsersPages($page, $customConfig);
                break;
                
            case 'admin':
                $this->configureAdminPages($page, $customConfig);
                break;
                
            case 'systems':
                $this->configureSystemsPages($page, $customConfig);
                break;
                
            default:
                $this->configureDefaultPages($page, $customConfig);
                break;
        }
    }
    
    /**
     * Configure for leads pages
     */
    private function configureLeadsPages(string $page, array $customConfig): void
    {
        switch ($page) {
            case 'new':
                $this->summernote->configureMultiple([
                    'textarea[name="structure_additional"]' => array_merge([
                        'toolbar' => 'minimal',
                        'height' => 80,
                        'placeholder' => 'Additional structure details...'
                    ], $customConfig['structure_additional'] ?? []),
                    
                    'textarea[name="notes"]' => array_merge([
                        'toolbar' => 'basic',
                        'height' => 120,
                        'placeholder' => 'Initial notes about this lead...'
                    ], $customConfig['notes'] ?? [])
                ]);
                break;
                
            case 'edit':
                $this->summernote->configureMultiple([
                    'textarea[name="note_text"]' => array_merge([
                        'toolbar' => 'minimal',
                        'height' => 80,
                        'placeholder' => 'Questions Asked'
                    ], $customConfig['note_text'] ?? []),
                    
                    'textarea[name="next_action_notes"]' => array_merge([
                        'toolbar' => 'minimal',
                        'height' => 80,
                        'placeholder' => 'What you promised...'
                    ], $customConfig['next_action_notes'] ?? []),
                    
                    'textarea[name="project_description"]' => array_merge([
                        'toolbar' => 'standard',
                        'height' => 150,
                        'placeholder' => 'Describe the project details...'
                    ], $customConfig['project_description'] ?? []),
                    
                    'textarea[name="notes"]' => array_merge([
                        'toolbar' => 'basic',
                        'height' => 120,
                        'placeholder' => 'Add notes about this lead...'
                    ], $customConfig['notes'] ?? [])
                ]);
                break;
                
            case 'compare_notes':
                $this->summernote->configureMultiple([
                    'textarea[name="notes"]' => array_merge([
                        'toolbar' => 'basic',
                        'height' => 200,
                        'placeholder' => 'Lead notes...'
                    ], $customConfig['notes'] ?? []),
                    
                    'textarea[name="lead_lost_notes"]' => array_merge([
                        'toolbar' => 'basic',
                        'height' => 200,
                        'placeholder' => 'Lead lost notes...'
                    ], $customConfig['lead_lost_notes'] ?? [])
                ]);
                break;
        }
    }
    
    /**
     * Configure for contacts pages
     */
    private function configureContactsPages(string $page, array $customConfig): void
    {
        switch ($page) {
            case 'new':
            case 'edit':
                $this->summernote->configure('textarea[name="notes"]', array_merge([
                    'toolbar' => 'basic',
                    'height' => 120,
                    'placeholder' => 'Contact notes...'
                ], $customConfig['notes'] ?? []));
                break;
        }
    }
    
    /**
     * Configure for users pages
     */
    private function configureUsersPages(string $page, array $customConfig): void
    {
        switch ($page) {
            case 'new':
            case 'edit':
                $this->summernote->configure('textarea[name="bio"]', array_merge([
                    'toolbar' => 'basic',
                    'height' => 100,
                    'placeholder' => 'User biography or notes...'
                ], $customConfig['bio'] ?? []));
                break;
        }
    }
    
    /**
     * Configure for admin pages
     */
    private function configureAdminPages(string $page, array $customConfig): void
    {
        // Admin pages might have email templates or system descriptions
        if (strpos($page, 'email') !== false) {
            $this->summernote->quickSetup('email', ['textarea']);
        } else {
            $this->summernote->quickSetup('description', ['textarea']);
        }
    }
    
    /**
     * Configure for systems pages
     */
    private function configureSystemsPages(string $page, array $customConfig): void
    {
        switch ($page) {
            case 'edit':
                // Replace the existing CKEditor with Summernote
                $this->summernote->configure('#floatingTextarea', array_merge([
                    'toolbar' => 'advanced',
                    'height' => 300,
                    'placeholder' => 'System description...'
                ], $customConfig['description'] ?? []));
                break;
        }
    }
    
    /**
     * Configure for default/unknown pages
     */
    private function configureDefaultPages(string $page, array $customConfig): void
    {
        // Apply basic configuration to all textareas
        $this->summernote->quickSetup('basic', ['textarea']);
    }
    
    /**
     * Add custom configuration for specific textarea
     */
    public function configureTextarea(string $selector, array $config): self
    {
        $this->summernote->configure($selector, $config);
        return $this;
    }
    
    /**
     * Add multiple textarea configurations
     */
    public function configureTextareas(array $configs): self
    {
        $this->summernote->configureMultiple($configs);
        return $this;
    }
    
    /**
     * Set up email template editor
     */
    public function setupEmailTemplate(string $selector = 'textarea'): self
    {
        $this->summernote->configure($selector, [
            'toolbar' => 'email',
            'height' => 400,
            'placeholder' => 'Design your email template...',
            'callbacks' => [
                'onImageUpload' => 'function(files) { 
                    // Handle image upload for email templates
                    console.log("Image upload:", files);
                }'
            ]
        ]);
        return $this;
    }
    
    /**
     * Get the CSS includes for the header
     */
    public function getCssIncludes(): string
    {
        return $this->summernote->getCssIncludes();
    }
    
    /**
     * Get the JavaScript includes and initialization for the footer
     */
    public function getJsIncludes(): string
    {
        return $this->summernote->getJsIncludes() . "\n" . 
               $this->summernote->getInitializationScript();
    }
    
    /**
     * Check if editor should be loaded for current page
     */
    public function shouldLoadEditor(string $dir, string $page): bool
    {
        // Pages that typically have textareas that benefit from rich text editing
        $editorPages = [
            'leads' => ['new', 'edit', 'compare_notes'],
            'contacts' => ['new', 'edit'],
            'users' => ['new', 'edit'],
            'systems' => ['edit'],
            'admin' => ['*'], // All admin pages might need editors
        ];
        
        if (!isset($editorPages[$dir])) {
            return false;
        }
        
        return in_array('*', $editorPages[$dir]) || in_array($page, $editorPages[$dir]);
    }
    
    /**
     * Auto-detect and configure textareas based on their attributes
     */
    public function autoDetectAndConfigure(): self
    {
        if (!$this->autoDetect) {
            return $this;
        }
        
        // This would be called via JavaScript to detect textareas and apply appropriate configs
        // For now, we'll use the page-based configuration
        return $this;
    }
    
    /**
     * Enable or disable auto-detection
     */
    public function setAutoDetect(bool $enabled): self
    {
        $this->autoDetect = $enabled;
        return $this;
    }
    
    /**
     * Get current page configuration
     */
    public function getPageConfig(): array
    {
        return $this->pageConfig;
    }
    
    /**
     * Use local assets instead of CDN
     */
    public function useLocalAssets(bool $useLocal = true): self
    {
        $this->summernote->useLocalAssets($useLocal);
        return $this;
    }
}