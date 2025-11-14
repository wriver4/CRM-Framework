---
title: Sales Structure Reorganization - Before & After
date: 2025-01-15
---

# 📊 Sales Structure Reorganization

## The Challenge

The previous role structure mixed internal sales team members with external sales partners, and scattered client roles across multiple role IDs. This made it difficult to:
- Distinguish internal vs. external relationships
- Manage channel partners separately
- Apply different permissions to customer accounts
- Scale to multiple partner types

## The Solution

**Complete reorganization** of sales and partner roles into three distinct tiers:

1. **Internal Sales Team** (20-29)
2. **External Sales Partners** (141-143) - NEW
3. **Customer Accounts** (150) - NEW

---

## Side-by-Side Comparison

### BEFORE: Mixed Structure
```
Sales Department (scattered roles 13-21)
├── Role 13: Sales Manager
├── Role 14: Sales Assistant
├── Role 15: Sales Person
├── Role 16: Bookkeeper          ← Wrong layer!
├── Role 17: Translator           ← Wrong layer!
├── Role 18: Client Advanced      ← Should be separate
├── Role 19: Client Standard      ← Should be separate
├── Role 20: Client Restricted    ← Should be separate
└── Role 21: Client Status        ← Should be separate

Problems:
❌ Finance role in sales layer
❌ Support role in sales layer
❌ 4 client roles scattered
❌ No external partner distinction
❌ Difficult to manage permissions
```

### AFTER: Organized Structure

#### **TIER 1: Internal Sales (Roles 20-29)**
```
VP Sales (Role 15)
    ├─ Sales Manager (Role 20)
    │   ├─ Sales Lead (Role 22)
    │   ├─ Sales Lead 2 (Role 23)
    │   └─ Sales User (Role 25)
    │
    └─ Partner Manager (Role 21)
        ├─ Partner Sales (Role 26)
        └─ [External Partners - see Tier 2]

Roles 24, 27-29: Reserved for future sales roles
```

#### **TIER 2: External Sales Partners (Roles 141-143) ← NEW**
```
VP Sales (Role 15)
    └─ Partner Manager (Role 21)
        ├─ Distributor (Role 141) ← NEW
        │   └── Handles channel distribution
        │
        ├─ Installer (Role 142) ← NEW
        │   └── Manages field installations
        │
        └─ Applicator (Role 143) ← NEW
            └── Manages field applications

Benefits:
✅ Separate tier for external partners
✅ Can apply different permissions
✅ Support multiple partner types
✅ Scalable: Add more roles (144-149 reserved)
```

#### **TIER 3: Customer Accounts (Role 150) ← NEW**
```
Customer (Role 150) ← NEW
├─ Customer Portal Access
├─ View Own Account
├─ Submit Support Tickets
└─ Place Orders (if enabled)

Benefits:
✅ One unified customer role
✅ Separate from employee structure
✅ Clear permissions model
✅ Support unlimited customer accounts
```

---

## 🗂️ Organizational Chart

### BEFORE (Confusing Structure)
```
President
├── Sales Dept
│   ├── Sales Manager (13)
│   ├── Sales Assistant (14)
│   ├── Sales Person (15)
│   ├── Bookkeeper (16) ⚠️ Finance role in sales!
│   ├── Translator (17) ⚠️ Support role in sales!
│   ├── Client Adv (18)
│   ├── Client Std (19)
│   ├── Client Res (20) ⚠️ Multiple client tiers!
│   └── Client Status (21)
└── [Other depts]
```

### AFTER (Clear Structure)
```
President
├── VP Sales (15)
│   ├── Sales Manager (20)
│   │   ├── Sales Lead (22)
│   │   ├── Sales Lead 2 (23)
│   │   └── Sales User (25)
│   │
│   └── Partner Manager (21)
│       ├── Partner Sales (26)
│       ├── External Partners (141-143)
│       │   ├── Distributor (141)
│       │   ├── Installer (142)
│       │   └── Applicator (143)
│       └── [Clients - separate tier 150]
│
├── CFO (12)
│   └── Bookkeeper (71) ✅ Finance layer now!
│
├── VP Administration (17)
│   └── Translator (80) ✅ Support layer now!
│
└── Customer Portal
    └── Client (150) ✅ Separate tier!
```

---

## 📋 Role Migration Guide

### If You Have Existing Users

