# DemoCRM Role Hierarchy Chart

**Last Updated**: 2025-11-17 (Consolidated Role Structure)  
**Total Roles**: 32 active roles across 9 departments + System & Executive

---

## System Administration (1-9)

| Role ID | Role Name           | Tier   | Notes                               |
| ------- | ------------------- | ------ | ----------------------------------- |
| 1       | Super Administrator | System | Full system access, all permissions |
| 2       | Administrator       | System | Full administrative access          |

---

## Executive Leadership (10-29)

| Role ID | Role Name                 | Tier    | Notes                         |
| ------- | ------------------------- | ------- | ----------------------------- |
| 10      | President                 | C-Level | President - Organization head |
| 11      | Vice President            | C-Level | VP - General management       |
| 12      | Chief Information Officer | C-Level | CIO - Operations executive    |
| 13      | Chief Technology Officer  | C-Level | CTO - Technology executive    |
| 14      | Chief Marketing Officer   | C-Level | CMO - Marketing executive     |

---

## Sales (30-39)

| Role ID | Role Name       | Tier       | Notes                        |
| ------- | --------------- | ---------- | ---------------------------- |
| 30      | Sales Manager   | Management | Sales team lead              |
| 35      | Sales Assistant | Sales      | Sales support representative |

---

## Engineering (40-49)

| Role ID | Role Name           | Tier        | Notes                         |
| ------- | ------------------- | ----------- | ----------------------------- |
| 40      | Engineering Manager | Management  | Engineering department lead   |
| 41      | Tech Lead           | Engineering | Technical leadership position |
| 42      | Technician 1        | Engineering | Engineering support level 1   |
| 43      | Technician 2        | Engineering | Engineering support level 2   |

---

## Manufacturing (50-59)

| Role ID | Role Name             | Tier          | Notes                         |
| ------- | --------------------- | ------------- | ----------------------------- |
| 50      | Manufacturing Manager | Manufacturing | Manufacturing department lead |
| 51      | Manufacturing Tech 1  | Manufacturing | Manufacturing support level 1 |
| 52      | Manufacturing Tech 2  | Manufacturing | Manufacturing support level 2 |

---

## Field Service (60-69)

| Role ID | Role Name     | Tier       | Notes                  |
| ------- | ------------- | ---------- | ---------------------- |
| 60      | Field Manager | Management | Field services manager |


---

## HR (70-79)

| Role ID | Role Name      | Tier       | Notes                   |
| ------- | -------------- | ---------- | ----------------------- |
| 70      | HR Manager     | Management | Human Resources manager |
| 72      | Office Manager | HR         | Office administration   |

---

## Accounting & Finance (80-89)

| Role ID | Role Name          | Tier       | Notes                       |
| ------- | ------------------ | ---------- | --------------------------- |
| 80      | Accounting Manager | Management | Accounting department lead  |
| 82      | AP/AR Clerk        | Accounting | Accounts Payable/Receivable |


---

## Support & Training (90-99)

| Role ID | Role Name       | Tier       | Notes             |
| ------- | --------------- | ---------- | ----------------- |
| 90      | Support Manager | Management | Support team lead |

---

## Partners (100-159)
| Role ID | Role Name         | Tier     | Notes                        |
| ------- | ----------------- | -------- | ---------------------------- |
| 100     | Strategic Partner | External | Strategic partner access     |
| 110     | Vendor            | External | Vendor/supplier access       |
| 120     | Distributor       | Partner  | Distributor access level     |
| 130     | Installer         | Partner  | Installer partner access     |
| 140     | Applicator        | Partner  | Applicator/service partner   |
| 150     | Contractor        | External | Contractor/consultant access |

---

## Clients (160-163)

| Role ID | Role Name         | Tier   | Notes                        |
| ------- | ----------------- | ------ | ---------------------------- |
| 160     | Client Standard   | Client | Standard client features     |
| 161     | Client Restricted | Client | Restricted client access     |
| 162     | Client Advanced   | Client | Advanced client features     |
| 163     | Client Status     | Client | Client status reporting only |

---

## Organizational Structure Overview

```
SYSTEM (Roles 1-2)
├── Super Administrator (1)
└── Administrator (2)

EXECUTIVE LEADERSHIP (Roles 10-14)
├── President (10)
├── Vice President (11)
├── Chief Information Officer (12)
├── Chief Technology Officer (13)
└── Chief Marketing Officer (14)

DEPARTMENTS

Sales (Roles 30-39)
├── Sales Manager (30)
└── Sales Assistant (35)

Engineering (Roles 40-49)
├── Engineering Manager (40)
├── Tech Lead (41)
├── Technician 1 (42)
└── Technician 2 (43)

Manufacturing (Roles 50-59)
├── Manufacturing Manager (50)
├── Manufacturing Tech 1 (51)
└── Manufacturing Tech 2 (52)

Field Service (Roles 60-69)
└── Field Manager (60)

HR (Roles 70-79)
├── HR Manager (70)
└── Office Manager (72)

Accounting & Finance (Roles 80-89)
├── Accounting Manager (80)
└── AP/AR Clerk (82)

Support & Training (Roles 90-99)
└── Support Manager (90)

EXTERNAL RELATIONSHIPS

Partners (Roles 100-159)
├── Strategic Partner (100)
├── Vendor (110)
├── Distributor (120)
├── Installer (130)
├── Applicator (140)
└── Contractor (150)

Clients (Roles 160-163)
├── Client Standard (160)
├── Client Restricted (161)
├── Client Advanced (162)
└── Client Status (163)
```

