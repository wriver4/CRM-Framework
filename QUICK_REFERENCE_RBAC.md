---
title: RBAC Restructure - Quick Reference Card
date: 2025-01-15
---

# ⚡ Quick Reference - RBAC Restructure

## ✅ What's Ready

- ✅ SQL Migration Script: `/sql/2025_01_15_RBAC_RESTRUCTURE_MIGRATION.sql`
- ✅ PHP Updated: `/classes/Models/Roles.php`
- ✅ English Updated: `/public_html/admin/languages/en.php`
- ✅ Spanish Updated: `/public_html/admin/languages/es.php`
- ✅ Documentation: 4 guides provided

---

## 🎯 3 Main Changes

### 1️⃣ Internal Sales Reorganized (20-29)
```
Sales Manager (20)
├─ Sales Lead (22, 23)
├─ Sales User (25)
└─ Partner Manager (21) → Partner Sales (26)
```

### 2️⃣ External Partners NEW (141-143)
```
Distributor (141) ← NEW
Installer (142) ← NEW
Applicator (143) ← NEW
```

### 3️⃣ Clients Moved (150)
```
Role 150: Client ← NEW (was scattered in 18-21)
```

---

## 🚀 Deploy in 3 Steps

### Step 1: Backup
```bash
mysqldump -u democrm_user -p democrm_democrm > backup_rbac.sql
```

### Step 2: Execute SQL
```bash
ssh wswg "mysql -u democrm_user -p democrm_democrm < /home/democrm/sql/2025_01_15_RBAC_RESTRUCTURE_MIGRATION.sql"
```

### Step 3: Verify
```bash
ssh wswg "mysql -u democrm_user -p democrm_democrm -e 'SELECT COUNT(*) as total_roles FROM roles;'"
# Expected: 50+
```

---

## 🧪 Quick Test

In browser:
1. Go to: **Users → Edit User**
2. Open **Role** dropdown
3. Verify:
   - ✅ 50+ roles visible
   - ✅ Roles 1-9 NOT visible
   - ✅ "Distributor" visible (141)
   - ✅ "Installer" visible (142)
   - ✅ "Applicator" visible (143)
   - ✅ "Client" visible (150)

---

## 📊 New Role IDs

| Layer              | Roles       | Examples                               |
| ------------------ | ----------- | -------------------------------------- |
| System             | 1-9         | Admin, Super Admin                     |
| Executive          | 10-19       | President, CFO, CTO                    |
| Sales (Internal)   | 20-29       | Sales Mgr, Partner Mgr                 |
| Engineering        | 30-39       | Eng Mgr, Tech Lead                     |
| Manufacturing      | 40-49       | Mfg Mgr, Prod Lead                     |
| Field Ops          | 50-59       | Field Mgr, Installer                   |
| Admin              | 60-69       | HR Mgr, Compliance                     |
| Finance            | 70-79       | Bookkeeper, Accountant                 |
| Support            | 80-89       | QA, Training, Support                  |
| External           | 90-99       | Vendor, Partner                        |
| **Sales Partners** | **141-143** | **Distributor, Installer, Applicator** |
| **Clients**        | **150**     | **Client**                             |

---

## 🔍 Database Queries

```sql
-- Count roles
SELECT COUNT(*) FROM roles;

-- List new sales partners
SELECT * FROM roles WHERE role_id IN (141, 142, 143);

-- List client role
SELECT * FROM roles WHERE role_id = 150;

-- List sales structure
SELECT role_id, role FROM roles 
WHERE role_id IN (20,21,22,23,25,26) ORDER BY role_id;
```

---

## 📁 Documentation Files

| File                                 | Purpose                   |
| ------------------------------------ | ------------------------- |
| `UNIVERSAL_RBAC_SYSTEM_PROPOSAL.md`  | Complete role definitions |
| `RBAC_RESTRUCTURE_IMPLEMENTATION.md` | Full deployment guide     |
| `DEPLOYMENT_SUMMARY.md`              | Quick summary             |
| `SALES_STRUCTURE_COMPARISON.md`      | Before/after comparison   |
| `FILES_READY_FOR_DEPLOYMENT.md`      | What was changed          |
| `QUICK_REFERENCE_RBAC.md`            | This file                 |

---

## ✅ Pre-Deployment Checklist

- [ ] Read DEPLOYMENT_SUMMARY.md
- [ ] Backup database
- [ ] Review SQL script
- [ ] Have SSH/MySQL access ready

---

## ✅ Post-Deployment Checklist

- [ ] SQL migration executed
- [ ] Database has 50+ roles
- [ ] Roles.php loads (no errors)
- [ ] Language files load (no errors)
- [ ] Role dropdown shows all roles
- [ ] System roles (1-9) excluded
- [ ] New sales partners visible (141-143)
- [ ] Client role visible (150)
- [ ] English translations show
- [ ] Spanish translations show

---

## 🚨 Rollback (If Needed)

```bash
# Restore database
mysql -u democrm_user -p democrm_democrm < backup_rbac.sql

# Git revert code
git checkout HEAD -- classes/Models/Roles.php
git checkout HEAD -- public_html/admin/languages/en.php
git checkout HEAD -- public_html/admin/languages/es.php
```

---

## 📞 Questions?

See full guides:
1. Quick overview → `DEPLOYMENT_SUMMARY.md`
2. Before/after → `SALES_STRUCTURE_COMPARISON.md`
3. Full deployment → `RBAC_RESTRUCTURE_IMPLEMENTATION.md`
4. Complete details → `UNIVERSAL_RBAC_SYSTEM_PROPOSAL.md`

---

**Status**: ✅ READY TO DEPLOY  
**Date**: 2025-01-15  
**Version**: 1.0