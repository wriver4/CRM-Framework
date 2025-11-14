# DemoCRM Role Hierarchy Chart

**Last Updated**: 2025-01-16  
**Total Roles**: 60 active roles across 9 organizational tiers

---

## Executive & Management (1-9)

| Role ID | Role Name                  | Tier          | Notes                               |
| ------- | -------------------------- | ------------- | ----------------------------------- |
| 1       | Super Administrator        | System        | Full system access, all permissions |
| 2       | Administrator              | System        | Full administrative access          |
| 3       | Operations Manager         | Management    | General operations oversight        |
| 4       | Operations Technician 1    | Operations    | Operations support level 1          |
| 5       | Operations Technician 2    | Operations    | Operations support level 2          |
| 6       | Operations Status          | Operations    | Operations status reporting only    |
| 7       | Manufacturing Manager      | Management    | Manufacturing department lead       |
| 8       | Manufacturing Technician 1 | Manufacturing | Manufacturing support level 1       |
| 9       | Manufacturing Technician 2 | Manufacturing | Manufacturing support level 2       |

---

## Field Operations (10-12)

| Role ID | Role Name                     | Tier       | Notes                            |
| ------- | ----------------------------- | ---------- | -------------------------------- |
| 10      | Field Operations Manager      | Management | Field operations department lead |
| 11      | Field Operations Technician 1 | Field Ops  | Field support level 1            |
| 12      | Field Operations Technician 2 | Field Ops  | Field support level 2            |

---

## Executive Leadership (13-19)

| Role ID | Role Name                | Tier     | Notes                             |
| ------- | ------------------------ | -------- | --------------------------------- |
| 13      | Chief Operating Officer  | C-Level  | COO - Operations executive        |
| 14      | Chief Technology Officer | C-Level  | CTO - Technology executive        |
| 15      | VP Sales                 | VP-Level | Vice President of Sales           |
| 16      | VP Operations            | VP-Level | Vice President of Operations      |
| 17      | VP Engineering           | VP-Level | Vice President of Engineering     |
| 18      | VP Support               | VP-Level | Vice President of Support         |
| 19      | VP Human Resources       | VP-Level | Vice President of Human Resources |

---

## Internal Sales (20-26)

| Role ID | Role Name         | Tier       | Notes                         |
| ------- | ----------------- | ---------- | ----------------------------- |
| 20      | Sales Manager     | Management | Sales team lead               |
| 21      | Partner Manager   | Management | Partner relationships manager |
| 22      | Sales Lead        | Sales      | Sales leadership position     |
| 23      | Senior Sales Lead | Sales      | Senior sales leadership       |
| 25      | Sales User        | Sales      | Standard sales representative |
| 26      | Partner Sales     | Sales      | Partner sales specialist      |

---

## Engineering (30-33)

| Role ID | Role Name           | Tier        | Notes                         |
| ------- | ------------------- | ----------- | ----------------------------- |
| 30      | Engineering Manager | Management  | Engineering department lead   |
| 31      | Tech Lead           | Engineering | Technical leadership position |
| 32      | Technician 1        | Engineering | Engineering support level 1   |
| 33      | Technician 2        | Engineering | Engineering support level 2   |

---

## Production & Quality (41-44)

| Role ID | Role Name       | Tier       | Notes                  |
| ------- | --------------- | ---------- | ---------------------- |
| 41      | Production Lead | Production | Production team lead   |
| 42      | Quality Lead    | Quality    | Quality assurance lead |
| 43      | Production Tech | Production | Production technician  |
| 44      | Quality Tech    | Quality    | Quality technician     |

---

## Field Operations (50-54)

| Role ID | Role Name        | Tier          | Notes                      |
| ------- | ---------------- | ------------- | -------------------------- |
| 50      | Field Manager    | Management    | Field services manager     |
| 51      | Service Lead     | Field Service | Service team lead          |
| 52      | Field Technician | Field Service | Field technician           |
| 53      | Installer Lead   | Field Service | Installation team lead     |
| 54      | Field Installer  | Field Service | Field installer technician |

