# Nextcloud Integration Guide

## 🎯 Complete Setup Instructions

This guide will walk you through integrating your CRM's CalDAV server with Nextcloud for seamless two-way synchronization.

---

## 🚀 Phase 1: Deploy CalDAV Server

### 1. Install Dependencies
```bash
# Install Composer dependencies
composer require sabre/dav sabre/vobject ramsey/uuid

# Or run the installation script
php setup/install_caldav.php
```

### 2. Configure Web Server

#### Apache Configuration
```apache
# Add to your .htaccess or virtual host
RewriteEngine On

# CalDAV discovery
RewriteRule ^\.well-known/caldav$ /caldav/ [R=301,L]

# CalDAV endpoints
RewriteRule ^caldav/(.*)$ caldav/server.php [QSA,L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set Access-Control-Allow-Origin "*"
Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS, PROPFIND, PROPPATCH, REPORT"
```

#### Nginx Configuration
```nginx
location /.well-known/caldav {
    return 301 /caldav/;
}

location /caldav/ {
    try_files $uri $uri/ /caldav/server.php?$query_string;
    
    # CORS headers
    add_header Access-Control-Allow-Origin *;
    add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS, PROPFIND, PROPPATCH, REPORT";
    add_header Access-Control-Allow-Headers "Content-Type, Authorization, Depth, User-Agent";
}
```

### 3. Test CalDAV Server
```bash
# Test server discovery
curl -X PROPFIND https://yourcrm.com/.well-known/caldav \
  -H "Content-Type: application/xml" \
  --user admin:admin123

# Test calendar access
curl -X PROPFIND https://yourcrm.com/caldav/calendars/admin/ \
  -H "Content-Type: application/xml" \
  -H "Depth: 1" \
  --user admin:admin123
```

---

## 🔗 Phase 2: Connect Nextcloud

### Method 1: External Calendar Subscription (Easier)

1. **In Nextcloud Calendar app:**
   - Click **"+ New subscription"**
   - Enter URL: `https://yourcrm.com/caldav/calendars/admin/crm-tasks/`
   - Username: `admin`
   - Password: `admin123`
   - Calendar name: `CRM Tasks`

