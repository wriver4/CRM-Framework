---
title: Universal RBAC (Role-Based Access Control) System Proposal
date: 2025-11-17
version: 4.0
status: UPDATED - Implementation Ready
scope: Multi-Application Enterprise System
---

# 🌐 Universal RBAC System for Multi-Application Enterprise

## Executive Summary

This document outlines a **universal, application-agnostic Role-Based Access Control (RBAC) system** designed to support:

✅ **Current**: DemoCRM software  
✅ **Future**: Manufacturing systems, Field operations, Finance, Client portals, etc.  
✅ **Scalable**: 163 role IDs with strategic expansion headroom  
✅ **Flexible**: Use subsets of roles per application  
✅ **Enterprise-Grade**: Support for 9+ departments with clear role structure  
✅ **Consolidated**: Simplified from previous structure for better maintainability  

---

## 📊 Complete Role Structure (Universal)

### **LAYER 1: SYSTEM MAINTENANCE (Roles 1-9)**
Reserved **exclusively** for application/software system maintainers

|   ID    | Role            | Purpose                         | Scope                      | Status     |
| :-----: | --------------- | ------------------------------- | -------------------------- | ---------- |
|  **1**  | **Super Admin** | Dev/Technical Lead              | Full System Infrastructure | ✅ KEEP     |
|  **2**  | **Admin**       | System Administrator            | Full System Administration | ✅ KEEP     |
| **3-9** | *Reserved*      | Future system maintenance roles | —                          | 📌 RESERVED |

---

### **LAYER 2: EXECUTIVE LEADERSHIP (Roles 10-14)**
C-Suite and strategic decision makers (cross-functional)

```
                 ┌──────────────────┐
                 │   ROLE ID: 10    │
                 │   PRESIDENT      │
                 │ (Chief Executive)│
                 └────────┬─────────┘
                          │
        ┌─────────────────┼─────────────────┬─────────────────┐
        │                 │                 │                 │
   ┌────▼─────┐      ┌───▼────┐       ┌───▼────┐        ┌───▼────┐
   │ROLE: 12  │      │ROLE: 13│       │ROLE: 11│        │ROLE: 14│
   │CIO       │      │CTO     │       │VP      │        │CMO     │
   │Info Tech │      │Tech    │       │Ops/Mfg │        │Marketing
   └──────────┘      └────────┘       └────────┘        └────────┘
```

|   ID   | Role                      | Title                          | Department | Authority |       Scope        | Users |
| :----: | ------------------------- | ------------------------------ | ---------- | :-------: | :----------------: | :---: |
| **10** | **President**             | President / CEO                | Executive  |   ⭐⭐⭐⭐⭐   |    All Systems     |   1   |
| **11** | **Vice President**         | VP - General Management        | Executive  |   ⭐⭐⭐⭐    | Cross-Functional   |   1   |
| **12** | **Chief Information Officer** | CIO - Operations & IT        | Executive  |   ⭐⭐⭐⭐    | Technology/Systems |   1   |
| **13** | **Chief Technology Officer** | CTO - Technology Executive   | Executive  |   ⭐⭐⭐⭐    | Tech Innovation    |   1   |
| **14** | **Chief Marketing Officer** | CMO - Marketing & Sales       | Executive  |   ⭐⭐⭐⭐    | Marketing/Sales    |   1   |

---

### **LAYER 3: DEPARTMENT MANAGEMENT (Roles 30-99)**
Operational leadership for specific departments (see detailed sections below)

---

### **SALES DEPARTMENT - INTERNAL (Roles 30-39)**
Internal Sales team structure for direct customer engagement

```
Executive (10-14)
    │
    ├─ Sales Manager (30)   ── Sales Assistant (35)
    └─ [Support roles: 31-34, 36-39 reserved]
```

|   ID   | Role                | Title                   | Reports To       | Scope       | Team Size | Users  |
| :----: | ------------------- | ----------------------- | ---------------- | :---------: | :-------: | :----: |
| **30** | **Sales Manager**   | Sales Manager           | Executive        | All Leads   |   5-10    |  1-3   |
| **35** | **Sales Assistant** | Sales Support           | Sales Mgr (30)   |  Assigned   |     —     | 10-50  |
| **31** | *Reserved*          | —                       | —                |      —      |     —     |   —    |
| **32** | *Reserved*          | —                       | —                |      —      |     —     |   —    |
| **33** | *Reserved*          | —                       | —                |      —      |     —     |   —    |
| **34** | *Reserved*          | —                       | —                |      —      |     —     |   —    |
| **36** | *Reserved*          | —                       | —                |      —      |     —     |   —    |
| **37** | *Reserved*          | —                       | —                |      —      |     —     |   —    |
| **38** | *Reserved*          | —                       | —                |      —      |     —     |   —    |
| **39** | *Reserved*          | —                       | —                |      —      |     —     |   —    |

