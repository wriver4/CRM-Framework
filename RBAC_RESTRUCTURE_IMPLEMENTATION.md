---
title: RBAC System Restructure Implementation Guide
date: 2025-11-17
version: 2.0
status: DOCUMENTATION UPDATED - Ready for Code Implementation
---

# 🚀 RBAC System Restructure - Implementation Guide (Updated)

## Overview

This document provides step-by-step instructions for implementing the consolidated RBAC system with the following key changes:

✅ **Executive Simplified** (Roles 10-14): 5 core C-suite roles
✅ **Department Consolidation** (Roles 30-99): Sales, Engineering, Manufacturing, Field Service, HR, Accounting, Support
✅ **Partners Consolidated** (Roles 100-159): Strategic Partner, Vendor, Distributor, Installer, Applicator, Contractor
✅ **Clients Clarified** (Roles 160-163): Standard, Restricted, Advanced, Status
✅ **Clean Hierarchy**: 32 active roles with clear role ranges
✅ **Multi-Language Support** (English & Spanish)

---

## 📋 Files Modified

### 1. **Database Schema**
- **File**: `/sql/migrations/2025_11_17_role_consolidation.sql`
- **Action**: Reorganizes roles to new consolidated structure (32 active roles)
- **Status**: ⏳ Ready for creation

### 2. **PHP Code - Roles Model**
- **File**: `/classes/Models/Roles.php`
- **Changes**:
  - Update `get_role_array()` method with new consolidated role structure
  - Update role constants for new role ID ranges
  - Update role name mappings
  - Ensure system roles (1-2) are excluded from role selections
  - Status: ⏳ Pending update

### 3. **Language Files**
- **English**: `/public_html/admin/languages/en.php`
- **Spanish**: `/public_html/admin/languages/es.php`
- **Changes**: Update all role translations for new role structure
- **Status**: ⏳ Pending update

---

## 🗄️ New Role Structure

### System Maintenance (1-2)
```
Role 1:  Super Administrator
Role 2:  Administrator
```

### Executive Leadership (10-14)
```
Role 10: President
Role 11: Vice President
Role 12: Chief Information Officer (CIO)
Role 13: Chief Technology Officer (CTO)
Role 14: Chief Marketing Officer (CMO)
```

### Sales Department (30-39)
```
Role 30: Sales Manager
Role 35: Sales Assistant
Roles 31-34, 36-39: Reserved
```

### Engineering Department (40-49)
```
Role 40: Engineering Manager
Role 41: Tech Lead
Role 42: Technician 1
Role 43: Technician 2
Roles 44-49: Reserved
```

### Manufacturing Department (50-59)
```
Role 50: Manufacturing Manager
Role 51: Manufacturing Tech 1
Role 52: Manufacturing Tech 2
Roles 53-59: Reserved
```

### HR & Administration Department (70-79)
```
Role 70: HR Manager
Role 72: Office Manager
Roles 71, 73-79: Reserved
```

### Accounting & Finance Department (80-89)
```
Role 80: Accounting Manager
Role 82: AP/AR Clerk
Roles 81, 83-89: Reserved
```

### Support & Training Department (90-99)
```
Role 90: Support Manager
Roles 91-99: Reserved
```

### External Partners (100-159)
```
Role 100: Strategic Partner
Role 110: Vendor
Role 120: Distributor
Role 130: Installer
Role 140: Applicator
Role 150: Contractor
Roles 101-109, 111-119, 121-129, 131-139, 141-149, 151-159: Reserved
```

### Client Accounts (160-163)
```
Role 160: Client Standard
Role 161: Client Restricted
Role 162: Client Advanced
Role 163: Client Status
```

---

## 📊 Key Changes Summary

### Complete Role Consolidation

**Before (Multiple Restructures):**
- Executive: 10-19 (10 roles)
- Sales: 20-29 (scattered)
- Engineering: 30-39
- Manufacturing: 7-9, 40-49 (fragmented)
- Field Service: 50-59
- HR: 60-69
- Accounting: 70-79
- Support: 80-89
- Partners: 90-99, 141-143 (scattered)
- Clients: 150-154

