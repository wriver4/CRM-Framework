---
title: RBAC Restructure - Quick Reference Card
date: 2025-11-17
---

# ⚡ Quick Reference - RBAC Restructure (Updated)

## ✅ What's Ready

- ✅ Role Hierarchy Chart: `/ROLE_HIERARCHY_CHART.md`
- ✅ SQL Migration Script: Ready for creation
- ✅ PHP Updates: Pending (Roles.php, Security.php)
- ✅ Language Updates: Pending (en.php, es.php)
- ✅ Documentation: Updated and consolidated

---

## 🎯 Major Changes in New Structure

### 1️⃣ Executive Simplified (10-14)
```
President (10)
├─ Vice President (11)
├─ Chief Information Officer (12)
├─ Chief Technology Officer (13)
└─ Chief Marketing Officer (14)
```

### 2️⃣ Department Consolidation (30-99)
```
Sales (30-39)           Engineering (40-49)      Manufacturing (50-59)
Field Service (60-69)   HR (70-79)               Accounting (80-89)
Support (90-99)
```

### 3️⃣ External Partners Consolidated (100-159)
```
Strategic Partner (100)    Vendor (110)           Distributor (120)
Installer (130)            Applicator (140)       Contractor (150)
```

### 4️⃣ Clients Clarified (160-163)
```
Client Standard (160)      Client Restricted (161)
Client Advanced (162)      Client Status (163)
```

---

## 🚀 Deploy in 3 Steps

### Step 1: Backup
```bash
ssh wswg "mysqldump -u democrm_democrm -p'b3J2sy5T4JNm60' democrm_democrm > /home/democrm/backup_rbac_2025_11_17.sql"
```

### Step 2: Execute SQL
```bash
ssh wswg "mysql -u democrm_democrm -p'b3J2sy5T4JNm60' democrm_democrm < /home/democrm/sql/migrations/2025_11_17_role_consolidation.sql"
```

### Step 3: Verify
```bash
ssh wswg "mysql -u democrm_democrm -p'b3J2sy5T4JNm60' democrm_democrm -e 'SELECT COUNT(*) as total_roles FROM roles;'"
# Expected: 32+ active roles
```

---

## 🧪 Quick Test

In browser:
1. Go to: **Users → Edit User**
2. Open **Role** dropdown
3. Verify:
   - ✅ 32+ roles visible
   - ✅ Roles 1-9 NOT visible
   - ✅ Sales roles (30-35) visible
   - ✅ Partner roles (100, 110, 120, 130, 140, 150) visible
   - ✅ Client roles (160-163) visible

---

## 📊 New Role ID Structure

| Layer              | Roles       | Examples                               |
| ------------------ | ----------- | -------------------------------------- |
| System             | 1-2         | Super Admin, Admin                     |
| Reserved           | 3-9         | (Future system maintenance)            |
| Executive          | 10-14       | President, VP, CIO, CTO, CMO           |
| Reserved           | 15-29       | (Future executive expansion)           |
| Sales (Internal)   | 30-39       | Sales Manager, Sales Assistant         |
| Engineering        | 40-49       | Eng Manager, Tech Lead, Technicians    |
| Manufacturing      | 50-59       | Mfg Manager, Tech 1, Tech 2            |
| Field Service      | 60-69       | Field Manager                          |
| HR                 | 70-79       | HR Manager, Office Manager             |
| Accounting         | 80-89       | Accounting Manager, AP/AR Clerk        |
| Support            | 90-99       | Support Manager                        |
| **Partners**       | **100-159** | **Strategic Partner, Vendor, Distributor, Installer, Applicator, Contractor** |
| **Clients**        | **160-163** | **Standard, Restricted, Advanced, Status** |

---

## 🔍 Database Queries

```sql
-- Count active roles
SELECT COUNT(*) as total_roles FROM roles WHERE id >= 1 AND id <= 163;

-- List executive roles (10-14)
SELECT id, rname FROM roles WHERE id BETWEEN 10 AND 14 ORDER BY id;

-- List sales department (30-39)
SELECT id, rname FROM roles WHERE id BETWEEN 30 AND 39 ORDER BY id;

-- List external partners (100-159)
SELECT id, rname FROM roles WHERE id BETWEEN 100 AND 159 ORDER BY id;

-- List client roles (160-163)
SELECT id, rname FROM roles WHERE id BETWEEN 160 AND 163 ORDER BY id;

-- View all active roles by category
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
    ELSE 'Reserved'
  END as category,
  COUNT(*) as count
FROM roles 
GROUP BY category 
ORDER BY MIN(id);
```

---

## 📁 Documentation Files

| File                                 | Purpose                          | Status       |
| ------------------------------------ | -------------------------------- | ------------ |
| `ROLE_HIERARCHY_CHART.md`            | Complete role definitions        | ✅ Updated   |
| `QUICK_REFERENCE_RBAC.md`            | This file (quick reference)      | ✅ Updated   |
| `UNIVERSAL_RBAC_SYSTEM_PROPOSAL.md`  | Detailed role structure proposal | ⏳ Updating   |
| `RBAC_RESTRUCTURE_IMPLEMENTATION.md` | Full deployment guide            | ⏳ Updating   |
| `RBAC_MIGRATION_PLAN.md`             | Schema enhancement planning      | ⏳ Updating   |

---

## ✅ Pre-Deployment Checklist

- [ ] Read ROLE_HIERARCHY_CHART.md
- [ ] Backup database (see Step 1 above)
- [ ] Review SQL migration script
- [ ] Have SSH/MySQL access ready
- [ ] All RBAC documents updated
- [ ] Code changes identified and documented

---

## ✅ Post-Deployment Checklist

- [ ] SQL migration executed successfully
- [ ] Database role count verification (32+ roles)
- [ ] Roles.php updated and loads (no errors)
- [ ] Language files updated and load (no errors)
- [ ] Role dropdown shows all roles in new structure
- [ ] System roles (1-2) present and functional
- [ ] Executive roles (10-14) visible and correct
- [ ] Department roles in new ranges (30-99)
- [ ] Partner roles (100-159) visible
- [ ] Client roles (160-163) visible
- [ ] English translations functional
- [ ] Spanish translations functional
- [ ] User role assignments functioning correctly
- [ ] Permissions work with new role IDs

---

## 🚨 Rollback (If Needed)

```bash
# SSH into server
ssh wswg

# Restore database from backup
mysql -u democrm_democrm -p'b3J2sy5T4JNm60' democrm_democrm < backup_rbac_2025_11_17.sql

# Revert code changes if committed
git checkout HEAD -- classes/Models/Roles.php
git checkout HEAD -- public_html/admin/languages/en.php
git checkout HEAD -- public_html/admin/languages/es.php
```

---

## 📞 Questions?

See detailed guides:
1. Role hierarchy overview → `ROLE_HIERARCHY_CHART.md`
2. Full system proposal → `UNIVERSAL_RBAC_SYSTEM_PROPOSAL.md`
3. Implementation details → `RBAC_RESTRUCTURE_IMPLEMENTATION.md`
4. Migration planning → `RBAC_MIGRATION_PLAN.md`

---

**Status**: 🔄 IN PROGRESS (Documentation Phase)  
**Date**: 2025-11-17  
**Version**: 2.0