---

## Role Range Assignment Strategy

| Range          | Category       | Count | Purpose                                                  |
| -------------- | -------------- | ----- | -------------------------------------------------------- |
| 1-2            | System Admin   | 2     | System-level access                                      |
| 3-9            | Reserved       | 7     | Reserved for future expansion                            |
| 10-14          | Executive      | 5     | President, VP, C-Level roles                             |
| 15-29          | Reserved       | 15    | Reserved for future executive expansion                  |
| 30-39          | Sales          | 2     | Sales team (30, 35 active; 31-34, 36-39 reserved)        |
| 40-49          | Engineering    | 4     | Engineering department (40-43 active; 44-49 reserved)    |
| 50-59          | Manufacturing  | 3     | Manufacturing operations (50-52 active; 53-59 reserved)  |
| 60-69          | Field Service  | 1     | Field service (60 active; 61-69 reserved)                |
| 70-79          | HR             | 2     | Human resources (70, 72 active; 71, 73-79 reserved)      |
| 80-89          | Accounting     | 2     | Finance & accounting (80, 82 active; 81, 83-89 reserved) |
| 90-99          | Support        | 1     | Support & training (90 active; 91-99 reserved)           |
| 100-159        | Partners       | 6     | Partner & vendor management (100, 110, 120, 130, 140, 150) |
| 160-163        | Clients        | 4     | Client access tiers (160, 161, 162, 163)                 |

---

## Notes

- **Roles 3-9**: All reserved for future system maintenance roles
- **Roles 15-29**: Reserved for future executive expansion (VP roles, additional C-suite positions)
- **Roles 31-34, 36-39**: Reserved for future Sales department expansion
- **Roles 44-49**: Reserved for future Engineering expansion
- **Roles 53-59**: Reserved for future Manufacturing expansion
- **Roles 61-69**: Reserved for future Field Service expansion
- **Role 71**: Reserved for future HR expansion
- **Roles 73-79**: Reserved for future HR expansion
- **Role 81**: Reserved for future Accounting expansion
- **Roles 83-89**: Reserved for future Accounting & Finance expansion
- **Roles 91-99**: Reserved for future Support & Training expansion
- **Roles 101-109, 111-119, etc.**: Reserved for additional Partner categories

---

## Migration & Deployment History

### 2025-11-17: Consolidated Role Structure Reorganization
- **Restructured**: Complete role ID consolidation across all departments
- **Executive**: Simplified to 5 core C-suite roles (President, VP, CIO, CTO, CMO)
- **Sales**: Moved to Roles 30-39 (was 20-29)
- **Engineering**: Moved to Roles 40-49 (was 30-39)
- **Manufacturing**: Consolidated to Roles 50-59 (was 7-9, 40-49)
- **Field Service**: Moved to Roles 60-69 (was 50-59)
- **HR**: Moved to Roles 70-79 (was 60-69)
- **Accounting**: Moved to Roles 80-89 (was 70-79)
- **Support**: Moved to Roles 90-99 (was 80-89)
- **Partners**: Consolidated to Roles 100-159 (was 90-99, 141-143)
- **Clients**: Moved to Roles 160-163 (was 150-154)
- **Total active roles**: Reduced from 35 to 32 with cleaner structure
- **Status**: ✅ Documentation updated, awaiting database migration

### 2025-01-17: Accounting & Support Roles Deletion
- **Deleted**: Accountant (73), Finance Analyst (74), Auditor (75), Training Specialist (82), Support Agent (84), QA Specialist (85)
- **Reserved**: Roles 71, 73-75 (Accounting); Roles 82, 84-85 (Support) now reserved for future expansion
- **Swapped Names**: Technical Writer (81) ↔ Support Manager (83)
- Updated total active roles from 41 to 35

### 2025-01-17: HR Roles Deletion
- **Deleted**: Compliance Manager (61), HR Specialist (63), Compliance Officer (64)
- **Reserved**: Roles 61, 63-64 now reserved for future expansion
- Updated total active roles from 44 to 41

### 2025-01-16: Department Simplification & Role Consolidation
- **Sales**: Deleted Partner Manager (21), Sales Lead (22), Senior Sales Lead (23), Partner Sales (26); Changed Sales User (25) to Sales Assistant
- **Field Service**: Kept only Field Manager (50); Reserved Service Lead (51), Field Technician (52), Installer Lead (53), Field Installer (54)
- **Operations**: Removed all roles (3-9 now all reserved)
- **Production & Quality**: Removed both departments (43-49 now all reserved)
- Migration file: `sql/migrations/2025_01_16_department_simplification.sql`

### 2025-01-16: Role Hierarchy Restructuring
- Deleted Field Operations roles 10-12 (consolidated into field service)
- Moved Manufacturing roles from 7-9 to new dedicated range 40-42
- Moved Executive Leadership from 13-19 to 10-19
- Added President (10) and Vice President (11) above COO
- Migration file: `sql/migrations/2025_01_16_restructure_role_hierarchy.sql`

### 2025-01-16: Executive Roles Creation (Previous)
- Added C-Level positions: COO (13), CTO (14)
- Added VP positions: Sales (15), Operations (16), Engineering (17), Support (18), HR (19)
- Migration file: `sql/migrations/2025_01_16_create_executive_roles.sql`

### 2025-01-16: Client Roles Restoration (Previous)
- Restored client tier roles to range 150-154
- Migration file: `sql/migrations/2025_01_16_restore_client_roles.sql`

---

**This chart represents the complete organizational role structure as of 2025-01-17.**