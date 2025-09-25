# Stage System Upgrade Documentation

## Overview

This document describes the new stage numbering system implemented to provide numeric spaces between stages and reorganize the sales pipeline flow.

## Key Changes

### 1. New Stage Numbering (10-unit increments)

| New Stage | Old Stage | Stage Name                | Notes                                       |
| --------- | --------- | ------------------------- | ------------------------------------------- |
| 10        | 1         | Lead                      | Entry point                                 |
| 20        | 2         | Pre-Qualification         |                                             |
| 30        | 3         | Qualified                 |                                             |
| 40        | 4         | Referral                  | **Trigger Stage**                           |
| 50        | 5         | Prospect                  | **Trigger Stage**                           |
| 60        | 6         | Prelim Design             |                                             |
| 70        | 7         | Manufacturing Estimate    |                                             |
| 80        | 8         | Contractor Estimate       |                                             |
| 90        | 9         | Completed Estimate        |                                             |
| 100       | 10        | Prospect Response         |                                             |
| 110       | 11        | Closing Conference        |                                             |
| 120       | 12        | Potential Client Response |                                             |
| **130**   | **14**    | **Closed Won**            | **Moved before Contracting**                |
| **140**   | **15**    | **Closed Lost**           | **Moved before Contracting, Trigger Stage** |
| **150**   | **13**    | **Contracting**           | **Moved after Won/Lost**                    |

### 2. Module Stage Filtering

#### Leads Module
Shows stages: 10, 20, 30, 40, 50, 140
- Lead through Prospect
- Plus Closed Lost for final disposition

#### Prospects Module  
Shows stages: 50, 60, 70, 80, 90, 100, 110, 120, 150
- Prospect through Contracting
- Excludes Won/Lost (handled at Potential Client Response)

#### Referrals Module
Shows stage: 40
- Referral stage only

#### Contracting Module
Shows stage: 150
- Contracting stage only

### 3. Stage Progression Logic

From **Lead (10)** can go to:
- 20 (Pre-Qualification)
- 30 (Qualified) 
- 40 (Referral) - **Trigger**
- 50 (Prospect) - **Trigger**
- 140 (Closed Lost) - **Trigger**

### 4. Trigger Stages

These stages trigger special actions (to be implemented):
- **40 (Referral)**: Referral processing actions
- **50 (Prospect)**: Prospect conversion actions  
- **140 (Closed Lost)**: Loss analysis and cleanup actions

## Implementation Files

### Core Files
- `/scripts/stage_remapping.php` - **Developer-editable configuration**
- `/scripts/migrate_stage_numbering.php` - Database migration script
- `/classes/Models/Leads.php` - Updated to use new system

### Updated Module Files
- `/public_html/leads/get.php` - Uses new lead stage filtering
- `/public_html/prospects/get.php` - Uses new prospect stage filtering
- `/public_html/referrals/get.php` - Uses new referral stage filtering
- `/public_html/contracting/get.php` - Uses new contracting stage filtering

### Testing
- `/scripts/test_stage_system.php` - Comprehensive test suite

## Migration Process

### 1. Test the System (Recommended)
```bash
cd /path/to/democrm
php scripts/test_stage_system.php
```

### 2. Dry Run Migration
```bash
php scripts/migrate_stage_numbering.php --dry-run
```

### 3. Execute Migration
```bash
php scripts/migrate_stage_numbering.php
```

### 4. Force Migration (Skip Confirmations)
```bash
php scripts/migrate_stage_numbering.php --force
```

## Developer Customization

### Modifying Stage Mappings

Edit `/scripts/stage_remapping.php` to customize:

```php
public static function getNewStageMapping() {
    return [
        10 => ['name' => 'Lead', 'old_stage' => 1],
        // Add new stages with available numbers (11-19, 21-29, etc.)
        15 => ['name' => 'Hot Lead', 'old_stage' => null], // New stage
        // ... rest of stages
    ];
}
```

### Adding New Stages

1. Choose an available number (e.g., 15, 25, 35, etc.)
2. Add to `getNewStageMapping()`
3. Update `getNewStageProgressions()` 
4. Update module filters in `getModuleStageFilters()`
5. Run migration script

### Modifying Module Filters

```php
public static function getModuleStageFilters() {
    return [
        'leads' => [10, 20, 30, 40, 50, 140],
        'prospects' => [50, 60, 70, 80, 90, 100, 110, 120, 150],
        // Add custom modules
        'hot_leads' => [15, 25], // Example custom module
    ];
}
```

## Benefits

1. **Numeric Spaces**: Room for 9 new stages between each existing stage
2. **Logical Flow**: Won/Lost decisions come before Contracting
3. **Maintainable**: Single configuration file for all stage logic
4. **Testable**: Comprehensive test suite ensures system integrity
5. **Flexible**: Easy to add new stages and modify progressions

## Rollback Plan

If issues arise, the old stage numbers are preserved in the mapping. A rollback script can be created by reversing the migration logic.

## Support

For questions or issues with the new stage system:
1. Run the test suite to identify problems
2. Check the migration logs
3. Review this documentation
4. Examine the stage remapping configuration