# CRM Calendar Setup Instructions

## Project Structure
```
crm-calendar/
├── index.html                 # Main calendar page
├── config/
│   └── database.php          # Database configuration
├── models/
│   └── Task.php              # Task model with database operations
├── api/
│   └── tasks.php             # RESTful API endpoints
└── sql/
    └── schema.sql            # Database schema
```

## Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher (or MariaDB 10.2+)
- Web server (Apache/Nginx)
- Modern web browser

## Installation Steps

### 1. Database Setup
1. Create a new MySQL database:
```sql
CREATE DATABASE crm_system;
```

2. Import the database schema from the SQL artifact
3. Update database credentials in `config/database.php`:
```php
private $host = 'localhost';
private $db_name = 'crm_system';
private $username = 'your_username';
private $password = 'your_password';
```

### 2. File Structure Setup
1. Create the directory structure as shown above
2. Place each file in its respective directory
3. Ensure proper permissions for PHP files

### 3. Web Server Configuration

#### Apache (.htaccess in root directory)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^api/(.*)$ api/tasks.php [QSA,L]

# Enable CORS for API
Header always set Access-Control-Allow-Origin "*"
Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
Header always set Access-Control-Allow-Headers "Content-Type, Authorization"
```

#### Nginx
```nginx
location /api/ {
    try_files $uri $uri/ /api/tasks.php?$query_string;
}

# Enable CORS
add_header Access-Control-Allow-Origin *;
add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS";
add_header Access-Control-Allow-Headers "Content-Type, Authorization";
```

### 4. Testing the Installation

1. **Test Database Connection**: Create a test file to verify database connection
2. **Test API Endpoints**: 
   - GET `/api/tasks.php` - Should return empty array or sample tasks
   - POST to `/api/tasks.php` with sample data
3. **Access Calendar**: Open `index.html` in your browser

## Features

### Calendar Features
- **Multiple Views**: Month, Week, Day, and List views
- **Drag & Drop**: Move tasks between dates
- **Event Resizing**: Adjust task duration
- **Click to Create**: Click on any date to create a new task
- **Event Details**: Click on events to view full details

### Task Management
- **Task Types**: Call, Email, Meeting, Follow-up
- **Priority Levels**: High, Medium, Low (with color coding)
- **Status Tracking**: Pending, Completed, Cancelled
- **Contact Information**: Name, phone, email for each task
- **Rich Details**: Description, notes, and timestamps

### Dashboard Stats
- **Daily Overview**: Count of calls, emails, meetings for today
- **Priority Alert**: Count of high-priority tasks
- **Real-time Updates**: Stats update when tasks are modified

### API Endpoints
- `GET /api/tasks.php` - Retrieve all tasks
- `GET /api/tasks.php?id=123` - Get specific task
- `POST /api/tasks.php` - Create new task
- `PUT /api/tasks.php?id=123` - Update existing task
- `DELETE /api/tasks.php?id=123` - Delete task

## Customization Options

### Colors and Styling
- Task type colors defined in `Task.php` `getColorByType()` method
- Priority border colors in `getBorderColorByPriority()` method
- Custom CSS in the `<style>` section of `index.html`

### Additional Fields
To add new fields to tasks:
1. Add column to database table
2. Update `Task.php` methods (`createTask`, `updateTask`, etc.)
3. Add form fields to the modal in `index.html`
4. Update JavaScript form handling

### Integration with Existing CRM
- Replace the database schema with your existing tables
- Modify the `Task.php` model to match your data structure
- Update API endpoints to integrate with your authentication system

## Security Considerations

### For Production Use:
1. **Add Authentication**: Implement user login and session management
2. **Input Validation**: Add server-side validation for all inputs
3. **SQL Injection Prevention**: The code uses PDO prepared statements
4. **CSRF Protection**: Add CSRF tokens to forms
5. **Rate Limiting**: Implement API rate limiting
6. **HTTPS**: Use SSL/TLS encryption
7. **Environment Variables**: Store database credentials securely

### Sample Authentication Integration:
```php
// Add to beginning of api/tasks.php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
```

## Troubleshooting

### Common Issues:
1. **"Connection error"**: Check database credentials and server status
2. **"Events not loading"**: Verify API endpoints are accessible
3. **"CORS errors"**: Ensure proper headers are set in web server config
4. **"Calendar not displaying"**: Check browser console for JavaScript errors

### Debug Mode:
Add to `config/database.php` for debugging:
```php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Browser Compatibility
- Chrome 60+
- Firefox 55+
- Safari 11+
- Edge 79+

## Performance Tips
- Add database indexes on frequently queried columns
- Implement caching for large datasets
- Use pagination for extensive task lists
- Optimize images and assets