# Email Processing System

## Overview

The Email Processing System automatically imports form submissions from email accounts and creates leads in the CRM. It supports three types of forms:

- **Estimate Forms** - Fire protection system quotes
- **LTR Forms** - Long-term retardant applications  
- **Contact Forms** - General inquiries

## Features

- ✅ **Automated Email Processing** - Monitors multiple email accounts via IMAP
- ✅ **Form Parsing** - Extracts structured data from email content
- ✅ **Lead Creation** - Integrates with existing leads system
- ✅ **Duplicate Detection** - Prevents duplicate lead creation
- ✅ **Web Interface** - Management dashboard for monitoring
- ✅ **REST API** - External integration support
- ✅ **Cron Job Support** - Automated processing every 5 minutes
- ✅ **Multilingual Support** - Follows CRM framework patterns

## Installation

### 1. Run Installation Script

```bash
cd /home/democrm
php scripts/install_email_system.php
```

This will:
- Create required database tables
- Set up default email account configurations
- Verify installation

### 2. Configure Email Accounts

Update email passwords in the database:

```sql
-- Update with actual base64-encoded passwords
UPDATE email_accounts_config SET 
password = 'base64_encoded_password_here' 
WHERE email_address = 'estimates@waveguardco.com';

UPDATE email_accounts_config SET 
password = 'base64_encoded_password_here' 
WHERE email_address = 'ltr@waveguardco.com';

UPDATE email_accounts_config SET 
password = 'base64_encoded_password_here' 
WHERE email_address = 'contact@waveguardco.com';
```

### 3. Set Up Cron Job

Add to server crontab:

```bash
# Edit crontab
crontab -e

# Add this line (runs every 5 minutes)
*/5 * * * * php /home/democrm/scripts/email_cron.php >> /home/democrm/logs/email_cron.log 2>&1
```

### 4. Test Installation

- **Web Interface**: Visit `/leads/email_import.php`
- **API Status**: `/api/email_forms.php/status?api_key=waveguard_api_key_2024`
- **Manual Processing**: Click "Process Emails Now" in web interface

## File Structure

```
classes/Models/
├── EmailFormProcessor.php      # Main email processing logic
└── EmailFormMapper.php         # Form-specific parsing rules

public_html/leads/
└── email_import.php           # Management web interface

public_html/api/
└── email_forms.php            # REST API endpoint

scripts/
├── email_cron.php             # Cron job script
└── install_email_system.php   # Installation script

sql/migrations/
└── add_email_processing_tables.sql  # Database migration

public_html/templates/
└── nav_item_email_import.php  # Navigation menu item
```

## Database Tables

### email_form_processing
Logs all email processing activities:
- `id` - Primary key
- `email_account` - Which email account processed
- `form_type` - estimate, ltr, or contact
- `sender_email` - Who sent the form
- `processing_status` - success, failed, duplicate, skipped
- `lead_id` - Created/updated lead ID
- `parsed_form_data` - Extracted data as JSON
- `raw_email_content` - Original email for debugging

### crm_sync_queue
Queue for syncing to external CRM systems:
- `lead_id` - Lead to sync
- `sync_action` - create, update, note_add
- `external_system` - hubspot, salesforce, mailchimp, custom
- `sync_status` - pending, completed, failed
- `retry_count` - Number of retry attempts

### email_accounts_config
Email account configuration:
- `email_address` - Email to monitor
- `form_type` - Type of forms this email receives
- `imap_host` - IMAP server settings
- `password` - Encrypted password
- `is_active` - Whether to process this account

## Form Processing Flow

1. **Cron Job Runs** - Every 5 minutes via `email_cron.php`
2. **Check Email Accounts** - Connect to each active IMAP account
3. **Find New Emails** - Search for unread emails from last 24 hours
4. **Parse Content** - Extract form data using regex patterns
5. **Create/Update Lead** - Use existing CRM lead system
6. **Add Notes** - Create detailed notes with form information
7. **Mark Processed** - Mark email as read and log results

## Form Type Mappings

### Estimate Forms
- **Lead Source**: 1 (Web)
- **Contact Type**: 1 (Homeowner)
- **Services**: full_system
- **Parsed Fields**: property details, fire protection needs, timeline, budget

### LTR Forms  
- **Lead Source**: 2 (Referral)
- **Contact Type**: 2 (Property Manager)
- **Services**: fire_retardant
- **Parsed Fields**: acreage, application type, agricultural details

### Contact Forms
- **Lead Source**: 4 (Email)
- **Contact Type**: 1 (Homeowner)  
- **Services**: general_inquiry
- **Parsed Fields**: basic contact info, inquiry type, message

## API Endpoints

### GET /api/email_forms.php/status
System status and statistics

### POST /api/email_forms.php/process
Trigger manual email processing

### POST /api/email_forms.php/forms/{type}
Submit form data directly (bypass email)

### GET /api/email_forms.php/processing
Recent processing records

### POST /api/email_forms.php/test/{account_id}
Test email connection for specific account

## Web Interface Features

- **Processing Statistics** - Total, successful, failed, today's counts
- **Manual Processing** - "Process Emails Now" button
- **Email Account Status** - View configuration and test connections
- **Recent Processing Log** - Detailed history with modal details
- **Error Monitoring** - Failed processing with error messages

## Security

- **API Key Authentication** - Required for all API access
- **Permission Checks** - Uses existing CRM security system
- **CSRF Protection** - Nonce validation for forms
- **Input Sanitization** - All form data is sanitized
- **Error Logging** - Comprehensive error tracking

## Monitoring & Troubleshooting

### Check Processing Logs
```sql
-- Recent processing
SELECT * FROM email_form_processing 
ORDER BY processed_at DESC LIMIT 10;

-- Failed processing
SELECT * FROM email_form_processing 
WHERE processing_status = 'failed' 
ORDER BY processed_at DESC;
```

### Check Cron Job
```bash
# View cron log
tail -f /home/democrm/logs/email_cron.log

# Test manually
php /home/democrm/scripts/email_cron.php
```

### Common Issues

**Emails not processing:**
1. Check email credentials in database
2. Verify IMAP settings
3. Check cron job is running
4. Review error logs

**Connection failures:**
1. Test connection via web interface
2. Verify IMAP host/port/encryption
3. Check firewall settings
4. Validate email passwords

**Parsing failures:**
1. Review raw email content in processing log
2. Check form mapping patterns
3. Verify email format matches expected patterns

## Integration with Existing CRM

The email processing system integrates seamlessly with the existing CRM:

- **Database Class** - Extends existing `Database` class
- **Leads System** - Uses existing `Leads` model for creation
- **Notes System** - Uses existing `Notes` model for lead notes
- **Security** - Uses existing `Security` class for permissions
- **Templates** - Follows existing template patterns
- **Multilingual** - Uses existing language system
- **Navigation** - Integrates with existing navigation

## Future Enhancements

- **OAuth Email Authentication** - Replace password-based auth
- **Advanced Form Parsing** - Machine learning for better extraction
- **File Attachment Processing** - Handle PDF attachments
- **Real-time Notifications** - Instant alerts for new leads
- **Advanced CRM Sync** - HubSpot, Salesforce integration
- **Email Templates** - Automated responses to form submissions
- **Analytics Dashboard** - Processing metrics and trends

## Support

For issues or questions:
1. Check processing logs in web interface
2. Review cron job logs
3. Test email connections
4. Verify database table structure
5. Check API endpoints for errors

The system is designed to be robust and self-healing, with comprehensive logging for troubleshooting.