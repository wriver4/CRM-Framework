<?php
/**
 * README.md Generator
 * 
 * Generates a developer-friendly README.md from .zencoder/rules/repo.md
 * Removes server-specific sections and adds developer quick-start content
 * 
 * Usage: php scripts/generate_readme.php
 */

echo "🚀 Generating README.md from repo.md...\n";

// Read the source repo.md file
$repoMdPath = __DIR__ . '/../.zencoder/rules/repo.md';
$readmePath = __DIR__ . '/../README.md';

if (!file_exists($repoMdPath)) {
    echo "❌ Error: repo.md not found at: $repoMdPath\n";
    exit(1);
}

$repoMd = file_get_contents($repoMdPath);

if ($repoMd === false) {
    echo "❌ Error: Could not read repo.md file\n";
    exit(1);
}

echo "📖 Read repo.md successfully\n";

// Remove the YAML front matter
$repoMd = preg_replace('/^---\s*\n.*?\n---\s*\n/s', '', $repoMd);

// Remove server-specific sections
$sectionsToRemove = [
    // Remove entire Server & Access Configuration section and everything until next ## section
    '/## Server & Access Configuration.*?(?=##\s)/s',
    // Remove any remaining SSH Configuration sections
    '/## SSH Configuration.*?(?=##\s)/s',
    '/### SSH Configuration.*?(?=###|##)/s',
    // Remove SSL Certificates sections (both ## and ### levels)
    '/## SSL Certificates.*?(?=##\s)/s',
    '/### SSL Certificates.*?(?=###|##)/s',
    // Remove Multi-Project Server Notes subsection
    '/### Multi-Project Server Notes.*?(?=###|##)/s',
    '/## Multi-Project Server Notes.*?(?=##\s)/s',
    // Remove Live URL references
    '/\*\*Live URL:\*\*.*?\n/s',
    // Remove any standalone SSL certificate content
    '/The project uses SSL certificates.*?(?=##)/s',
];

foreach ($sectionsToRemove as $pattern) {
    $repoMd = preg_replace($pattern, '', $repoMd);
}

echo "🧹 Removed server-specific sections\n";

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

// Replace the title and add developer content
$readme = str_replace('# CRM Framework Information', "# CRM Framework\n\n$developerContent", $repoMd);

// Clean up any double newlines that might have been created
$readme = preg_replace('/\n{3,}/', "\n\n", $readme);

// Write the README.md file
$result = file_put_contents($readmePath, $readme);

if ($result === false) {
    echo "❌ Error: Could not write README.md file\n";
    exit(1);
}

echo "✅ README.md generated successfully!\n";
echo "📍 Location: $readmePath\n";
echo "📊 Size: " . number_format(strlen($readme)) . " characters\n";

// Show what sections were included
echo "\n📋 Sections included in README.md:\n";
preg_match_all('/^## (.+)$/m', $readme, $matches);
foreach ($matches[1] as $section) {
    echo "   • $section\n";
}

echo "\n🎯 Remember to run this script whenever you update .zencoder/rules/repo.md\n";