---

## HR & Compliance (60-64)

| Role ID | Role Name          | Tier       | Notes                   |
| ------- | ------------------ | ---------- | ----------------------- |
| 60      | HR Manager         | Management | Human Resources manager |
| 61      | Compliance Manager | Management | Compliance officer      |
| 62      | Office Manager     | HR         | Office administration   |
| 63      | HR Specialist      | HR         | HR support specialist   |
| 64      | Compliance Officer | Compliance | Compliance enforcement  |

---

## Accounting & Finance (70-75)

| Role ID | Role Name          | Tier       | Notes                       |
| ------- | ------------------ | ---------- | --------------------------- |
| 70      | Accounting Manager | Management | Accounting department lead  |
| 72      | AP/AR Clerk        | Accounting | Accounts Payable/Receivable |
| 73      | Accountant         | Accounting | Staff accountant            |
| 74      | Finance Analyst    | Finance    | Financial analysis          |
| 75      | Auditor            | Finance    | Internal/external auditor   |

---

## Support & Training (81-85)

| Role ID | Role Name           | Tier       | Notes                        |
| ------- | ------------------- | ---------- | ---------------------------- |
| 81      | Technical Writer    | Support    | Technical documentation      |
| 82      | Training Specialist | Support    | Training & development       |
| 83      | Support Manager     | Management | Support team lead            |
| 84      | Support Agent       | Support    | Customer support agent       |
| 85      | QA Specialist       | Quality    | Quality assurance specialist |

---

## Vendors & Partners (90-93)

| Role ID | Role Name         | Tier     | Notes                        |
| ------- | ----------------- | -------- | ---------------------------- |
| 90      | Vendor            | External | Vendor/supplier access       |
| 91      | Strategic Partner | External | Strategic partner access     |
| 92      | Contractor        | External | Contractor/consultant access |
| 93      | Guest             | External | Guest/temporary access       |

---

## Viewer (99)

| Role ID | Role Name | Tier     | Notes                             |
| ------- | --------- | -------- | --------------------------------- |
| 99      | Viewer    | External | Read-only access to specific data |

---

## External Sales Partners (141-143)

| Role ID | Role Name   | Tier    | Notes                      |
| ------- | ----------- | ------- | -------------------------- |
| 141     | Distributor | Partner | Distributor access level   |
| 142     | Installer   | Partner | Installer partner access   |
| 143     | Applicator  | Partner | Applicator/service partner |

---

## Clients (150-154)

| Role ID | Role Name         | Tier   | Notes                        |
| ------- | ----------------- | ------ | ---------------------------- |
| 150     | Client            | Client | Base client access           |
| 151     | Client Advanced   | Client | Advanced client features     |
| 152     | Client Standard   | Client | Standard client features     |
| 153     | Client Restricted | Client | Restricted client access     |
| 154     | Client Status     | Client | Client status reporting only |

---

## Organizational Structure Overview

