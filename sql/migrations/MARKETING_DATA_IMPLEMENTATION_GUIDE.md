# Marketing Data Implementation Guide

## Overview

This guide implements a dedicated `lead_marketing_data` table to handle "How Did You Hear About Us" marketing attribution data for leads, replacing the current approach of storing this data directly in the `leads` table.

## Current vs. Proposed Architecture

### Current Approach (leads table)
```sql
-- Current fields in leads table
`hear_about` varchar(20) DEFAULT NULL,
`hear_about_other` varchar(255) DEFAULT NULL,
```

**Limitations:**
- Limited to single marketing channel per lead
- Checkbox arrays get serialized as strings
- Difficult to generate marketing attribution reports
- No support for multi-touch attribution
- Limited space for campaign tracking

### Proposed Approach (separate table)
```sql
-- New lead_marketing_data table
CREATE TABLE `lead_marketing_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lead_id` int(11) NOT NULL,
  `marketing_channel` varchar(50) NOT NULL,
  `marketing_channel_other` varchar(255) DEFAULT NULL,
  `attribution_weight` decimal(3,2) DEFAULT 1.00,
  `campaign_source` varchar(100) DEFAULT NULL,
  `referral_details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_lead_id` (`lead_id`),
  FOREIGN KEY (`lead_id`) REFERENCES `leads`(`id`) ON DELETE CASCADE
);
```

**Benefits:**
- ✅ Supports multiple marketing channels per lead
- ✅ Proper handling of checkbox arrays
- ✅ Multi-touch attribution with weights
- ✅ Enhanced reporting capabilities
- ✅ Campaign tracking support
- ✅ Referral details storage
- ✅ Better data normalization

## Implementation Steps

### Step 1: Create the Marketing Data Table

Run the SQL migration:
```bash
# Execute in phpMyAdmin or MySQL client
/sql/migrations/create_lead_marketing_data_table.sql
```

This will:
- Show current marketing fields in leads table
- Create the new `lead_marketing_data` table
- Add proper indexes and foreign key constraints
- Display the final table structure

### Step 2: Migrate Existing Data

Run the data migration:
```bash
# Execute in phpMyAdmin or MySQL client
/sql/migrations/migrate_existing_marketing_data.sql
```

This will:
- Analyze current marketing data distribution
- Migrate existing `hear_about` and `hear_about_other` data
- Map legacy values to standardized marketing channels
- Provide validation and summary reports

### Step 3: Integrate PHP Class

The `LeadMarketingData` class is located at:
```
/classes/Models/LeadMarketingData.php
```

**Key Methods:**
- `createMarketingData($leadId, $marketingChannels, $otherDetails)` - Create marketing data for new leads
- `getMarketingDataByLead($leadId)` - Retrieve marketing data for a lead
- `updateMarketingData($leadId, $marketingChannels, $otherDetails)` - Update marketing data
- `getMarketingAttributionReport($startDate, $endDate)` - Generate attribution reports
- `getMultiTouchLeads()` - Find leads with multiple marketing touchpoints

### Step 4: Update Form Processing

Modify `/public_html/leads/post.php` to use the new system:

```php
// Add near the top after other includes
require_once dirname(__DIR__, 2) . '/classes/Models/LeadMarketingData.php';

// Process marketing data separately from main lead data
$marketingChannels = $_POST['hear_about'] ?? [];
$marketingOtherDetails = sanitize_input($_POST['hear_about_other'] ?? '');

// Ensure marketing channels is an array
if (!is_array($marketingChannels)) {
    $marketingChannels = !empty($marketingChannels) ? [$marketingChannels] : [];
}

// After successful lead creation
if ($result['success']) {
    try {
        $leadMarketingData = new LeadMarketingData();
        $marketingSuccess = $leadMarketingData->createMarketingData(
            $result['lead_id'], 
            $marketingChannels, 
            $marketingOtherDetails
        );
        
        if ($marketingSuccess) {
            // Log successful marketing data creation
            $audit->log(/* ... audit parameters ... */);
        }
    } catch (Exception $e) {
        error_log("Marketing data error: " . $e->getMessage());
    }
}
```

## Marketing Channel Mapping

The system maps legacy values to standardized channels:

| Legacy Value      | Standardized Channel | Description                      |
| ----------------- | -------------------- | -------------------------------- |
| `Mass mailing`    | `mass_mailing`       | Direct mail campaigns            |
| `TV/radio ad`     | `tv_radio`           | Television and radio advertising |
| `Internet search` | `internet`           | Search engines, online ads       |
| `Neighbor/friend` | `neighbor`           | Word of mouth referrals          |
| `Trade show`      | `trade_show`         | Industry events and exhibitions  |
| `Other`           | `other`              | Custom marketing channels        |

## Form Integration

The existing form in `/leads/new.php` will continue to work without changes:

