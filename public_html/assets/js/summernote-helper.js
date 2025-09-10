/**
 * Summernote Helper JavaScript
 * 
 * Provides additional functionality for Summernote integration
 * Supports plugin loading and custom callbacks
 */

class SummernoteHelper {
  constructor() {
    this.plugins = new Map();
    this.callbacks = new Map();
    this.initialized = false;
  }

  /**
   * Initialize the helper
   */
  init () {
    if (this.initialized) return;

    $(document).ready(() => {
      this.setupGlobalCallbacks();
      this.initialized = true;
    });
  }

  /**
   * Setup global callbacks for all Summernote instances
   */
  setupGlobalCallbacks () {
    // Image upload handler
    this.addGlobalCallback('onImageUpload', (files, editor, welEditable) => {
      this.handleImageUpload(files, editor, welEditable);
    });

    // File upload handler
    this.addGlobalCallback('onFileUpload', (files, editor, welEditable) => {
      this.handleFileUpload(files, editor, welEditable);
    });

    // Change handler for auto-save
    this.addGlobalCallback('onChange', (contents, $editable) => {
      this.handleContentChange(contents, $editable);
    });
  }

  /**
   * Add a global callback that applies to all editors
   */
  addGlobalCallback (eventName, callback) {
    this.callbacks.set(eventName, callback);
  }

