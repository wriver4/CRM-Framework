<?php
// setup/install_caldav.php - Installation script for CalDAV integration
require_once __DIR__ . '/../config/database.php';

class CalDAVInstaller {
    private $db;
    private $errors = [];
    private $success = [];
    
    public function __construct() {
        try {
            $this->db = initializeCalDAVDatabase();
        } catch (Exception $e) {
            $this->errors[] = "Database connection failed: " . $e->getMessage();
        }
    }
    
    public function install() {
        echo "🚀 Starting CalDAV Installation...\n\n";
        
        // Step 1: Check requirements
        $this->checkRequirements();
        
        // Step 2: Create directory structure
        $this->createDirectories();
        
        // Step 3: Install Composer dependencies
        $this->installDependencies();
        
        // Step 4: Set up database tables
        $this->setupDatabase();
        
        // Step 5: Create default data
        $this->createDefaultData();
        
        // Step 6: Set permissions
        $this->setPermissions();
        
        // Step 7: Create configuration files
        $this->createConfigFiles();
        
        // Step 8: Test CalDAV server
        $this->testCalDAVServer();
        
        // Report results
        $this->showResults();
        
        return empty($this->errors);
    }
    
    private function checkRequirements() {
        echo "📋 Checking requirements...\n";
        
        // PHP version
        if (version_compare(PHP_VERSION, '7.4.0', '<')) {
            $this->errors[] = "PHP 7.4 or higher required. Current version: " . PHP_VERSION;
        } else {
            $this->success[] = "PHP version OK: " . PHP_VERSION;
        }
        
        // Required PHP extensions
        $requiredExtensions = ['pdo', 'pdo_mysql', 'dom', 'libxml', 'mbstring', 'curl'];
        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $this->errors[] = "Required PHP extension missing: $ext";
            } else {
                $this->success[] = "PHP extension OK: $ext";
            }
        }
        
        // Memory limit
        $memoryLimit = ini_get('memory_limit');
        $memoryBytes = $this->parseMemoryLimit($memoryLimit);
        if ($memoryBytes < 128 * 1024 * 1024) {
            $this->errors[] = "Memory limit too low: $memoryLimit (minimum 128M recommended)";
        } else {
            $this->success[] = "Memory limit OK: $memoryLimit";
        }
        
        // Check if composer is available
        $composerPath = shell_exec('which composer');
        if (empty($composerPath)) {
            $this->errors[] = "Composer not found. Please install Composer first.";
        } else {
            $this->success[] = "Composer found: " . trim($composerPath);
        }
        
        echo "   ✓ Requirements check completed\n\n";
    }
    
    private function createDirectories() {
        echo "📁 Creating directory structure...\n";
        
        $directories = [
            __DIR__ . '/../caldav',
            __DIR__ . '/../caldav/backends',
            __DIR__ . '/../caldav/utils',
            __DIR__ . '/../caldav/config',
            __DIR__ . '/../logs',
            __DIR__ . '/../vendor'
        ];
        
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                if (mkdir($dir, 0755, true)) {
                    $this->success[] = "Created directory: $dir";
                } else {
                    $this->errors[] = "Failed to create directory: $dir";
                }
            } else {
                $this->success[] = "Directory exists: $dir";
            }
        }
        
        echo "   ✓ Directory structure created\n\n";
    }
    
    private function installDependencies() {
        echo "📦 Installing Composer dependencies...\n";
        
        $composerJson = [
            "require" => [
                "sabre/dav" => "^4.4",
                "sabre/vobject" => "^4.5",
                "ramsey/uuid" => "^4.7"
            ],
            "autoload" => [
                "psr-4" => [
                    "CRM\\CalDAV\\" => "caldav/"
                ]
            ]
        ];
        
        $composerFile = __DIR__ . '/../composer.json';
        if (!file_exists($composerFile)) {
            file_put_contents($composerFile, json_encode($composerJson, JSON_PRETTY_PRINT));
            $this->success[] = "Created composer.json";
        }
        
        // Run composer install
        $output = [];
        $returnCode = 0;
        exec('cd ' . __DIR__ . '/.. && composer install --no-dev --optimize-autoloader 2>&1', $output, $returnCode);
        
        if ($returnCode === 0) {
            $this->success[] = "Composer dependencies installed successfully";
        } else {
            $this->errors[] = "Composer install failed: " . implode("\n", $output);
        }
        
        echo "   ✓ Dependencies installation completed\n\n";
    }
    
    private function setupDatabase() {
        echo "🗄️  Setting up database...\n";
        
        if (!$this->db) {
            $this->errors[] = "Database connection not available";
            return;
        }
        
        try {
            $pdo = getDB();
            
            // Create CalDAV tables
            $this->db->createCalDAVTables();
            $this->success[] = "CalDAV database tables created";
            
            // Update existing tasks table
            $pdo->exec("UPDATE tasks SET caldav_uid = CONCAT(UUID(), '@yourcrm.com') WHERE caldav_uid IS NULL");
            $this->success[] = "Updated existing tasks with CalDAV UIDs";
            
            // Create indexes for performance
            $indexes = [
                "CREATE INDEX IF NOT EXISTS idx_tasks_caldav_lookup ON tasks(calendar_id, caldav_uid)",
                "CREATE INDEX IF NOT EXISTS idx_tasks_date_range ON tasks(start_datetime, end_datetime)",
                "CREATE INDEX IF NOT EXISTS idx_caldav_sync_log_uid ON caldav_sync_log(caldav_uid, sync_timestamp)",
                "CREATE INDEX IF NOT EXISTS idx_caldav_properties_path ON caldav_properties(path(255))"
            ];
            
            foreach ($indexes as $sql) {
                $pdo->exec($sql);
            }
            
            $this->success[] = "Database indexes created for performance";
            
        } catch (Exception $e) {
            $this->errors[] = "Database setup failed: " . $e->getMessage();
        }
        
        echo "   ✓ Database setup completed\n\n";
    }
    
    private function createDefaultData() {
        echo "👤 Creating default data...\n";
        
        try {
            $pdo = getDB();
            
            // Create admin user if doesn't exist
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'admin'");
            $stmt->execute();
            
            if (!$stmt->fetch()) {
                $stmt = $pdo->prepare("
                    INSERT INTO users (username, password, email, display_name) 
                    VALUES ('admin', ?, 'admin@yourcrm.com', 'CRM Administrator')
                ");
                $stmt->execute([password_hash('admin123', PASSWORD_DEFAULT)]);
                $this->success[] = "Created admin user (username: admin, password: admin123)";
            }
            
            // Create default calendar
            $stmt = $pdo->prepare("SELECT id FROM caldav_calendars WHERE user_id = 'admin' AND uri = 'crm-tasks'");
            $stmt->execute();
            
            if (!$stmt->fetch()) {
                $stmt = $pdo->prepare("
                    INSERT INTO caldav_calendars (user_id, uri, displayname, description, color) 
                    VALUES ('admin', 'crm-tasks', 'CRM Tasks', 'Tasks and events from CRM system', '#007bff')
                ");
                $stmt->execute();
                $this->success[] = "Created default CRM calendar";
            }
            
            // Link existing tasks to default calendar
            $stmt = $pdo->prepare("UPDATE tasks SET calendar_id = 1 WHERE calendar_id IS NULL OR calendar_id = 0");
            $stmt->execute();
            $this->success[] = "Linked existing tasks to default calendar";
            
        } catch (Exception $e) {
            $this->errors[] = "Failed to create default data: " . $e->getMessage();
        }
        
        echo "   ✓ Default data creation completed\n\n";
    }
    
    private function setPermissions() {
        echo "🔐 Setting file permissions...\n";
        
        $paths = [
            __DIR__ . '/../logs' => 0755,
            __DIR__ . '/../caldav' => 0755,
            __DIR__ . '/../vendor' => 0755
        ];
        
        foreach ($paths as $path => $permission) {
            if (is_dir($path)) {
                if (chmod($path, $permission)) {
                    $this->success[] = "Set permissions for: $path";
                } else {
                    $this->errors[] = "Failed to set permissions for: $path";
                }
            }
        }
        
        echo "   ✓ File permissions set\n\n";
    }
    
    private function createConfigFiles() {
        echo "⚙️  Creating configuration files...\n";
        
        // Apache .htaccess for CalDAV
        $htaccess = "# CalDAV Server Configuration
RewriteEngine On

# CalDAV discovery
RewriteRule ^\.well-known/caldav$ /caldav/ [R=301,L]

# CalDAV endpoints  
RewriteRule ^caldav/(.*)$ caldav/server.php [QSA,L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection \"1; mode=block\"
Header always set Strict-Transport-Security \"max-age=31536000; includeSubDomains\"

# CORS for CalDAV
Header always set Access-Control-Allow-Origin \"*\"
Header always set Access-Control-Allow-Methods \"GET, POST, PUT, DELETE, OPTIONS, PROPFIND, PROPPATCH, REPORT, MKCALENDAR\"
Header always set Access-Control-Allow-Headers \"Content-Type, Authorization, Depth, User-Agent, X-File-Size, X-Requested-With, If-Modified-Since, X-File-Name, Cache-Control, Prefer, Brief\"
";
        
        file_put_contents(__DIR__ . '/../.htaccess', $htaccess);
        $this->success[] = "Created Apache .htaccess configuration";
        
        // CalDAV discovery endpoint
        $discoveryDir = __DIR__ . '/../.well-known';
        if (!is_dir($discoveryDir)) {
            mkdir($discoveryDir, 0755, true);
        }
        
        $discovery = "<?php
header('Location: /caldav/', true, 301);
exit;
";
        file_put_contents($discoveryDir . '/caldav', $discovery);
        $this->success[] = "Created CalDAV discovery endpoint";
        
        echo "   ✓ Configuration files created\n\n";
    }
    
    private function testCalDAVServer() {
        echo "🧪 Testing CalDAV server...\n";
        
        try {
            // Test basic server response
            $baseUrl = $this->getBaseUrl();
            $testUrl = $baseUrl . '/caldav/';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $testUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 401) {
                $this->success[] = "CalDAV server responding correctly (authentication required)";
            } elseif ($httpCode === 200) {
                $this->success[] = "CalDAV server accessible";
            } else {
                $this->errors[] = "CalDAV server test failed. HTTP code: $httpCode";
            }
            
        } catch (Exception $e) {
            $this->errors[] = "CalDAV server test failed: " . $e->getMessage();
        }
        
        echo "   ✓ CalDAV server testing completed\n\n";
    }
    
    private function showResults() {
        echo "📊 Installation Results:\n";
        echo "========================\n\n";
        
        if (!empty($this->success)) {
            echo "✅ Success:\n";
            foreach ($this->success as $message) {
                echo "   • $message\n";
            }
            echo "\n";
        }
        
        if (!empty($this->errors)) {
            echo "❌ Errors:\n";
            foreach ($this->errors as $error) {
                echo "   • $error\n";
            }
            echo "\n";
        }
        
        if (empty($this->errors)) {
            echo "🎉 CalDAV installation completed successfully!\n\n";
            echo "Next steps:\n";
            echo "1. Update your database credentials in config/database.php\n";
            echo "2. Configure your web server to serve the CalDAV endpoints\n";
            echo "3. Test the CalDAV server at: " . $this->getBaseUrl() . "/caldav/\n";
            echo "4. Add the calendar to Nextcloud:\n";
            echo "   URL: " . $this->getBaseUrl() . "/caldav/calendars/admin/crm-tasks/\n";
            echo "   Username: admin\n";
            echo "   Password: admin123\n\n";
            echo "⚠️  IMPORTANT: Change the default admin password before production use!\n";
        } else {
            echo "💥 Installation failed. Please fix the errors above and try again.\n";
        }
    }
    
    private function parseMemoryLimit($limit) {
        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit)-1]);
        $value = (int) $limit;
        
        switch($last) {
            case 'g': $value *= 1024;
            case 'm': $value *= 1024;
            case 'k': $value *= 1024;
        }
        
        return $value;
    }
    
    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return "$protocol://$host";
    }
}

