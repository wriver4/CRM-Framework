# Email Template System - Admin Interface

## Overview
Complete admin interface for managing the email template system, including templates, triggers, queue, and logs.

## Directory Structure

```
/admin/system_email_management/
├── index.php                    # Dashboard with statistics and quick links
├── templates/                   # Template Management
│   ├── list.php                # List all templates
│   ├── new.php                 # Create new template
│   ├── edit.php                # Edit template (TODO)
│   ├── view.php                # View template details
│   ├── content.php             # Manage multilingual content & variables
│   ├── content_form.php        # Helper for content forms
│   ├── get.php                 # Data retrieval logic
│   ├── post.php                # Form submission handler
│   └── delete.php              # Delete template
├── triggers/                    # Trigger Management
│   ├── list.php                # List all triggers
│   ├── new.php                 # Create new trigger (TODO)
│   ├── edit.php                # Edit trigger (TODO)
│   ├── get.php                 # Data retrieval logic
│   ├── post.php                # Form submission handler (TODO)
│   └── delete.php              # Delete trigger (TODO)
├── queue/                       # Queue Management
│   ├── list.php                # List queue items
│   ├── view.php                # View queue item details (TODO)
│   ├── get.php                 # Data retrieval logic
│   ├── approve.php             # Approve email (TODO)
│   ├── reject.php              # Reject email (TODO)
│   ├── retry.php               # Retry failed email (TODO)
│   ├── delete.php              # Delete queue item (TODO)
│   └── process.php             # Process queue manually (TODO)
└── logs/                        # Logs & Reports
    ├── list.php                # List sent emails
    ├── view.php                # View log details (TODO)
    ├── get.php                 # Data retrieval logic
    └── stats.php               # Statistics dashboard (TODO)
```

## Features Implemented

### ✅ Dashboard (index.php)
- Statistics cards showing:
  - Active templates count
  - Pending queue count
  - Active triggers count
  - Emails sent today/this week
- Quick access cards for each management section
- Quick action buttons

### ✅ Template Management
- **List Templates** - View all templates with filtering by module/status
- **Create Template** - Form to create new email template
- **View Template** - Detailed view with content, variables, and triggers
- **Edit Content** - Manage multilingual content (English, Spanish)
- **Manage Variables** - Add/remove template variables
- **Delete Template** - Remove template and all related data

### ✅ Queue Management
- **List Queue** - View all queued emails with status filtering
- **Statistics** - Count by status (pending, approval, sent, failed)
- **Priority Display** - Visual indicators for email priority

### ✅ Logs Management
- **List Logs** - View sent email history
- **Filter Options** - By template, status, date range
- **Statistics** - Sent, delivered, bounced, failed counts

### ✅ Triggers Management
- **List Triggers** - View all trigger rules
- **Filter by Module** - Leads, referrals, prospects, etc.
- **View Conditions** - Modal popup showing JSON conditions

## Features TODO

### Templates
- [ ] Edit template form (edit.php)
- [ ] Preview functionality
- [ ] Duplicate template
- [ ] Import/export templates

### Triggers
- [ ] Create trigger form (new.php)
- [ ] Edit trigger form (edit.php)
- [ ] Delete trigger (delete.php)
- [ ] Test trigger
- [ ] Trigger history/logs

### Queue
- [ ] View queue item details (view.php)
- [ ] Approve email (approve.php)
- [ ] Reject email (reject.php)
- [ ] Retry failed email (retry.php)
- [ ] Delete queue item (delete.php)
- [ ] Process queue manually (process.php)
- [ ] Bulk approve
- [ ] Bulk delete

### Logs
- [ ] View log details (view.php)
- [ ] Statistics dashboard (stats.php)
- [ ] Export logs
- [ ] Email analytics

### Global
- [ ] Global templates management (headers/footers)
- [ ] Variable library/documentation
- [ ] Email testing interface
- [ ] WYSIWYG editor integration
- [ ] Template versioning
- [ ] Audit trail

## Access Control

All pages require admin permissions:
```php
$security = new Security();
$security->check_user_permissions('admin', 'read');  // For viewing
$security->check_user_permissions('admin', 'create'); // For creating
$security->check_user_permissions('admin', 'update'); // For editing
$security->check_user_permissions('admin', 'delete'); // For deleting
```

## URL Structure

- Dashboard: `/admin/system_email_management/`
- Templates: `/admin/system_email_management/templates/list.php`
- Triggers: `/admin/system_email_management/triggers/list.php`
- Queue: `/admin/system_email_management/queue/list.php`
- Logs: `/admin/system_email_management/logs/list.php`

## Database Tables Used

1. **email_templates** - Template definitions
2. **email_template_content** - Multilingual content
3. **email_template_variables** - Variable definitions
4. **email_trigger_rules** - Automatic triggers
5. **email_queue** - Outbound email queue
6. **email_send_log** - Sent email history
7. **email_global_templates** - Reusable headers/footers

## Session Messages

The interface uses session-based flash messages:
```php
$_SESSION['email_template_message'] = "Success message";
$_SESSION['email_template_message_type'] = "success"; // or "danger", "warning", "info"
```

Message types by section:
- Templates: `email_template_message`
- Triggers: `email_trigger_message`
- Queue: `email_queue_message`
- Logs: `email_log_message`

## Form Security

All forms use nonce tokens for CSRF protection:
```php
$nonce = new Nonce();
$nonce_token = $nonce->create('email_template');
```

Verification in post.php:
```php
if (!$nonce->verify($_POST['nonce'] ?? '', 'email_template')) {
    // Handle invalid token
}
```

## Styling

The interface uses Bootstrap 5 with Font Awesome icons:
- Cards for content sections
- Tables for data listing
- Badges for status indicators
- Modals for popups
- Tabs for multi-section pages

## Integration with Existing System

The admin interface follows the framework's patterns:
- Uses standard header/footer templates
- Follows routing variable conventions
- Integrates with navigation system
- Uses existing Database class
- Follows security patterns

## Next Steps

1. Complete TODO items listed above
2. Add WYSIWYG editor for HTML content
3. Implement email preview functionality
4. Add template testing interface
5. Create comprehensive statistics dashboard
6. Add export/import functionality
7. Implement template versioning
8. Add email analytics and tracking

## Testing

Test the interface at:
- Dashboard: `http://yourdomain.com/admin/system_email_management/`
- Test System: `http://yourdomain.com/test_email_templates.php`

## Documentation

Related documentation:
- Architecture: `/.zencoder/rules/architecture-complete.md`
- Database Schema: `/.zencoder/rules/database-operations.md`
- Quick Reference: `/.zencoder/rules/email-template-system.md`
- Complete Guide: `/.zencoder/EMAIL_TEMPLATE_SYSTEM_COMPLETE.md`

---

**Status:** Core functionality complete, additional features in progress
**Version:** 1.0
**Last Updated:** 2025