  /**
   * Handle image upload
   */
  handleImageUpload (files, editor, welEditable) {
    console.log('Image upload requested:', files);

    // For now, just insert a placeholder
    // In the future, this could upload to a server
    for (let file of files) {
      if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => {
          $(editor).summernote('insertImage', e.target.result, file.name);
        };
        reader.readAsDataURL(file);
      }
    }
  }

  /**
   * Handle file upload
   */
  handleFileUpload (files, editor, welEditable) {
    console.log('File upload requested:', files);

    // Placeholder for file upload functionality
    // Could be extended to handle document uploads for email templates
  }

  /**
   * Handle content changes (for auto-save)
   */
  handleContentChange (contents, $editable) {
    const editorId = $editable.attr('id') || 'unknown';

    // Debounce auto-save
    clearTimeout(this.autoSaveTimeout);
    this.autoSaveTimeout = setTimeout(() => {
      this.autoSave(editorId, contents);
    }, 2000);
  }

  /**
   * Auto-save functionality
   */
  autoSave (editorId, contents) {
    // Placeholder for auto-save functionality
    console.log(`Auto-saving ${editorId}:`, contents.substring(0, 50) + '...');

    // Could send AJAX request to save draft
    // this.saveDraft(editorId, contents);
  }

  /**
   * Load a Summernote plugin
   */
  loadPlugin (pluginName, pluginUrl) {
    if (this.plugins.has(pluginName)) {
      return Promise.resolve();
    }

    return new Promise((resolve, reject) => {
      const script = document.createElement('script');
      script.src = pluginUrl;
      script.onload = () => {
        this.plugins.set(pluginName, true);
        console.log(`Summernote plugin loaded: ${pluginName}`);
        resolve();
      };
      script.onerror = () => {
        console.error(`Failed to load Summernote plugin: ${pluginName}`);
        reject(new Error(`Failed to load plugin: ${pluginName}`));
      };
      document.head.appendChild(script);
    });
  }

  /**
   * Load multiple plugins
   */
  async loadPlugins (plugins) {
    const promises = plugins.map(plugin => {
      if (typeof plugin === 'string') {
        // Assume it's a URL
        const name = plugin.split('/').pop().replace('.js', '');
        return this.loadPlugin(name, plugin);
      } else if (plugin.name && plugin.url) {
        return this.loadPlugin(plugin.name, plugin.url);
      }
    });

    try {
      await Promise.all(promises);
      console.log('All Summernote plugins loaded successfully');
    } catch (error) {
      console.error('Error loading Summernote plugins:', error);
    }
  }

  /**
   * Configure email template editor with additional features
   */
  setupEmailTemplate (selector, options = {}) {
    const defaultOptions = {
      height: 400,
      toolbar: [
        ['style', ['style', 'bold', 'italic', 'underline']],
        ['font', ['fontsize', 'fontname', 'color']],
        ['para', ['ul', 'ol', 'paragraph', 'height']],
        ['table', ['table']],
        ['insert', ['link', 'picture']],
        ['view', ['fullscreen', 'codeview']],
        ['misc', ['undo', 'redo']]
      ],
      placeholder: 'Design your email template...',
      callbacks: {
        onInit: function () {
          console.log('Email template editor initialized');
        },
        onImageUpload: (files, editor, welEditable) => {
          this.handleImageUpload(files, editor, welEditable);
        }
      }
    };

    const finalOptions = Object.assign({}, defaultOptions, options);
    $(selector).summernote(finalOptions);
  }

  /**
   * Setup template variables for email templates
   */
  setupTemplateVariables (selector, variables = []) {
    const defaultVariables = [
      { name: 'Customer Name', value: '{{customer_name}}' },
      { name: 'Company Name', value: '{{company_name}}' },
      { name: 'Lead Number', value: '{{lead_number}}' },
      { name: 'Project Name', value: '{{project_name}}' },
      { name: 'Current Date', value: '{{current_date}}' },
      { name: 'User Name', value: '{{user_name}}' }
    ];

    const allVariables = [...defaultVariables, ...variables];

    // Add template variable buttons to toolbar
    const $editor = $(selector);
    if ($editor.length) {
      // Create variable dropdown
      const $variableDropdown = this.createVariableDropdown(allVariables);

      // Add to editor toolbar (this would need custom toolbar button)
      console.log('Template variables available:', allVariables);
    }
  }

  /**
   * Create variable dropdown for email templates
   */
  createVariableDropdown (variables) {
    const $dropdown = $('<div class="dropdown summernote-variables">');
    const $button = $('<button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">Variables</button>');
    const $menu = $('<div class="dropdown-menu">');

    variables.forEach(variable => {
      const $item = $(`<a class="dropdown-item" href="#" data-variable="${variable.value}">${variable.name}</a>`);
      $item.on('click', (e) => {
        e.preventDefault();
        // Insert variable into active editor
        const activeEditor = $('.summernote').filter(':focus').first();
        if (activeEditor.length) {
          activeEditor.summernote('insertText', variable.value);
        }
      });
      $menu.append($item);
    });

    $dropdown.append($button, $menu);
    return $dropdown;
  }

  /**
   * Export content as HTML for email templates
   */
  exportAsEmailTemplate (selector) {
    const content = $(selector).summernote('code');

    // Clean up HTML for email compatibility
    const cleanContent = this.cleanHtmlForEmail(content);

    return {
      html: cleanContent,
      text: $(content).text() // Plain text version
    };
  }

  /**
   * Clean HTML for email compatibility
   */
  cleanHtmlForEmail (html) {
    // Remove Summernote-specific classes and attributes
    let cleaned = html.replace(/class="[^"]*"/g, '');
    cleaned = cleaned.replace(/contenteditable="[^"]*"/g, '');
    cleaned = cleaned.replace(/spellcheck="[^"]*"/g, '');

    // Convert to inline styles for better email client support
    // This is a basic implementation - could be enhanced

    return cleaned;
  }

  /**
   * Validate content for specific requirements
   */
  validateContent (selector, rules = {}) {
    const content = $(selector).summernote('code');
    const text = $(content).text();

    const validation = {
      valid: true,
      errors: []
    };

    // Check minimum length
    if (rules.minLength && text.length < rules.minLength) {
      validation.valid = false;
      validation.errors.push(`Content must be at least ${rules.minLength} characters long`);
    }

    // Check maximum length
    if (rules.maxLength && text.length > rules.maxLength) {
      validation.valid = false;
      validation.errors.push(`Content must be no more than ${rules.maxLength} characters long`);
    }

    // Check for required elements
    if (rules.requiredElements) {
      rules.requiredElements.forEach(element => {
        if ($(content).find(element).length === 0) {
          validation.valid = false;
          validation.errors.push(`Content must contain at least one ${element} element`);
        }
      });
    }

    return validation;
  }

  /**
   * Get available awesome-summernote plugins
   */
  getAvailablePlugins () {
    return {
      // Popular plugins from awesome-summernote
      'summernote-ext-emoji': 'https://cdn.jsdelivr.net/npm/summernote-ext-emoji@latest/dist/summernote-ext-emoji.min.js',
      'summernote-ext-mention': 'https://cdn.jsdelivr.net/npm/summernote-ext-mention@latest/dist/summernote-ext-mention.min.js',
      'summernote-ext-template': 'https://cdn.jsdelivr.net/npm/summernote-ext-template@latest/dist/summernote-ext-template.min.js',
      'summernote-ext-print': 'https://cdn.jsdelivr.net/npm/summernote-ext-print@latest/dist/summernote-ext-print.min.js',
      'summernote-ext-specialchars': 'https://cdn.jsdelivr.net/npm/summernote-ext-specialchars@latest/dist/summernote-ext-specialchars.min.js'
    };
  }

  /**
   * Load popular plugins for email templates
   */
  async loadEmailTemplatePlugins () {
    const plugins = [
      { name: 'emoji', url: this.getAvailablePlugins()['summernote-ext-emoji'] },
      { name: 'template', url: this.getAvailablePlugins()['summernote-ext-template'] },
      { name: 'specialchars', url: this.getAvailablePlugins()['summernote-ext-specialchars'] }
    ];

    await this.loadPlugins(plugins);
  }
}

// Create global instance
window.summernoteHelper = new SummernoteHelper();

// Auto-initialize when DOM is ready
$(document).ready(function () {
  window.summernoteHelper.init();
});