**After (Consolidated):**
- **System**: Roles 1-2 (Super Admin, Admin)
- **Executive**: Roles 10-14 (5 C-suite roles - simplified from 10)
- **Sales**: Roles 30-39 (Manager, Assistant, + reserves)
- **Engineering**: Roles 40-49 (Manager, Tech Lead, Technicians)
- **Manufacturing**: Roles 50-59 (Manager, Technicians)
- **Field Service**: Roles 60-69 (Manager, + reserves)
- **HR/Admin**: Roles 70-79 (Manager, Office Manager, + reserves)
- **Accounting/Finance**: Roles 80-89 (Manager, Clerk, + reserves)
- **Support**: Roles 90-99 (Manager, + reserves)
- **Partners**: Roles 100-159 (6 strategic partner types with reserved ranges)
- **Clients**: Roles 160-163 (4 client tier levels)

---

## 🔧 Implementation Steps

### Step 1: Backup Database
```bash
# Backup current database via SSH
ssh wswg "mysqldump -u democrm_democrm -p'b3J2sy5T4JNm60' democrm_democrm > /home/democrm/backup_rbac_2025_11_17.sql"
```

### Step 2: Create SQL Migration Script
Create `/sql/migrations/2025_11_17_role_consolidation.sql` with all role ID updates
- Status: ⏳ To be created in next phase

### Step 3: Execute SQL Migration
```bash
# Execute migration via SSH
ssh wswg "mysql -u democrm_democrm -p'b3J2sy5T4JNm60' democrm_democrm < /home/democrm/sql/migrations/2025_11_17_role_consolidation.sql"
```

### Step 4: Verify Database Changes
```sql
-- Count total active roles (should be 32)
SELECT COUNT(*) as total_roles FROM roles WHERE id >= 1 AND id <= 163;

-- List all roles by department
SELECT 
  CASE 
    WHEN id BETWEEN 1 AND 2 THEN 'System'
    WHEN id BETWEEN 10 AND 14 THEN 'Executive'
    WHEN id BETWEEN 30 AND 39 THEN 'Sales'
    WHEN id BETWEEN 40 AND 49 THEN 'Engineering'
    WHEN id BETWEEN 50 AND 59 THEN 'Manufacturing'
    WHEN id BETWEEN 60 AND 69 THEN 'Field Service'
    WHEN id BETWEEN 70 AND 79 THEN 'HR'
    WHEN id BETWEEN 80 AND 89 THEN 'Accounting'
    WHEN id BETWEEN 90 AND 99 THEN 'Support'
    WHEN id BETWEEN 100 AND 159 THEN 'Partners'
    WHEN id BETWEEN 160 AND 163 THEN 'Clients'
  END as department,
  COUNT(*) as count
FROM roles
GROUP BY department
ORDER BY MIN(id);

-- Verify executive roles (10-14)
SELECT id, rname FROM roles WHERE id BETWEEN 10 AND 14 ORDER BY id;

-- Verify partner roles (100, 110, 120, 130, 140, 150)
SELECT id, rname FROM roles WHERE id IN (100, 110, 120, 130, 140, 150) ORDER BY id;

-- Verify client roles (160-163)
SELECT id, rname FROM roles WHERE id BETWEEN 160 AND 163 ORDER BY id;
```

### Step 5: Deploy Code Changes

1. **Update Roles.php**
   - File: `/classes/Models/Roles.php`
   - Update `get_role_array()` method with new role structure
   - Update role constants for new ID ranges
   - Status: ⏳ Pending update

2. **Update Language Files**
   - English: `/public_html/admin/languages/en.php`
   - Spanish: `/public_html/admin/languages/es.php`
   - Add/update translations for all 32 active roles
   - Status: ⏳ Pending update

3. **Update Security/Permission Classes**
   - Check for hardcoded role ID checks
   - Update role hierarchy references
   - Status: ⏳ To be assessed

### Step 6: Clear Application Cache (if applicable)
```bash
# SSH into server and clear cache
ssh wswg "rm -rf /home/democrm/tmp/session/*"
# Clear browser cache (user-side)
```