---

### **EXTERNAL PARTNERS (Roles 100-159)**
External partner relationships (Strategic partners, vendors, distributors, installers, applicators, contractors)

```
Executive (10-14)
    │
    ├─ Strategic Partner (100)    ← Strategic relationships
    ├─ Vendor (110)               ← Supplier/Vendor
    ├─ Distributor (120)          ← Channel distributor
    ├─ Installer (130)            ← Installation partner
    ├─ Applicator (140)           ← Service/Application partner
    └─ Contractor (150)           ← Contractor/Consultant
```

|     ID      | Role                | Title                   | Department | Purpose               | Authority |      Users       |
| :---------: | ------------------- | ----------------------- | ---------- | --------------------- | :-------: | :--------------: |
|   **100**   | **Strategic Partner**| Strategic Partner       | External   | Long-term Partnership |     ⭐     |    Unlimited     |
|   **110**   | **Vendor**          | Vendor / Supplier       | External   | Supplier Portal       |     ⭐     |     10-100       |
|   **120**   | **Distributor**     | Channel Distributor     | External   | Channel Distribution  |     ⭐     |     10-100       |
|   **130**   | **Installer**       | Installation Partner    | External   | Installation Services |     ⭐     |     10-50        |
|   **140**   | **Applicator**      | Application Partner     | External   | Service Delivery      |     ⭐     |      5-50        |
|   **150**   | **Contractor**      | Contractor / Consultant | External   | Consulting Services   |     ⭐     |      5-50        |
| **101-109** | *Reserved*          | —                       | —          | —                     |     —     |        —         |
| **111-119** | *Reserved*          | —                       | —          | —                     |     —     |        —         |
| **121-129** | *Reserved*          | —                       | —          | —                     |     —     |        —         |
| **131-139** | *Reserved*          | —                       | —          | —                     |     —     |        —         |
| **141-149** | *Reserved*          | —                       | —          | —                     |     —     |        —         |
| **151-159** | *Reserved*          | —                       | —          | —                     |     —     |        —         |

---

### **ENGINEERING & TECHNICAL (Roles 40-49)**
Software, systems, and technical team

```
Executive (10-14)
    │
    ├─ Engineering Manager (40) 
    │   ├─ Tech Lead (41)
    │   ├─ Technician 1 (42)
    │   └─ Technician 2 (43)
    │
    └─ [Reserved: 44-49]
```

|    ID     | Role                    | Title                | Reports To    | Authority | Users |
| :-------: | ----------------------- | -------------------- | ------------- | :-------: | :---: |
|  **40**   | **Engineering Manager** | Engineering Manager  | Executive     |    ⭐⭐     |  1-2  |
|  **41**   | **Tech Lead**           | Technical Team Lead  | Eng Mgr (40)  |     ⭐     |  1-2  |
|  **42**   | **Technician 1**        | Senior Technician    | Eng Mgr (40)  |     ⭐     |  2-5  |
|  **43**   | **Technician 2**        | Junior Technician    | Eng Mgr (40)  |     ⭐     |  2-5  |
| **44-49** | *Reserved*              | —                    | —             |     —     |   —   |

---

### **MANUFACTURING & OPERATIONS (Roles 50-59)**
Production, quality control, and manufacturing operations

```
Executive (10-14)
    │
    ├─ Manufacturing Manager (50)
    │   ├─ Manufacturing Tech 1 (51)
    │   └─ Manufacturing Tech 2 (52)
    │
    └─ [Reserved: 53-59]
```

