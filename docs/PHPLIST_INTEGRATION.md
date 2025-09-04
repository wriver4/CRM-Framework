# phpList Integration Documentation

## Overview

This document describes the phpList integration for the DemoCRM system. The integration provides automated email marketing list management by syncing lead data with phpList when users opt-in for updates.

## Architecture

### Hybrid Approach
The integration uses a **hybrid approach** combining immediate flagging with batch processing:

1. **Immediate Flagging**: When a lead is created with `get_updates = 1`, a subscriber record is immediately created in the `phplist_subscribers` table with `sync_status = 'pending'`
2. **Batch Processing**: A cron job runs every 15 minutes (configurable) to sync pending subscribers with phpList via API

### Database Tables

#### `phplist_subscribers`
Main table for tracking phpList subscribers and sync status:
- `lead_id` - Foreign key to leads table
- `contact_id` - Foreign key to contacts table (optional)
- `phplist_subscriber_id` - phpList subscriber ID after successful sync
- `email` - Email address (copied from lead for quick access)
- `sync_status` - Current sync status (pending, synced, failed, skipped, unsubscribed)
- `sync_attempts` - Number of sync attempts made
- `phplist_lists` - JSON array of phpList list IDs for segmentation
- `segmentation_data` - JSON data for list segmentation
- `error_message` - Last error message if sync failed

#### `phplist_config`
Configuration table for phpList integration settings:
- API credentials and connection settings
- Sync frequency and batch size settings
- List mapping for geographic and service-based segmentation
- Debug and advanced options

#### `phplist_sync_log`
Detailed logging table for sync operations:
- Individual sync operation results
- API response data
- Processing times
- Error details for debugging

## Features

### Automatic List Segmentation
Subscribers are automatically assigned to multiple phpList lists based on:

1. **Geographic Segmentation**: Based on `form_state` (e.g., US-CA, US-TX, US-CO)
2. **Service Segmentation**: Based on `structure_type` (residential types)
3. **Source Segmentation**: Based on `hear_about` (lead source tracking)
4. **Default List**: All subscribers are added to a default list

### Configuration Management
- Web-based admin interface for configuration
- API connection testing
- Sync status monitoring
- Subscriber statistics dashboard

### Error Handling & Retry Logic
- Maximum retry attempts (configurable, default: 3)
- Detailed error logging
- Manual retry capability for failed syncs
- Graceful degradation (lead creation never fails due to phpList issues)

## Installation

### 1. Database Migration
Run the database migration to create required tables:

```bash
php /path/to/democrm/sql/migrations/run_phplist_migration.php
```

### 2. Configure phpList Settings
1. Access the admin interface: `/admin/phplist/config.php`
2. Configure API credentials:
   - phpList API URL
   - API Username
   - API Password
3. Set up list mappings for segmentation
4. Enable sync and set frequency

### 3. Set Up Cron Job
Add the following cron job to sync subscribers every 15 minutes:

```bash
*/15 * * * * php /path/to/democrm/scripts/phplist_sync.php
```

### 4. Test Integration
1. Create a new lead with `get_updates = 1`
2. Check that a subscriber record is created in `phplist_subscribers`
3. Run the sync script manually to test API integration
4. Verify subscriber appears in phpList

## Configuration Options

### API Settings
- **phplist_api_url**: Full URL to phpList admin directory
- **phplist_api_username**: API username
- **phplist_api_password**: API password (encrypted in database)
- **api_timeout_seconds**: API request timeout (default: 30)

### Sync Settings
- **sync_enabled**: Enable/disable sync (1/0)
- **sync_frequency_minutes**: How often cron job runs (default: 15)
- **max_sync_attempts**: Maximum retry attempts (default: 3)
- **batch_size**: Records processed per sync batch (default: 50)

### List Mapping (JSON Configuration)
```json
{
  "phplist_geographic_lists": {
    "US-CA": 2,
    "US-TX": 3,
    "US-CO": 4
  },
  "phplist_service_lists": {
    "1": 10,
    "2": 11
  },
  "phplist_source_lists": {
    "Internet search": 20,
    "Referral": 21
  }
}
```