```
SYSTEM (Roles 1-2)
├── Super Administrator (1)
└── Administrator (2)

EXECUTIVE LEADERSHIP (Roles 13-19)
├── Chief Operating Officer (13)
├── Chief Technology Officer (14)
├── VP Sales (15)
├── VP Operations (16)
├── VP Engineering (17)
├── VP Support (18)
└── VP Human Resources (19)

DEPARTMENTS

Sales Department (Roles 20-26)
├── Sales Manager (20)
├── Partner Manager (21)
├── Sales Lead (22)
├── Senior Sales Lead (23)
├── Sales User (25)
└── Partner Sales (26)

Operations Department (Roles 3-9)
├── Operations Manager (3)
├── Operations Technician 1 (4)
├── Operations Technician 2 (5)
├── Operations Status (6)
├── Manufacturing Manager (7)
├── Manufacturing Technician 1 (8)
└── Manufacturing Technician 2 (9)

Field Operations (Roles 10-12, 50-54)
├── Field Operations Manager (10)
├── Field Operations Technician 1 (11)
├── Field Operations Technician 2 (12)
├── Field Manager (50)
├── Service Lead (51)
├── Field Technician (52)
├── Installer Lead (53)
└── Field Installer (54)

Engineering Department (Roles 30-33)
├── Engineering Manager (30)
├── Tech Lead (31)
├── Technician 1 (32)
└── Technician 2 (33)

Production & Quality (Roles 41-44)
├── Production Lead (41)
├── Quality Lead (42)
├── Production Tech (43)
└── Quality Tech (44)

HR & Compliance (Roles 60-64)
├── HR Manager (60)
├── Compliance Manager (61)
├── Office Manager (62)
├── HR Specialist (63)
└── Compliance Officer (64)

Accounting & Finance (Roles 70-75)
├── Accounting Manager (70)
├── AP/AR Clerk (72)
├── Accountant (73)
├── Finance Analyst (74)
└── Auditor (75)

Support & Training (Roles 81-85)
├── Technical Writer (81)
├── Training Specialist (82)
├── Support Manager (83)
├── Support Agent (84)
└── QA Specialist (85)

EXTERNAL RELATIONSHIPS

Vendors & Partners (Roles 90-93, 141-143)
├── Vendor (90)
├── Strategic Partner (91)
├── Contractor (92)
├── Guest (93)
├── Distributor (141)
├── Installer (142)
└── Applicator (143)

Viewers (Role 99)
└── Viewer (99) - Read-only access

CLIENT TIERS (Roles 150-154)
├── Client (150)
├── Client Advanced (151)
├── Client Standard (152)
├── Client Restricted (153)
└── Client Status (154)
```

---

## Role Range Assignment Strategy

| Range   | Category         | Count | Purpose                  |
| ------- | ---------------- | ----- | ------------------------ |
| 1-2     | System Admin     | 2     | System-level access      |
| 3-9     | Operations Core  | 7     | Core internal operations |
| 10-12   | Field Operations | 3     | Field teams management   |
| 13-19   | Executive        | 7     | C-Level & VPs            |
| 20-26   | Sales            | 6     | Internal sales team      |
| 30-33   | Engineering      | 4     | Engineering department   |
| 41-44   | Production/QA    | 4     | Manufacturing & quality  |
| 50-54   | Field Service    | 5     | Field service delivery   |
| 60-64   | HR/Compliance    | 5     | Human resources          |
| 70-75   | Accounting       | 5     | Finance department       |
| 81-85   | Support          | 5     | Support & training       |
| 90-93   | Vendors          | 4     | Vendor management        |
| 99      | Viewers          | 1     | Read-only access         |
| 141-143 | Partners         | 3     | External sales partners  |
| 150-154 | Clients          | 5     | Client access tiers      |

---

## Notes

- **Role ID 24**: Currently unassigned (reserved)
- **Role ID 71**: Currently unassigned (reserved)
- **Gaps in ranges**: Some role IDs are reserved for future expansion
- **Executive Roles**: Recently created in range 13-19 for organizational hierarchy
- **Client Roles**: Restored in range 150-154 after previous cleanup
- **Field Operations**: Spans two ranges (10-12 for management, 50-54 for delivery)

---

## Migration & Deployment History

### 2025-01-16: Executive Roles Creation
- Added C-Level positions: COO (13), CTO (14)
- Added VP positions: Sales (15), Operations (16), Engineering (17), Support (18), HR (19)
- Migration file: `sql/migrations/2025_01_16_create_executive_roles.sql`

### 2025-01-16: Client Roles Restoration
- Restored client tier roles to range 150-154
- Migration file: `sql/migrations/2025_01_16_restore_client_roles.sql`

---

**This chart represents the complete organizational role structure as of 2025-01-16.**