|    ID     | Role                        | Title                 | Department     | Reports To       | Authority | Team Size | Users |
| :-------: | --------------------------- | --------------------- | -------------- | ---------------- | :-------: | :-------: | :---: |
|  **50**   | **Manufacturing Manager**   | Mfg Manager           | Manufacturing  | Executive        |    ⭐⭐     |   15-30   |  1-2  |
|  **51**   | **Manufacturing Tech 1**    | Manufacturing Tech Sr | Manufacturing  | Mfg Mgr (50)     |     ⭐     |   5-10    |  2-4  |
|  **52**   | **Manufacturing Tech 2**    | Manufacturing Tech Jr | Manufacturing  | Mfg Mgr (50)     |     ⭐     |   5-10    |  1-2  |
| **53-59** | *Reserved*                  | —                     | —              | —                |     —     |     —     |   —   |

---

### **FIELD SERVICE & OPERATIONS (Roles 60-69)**
Field service, installations, maintenance, and support

```
Executive (10-14)
    │
    ├─ Field Manager (60)
    │   └─ [Reserved: 61-69]
```

|    ID     | Role                 | Title                    | Department | Reports To       | Authority | Team  | Users  |
| :-------: | -------------------- | ------------------------ | ---------- | ---------------- | :-------: | :---: | :----: |
|  **60**   | **Field Manager**    | Field Operations Manager | Field Ops  | Executive        |    ⭐⭐     | 20-50 |  1-2   |
| **61-69** | *Reserved*           | —                        | —          | —                |     —     |   —   |   —    |

---

### **HUMAN RESOURCES & ADMINISTRATION (Roles 70-79)**
HR, office management, compliance, and business operations

```
Executive (10-14)
    │
    ├─ HR Manager (70)
    ├─ Office Manager (72)
    └─ [Reserved: 71, 73-79]
```

|    ID     | Role                   | Title                     | Department | Reports To      | Authority | Users |
| :-------: | ---------------------- | ------------------------- | ---------- | --------------- | :-------: | :---: |
|  **70**   | **HR Manager**         | Human Resources Manager   | Admin      | Executive       |    ⭐⭐     |  1-2  |
|  **72**   | **Office Manager**     | Office Operations Manager | Admin      | Executive       |     ⭐     |  1-2  |
| **71**    | *Reserved*             | —                         | —          | —               |     —     |   —   |
| **73-79** | *Reserved*             | —                         | —          | —               |     —     |   —   |

---

### **ACCOUNTING & FINANCE (Roles 80-89)**
Bookkeeping, accounting, financial operations, invoicing

```
Executive (10-14)
    │
    ├─ Accounting Manager (80)
    ├─ AP/AR Clerk (82)
    └─ [Reserved: 81, 83-89]
```

|    ID     | Role                   | Title                       | Department | Reports To       | Authority | Users |
| :-------: | ---------------------- | --------------------------- | ---------- | ---------------- | :-------: | :---: |
|  **80**   | **Accounting Manager** | Accounting Manager          | Finance    | Executive        |    ⭐⭐     |  1-2  |
|  **82**   | **AP/AR Clerk**        | Accounts Payable/Receivable | Finance    | Acct Mgr (80)    |     ⭐     |  1-2  |
| **81**    | *Reserved*             | —                           | —          | —                |     —     |   —   |
| **83-89** | *Reserved*             | —                           | —          | —                |     —     |   —   |

---

### **SUPPORT & TRAINING (Roles 90-99)**
Customer support, training, and internal support services

```
Executive (10-14)
    │
    ├─ Support Manager (90)
    └─ [Reserved: 91-99]
```

|    ID     | Role                    | Title                        | Department | Reports To       | Authority | Users |
| :-------: | ----------------------- | ---------------------------- | ---------- | ---------------- | :-------: | :---: |
|  **90**   | **Support Manager**     | Support & Training Manager   | Support    | Executive        |    ⭐⭐     |   1   |
| **91-99** | *Reserved*              | —                            | —          | —                |     —     |   —   |

---

### **CLIENTS & ACCOUNTS (Roles 160-163)**
Customer accounts and client portal users (primary customer interface)

```
└─ Client (160-163)      ← Customer/Account Tiers
```

|     ID      | Role                    | Title                  | Purpose            | Lead Access | Authority |   Users   |
| :---------: | ----------------------- | ---------------------- | ------------------ | :---------: | :-------: | :-------: |
|   **160**   | **Client Standard**     | Standard Client        | Client Portal      | Own Account |     ⭐     | Unlimited |
|   **161**   | **Client Restricted**   | Restricted Access      | Limited Features   | Own Account |     ⭐     | Unlimited |
|   **162**   | **Client Advanced**     | Advanced Features      | Premium Features   | Own Account |     ⭐     | Unlimited |
|   **163**   | **Client Status**       | Status Reporting Only  | Read-Only Viewing  | Own Account |     ⭐     | Unlimited |

