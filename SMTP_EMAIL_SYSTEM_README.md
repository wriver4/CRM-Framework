# SMTP Email System for Lead Thank You Emails

## Overview
This system automatically sends thank you emails to leads when they are created in the CRM. Each of the 6 lead source types has a customized email template with links to NextCloud resources.

## Implementation Summary

### 1. Database Tables Created ✅

#### `smtp_config` Table
Stores SMTP server configurations with support for:
- Multiple configurations per user
- System-wide default configuration (user_id = NULL)
- Per-user default configurations
- Active/inactive status
- Encrypted password storage (base64)
- Full SMTP settings (host, port, encryption, credentials)
- From/Reply-To email configuration

#### `email_send_log` Table
Comprehensive logging of all outgoing emails:
- Links to smtp_config, leads, contacts, and users
- Stores complete email content (HTML and plain text)
- Tracks status: pending, sent, failed, bounced
- Error message logging for debugging
- Timestamps for creation and sending

### 2. Admin Interface ✅

**Location:** `/admin/email/smtp_config.php`

Features:
- Add/Edit/Delete SMTP configurations
- User-specific or system-wide configurations
- Set default configuration per user
- Test SMTP configuration
- Active/inactive toggle
- Secure password handling (never displayed after saving)

### 3. Core Classes Created ✅

#### `EmailService` Class
**Location:** `/classes/Utilities/EmailService.php`

Key Methods:
- `send_email($params)` - Send emails via configured SMTP
- `get_smtp_config($user_id, $smtp_config_id)` - Retrieve SMTP configuration with fallback logic
- `get_lead_email_history($lead_id)` - View email history for a lead
- `get_contact_email_history($contact_id)` - View email history for a contact
- `test_smtp_config($smtp_config_id, $test_email)` - Test SMTP configuration

Features:
- Automatic SMTP config selection (user-specific → system default)
- Multipart emails (HTML + plain text)
- Comprehensive error handling
- Audit logging integration
- Email send status tracking

#### `LeadEmailTemplates` Class
**Location:** `/classes/Utilities/LeadEmailTemplates.php`

Generates customized email templates for 6 lead sources:
1. **Web Estimate** - Thank you for estimate request
2. **LTR Form** - Thank you for LTR submission
3. **Contact Form** - Thank you for contacting us
4. **Phone Inquiry** - Follow-up after phone call
5. **Cold Call** - Follow-up after cold call
6. **In Person** - Thank you for in-person meeting

Each template includes:
- Personalized greeting (uses first name)
- Source-specific messaging
- NextCloud download links section
- Contact information section
- Professional HTML styling
- Plain text alternative

### 4. Integration with Lead Creation ✅

**Location:** `/public_html/leads/post.php`

Email sending is triggered automatically when:
- A new lead is created
- The lead has a valid email address
- Email sending happens AFTER successful lead creation
- Failures don't prevent lead creation (logged for review)

Process flow:
1. Lead created successfully
2. Email template generated based on lead source
3. Email sent via configured SMTP
4. Result logged to `email_send_log` table
5. Audit log entry created

### 5. Translation Keys Added ✅

**Location:** `/public_html/admin/languages/en.php`

Added 50+ translation keys for:
- Email subjects (per lead source)
- Email body content (per lead source)
- Common email elements (greetings, footers, CTAs)
- Contact information placeholders
- Download section content

### 6. Dependencies Installed ✅

**PHPMailer 6.9+** installed via Composer
- Industry-standard email library
- Full SMTP support with TLS/SSL
- Multipart email support
- Comprehensive error handling

## Configuration Required

### Step 1: Configure SMTP Server

1. Navigate to `/admin/email/smtp_config.php`
2. Click "Add Configuration"
3. Fill in SMTP details:
   - **Configuration Name:** e.g., "Main SMTP Server"
   - **User:** Leave empty for system-wide default
   - **SMTP Host:** Your SMTP server (e.g., smtp.gmail.com)
   - **SMTP Port:** 587 (TLS) or 465 (SSL)
   - **Encryption:** TLS or SSL
   - **Username:** SMTP username
   - **Password:** SMTP password
   - **From Email:** Email address to send from
   - **From Name:** Company name
   - **Reply-To Email:** (Optional) Different reply-to address
   - **Set as default:** ✓ Check this box
   - **Active:** ✓ Check this box
4. Click "Save Configuration"

### Step 2: Configure Email Content

Edit `/public_html/admin/languages/en.php` and update these keys:

```php
// Contact information (displayed in emails)
'email_contact_phone' => '+1 (555) 123-4567',
'email_contact_email' => 'info@yourcompany.com',
'email_contact_website' => 'https://www.yourcompany.com',

// NextCloud download link
'email_nextcloud_link' => 'https://nextcloud.yourcompany.com/s/shared-link',
```