### Step 7: Test Role Assignments

Navigate to Users > Edit User and verify:
- ✅ All 32 active roles appear in dropdown
- ✅ System roles (1-2) excluded from selection  
- ✅ Executive roles (10-14) visible
- ✅ Department roles (30-99) visible and grouped correctly
- ✅ Partner roles (100, 110, 120, 130, 140, 150) visible
- ✅ Client roles (160-163) visible
- ✅ Role names display correctly in both English and Spanish
- ✅ User role assignments still functional with migrated role IDs

---

## ✅ Verification Checklist

After implementation, verify:

- [ ] Database has 50+ roles
- [ ] SQL migration completed without errors
- [ ] Roles.php loads correctly (no PHP errors)
- [ ] Language files parse correctly
- [ ] User role dropdown shows all available roles
- [ ] System roles (1-9) NOT shown in user assignment dropdown
- [ ] English translations display correctly
- [ ] Spanish translations display correctly
- [ ] Sales structure visible (roles 20-29)
- [ ] External partners visible (roles 141-143)
- [ ] Client role visible (role 150)

---

## 🚨 Rollback Plan

If issues occur:

```bash
# 1. Restore database from backup
mysql -u democrm_user -p democrm_democrm < backup_before_rbac_restructure.sql

# 2. Git revert code changes
git checkout HEAD -- classes/Models/Roles.php
git checkout HEAD -- public_html/admin/languages/en.php
git checkout HEAD -- public_html/admin/languages/es.php

# 3. Clear cache
rm -rf /home/democrm/tmp/session/*
```

---

## 📝 Migration Records

### Roles Added
- **Executive Layer (10-19)**: 10 roles for C-suite and VPs
- **Internal Sales (20-29)**: 6 roles with clear internal team structure
- **External Sales Partners (141-143)**: 3 new roles for channel partners
- **Engineering (30-39)**: 5 roles including translator
- **Manufacturing (40-49)**: 6 roles including production and QC
- **Field Operations (50-59)**: 5 roles including installation
- **Administration (60-69)**: 5 roles including HR and compliance
- **Finance (70-79)**: 6 roles including bookkeeper and auditor
- **Support (80-89)**: 6 roles including QA and training
- **External Partners (90-99)**: 5 roles for vendor and contractor access
- **Clients (150)**: 1 role for customer accounts

**Total Active Roles**: 50+
**Reserved Slots**: 30+ for future expansion

### Language Support
- ✅ English (en.php): All 50+ roles translated
- ✅ Spanish (es.php): All 50+ roles translated

---

## 🔄 Post-Implementation

### User Role Migration
If you need to migrate existing users:

```sql
-- Example: Migrate all old "Client Restricted" (20) to new Client (150)
-- UPDATE users SET role_id = 150 WHERE role_id = 20;

-- IMPORTANT: Review before executing!
SELECT role_id, COUNT(*) FROM users GROUP BY role_id;
```

### Permission Assignment
The permission matrix is ready in the UNIVERSAL_RBAC_SYSTEM_PROPOSAL.md document.
Implement permissions based on role requirements.

---

## 📞 Support

For questions or issues:
1. Review UNIVERSAL_RBAC_SYSTEM_PROPOSAL.md for role definitions
2. Check database for role existence: `SELECT * FROM roles WHERE role_id = X;`
3. Verify language keys exist in language files
4. Check PHP error logs: `/home/democrm/logs/php_error.log`

---

## 📚 Additional Resources

- **UNIVERSAL_RBAC_SYSTEM_PROPOSAL.md**: Complete role definitions and organizational structure
- **SQL Migration Script**: `/sql/2025_01_15_RBAC_RESTRUCTURE_MIGRATION.sql`
- **Roles Model**: `/classes/Models/Roles.php`
- **Language Files**: 
  - `/public_html/admin/languages/en.php`
  - `/public_html/admin/languages/es.php`

---

**Status**: ✅ Ready for Production Deployment
**Version**: 1.0
**Date**: 2025-01-15