---

## 📈 Complete Organizational Hierarchy

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        UNIVERSAL ENTERPRISE HIERARCHY                       │
└─────────────────────────────────────────────────────────────────────────────┘

                    SYSTEM MAINTENANCE (1-9)
                          │
                    ┌─────▼──────┐
                    │ PRESIDENT  │ (10)
                    └─────┬──────┘
                          │
    ┌─────────────────────┼─────────────────────┬────────────────┐
    │                     │                     │                │
 CTO (11)             CFO (12)              COO (13)         VP Ops (14)
    │                     │                     │                │
    ├─ VP Eng (16)        ├─ Acct Mgr (70)     │            ├─ VP Mfg (18)
    │  ├─ Eng Mgr (30)    │  ├─ Bookkeeper(71) │            │  ├─ Mfg Mgr (40)
    │  ├─ Tech Lead (31)  │  ├─ AP/AR (72)     │            │  ├─ Prod Lead (41)
    │  ├─ Tech 1-2 (32-33)│  ├─ Accountant(73) │            │  ├─ QA Lead (42)
    │  └─ Translator (34) │  └─ Fin Analyst(74)│            │  └─ Prod Tech (43)
    │                     │                     │            │
    └─ Support (80-85)    └─ Auditor (75)      │         └─ VP Field Ops (19)
       ├─ Tech Writer     │                     │            ├─ Field Mgr (50)
       ├─ QA (85)         └─ Compliance (61)    │            ├─ Service Lead (51)
       └─ Training                              │            ├─ Field Tech (52)
                                                │            └─ Installer (47/54)
                                          ├─ VP Admin (17)
                                          │  ├─ HR Mgr (60)
                                          │  ├─ Compliance (61)
                                          │  └─ Office Mgr (62)
                                          │
                                          └─ VP Sales (15)
                                             ├─ Sales Mgr (20)
                                             ├─ Partner Mgr (21)
                                             ├─ Sales Lead (22)
                                             ├─ Sales User (25)
                                             ├─ Partner Sales (26)
                                             └─ Distributor (27)


