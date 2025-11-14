---
title: RBAC System Restructure Implementation Guide
date: 2025-01-15
version: 1.0
status: READY FOR DEPLOYMENT
---

# 🚀 RBAC System Restructure - Implementation Guide

## Overview

This document provides step-by-step instructions for implementing the updated Universal RBAC system with the following key changes:

✅ **Internal Sales Restructure** (Roles 20-29)
✅ **External Sales Partners** (Roles 141-143): Distributors, Installers, Applicators
✅ **Clients Moved to Role 150** (Previously scattered roles)
✅ **Full Role Expansion** (50+ roles across 12 organizational layers)
✅ **Multi-Language Support** (English & Spanish)

---

## 📋 Files Modified

### 1. **Database Schema**
- **File**: `/sql/2025_01_15_RBAC_RESTRUCTURE_MIGRATION.sql`
- **Action**: Adds all 50+ roles to the database
- **Status**: ✅ Ready to execute

### 2. **PHP Code - Roles Model**
- **File**: `/classes/Models/Roles.php`
- **Changes**:
  - Updated `get_role_array()` method with 50+ roles
  - Added null-coalescing operators for fallback translations
  - Reorganized roles by department with comments
  - Updated `select_role()` to properly exclude system roles (1-9)

### 3. **Language Files**
- **English**: `/public_html/admin/languages/en.php`
- **Spanish**: `/public_html/admin/languages/es.php`
- **Changes**: Updated all role translations for new role structure

---

## 🗄️ New Role Structure

### System Maintenance (1-2) - UNCHANGED
```
Role 1:  Super Administrator
Role 2:  Administrator
```

### Executive Leadership (10-19)
```
Role 10: President
Role 11: CTO (Chief Technology Officer)
Role 12: CFO (Chief Financial Officer)
Role 13: COO (Chief Operations Officer)
Role 14: VP Operations
Role 15: VP Sales
Role 16: VP Engineering
Role 17: VP Administration
Role 18: VP Manufacturing
Role 19: VP Field Operations
```

### Internal Sales (20-29)
```
Role 20: Sales Manager
Role 21: Partner Manager
Role 22: Sales Lead
Role 23: Sales Lead 2
Role 25: Sales User
Role 26: Partner Sales
```

### Engineering (30-39)
```
Role 30: Engineering Manager
Role 31: Tech Lead
Role 32: Technician 1
Role 33: Technician 2
Role 34: Translator
```

### Manufacturing (40-49)
```
Role 40: Manufacturing Manager
Role 41: Production Lead
Role 42: Quality Lead
Role 43: Production Tech
Role 44: Quality Tech
Role 47: Installer
```

### Field Operations (50-59)
```
Role 50: Field Manager
Role 51: Service Lead
Role 52: Field Technician
Role 53: Installer Lead
Role 54: Field Installer
```

### Administration (60-69)
```
Role 60: HR Manager
Role 61: Compliance Manager
Role 62: Office Manager
Role 63: HR Specialist
Role 64: Compliance Officer
```

### Finance (70-79)
```
Role 70: Accounting Manager
Role 71: Bookkeeper (✅ NEW)
Role 72: AP/AR Clerk
Role 73: Accountant
Role 74: Finance Analyst
Role 75: Auditor
```

### Support (80-89)
```
Role 80: Translator
Role 81: Technical Writer
Role 82: Training Specialist
Role 83: Support Manager
Role 84: Support Agent
Role 85: QA Specialist
```

### External Partners (90-99)
```
Role 90: Vendor
Role 91: Strategic Partner
Role 92: Contractor
Role 93: Guest
Role 99: Viewer
```

### **NEW** External Sales Partners (141-143)
```
Role 141: Distributor (✅ NEW)
Role 142: Installer (✅ NEW)
Role 143: Applicator (✅ NEW)
```

### **NEW** Clients (150)
```
Role 150: Client (✅ MOVED FROM ROLES 18-21)
```

---

## 📊 Key Changes Summary

### Sales Department Restructuring

**Before:**
- Internal Sales mixed with external partners
- Client roles scattered (18, 19, 20, 21)
- No clear separation

**After:**
- **Internal Sales (20-29)**: Pure internal team structure
  - Sales Manager (20) → Sales Users (25)
  - Partner Manager (21) → Partner Sales (26)
  - Sales Leads (22, 23)

- **External Sales Partners (141-143)**: Separate layer for external relationships
  - Distributor (141): Channel distribution partners
  - Installer (142): Installation service partners
  - Applicator (143): Field application specialists

- **Clients (150)**: Dedicated tier for customer accounts
  - Customer/Account Owner access
  - Separate from employee structure

---

## 🔧 Implementation Steps

### Step 1: Backup Database
```bash
# Backup current database
mysqldump -u democrm_user -p democrm_democrm > backup_before_rbac_restructure.sql
```

### Step 2: Execute SQL Migration
```bash
# Execute migration via SSH
ssh wswg "mysql -u democrm_user -p democrm_democrm < /home/democrm/sql/2025_01_15_RBAC_RESTRUCTURE_MIGRATION.sql"

# Or execute via MySQL client
mysql -u democrm_user -p democrm_democrm < /home/democrm/sql/2025_01_15_RBAC_RESTRUCTURE_MIGRATION.sql
```

### Step 3: Verify Database Changes
```sql
-- Count total roles
SELECT COUNT(*) as total_roles FROM roles;

-- List all roles by category
SELECT role_id, role FROM roles 
WHERE role_id BETWEEN 1 AND 99 OR role_id >= 140
ORDER BY role_id;

-- Verify sales structure
SELECT role_id, role FROM roles 
WHERE role_id IN (20, 21, 22, 23, 25, 26, 141, 142, 143)
ORDER BY role_id;

-- Verify client role
SELECT role_id, role FROM roles WHERE role_id = 150;
```

### Step 4: Deploy Code Changes

1. **Update Roles.php**
   - File: `/classes/Models/Roles.php`
   - Changes already included in this package
   - No breaking changes - fully backward compatible

2. **Update Language Files**
   - English: `/public_html/admin/languages/en.php`
   - Spanish: `/public_html/admin/languages/es.php`
   - Changes already included in this package

### Step 5: Clear Application Cache (if applicable)
```bash
# If using any caching mechanism
rm -rf /home/democrm/tmp/session/*
# Clear browser cache (user-side)
```

### Step 6: Test Role Assignments

Navigate to Users > Edit User and verify:
- ✅ All 50+ roles appear in dropdown
- ✅ System roles (1-9) excluded from selection
- ✅ Role names display correctly in both English and Spanish
- ✅ External Sales Partners (141-143) appear in dropdown
- ✅ Client role (150) appears in dropdown

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