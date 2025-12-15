<?php
/**
 * README.md Generator
 * 
 * Generates a comprehensive developer README.md from all .zencoder/rules/ files
 * Removes only sensitive information (passwords, SSL certificates)
 * Includes all technical documentation for developers
 * 
 * Usage: php scripts/generate_readme.php
 */

echo "🚀 Generating comprehensive developer README.md...\n";

$rulesDir = __DIR__ . '/../.zencoder/rules/';
$readmePath = __DIR__ . '/../README.md';

// Define the order of sections and their source files
$sectionFiles = [
    'repo.md' => 'Core Overview',
    'development.md' => 'Development Patterns',
    'database.md' => 'Database Configuration',
    'multilingual.md' => 'Multilingual System',
    'testing.md' => 'Testing Systems',
    'workflows.md' => 'Business Workflows',
    'server-access.md' => 'Server Access (filtered)'
];

$allContent = '';

// Process each file
foreach ($sectionFiles as $filename => $description) {
    $filePath = $rulesDir . $filename;
    
    if (!file_exists($filePath)) {
        echo "⚠️  Warning: $filename not found, skipping...\n";
        continue;
    }
    
    $content = file_get_contents($filePath);
    if ($content === false) {
        echo "⚠️  Warning: Could not read $filename, skipping...\n";
        continue;
    }
    
    echo "📖 Processing $filename ($description)\n";
    
    // Remove YAML front matter
    $content = preg_replace('/^---\s*\n.*?\n---\s*\n/s', '', $content);
    
    // Apply file-specific filtering
    if ($filename === 'server-access.md') {
        $content = filterSensitiveServerInfo($content);
    } elseif ($filename === 'database.md') {
        $content = filterSensitiveDbInfo($content);
    }
    
    $allContent .= $content . "\n\n";
}

echo "🔗 Combining all sections...\n";

// Remove sensitive information patterns across all content
$allContent = filterSensitiveInfo($allContent);

echo "🧹 Filtered sensitive information\n";

/**
 * Filter sensitive server information
 */
function filterSensitiveServerInfo($content) {
    $patterns = [
        // Remove SSH private key content
        '/ssh-ed25519 AAAAC3NzaC1lZDI1NTE5[A-Za-z0-9+\/=]+ [^\n]+/s',
        // Remove specific IP addresses and ports
        '/159\.203\.116\.150/s',
        '/Port 222/s',
        // Remove SSL certificate paths and content
        '/ssl\/[^\s]+/s',
        '/\*\*Live URL:\*\*.*?\n/s',
        // Remove specific SSH key references and file paths
        '/IdentityFile ~\/\.ssh\/wswg_key/s',
        '/wswg_key/s',
        '/\.ssh\/wswg_key[^\s]*/s',
        // Remove public key setup instructions with actual keys
        '/# Copy this public key.*?mark@king/s',
        // Remove specific host configurations
        '/Host wswg\s*\n.*?IdentitiesOnly yes/s',
        // Remove specific user references
        '/mark@king/s',
    ];
    
    foreach ($patterns as $pattern) {
        $content = preg_replace($pattern, '[REDACTED]', $content);
    }
    
    return $content;
}

/**
 * Filter sensitive database information
 */
function filterSensitiveDbInfo($content) {
    $patterns = [
        // Remove database passwords
        '/\$this->crm_password = \'[^\']+\';/s',
        '/democrm_democrm.*?b3J2sy5T4JNm60/s',
        // Keep structure but remove actual password
        '/\'b3J2sy5T4JNm60\'/s',
    ];
    
    foreach ($patterns as $pattern) {
        $content = preg_replace($pattern, '[PASSWORD_REDACTED]', $content);
    }
    
    return $content;
}

/**
 * Filter general sensitive information
 */
function filterSensitiveInfo($content) {
    $patterns = [
        // Remove any remaining passwords
        '/password[\'"]?\s*[=:]\s*[\'"][^\'"\n]+[\'"]?/i',
        // Remove SSL certificate content
        '/-----BEGIN [^-]+-----.*?-----END [^-]+-----/s',
        // Remove any remaining specific server details
        '/democrm\.waveguardco\.net/s',
    ];
    
    foreach ($patterns as $pattern) {
        $content = preg_replace($pattern, '[REDACTED]', $content);
    }
    
    return $content;
}

// Add developer-specific quick start content
$developerContent = '
## Quick Start

### Prerequisites
- PHP 8.4.8+
- MySQL/MariaDB
- Composer
- Web server (Apache/Nginx)

### Local Development Setup
1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd democrm
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure database**
   - Edit database credentials in `classes/Core/Database.php`
   - Update the connection details in the constructor

4. **Import database schema**
   ```bash
   mysql -u username -p database_name < sql/democrm_democrm.sql
   ```

5. **Set up web server**
   - Point document root to `public_html/` directory
   - Ensure proper file permissions (644 for files, 755 for directories)

6. **Verify installation**
   - Access the application through your web server
   - Check `logs/php_errors.log` for any issues

### Running Tests

**PHPUnit Tests:**
```bash
# Local development
./vendor/bin/phpunit

# Remote server
php phpunit.phar
```

**Playwright E2E Tests:**
```bash
# Install Playwright
npm install @playwright/test

# Run tests
npx playwright test
```

### Development Notes
- This is **NOT a traditional MVC framework** - it uses direct file routing
- Database credentials are hardcoded in `classes/Core/Database.php`
- All models extend the `Database` class for connection access
- Language files are stored in `public_html/admin/languages/`
- Templates are included directly, not rendered through a template engine

';

// Build the final README content
$readme = "# CRM Framework - Developer Documentation\n\n$developerContent\n\n" . $allContent;

// Clean up formatting
$readme = preg_replace('/\n{3,}/', "\n\n", $readme); // Remove excessive newlines
$readme = preg_replace('/^# CRM Framework Information\n\n/m', '', $readme); // Remove duplicate title
$readme = str_replace('# CRM Framework Information', '# CRM Framework Overview', $readme); // Rename any remaining titles

// Write the README.md file
$result = file_put_contents($readmePath, $readme);

if ($result === false) {
    echo "❌ Error: Could not write README.md file\n";
    exit(1);
}

echo "✅ Comprehensive developer README.md generated successfully!\n";
echo "📍 Location: $readmePath\n";
echo "📊 Size: " . number_format(strlen($readme)) . " characters\n";

// Show what sections were included
echo "\n📋 Sections included in README.md:\n";
preg_match_all('/^## (.+)$/m', $readme, $matches);
foreach ($matches[1] as $section) {
    echo "   • $section\n";
}

echo "\n🎯 Remember to run this script whenever you update any .zencoder/rules/ files\n";
echo "🔒 Sensitive information (passwords, SSL certs, specific IPs) has been filtered out\n";