EXTERNAL STAKEHOLDERS (90-99):
├─ Client (90)
├─ Distributor (91)
├─ Installer (92)
├─ Vendor (93)
├─ Partner (94)
├─ Contractor (95)
├─ Guest (96)
└─ Viewer (99)
```

---

## 🗺️ Role ID Allocation Strategy

```
RANGE       PURPOSE                         EXPANSION ROOM
─────────────────────────────────────────────────────────
1-9         System Maintenance              3-9 (7 slots)
10-19       Executive Leadership            11-14 (5 available)
20-29       Sales Operations                5 active, 5 reserved
30-39       Engineering/Technical           5 active, 5 reserved
40-49       Manufacturing/Operations        7 active, 3 reserved
50-59       Field Operations/Service        6 active, 4 reserved
60-69       Administration/HR/Legal         6 active, 4 reserved
70-79       Accounting/Finance              6 active, 4 reserved
80-89       Specialized Support             7 active, 3 reserved
90-99       External Partners               10 active, 0 reserved
─────────────────────────────────────────────────────────
TOTAL:      50+ roles with 30+ reserved slots for growth
```

---

## 📋 Department Mapping

### **By Function:**

| Department         | Role Range | Key Roles                              | Manager        | Users  |
| ------------------ | ---------- | -------------------------------------- | -------------- | :----: |
| **Executive**      | 10-19      | President, CTO, CFO, COO               | President (10) |  4-5   |
| **Sales**          | 20-29      | Sales Mgr, Partner Mgr, Sales Rep      | VP Sales (15)  | 20-100 |
| **Engineering**    | 30-39      | Eng Mgr, Tech Lead, Technicians        | VP Eng (16)    |  5-15  |
| **Manufacturing**  | 40-49      | Mfg Mgr, Production Lead, QC           | VP Mfg (18)    | 25-50  |
| **Field Ops**      | 47-59      | Field Mgr, Service Lead, Installers    | VP Field (19)  | 30-100 |
| **Administration** | 17, 60-69  | HR Mgr, Compliance, Office Mgr         | VP Admin (17)  |  5-10  |
| **Finance**        | 12, 70-79  | Accounting Mgr, Bookkeeper, Accountant | CFO (12)       |  5-10  |
| **Support**        | 80-89      | Translator, Tech Writer, Support Agent | VP Admin (17)  | 10-20  |
| **External**       | 90-99      | Client, Distributor, Installer, Vendor | —              |  100+  |

---

## 🔐 Permission Matrix (Critical Operations)

| Action           | Admin | Pres  |  CTO  |  CFO  |  COO  | Sales Mgr | Eng Mgr | Mfg Mgr | Field Mgr | Bookkeeper | Installer | Client | Viewer |
| ---------------- | :---: | :---: | :---: | :---: | :---: | :-------: | :-----: | :-----: | :-------: | :--------: | :-------: | :----: | :----: |
| System Config    |   ✅   |   ⚠️   |   ✅   |   ❌   |   ❌   |     ❌     |    ❌    |    ❌    |     ❌     |     ❌      |     ❌     |   ❌    |   ❌    |
| User Management  |   ✅   |   ✅   |   ⚠️   |   ❌   |   ⚠️   |     ❌     |    ❌    |    ❌    |     ❌     |     ❌      |     ❌     |   ❌    |   ❌    |
| Create Leads     |   ✅   |   ✅   |   ❌   |   ❌   |   ❌   |     ✅     |    ❌    |    ❌    |     ❌     |     ❌      |     ❌     |   ⚠️    |   ❌    |
| Edit Leads       |   ✅   |   ✅   |   ❌   |   ❌   |   ❌   |     ✅     |    ⚠️    |    ⚠️    |     ⚠️     |     ❌      |     ⚠️     |   ⚠️    |   ❌    |
| Assign Leads     |   ✅   |   ✅   |   ❌   |   ❌   |   ✅   |     ✅     |    ❌    |    ❌    |     ❌     |     ❌      |     ❌     |   ❌    |   ❌    |
| View Financials  |   ✅   |   ✅   |   ❌   |   ✅   |   ✅   |     ⚠️     |    ❌    |    ❌    |     ❌     |     ✅      |     ❌     |   ❌    |   ❌    |
| Approve Invoice  |   ✅   |   ✅   |   ❌   |   ✅   |   ⚠️   |     ❌     |    ❌    |    ⚠️    |     ❌     |     ❌      |     ❌     |   ❌    |   ❌    |
| Create PO        |   ✅   |   ✅   |   ⚠️   |   ✅   |   ✅   |     ❌     |    ⚠️    |    ✅    |     ❌     |     ❌      |     ❌     |   ❌    |   ❌    |
| Schedule Install |   ✅   |   ✅   |   ❌   |   ❌   |   ✅   |     ✅     |    ❌    |    ❌    |     ✅     |     ❌      |     ⚠️     |   ⚠️    |   ❌    |
| Generate Reports |   ✅   |   ✅   |   ✅   |   ✅   |   ✅   |     ✅     |    ✅    |    ✅    |     ✅     |     ✅      |     ❌     |   ⚠️    |   ✅    |

Legend: ✅ = Full Permission | ⚠️ = Restricted Permission | ❌ = No Permission

---

## 🎯 Application-Specific Role Subsets

### **Subset 1: DemoCRM (Sales & Engineering Focus)**
```
Roles: 1-2, 10-22, 25-34, 40, 83-84, 99
Active: 15 roles
Purpose: Customer relationship management, lead tracking
```

### **Subset 2: Manufacturing System**
```
Roles: 1-2, 10-14, 18, 40-44, 70-75
Active: 12 roles
Purpose: Production planning, QC, inventory, billing
```

### **Subset 3: Field Service Platform**
```
Roles: 1-2, 10, 14, 19, 47, 50-54, 80-85, 92
Active: 11 roles
Purpose: Service scheduling, installations, technician tracking
```

### **Subset 4: Finance Portal**
```
Roles: 1-2, 10, 12, 70-75, 99
Active: 8 roles
Purpose: Accounting, invoicing, reporting
```

### **Subset 5: Client Portal**
```
Roles: 1-2, 90, 99
Active: 4 roles
Purpose: Customer self-service, account management
```

### **Subset 6: Partner Portal (Distributors & Installers)**
```
Roles: 1-2, 91-92, 94-95, 99
Active: 6 roles
Purpose: Distribution, installation management, partner operations
```

---

## 🗄️ Database Implementation

### **SQL: Create Complete Role Structure**

```sql
-- LAYER 1: System Maintenance (1-9)
INSERT INTO roles (role_id, role, created_at, updated_at) VALUES
  (1, 'Super Admin', NOW(), NOW()),
  (2, 'Admin', NOW(), NOW());