// Run the installer
if (php_sapi_name() === 'cli') {
    // Command line installation
    $installer = new CalDAVInstaller();
    $success = $installer->install();
    exit($success ? 0 : 1);
} else {
    // Web-based installation
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>CalDAV Installation</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
            .success { color: #28a745; }
            .error { color: #dc3545; }
            .info { color: #007bff; }
            pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
        </style>
    </head>
    <body>
        <h1>🚀 CalDAV Installation</h1>
        
        <?php if (isset($_POST['install'])): ?>
            <div class="info">Starting installation...</div>
            <pre><?php
                ob_start();
                $installer = new CalDAVInstaller();
                $success = $installer->install();
                $output = ob_get_clean();
                echo htmlspecialchars($output);
            ?></pre>
        <?php else: ?>
            <p>This will install the CalDAV integration for your CRM system.</p>
            <p><strong>Make sure you have:</strong></p>
            <ul>
                <li>PHP 7.4 or higher</li>
                <li>Composer installed</li>
                <li>Database connection configured</li>
                <li>Write permissions for the web directory</li>
            </ul>
            
            <form method="post">
                <button type="submit" name="install" style="padding: 10px 20px; font-size: 16px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    Install CalDAV Integration
                </button>
            </form>
        <?php endif; ?>
        
    </body>
    </html>
    <?php
}
?>