2. **Result:**
   - ✅ CRM tasks appear in Nextcloud
   - ❌ One-way sync only (Nextcloud → CRM doesn't work)
   - ⚠️ Updates every hour by default

### Method 2: Full CalDAV Integration (Recommended)

1. **In Nextcloud Calendar app:**
   - Go to **Settings & Import** (bottom left)
   - Click **"+ Add calendar"**
   - Select **"Add calendar from CalDAV server"**

2. **Enter CalDAV details:**
   ```
   Server URL: https://yourcrm.com/caldav/
   Username: admin
   Password: admin123
   ```

3. **Select calendars:**
   - Nextcloud will discover available calendars
   - Select "CRM Tasks" calendar
   - Click **"Add"**

4. **Result:**
   - ✅ Full two-way synchronization
   - ✅ Real-time updates
   - ✅ Mobile sync via Nextcloud apps

---

## 📱 Phase 3: Mobile Integration

### iOS Setup
1. **Add CalDAV Account:**
   - Settings → Mail → Accounts → Add Account
   - Select "Other" → "Add CalDAV Account"
   - Server: `yourcrm.com/caldav/`
   - Username: `admin`
   - Password: `admin123`

2. **Or via Nextcloud app:**
   - Install Nextcloud iOS app
   - Login to your Nextcloud instance
   - Calendar sync will happen automatically

### Android Setup
1. **Using DAVx5 (Recommended):**
   - Install DAVx5 from Google Play or F-Droid
   - Add account with base URL: `https://yourcrm.com/caldav/`
   - Enter credentials: `admin` / `admin123`
   - Select calendars to sync

2. **Via Nextcloud app:**
   - Install Nextcloud Android app
   - Login to your Nextcloud instance
   - Enable calendar sync in app settings

---

## 🛠️ Phase 4: Advanced Configuration

### Custom User Accounts

Create additional CRM users for team access:

```php
// Add new user via PHP
$principalBackend = new CRM\CalDAV\PrincipalBackend($pdo);
$principalBackend->createUser(
    'john.doe',           // username
    'secure_password',    // password
    'john@company.com',   // email
    'John Doe'           // display name
);

// Create personal calendar for user
$calendarBackend = new CRM\CalDAV\CalendarBackend($pdo);
$calendarBackend->createCalendar(
    'principals/users/john.doe',
    'personal-tasks',
    [
        '{DAV:}displayname' => 'Personal Tasks',
        '{urn:ietf:params:xml:ns:caldav}calendar-description' => 'John\'s personal task calendar',
        '{http://apple.com/ns/ical/}calendar-color' => '#28a745'
    ]
);
```

### Multiple Calendar Support

```sql
-- Create different calendars for different purposes
INSERT INTO caldav_calendars (user_id, uri, displayname, description, color) VALUES
('admin', 'calls', 'Phone Calls', 'Scheduled phone calls', '#dc3545'),
('admin', 'emails', 'Email Tasks', 'Email follow-ups', '#28a745'),
('admin', 'meetings', 'Meetings', 'Scheduled meetings', '#ffc107');

-- Update tasks to use specific calendars
UPDATE tasks SET calendar_id = 2 WHERE task_type = 'call';
UPDATE tasks SET calendar_id = 3 WHERE task_type = 'email';
UPDATE tasks SET calendar_id = 4 WHERE task_type = 'meeting';
```

### Sync Frequency Configuration

**Nextcloud side:**
```bash
# Adjust sync frequency in Nextcloud config
sudo -u www-data php occ config:app:set dav calendarSubscriptionRefreshRate --value="PT15M"  # 15 minutes
```

**CRM side (real-time push):**
```php
// Add webhook to notify Nextcloud of changes
function notifyNextcloudOfChange($taskId, $action) {
    $webhookUrl = 'https://nextcloud.yourdomain.com/remote.php/dav/calendars/admin/crm-tasks/';
    
    // Send notification to Nextcloud
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $webhookUrl);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PROPFIND');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/xml',
        'Depth: 1'
    ]);
    curl_setopt($ch, CURLOPT_USERPWD, 'admin:admin123');
    curl_exec($ch);
    curl_close($ch);
}
```

---

## 🔧 Phase 5: Troubleshooting

### Common Issues

#### 1. "Calendar not found" Error
**Solution:**
```bash
# Check CalDAV server logs
tail -f logs/caldav_errors.log

# Verify calendar exists
mysql -u root -p crm_system -e "SELECT * FROM caldav_calendars;"

# Test direct calendar access
curl -X PROPFIND https://yourcrm.com/caldav/calendars/admin/crm-tasks/ \
  --user admin:admin123 -v
```

#### 2. Authentication Failures
**Solution:**
```php
// Reset admin password
$pdo = getDB();
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
$stmt->execute([password_hash('new_password', PASSWORD_DEFAULT)]);
```

#### 3. CORS Issues
**Solution:**
```apache
# Add to .htaccess
Header always set Access-Control-Allow-Origin "*"
Header always set Access-Control-Allow-Credentials "true"
Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS, PROPFIND, PROPPATCH, REPORT"
Header always set Access-Control-Allow-Headers "Content-Type, Authorization, Depth, User-Agent, X-File-Size, X-Requested-With, If-Modified-Since, X-File-Name, Cache-Control, Prefer, Brief"
```

#### 4. Sync Not Working
**Solution:**
```sql
-- Check sync log
SELECT * FROM caldav_sync_log ORDER BY sync_timestamp DESC LIMIT 10;

-- Manually trigger calendar update
UPDATE caldav_calendars SET ctag = ctag + 1 WHERE uri = 'crm-tasks';
```

### Debug Mode

Enable debug logging:
```php
// Add to caldav/server.php
$server->addPlugin(new DAV\Browser\Plugin());  // Web interface for debugging
$server->debugExceptions = true;               // Show detailed errors
```

Access debug interface: `https://yourcrm.com/caldav/?debug=1`

---

## 📊 Phase 6: Monitoring & Maintenance

### Performance Monitoring

```sql
-- Monitor sync performance
SELECT 
    action,
    sync_status,
    COUNT(*) as count,
    AVG(TIMESTAMPDIFF(SECOND, sync_timestamp, NOW())) as avg_age_seconds
FROM caldav_sync_log 
WHERE sync_timestamp > DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY action, sync_status;

-- Check calendar activity
SELECT 
    c.displayname,
    COUNT(t.id) as task_count,
    MAX(t.caldav_lastmod) as last_modified
FROM caldav_calendars c
LEFT JOIN tasks t ON c.id = t.calendar_id
GROUP BY c.id;
```

### Automated Cleanup

```sql
-- Clean old sync logs (run weekly)
DELETE FROM caldav_sync_log 
WHERE sync_timestamp < DATE_SUB(NOW(), INTERVAL 30 DAY);

-- Clean old rate limit entries
DELETE FROM caldav_rate_limit 
WHERE timestamp < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 DAY));
```

### Backup Strategy

```bash
#!/bin/bash
# backup_caldav.sh

# Backup database
mysqldump crm_system caldav_calendars caldav_properties caldav_sync_log > caldav_backup_$(date +%Y%m%d).sql

# Backup CalDAV files
tar -czf caldav_files_$(date +%Y%m%d).tar.gz caldav/ logs/
```

---

## 🚀 Success Checklist

- [ ] ✅ CalDAV server responds to PROPFIND requests
- [ ] ✅ Nextcloud discovers CRM calendars automatically  
- [ ] ✅ Tasks created in CRM appear in Nextcloud within 15 minutes
- [ ] ✅ Events created in Nextcloud sync back to CRM
- [ ] ✅ Contact information is preserved in both directions
- [ ] ✅ Task types map correctly (Call/Email/Meeting)
- [ ] ✅ Mobile devices can access CRM tasks via Nextcloud apps
- [ ] ✅ Multiple users can access their own calendars
- [ ] ✅ Real-time sync works for critical updates
- [ ] ✅ Backup and monitoring systems are in place

---

## 🎉 Final Result

Once fully configured, you'll have:

- **Seamless Integration**: CRM tasks automatically sync with Nextcloud
- **Mobile Access**: View and edit CRM tasks on any mobile device
- **Team Collaboration**: Share task calendars with team members
- **Real-time Updates**: Changes sync immediately between systems
- **Professional Features**: CalDAV compliance ensures compatibility with all major calendar clients
- **Scalable Architecture**: Easily add more users, calendars, and integrations

Your CRM now functions as a professional calendar server that integrates with the entire productivity ecosystem! 🎯