```html
<!-- The checkbox array structure is preserved -->
<input class="form-check-input" type="checkbox" 
       name="hear_about[]" value="mass_mailing" id="hear_mass_mailing">
<input class="form-check-input" type="checkbox" 
       name="hear_about[]" value="tv_radio" id="hear_tv_radio">
<!-- etc. -->

<input type="text" name="hear_about_other" id="hear_about_other">
```

## Reporting Capabilities

### Marketing Attribution Report
```php
$leadMarketingData = new LeadMarketingData();
$report = $leadMarketingData->getMarketingAttributionReport('2024-01-01', '2024-12-31');

// Results include:
// - marketing_channel
// - lead_count
// - total_attribution
// - avg_attribution
// - first/last_occurrence
```

### Multi-Touch Attribution
```php
$multiTouchLeads = $leadMarketingData->getMultiTouchLeads();

// Results include leads with multiple marketing touchpoints
// - lead_id, lead_number, full_name
// - marketing_touchpoints (count)
// - marketing_channels (comma-separated)
```

## Display Integration

### Lead Edit/View Pages
```php
function displayLeadMarketingData($leadId, $lang) {
    $leadMarketingData = new LeadMarketingData();
    $marketingData = $leadMarketingData->getMarketingDataByLead($leadId);
    
    foreach ($marketingData as $data) {
        $channelName = $leadMarketingData->getMarketingChannelOptions($lang)[$data['marketing_channel']];
        echo '<div class="marketing-channel-item">';
        echo '<strong>' . htmlspecialchars($channelName) . '</strong>';
        
        if (!empty($data['marketing_channel_other'])) {
            echo ' - ' . htmlspecialchars($data['marketing_channel_other']);
        }
        
        if ($data['attribution_weight'] < 1.00) {
            echo ' <small>(' . round($data['attribution_weight'] * 100) . '% attribution)</small>';
        }
        echo '</div>';
    }
}
```

## Backward Compatibility

### Phase 1: Parallel Operation
- Keep original `hear_about` fields in leads table
- New leads use both old and new systems
- Gradually migrate displays to use new table

### Phase 2: Migration
- Update all forms and displays to use new system
- Verify all functionality works correctly
- Keep old fields for emergency rollback

### Phase 3: Cleanup
- Remove old `hear_about` fields from leads table
- Update database schema documentation
- Remove legacy code references

## Testing Checklist

### Database Testing
- [ ] Run table creation script successfully
- [ ] Verify foreign key constraints work
- [ ] Run data migration script
- [ ] Validate migrated data accuracy
- [ ] Test cascade delete functionality

### Application Testing
- [ ] Test lead creation with marketing data
- [ ] Test lead editing with marketing data updates
- [ ] Test marketing attribution reports
- [ ] Test multi-touch lead identification
- [ ] Verify form submission handling

### Integration Testing
- [ ] Test with existing phpList integration
- [ ] Verify audit logging works
- [ ] Test error handling and logging
- [ ] Validate multilingual support

## Performance Considerations

### Indexes
The table includes optimized indexes:
- `idx_lead_id` - Fast lead lookups
- `idx_marketing_channel` - Channel-based reporting
- `idx_created_at` - Time-based queries

### Query Optimization
- Use prepared statements for all operations
- Implement proper JOIN strategies for reports
- Consider caching for frequently accessed data

## Security Considerations

### Data Validation
- Sanitize all input data
- Validate marketing channel values against allowed list
- Implement proper SQL injection protection

### Access Control
- Maintain existing role-based permissions
- Audit all marketing data changes
- Implement proper error handling

## Maintenance

### Regular Tasks
- Monitor table growth and performance
- Review marketing channel effectiveness
- Clean up old or invalid data
- Update channel mappings as needed

### Monitoring
- Track marketing data creation success rates
- Monitor attribution report performance
- Log and review integration errors

## Migration Timeline

### Week 1: Database Setup
- Create new table structure
- Migrate existing data
- Validate migration accuracy

### Week 2: Code Integration
- Implement PHP class
- Update form processing
- Add display functions

### Week 3: Testing
- Comprehensive testing
- Performance validation
- User acceptance testing

### Week 4: Deployment
- Production deployment
- Monitor for issues
- Gather user feedback

## Support and Troubleshooting

### Common Issues
1. **Foreign key constraint errors**: Ensure leads table has proper ID structure
2. **Migration data mismatches**: Review channel mapping logic
3. **Performance issues**: Check index usage and query optimization

### Logging
All marketing data operations are logged for troubleshooting:
- Lead creation with marketing data
- Marketing data updates
- Attribution report generation
- Integration errors

## Conclusion

This implementation provides a robust, scalable solution for marketing attribution tracking that:
- Supports complex marketing scenarios
- Enables detailed reporting and analysis
- Maintains backward compatibility
- Follows database best practices
- Integrates seamlessly with existing CRM functionality

The separate table approach ensures better data organization, enhanced reporting capabilities, and future scalability for advanced marketing attribution needs.