**Sales Team Members:**
```sql
-- Update to new sales roles (example)
UPDATE users SET role_id = 20 WHERE old_role = 13; -- Sales Manager
UPDATE users SET role_id = 25 WHERE old_role = 15; -- Sales User
```

**Partners/Distributors:**
```sql
-- Move to external partner roles
UPDATE users SET role_id = 141 WHERE partner_type = 'distributor';
UPDATE users SET role_id = 142 WHERE partner_type = 'installer';
UPDATE users SET role_id = 143 WHERE partner_type = 'applicator';
```

**Customers:**
```sql
-- All customers go to role 150
UPDATE users SET role_id = 150 WHERE customer = 1;
```

**Finance/Support:**
```sql
-- Move finance roles out of sales layer
UPDATE users SET role_id = 71 WHERE role_id = 16; -- Bookkeeper
UPDATE users SET role_id = 80 WHERE role_id = 17; -- Translator
```

---

## 🔐 Permission Examples

### Before (Confusing)
```
Role 15 (Sales Person) permissions:
- View leads? Maybe
- Create leads? Maybe
- View customers? Maybe (but also clients?)
- Edit products? No
- Generate reports? Yes

❌ What exactly can they do?
```

### After (Clear)
```
Role 25 (Sales User) permissions:
- View assigned leads: YES
- Create leads: YES
- Manage customers: NO
- View reports: YES (assigned only)
- Access admin: NO

Role 141 (Distributor) permissions:
- View own account: YES
- View assigned leads: YES
- Create orders: YES (via portal)
- Manage sub-distributors: YES
- Access admin: NO

Role 150 (Client) permissions:
- View own account: YES
- Submit support tickets: YES
- View invoices: YES
- Place orders: YES
- Access admin: NO

✅ Clear, distinct permissions per role
```

---

## 💡 Key Benefits

| Aspect                | Before             | After                             |
| --------------------- | ------------------ | --------------------------------- |
| **Internal Sales**    | Mixed in one layer | Dedicated 20-29 layer             |
| **External Partners** | No clear structure | Dedicated 141-143 layer           |
| **Customers**         | 4 different roles  | Single role 150                   |
| **Bookkeeper**        | Sales role 16      | Finance role 71                   |
| **Translator**        | Sales role 17      | Support role 80                   |
| **Scalability**       | Hard to expand     | Reserved slots 24, 27-29, 144-149 |
| **Permissions**       | Confusing          | Clear by role                     |
| **Reporting**         | Mixed categories   | By department                     |

---

## 📈 Expansion Capacity

### Reserved Slots for Future Roles

**Internal Sales (20-29)**
```
Used: 20, 21, 22, 23, 25, 26
Reserved: 24, 27, 28, 29 ← Can add 4 more sales roles
```

**External Sales Partners (141-149)**
```
Used: 141, 142, 143
Reserved: 144, 145, 146, 147, 148, 149 ← Can add 6 more partner types
(Examples: Reseller, Integrator, Consultant, etc.)
```

**Customers (150-159)**
```
Used: 150
Reserved: 151-159 ← Can add customer tier variations if needed
(Examples: Premium Customer, Trial Customer, etc.)
```

---

## ✅ Implementation Checklist

- [ ] Execute SQL migration script
- [ ] Update Roles.php model
- [ ] Update English language file
- [ ] Update Spanish language file
- [ ] Test role dropdown in User Edit
- [ ] Verify system roles (1-9) excluded
- [ ] Test role assignments
- [ ] Verify permissions work correctly
- [ ] Document any custom permission adjustments
- [ ] Train users on new structure

---

## 🎯 Next Steps

1. **Database**: Execute migration script (`2025_01_15_RBAC_RESTRUCTURE_MIGRATION.sql`)
2. **Code**: Deploy updated Roles.php and language files
3. **Users**: Migrate existing users to new roles (optional, if needed)
4. **Permissions**: Set up permissions per role in your authorization system
5. **Testing**: Verify all roles work correctly
6. **Documentation**: Update internal docs to reflect new structure

---

## 📞 Questions?

Refer to:
- **UNIVERSAL_RBAC_SYSTEM_PROPOSAL.md** - Complete role definitions
- **RBAC_RESTRUCTURE_IMPLEMENTATION.md** - Full deployment guide
- **SQL Migration Script** - Database changes with verification

---

**Date**: 2025-01-15  
**Version**: 1.0  
**Status**: Ready for Production