-- LAYER 2: Executive (10-19)
INSERT INTO roles (role_id, role, created_at, updated_at) VALUES
  (10, 'President', NOW(), NOW()),
  (11, 'CTO', NOW(), NOW()),
  (12, 'CFO', NOW(), NOW()),
  (13, 'COO', NOW(), NOW()),
  (14, 'VP Operations', NOW(), NOW()),
  (15, 'VP Sales', NOW(), NOW()),
  (16, 'VP Engineering', NOW(), NOW()),
  (17, 'VP Administration', NOW(), NOW()),
  (18, 'VP Manufacturing', NOW(), NOW()),
  (19, 'VP Field Operations', NOW(), NOW());

-- LAYER 3: Sales (20-29)
INSERT INTO roles (role_id, role, created_at, updated_at) VALUES
  (20, 'Sales Manager', NOW(), NOW()),
  (21, 'Partner Manager', NOW(), NOW()),
  (22, 'Sales Lead', NOW(), NOW()),
  (25, 'Sales User', NOW(), NOW()),
  (26, 'Partner Sales', NOW(), NOW()),
  (27, 'Distributor', NOW(), NOW()),
  (28, 'Client', NOW(), NOW());

-- LAYER 4: Engineering (30-39)
INSERT INTO roles (role_id, role, created_at, updated_at) VALUES
  (30, 'Engineering Manager', NOW(), NOW()),
  (31, 'Tech Lead', NOW(), NOW()),
  (32, 'Technician 1', NOW(), NOW()),
  (33, 'Technician 2', NOW(), NOW()),
  (34, 'Translator', NOW(), NOW());

-- LAYER 5: Manufacturing (40-49)
INSERT INTO roles (role_id, role, created_at, updated_at) VALUES
  (40, 'Manufacturing Manager', NOW(), NOW()),
  (41, 'Production Lead', NOW(), NOW()),
  (42, 'Quality Lead', NOW(), NOW()),
  (43, 'Production Technician', NOW(), NOW()),
  (44, 'Quality Technician', NOW(), NOW()),
  (47, 'Installer', NOW(), NOW());

-- LAYER 6: Field Operations (50-59)
INSERT INTO roles (role_id, role, created_at, updated_at) VALUES
  (50, 'Field Manager', NOW(), NOW()),
  (51, 'Service Lead', NOW(), NOW()),
  (52, 'Field Technician', NOW(), NOW()),
  (53, 'Installer Lead', NOW(), NOW()),
  (54, 'Field Installer', NOW(), NOW());

-- LAYER 7: Administration (60-69)
INSERT INTO roles (role_id, role, created_at, updated_at) VALUES
  (60, 'HR Manager', NOW(), NOW()),
  (61, 'Compliance Manager', NOW(), NOW()),
  (62, 'Office Manager', NOW(), NOW()),
  (63, 'HR Specialist', NOW(), NOW()),
  (64, 'Compliance Officer', NOW(), NOW());

-- LAYER 8: Finance & Accounting (70-79)
INSERT INTO roles (role_id, role, created_at, updated_at) VALUES
  (70, 'Accounting Manager', NOW(), NOW()),
  (71, 'Bookkeeper', NOW(), NOW()),
  (72, 'AP/AR Clerk', NOW(), NOW()),
  (73, 'Accountant', NOW(), NOW()),
  (74, 'Finance Analyst', NOW(), NOW()),
  (75, 'Auditor', NOW(), NOW());

-- LAYER 9: Specialized Support (80-89)
INSERT INTO roles (role_id, role, created_at, updated_at) VALUES
  (80, 'Translator', NOW(), NOW()),
  (81, 'Technical Writer', NOW(), NOW()),
  (82, 'Training Specialist', NOW(), NOW()),
  (83, 'Support Manager', NOW(), NOW()),
  (84, 'Support Agent', NOW(), NOW()),
  (85, 'QA Specialist', NOW(), NOW());

