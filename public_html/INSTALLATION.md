# Framework Installation Guide

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher (or MariaDB 10.2+)
- Web server (Apache, Nginx, etc.)
- PDO MySQL extension

## Installation Steps

### 1. Download and Setup

1. Copy the framework folder to your web server
2. Ensure the web server has read/write permissions for the framework directory

### 2. Database Setup

1. Create a new MySQL database for your application
2. Import the database schema:
   ```bash
   mysql -u your_username -p your_database_name < framework/database/schema.sql
   ```

### 3. Configuration

1. Copy the database configuration file:
   ```bash
   cp framework/config/database.php framework/config/database.local.php
   ```

2. Edit `framework/config/database.local.php` with your database credentials:
   ```php
   return [
       'host' => 'localhost',
       'database' => 'your_database_name',
       'username' => 'your_username',
       'password' => 'your_password',
       'charset' => 'utf8mb4'
   ];
   ```

3. Update the autoloader to use your local config:
   ```php
   // In framework/autoload.php, modify the config loading:
   $config_file = file_exists(__DIR__ . '/config/database.local.php') 
       ? __DIR__ . '/config/database.local.php'
       : __DIR__ . '/config/database.php';
   ```

### 4. Web Server Configuration

#### Apache (.htaccess)
Create a `.htaccess` file in your web root:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
```

#### Nginx
Add to your server block:
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}

# Security headers
add_header X-Content-Type-Options nosniff;
add_header X-Frame-Options DENY;
add_header X-XSS-Protection "1; mode=block";
```

### 5. File Permissions

Set appropriate permissions:
```bash
# Make sure web server can read all files
chmod -R 644 framework/
chmod -R 755 framework/*/

# If using file-based sessions or logs
chmod -R 755 framework/storage/
```

### 6. Test Installation

1. Create a simple test file (`test.php`):
   ```php
   <?php
   require_once 'framework/autoload.php';
   
   try {
       $db = new Database();
       $connection = $db->getConnection();
       echo "Database connection successful!";
   } catch (Exception $e) {
       echo "Error: " . $e->getMessage();
   }
   ?>
   ```

2. Access the test file in your browser to verify the installation

### 7. Default Login

The framework comes with a default admin user:
- **Username:** admin
- **Password:** admin123
- **Email:** admin@example.com

**Important:** Change this password immediately after installation!

## Security Considerations

1. **Change default passwords** immediately
2. **Remove test files** from production
3. **Set up SSL/HTTPS** for production environments
4. **Configure proper file permissions**
5. **Enable error logging** and disable error display in production
6. **Regular security updates**

## Directory Structure After Installation

```
your-project/
├── framework/
│   ├── classes/           # Core framework classes
│   ├── config/           # Configuration files
│   ├── templates/        # HTML templates
│   ├── assets/          # CSS, JS files
│   ├── examples/        # Example implementations
│   ├── database/        # Database schema
│   └── autoload.php     # Autoloader
├── your-app/            # Your application files
├── public/              # Web accessible files
└── .htaccess           # Web server configuration
```

## Next Steps

1. Review the examples in `framework/examples/`
2. Create your first application following the patterns shown
3. Customize the templates and styling to match your needs
4. Set up proper error logging and monitoring
5. Configure backups for your database

## Troubleshooting

### Common Issues

1. **Database connection errors**
   - Check database credentials in config file
   - Verify database server is running
   - Ensure PDO MySQL extension is installed

2. **Permission errors**
   - Check file permissions
   - Verify web server user has access to files

3. **Class not found errors**
   - Ensure autoloader is included
   - Check class file names match class names exactly

4. **Session issues**
   - Verify session directory is writable
   - Check PHP session configuration

For more help, check the documentation in the `docs/` directory or review the example implementations.