### Advanced Settings
- **debug_mode**: Enable detailed logging (1/0)
- **auto_create_lists**: Automatically create missing lists (1/0)
- **phplist_default_list_id**: Default list for all subscribers

## Usage

### For Lead Creation
The integration is automatic when creating leads:

1. User fills out lead form
2. If `get_updates` checkbox is checked, subscriber record is created
3. Cron job syncs subscriber to phpList within 15 minutes
4. User receives confirmation emails from phpList

### Admin Management
Administrators can:

1. **View Subscribers**: `/admin/phplist/subscribers.php`
   - Filter by sync status
   - Search by email
   - View sync attempts and errors
   - Manually retry failed syncs

2. **Configuration**: `/admin/phplist/config.php`
   - Update API settings
   - Test API connection
   - View subscriber statistics
   - Modify sync settings

3. **Sync Logs**: `/admin/phplist/sync_log.php`
   - View detailed sync operation logs
   - Debug API issues
   - Monitor performance

## API Integration

### phpList API Requirements
- phpList 3.x with REST API enabled
- API user with appropriate permissions
- HTTPS recommended for security

### API Operations
- **Add Subscriber**: Creates new subscriber with attributes
- **Update Subscriber**: Updates existing subscriber information
- **Get Subscriber**: Retrieves subscriber details
- **List Management**: Retrieves available lists

### Custom Attributes Mapping
The integration maps CRM data to phpList attributes:
- `attribute1`: First Name
- `attribute2`: Last Name
- `attribute3`: State
- `attribute4`: City
- `attribute5`: Lead Source
- `attribute6`: Business Name

## Monitoring & Troubleshooting

### Sync Status Monitoring
Check subscriber sync status in the admin interface:
- **Pending**: Waiting for sync
- **Synced**: Successfully synced to phpList
- **Failed**: Sync failed (check error message)
- **Skipped**: Intentionally skipped (e.g., invalid email)

### Common Issues

#### API Connection Failures
- Verify phpList URL and credentials
- Check firewall/network connectivity
- Ensure phpList API is enabled
- Test connection using admin interface

#### Sync Failures
- Check error messages in subscriber records
- Review sync logs for detailed information
- Verify phpList list IDs exist
- Check API rate limits

#### Performance Issues
- Reduce batch size if timeouts occur
- Increase API timeout setting
- Monitor server resources during sync
- Consider running sync less frequently

### Debug Mode
Enable debug mode for detailed logging:
1. Set `debug_mode = 1` in configuration
2. Check PHP error logs for detailed API communication
3. Review sync logs for processing times
4. Monitor cron job output

## Security Considerations

### Data Protection
- API passwords are marked for encryption in database
- HTTPS recommended for API communication
- Subscriber data is only synced with explicit opt-in
- Audit logging tracks all sync operations

### Access Control
- Admin interface requires login
- Configuration changes are logged
- Subscriber management requires admin privileges

## Maintenance

### Regular Tasks
1. **Monitor Sync Status**: Check for failed syncs weekly
2. **Review Error Logs**: Address recurring API issues
3. **Update List Mappings**: Add new geographic/service segments
4. **Performance Monitoring**: Check sync processing times

### Backup Considerations
- Include phpList tables in database backups
- Export subscriber data before major changes
- Test restore procedures including phpList integration

## Future Enhancements

### Planned Features
1. **Unsubscribe Handling**: Sync unsubscribe requests back to CRM
2. **Campaign Integration**: Track email campaign performance
3. **Advanced Segmentation**: Dynamic list assignment based on lead behavior
4. **Bulk Operations**: Mass subscriber management tools
5. **Webhook Integration**: Real-time sync instead of batch processing

### Customization Options
- Custom attribute mapping
- Additional segmentation criteria
- Integration with other email platforms
- Advanced reporting and analytics

## Support

### Documentation
- API documentation: phpList REST API docs
- Configuration examples in `/docs/examples/`
- Troubleshooting guide in admin interface

### Logging
- Application logs: `/logs/php_errors.log`
- Sync logs: Database table `phplist_sync_log`
- Audit logs: Database table `audit`
- Cron job logs: System cron logs

For technical support, check the sync logs and error messages first, then review the configuration settings and API connectivity.