-- LAYER 10: External Partners (90-99)
INSERT INTO roles (role_id, role, created_at, updated_at) VALUES
  (90, 'Client', NOW(), NOW()),
  (91, 'Distributor', NOW(), NOW()),
  (92, 'Installer', NOW(), NOW()),
  (93, 'Vendor', NOW(), NOW()),
  (94, 'Partner', NOW(), NOW()),
  (95, 'Contractor', NOW(), NOW()),
  (96, 'Guest', NOW(), NOW()),
  (99, 'Viewer', NOW(), NOW());

-- Verify all roles
SELECT * FROM roles WHERE role_id >= 10 ORDER BY role_id;
```

---

## 📝 Language File Keys (i18n)

```php
// English Language File
$lang = [
    // System Maintenance
    'role_id_1' => 'Super Admin',
    'role_id_2' => 'Admin',
    
    // Executive
    'role_id_10' => 'President',
    'role_id_11' => 'Chief Technology Officer',
    'role_id_12' => 'Chief Financial Officer',
    'role_id_13' => 'Chief Operations Officer',
    'role_id_14' => 'VP Operations',
    'role_id_15' => 'VP Sales',
    'role_id_16' => 'VP Engineering',
    'role_id_17' => 'VP Administration',
    'role_id_18' => 'VP Manufacturing',
    'role_id_19' => 'VP Field Operations',
    
    // Sales
    'role_id_20' => 'Sales Manager',
    'role_id_21' => 'Partner Manager',
    'role_id_22' => 'Sales Lead',
    'role_id_25' => 'Sales User',
    'role_id_26' => 'Partner Sales',
    'role_id_27' => 'Distributor',
    'role_id_28' => 'Client',
    
    // Engineering
    'role_id_30' => 'Engineering Manager',
    'role_id_31' => 'Tech Lead',
    'role_id_32' => 'Senior Technician',
    'role_id_33' => 'Junior Technician',
    'role_id_34' => 'Translator',
    
    // Manufacturing
    'role_id_40' => 'Manufacturing Manager',
    'role_id_41' => 'Production Lead',
    'role_id_42' => 'Quality Lead',
    'role_id_43' => 'Production Technician',
    'role_id_44' => 'Quality Technician',
    'role_id_47' => 'Installer',
    
    // Field Operations
    'role_id_50' => 'Field Manager',
    'role_id_51' => 'Service Lead',
    'role_id_52' => 'Field Technician',
    'role_id_53' => 'Installer Lead',
    'role_id_54' => 'Field Installer',
    
    // Administration
    'role_id_60' => 'HR Manager',
    'role_id_61' => 'Compliance Manager',
    'role_id_62' => 'Office Manager',
    'role_id_63' => 'HR Specialist',
    'role_id_64' => 'Compliance Officer',
    
    // Finance
    'role_id_70' => 'Accounting Manager',
    'role_id_71' => 'Bookkeeper',
    'role_id_72' => 'Accounts Payable/Receivable',
    'role_id_73' => 'Accountant',
    'role_id_74' => 'Finance Analyst',
    'role_id_75' => 'Auditor',
    
    // Support
    'role_id_80' => 'Translator',
    'role_id_81' => 'Technical Writer',
    'role_id_82' => 'Training Specialist',
    'role_id_83' => 'Support Manager',
    'role_id_84' => 'Support Agent',
    'role_id_85' => 'QA Specialist',
    
    // External
    'role_id_90' => 'Client',
    'role_id_91' => 'Distributor',
    'role_id_92' => 'Installer',
    'role_id_93' => 'Vendor',
    'role_id_94' => 'Partner',
    'role_id_95' => 'Contractor',
    'role_id_96' => 'Guest',
    'role_id_99' => 'Viewer',
];
```

---

## 🚀 Implementation Roadmap

### **Phase 1: Core Infrastructure (Week 1)**
- [ ] Add all 50+ roles to database
- [ ] Update language files (English, Spanish, etc.)
- [ ] Document role hierarchy and relationships

### **Phase 2: Authorization Engine (Week 2)**
- [ ] Create universal permission matrix
- [ ] Build role-based access helper
- [ ] Implement department-level access control

### **Phase 3: Team & Assignment System (Week 3)**
- [ ] Create sales_teams table
- [ ] Create team_members junction table
- [ ] Create lead_assignments table
- [ ] Build team management UI

### **Phase 4: DemoCRM Integration (Week 3-4)**
- [ ] Update Leads model with access control
- [ ] Implement lead visibility by role
- [ ] Add partner isolation logic
- [ ] Create role management interface

### **Phase 5: Extended Systems (Weeks 4-6)**
- [ ] Manufacturing system integration
- [ ] Field operations system integration
- [ ] Finance/accounting integration
- [ ] Client portal integration

### **Phase 6: Testing & Deployment (Week 6-7)**
- [ ] Comprehensive role access testing
- [ ] Multi-application scenario testing
- [ ] Security audit
- [ ] Production deployment

---

## 📊 Growth Headroom

```
Current Active Roles: 50
Reserved Slots: 30+