### Step 3: Test Email Sending

1. Go to `/admin/email/smtp_config.php`
2. Click "Edit" on your SMTP configuration
3. Use the test function (if implemented) or:
4. Create a test lead with your email address
5. Check if you receive the thank you email

## Email Templates by Lead Source

### 1. Web Estimate (Source ID: 1)
**Subject:** "Thank You for Your Web Estimate Request"
- Thanks for estimate request
- Team reviewing project details
- Will contact within 1-2 business days

### 2. LTR Form (Source ID: 2)
**Subject:** "Thank You for Your LTR Form Submission"
- Thanks for LTR submission
- Excited to learn about project
- Will reach out shortly

### 3. Contact Form (Source ID: 3)
**Subject:** "Thank You for Contacting Us"
- Thanks for reaching out
- Message received
- Response within 24 hours

### 4. Phone Inquiry (Source ID: 4)
**Subject:** "Thank You for Your Phone Inquiry"
- Thanks for speaking today
- Enjoyed learning about project
- Follow-up with resources

### 5. Cold Call (Source ID: 5)
**Subject:** "Great Speaking With You Today"
- Pleasure speaking today
- Additional information provided
- Schedule detailed consultation

### 6. In Person (Source ID: 6)
**Subject:** "Thank You for Meeting With Us"
- Thanks for in-person meeting
- Great discussing face-to-face
- Follow-up resources provided

## Monitoring & Troubleshooting

### View Email Send History

**For a specific lead:**
```php
$emailService = new EmailService();
$history = $emailService->get_lead_email_history($lead_id);
```

**For a specific contact:**
```php
$emailService = new EmailService();
$history = $emailService->get_contact_email_history($contact_id);
```

### Check Email Send Logs

Query the database:
```sql
-- Recent email sends
SELECT * FROM email_send_log 
ORDER BY created_at DESC 
LIMIT 50;

-- Failed emails
SELECT * FROM email_send_log 
WHERE status = 'failed' 
ORDER BY created_at DESC;

-- Emails for specific lead
SELECT * FROM email_send_log 
WHERE lead_id = 123 
ORDER BY created_at DESC;
```

### Common Issues

**Issue:** No emails being sent
- Check SMTP configuration is active and set as default
- Verify SMTP credentials are correct
- Check error logs: `/var/log/php-errors.log`
- Review `email_send_log` table for error messages

**Issue:** Emails going to spam
- Configure SPF, DKIM, and DMARC records for your domain
- Use a reputable SMTP service (SendGrid, Mailgun, etc.)
- Ensure "From" email matches your domain

**Issue:** SMTP authentication failed
- Verify username and password are correct
- Check if 2FA is enabled (may need app-specific password)
- Confirm SMTP port and encryption match server requirements

## Cron Job Integration

The email system will automatically work with cron-created leads since it's integrated into `/leads/post.php`. When the cron job creates leads from incoming emails, thank you emails will be sent automatically.

**Cron Script:** `/scripts/email_cron.php`
- Already processes incoming emails
- Creates leads automatically
- Will trigger thank you emails via post.php integration

## Security Considerations

1. **Password Encryption:** SMTP passwords are base64 encoded (consider upgrading to stronger encryption)
2. **Access Control:** Only admins can configure SMTP settings
3. **Audit Logging:** All email sends are logged with full audit trail
4. **Error Handling:** Email failures don't prevent lead creation
5. **Input Sanitization:** All email content is sanitized before sending

## Future Enhancements

Potential improvements:
- [ ] Email template editor in admin interface
- [ ] Email scheduling/queuing system
- [ ] Bounce handling and email validation
- [ ] Email open/click tracking
- [ ] A/B testing for email templates
- [ ] Attachment support
- [ ] Email signature management
- [ ] Multi-language email templates (Spanish)
- [ ] Email preview before sending
- [ ] Bulk email sending for campaigns

## Files Modified/Created

### Created:
- `/sql/migrations/2025_01_15_smtp_configuration.sql`
- `/public_html/admin/email/smtp_config.php`
- `/classes/Utilities/EmailService.php`
- `/classes/Utilities/LeadEmailTemplates.php`

### Modified:
- `/composer.json` (added PHPMailer dependency)
- `/public_html/admin/languages/en.php` (added email translation keys)
- `/public_html/leads/post.php` (integrated email sending)

## Support

For issues or questions:
1. Check error logs in `email_send_log` table
2. Review PHP error logs
3. Test SMTP configuration in admin interface
4. Verify translation keys are properly configured
5. Ensure PHPMailer is installed: `composer show phpmailer/phpmailer`

---

**Implementation Date:** January 15, 2025
**Status:** ✅ Complete - Ready for Testing
**Next Step:** Configure SMTP credentials and test with real email