Expansion Capacity:
├─ System Maintenance: 3-9 (7 available)
├─ Executive: 11-14 (5 available)
├─ Sales: 23-24, 29 (3 available)
├─ Engineering: 35-39 (5 available)
├─ Manufacturing: 45-46, 48-49 (4 available)
├─ Field Ops: 55-59 (5 available)
├─ Admin: 65-69 (5 available)
├─ Finance: 76-79 (4 available)
└─ Support: 86-89 (4 available)

Future Additions Possible:
✓ Compliance Officer
✓ Security Officer
✓ Business Analyst
✓ Project Manager
✓ Consultant
✓ Advisor
✓ Board Member
✓ Custom department roles
```

---

## ✅ Key Advantages

✅ **Universal**: Reusable across any enterprise system  
✅ **Scalable**: 100+ role IDs with expansion room  
✅ **Enterprise-Grade**: Supports complex organizational structures  
✅ **Multi-Department**: Sales, Engineering, Mfg, Finance, Operations, Support  
✅ **Partner-Friendly**: Dedicated external stakeholder roles  
✅ **Flexible**: Application subsets can use role combinations  
✅ **Backward Compatible**: Preserves existing roles 1-9  
✅ **Internationalized**: Full language support  
✅ **Future-Proof**: 30+ reserved slots for growth  

---

## 📚 Reference Architecture

```
Universal RBAC System
    │
    ├─── DemoCRM Application (Subset)
    │    ├─ Sales Module (20-28)
    │    ├─ Engineering Module (30-34)
    │    ├─ Leads Management (20-28, 30-34)
    │    └─ Reports (all roles)
    │
    ├─── Manufacturing System (Subset)
    │    ├─ Production (40-44)
    │    ├─ Quality Control (42, 44)
    │    ├─ Inventory (70-75)
    │    └─ Billing (70-75)
    │
    ├─── Field Operations (Subset)
    │    ├─ Service Management (50-54)
    │    ├─ Installation (47, 50-54)
    │    ├─ Scheduling (50-54)
    │    └─ Tracking (50-54)
    │
    ├─── Finance System (Subset)
    │    ├─ Accounting (70-75)
    │    ├─ Invoicing (70-75)
    │    └─ Reporting (70-75)
    │
    ├─── Client Portal (Subset)
    │    ├─ Account Management (90)
    │    └─ Self-Service (90, 99)
    │
    └─── Partner Portal (Subset)
         ├─ Distribution (91, 94)
         ├─ Installation (92)
         └─ Vendor (93)
```

---

## 📋 Summary

**What This Provides:**

1. **50+ Universally Applicable Roles** across 10 layers
2. **Support for 11+ Departments** (Sales, Eng, Mfg, Finance, Ops, Support, Admin, etc.)
3. **External Stakeholder Access** (Clients, Distributors, Installers, Vendors, Partners)
4. **Specialized Support Roles** (Bookkeeper, Translator, QA, Support Agent, etc.)
5. **Flexible Application Subsets** for different business systems
6. **30+ Reserved Slots** for future expansion
7. **Complete Organizational Hierarchy** from President to field workers
8. **International Support** with language file integration

---

**Status**: 📌 READY FOR COMPREHENSIVE REVIEW

**Next Steps:**
1. Review role allocation across all departments
2. Validate with key stakeholders
3. Adjust permissions matrix as needed
4. Plan database migration
5. Begin Phase 1 implementation

---

*Last Updated: 2025-01-15*  
*Version: 3.0 - Universal Multi-Department RBAC System*
*For use across: DemoCRM, Manufacturing, Field Operations, Finance, Client Portal, Partner Portal*