---
title: DemoCRM Role Restructure Proposal
date: 2025-01-15
version: 2.0
status: PROPOSAL
---

# 🏢 DemoCRM Complete Role Restructure Proposal

## Executive Summary

This document outlines a comprehensive role restructuring strategy that:
- ✅ Preserves existing **Super Admin (1)** and **Admin (2)** for system maintenance
- ✅ Reserves **Roles 1-9** exclusively for CRM software system maintainers
- ✅ Establishes **Role 10** as President (executive leadership)
- ✅ Builds organizational hierarchy from Role 11 onwards
- ✅ Maintains backward compatibility with existing permissions

---

## 📊 Complete Role Structure

### **LAYER 1: SYSTEM MAINTENANCE (Roles 1-9)**
Reserved exclusively for CRM software system maintainers

```
┌─────────────────────────────────────┐
│  CRM SOFTWARE SYSTEM MAINTAINERS    │
│         (Roles 1-9)                 │
└─────────────────────────────────────┘
         │                 │
    ┌────▼─────┐      ┌────▼─────┐
    │ ROLE: 1  │      │ ROLE: 2   │
    │SUPER ADMIN       │  ADMIN    │
    │(Dev/Tech Lead)   │(Technical)│
    │Full Authority    │Full Auth  │
    │(Keep as-is)      │(Keep as-is)
    └──────────┘      └──────────┘
    
    ✅ Reserved for future system maintenance roles: 3-9
```

|   ID    | Role Name       | Purpose                         | Scope           | Users | Status     |
| :-----: | --------------- | ------------------------------- | --------------- | :---: | ---------- |
|  **1**  | **Super Admin** | Development/Technical Lead      | Full CRM System |  1-2  | ✅ KEEP     |
|  **2**  | **Admin**       | System Administrator            | Full CRM System |  1-3  | ✅ KEEP     |
| **3-9** | *Reserved*      | Future system maintenance roles | Full CRM System |   —   | 📌 RESERVED |

---

### **LAYER 2: EXECUTIVE LEADERSHIP (Roles 10-15)**
C-Suite and executive decision makers

```
                 ┌──────────────────┐
                 │   ROLE ID: 10    │
                 │   PRESIDENT      │
                 │ (Full Authority) │
                 └────────┬─────────┘
                          │
        ┌─────────────────┼─────────────────┐
        │                 │                 │
   ┌────▼─────┐      ┌───▼────┐       ┌───▼────┐
   │ROLE: 13  │      │ROLE: 12│       │ROLE: 11│
   │VP SALES  │      │VP ENGG │       │VP ADMIN│
   │          │      │        │       │        │
   └──────────┘      └────────┘       └────────┘
```

|   ID   | Role Name          | Department     | Title                | Authority | Lead Access | Users |
| :----: | ------------------ | -------------- | -------------------- | :-------: | :---------: | :---: |
| **10** | **President**      | Executive      | President            |   ⭐⭐⭐⭐⭐   |  All Leads  |   1   |
| **11** | **VP Admin**       | Administration | VP of Administration |   ⭐⭐⭐⭐    |  All Leads  |   1   |
| **12** | **VP Engineering** | Engineering    | VP of Engineering    |   ⭐⭐⭐⭐    |  All Leads  |   1   |
| **13** | **VP Sales**       | Sales          | VP of Sales          |   ⭐⭐⭐⭐    |  All Leads  |   1   |

---

### **LAYER 3: DEPARTMENT MANAGEMENT (Roles 20-25)**
Team leads and department managers

```
    Sales Manager      Engineering Manager       Partner Manager
    (Role 20)          (Role 21)                 (Role 22)
         │                  │                          │
    ┌────┴────┐         ┌───┴────┐         ┌──────────┴────────┐
    │          │         │        │         │                   │
 Sales User  Partner  Tech Lead Technicians Partner Sales Users
 (Role 30)   Sales    (Role 23) (Roles 24-25) (Role 31)
             (Role 31)
```

|   ID   | Role Name               | Department  | Reports To     | Authority | Lead Access | Team Size |
| :----: | ----------------------- | ----------- | -------------- | :-------: | :---------: | :-------: |
| **20** | **Sales Manager**       | Sales       | VP Sales       |    ⭐⭐     |  All Leads  |   5-10    |
| **21** | **Engineering Manager** | Engineering | VP Engineering |    ⭐⭐     |  Assigned   |    5-8    |
| **22** | **Partner Manager**     | Sales       | VP Sales       |    ⭐⭐     | Team Leads  |    2-5    |

---

### **LAYER 4: TEAM LEADS & SPECIALISTS (Roles 23-29)**
Individual contributors with leadership responsibilities

|   ID   | Role Name        | Department  | Reports To       | Authority | Lead Access |  Team Size  |
| :----: | ---------------- | ----------- | ---------------- | :-------: | :---------: | :---------: |
| **23** | **Tech Lead**    | Engineering | Eng Manager (21) |     ⭐     |  Assigned   | Coordinates |
| **24** | **Technician 1** | Engineering | Eng Manager (21) |     ⭐     |  Assigned   | Individual  |
| **25** | **Technician 2** | Engineering | Eng Manager (21) |     ⭐     |  Assigned   | Individual  |

---

### **LAYER 5: INDIVIDUAL CONTRIBUTORS (Roles 30-39)**
Operational team members

```
Org Chart (Contributors):

Sales Manager (20)           Engineering Manager (21)      Partner Manager (22)
      │                             │                              │
      │                        ┌────┼────┐                         │
      │                        │    │    │                         │
   ┌──▼────┐            ┌─────▼─┐ ┌▼──┐ ┌▼──┐            ┌──────▼──┐
   │Sales  │            │Tech  │ │T1 │ │T2 │            │Partner │
   │User   │            │Lead  │ │   │ │   │            │Sales   │
   │(30)   │            │(23)  │ │24 │ │25 │            │(31)    │
   └───────┘            └──────┘ └───┘ └───┘            └────────┘
```

|   ID   | Role Name         | Department | Reports To           | Authority |  Lead Access  | Typical Count |
| :----: | ----------------- | ---------- | -------------------- | :-------: | :-----------: | :-----------: |
| **30** | **Sales User**    | Sales      | Sales Manager (20)   |     ⭐     |   All Leads   |     10-20     |
| **31** | **Partner Sales** | Sales      | Partner Manager (22) |     ⭐     | Assigned Only |     5-15      |
| **39** | *Reserved*        | —          | —                    |     —     |       —       |       —       |

---

### **LAYER 6: UTILITY ROLES (Roles 40-49)**
Special purpose roles not tied to specific departments

|    ID     | Role Name  | Purpose              |  Lead Access  | Authority | Use Case                       |
| :-------: | ---------- | -------------------- | :-----------: | :-------: | ------------------------------ |
|  **40**   | **Viewer** | Read-Only Access     | Assigned Only |     ⭐     | Consultants, auditors, clients |
| **41-49** | *Reserved* | Future utility roles |       —       |     —     | —                              |

---

## 🔍 Detailed Role Descriptions

### **System Maintenance Layer (1-9)**

#### **Role 1: Super Admin**
- **Purpose**: Development and technical leadership
- **Access**: Full CRM system + database + server
- **Responsibilities**: 
  - System architecture decisions
  - Database schema management
  - Critical security patches
  - Emergency system recovery
- **Current Users**: Technical Lead/Developer
- **Status**: ✅ KEEP AS-IS

#### **Role 2: Admin**
- **Purpose**: System administration and maintenance
- **Access**: Full CRM system + user management + backups
- **Responsibilities**:
  - User account creation/management
  - Database backups and restoration
  - System monitoring
  - Technical support
- **Current Users**: System Administrator(s)
- **Status**: ✅ KEEP AS-IS

#### **Roles 3-9: Reserved**
- Reserved for future system maintenance specialists
- Examples: Database Admin (Role 3), Security Admin (Role 4), etc.

---

### **Executive Leadership Layer (10-15)**

#### **Role 10: President**
- **Title**: President / Chief Executive Officer
- **Department**: Executive
- **Reports To**: Board of Directors
- **Lead Access**: All leads, all data
- **Permissions**:
  - Full system access
  - Strategic decisions
  - All user management
  - Budget approval
  - Override any departmental decision
- **Typical Count**: 1

#### **Role 11: VP Admin**
- **Title**: VP of Administration
- **Department**: Administration
- **Reports To**: President (10)
- **Lead Access**: All leads
- **Permissions**:
  - User management (non-system)
  - Department oversight
  - Process management
  - Compliance oversight
  - Resource allocation

#### **Role 12: VP Engineering**
- **Title**: VP of Engineering
- **Department**: Engineering
- **Reports To**: President (10)
- **Lead Access**: All leads (for engineering tasks)
- **Permissions**:
  - Engineering team management
  - Project oversight
  - Technical standard setting
  - Quality assurance decisions

#### **Role 13: VP Sales**
- **Title**: VP of Sales
- **Department**: Sales
- **Reports To**: President (10)
- **Lead Access**: All leads
- **Permissions**:
  - Sales strategy
  - Sales team management
  - Revenue targets
  - Customer relationship oversight

---

### **Department Management Layer (20-25)**

#### **Role 20: Sales Manager**
- **Title**: Sales Manager
- **Department**: Sales
- **Reports To**: VP Sales (13)
- **Lead Access**: All leads (internal sales)
- **Permissions**:
  - Manage internal sales team (5-10 people)
  - Assign leads to team members
  - View team performance
  - Approve deals
- **Typical Count**: 1-3

#### **Role 21: Engineering Manager**
- **Title**: Engineering Manager
- **Department**: Engineering
- **Reports To**: VP Engineering (12)
- **Lead Access**: Assigned engineering tasks
- **Permissions**:
  - Manage engineering team (5-8 people)
  - Assign technical tasks
  - Resource allocation
  - Quality oversight

#### **Role 22: Partner Manager**
- **Title**: Partner Sales Manager
- **Department**: Sales
- **Reports To**: VP Sales (13)
- **Lead Access**: Only partner team's assigned leads
- **Permissions**:
  - Manage partner sales reps (2-5 people)
  - Assign leads to partner team
  - Track partner performance
  - Commission management
- **Typical Count**: 1-2 (per partner tier)

---

### **Team Leads & Specialists (23-29)**

#### **Role 23: Tech Lead**
- **Title**: Technical Lead
- **Department**: Engineering
- **Reports To**: Engineering Manager (21)
- **Lead Access**: Assigned technical tasks only
- **Permissions**:
  - Coordinate technical work
  - Assign tickets to technicians
  - Technical decision-making
  - Mentor junior technicians

#### **Role 24: Technician 1 (Senior)**
- **Title**: Senior Technician
- **Department**: Engineering
- **Reports To**: Engineering Manager (21)
- **Lead Access**: Assigned tasks only
- **Permissions**:
  - Execute technical tasks
  - Document solutions
  - Handle escalations
  - Train Technician 2

#### **Role 25: Technician 2 (Junior)**
- **Title**: Junior Technician
- **Department**: Engineering
- **Reports To**: Engineering Manager (21)
- **Lead Access**: Assigned tasks only
- **Permissions**:
  - Execute routine technical tasks
  - Document procedures
  - Follow technical guidelines
  - Support Technician 1

---

### **Individual Contributors (30-39)**

#### **Role 30: Sales User**
- **Title**: Sales Representative (Internal)
- **Department**: Sales
- **Reports To**: Sales Manager (20)
- **Lead Access**: All leads (company-wide)
- **Permissions**:
  - View/edit all leads
  - Create opportunities
  - Update customer info
  - Generate reports
- **Typical Count**: 10-20+

#### **Role 31: Partner Sales**
- **Title**: Partner Sales Representative (3rd Party)
- **Department**: Sales
- **Reports To**: Partner Manager (22)
- **Lead Access**: **ONLY assigned leads**
- **Permissions**:
  - View/edit assigned leads
  - Create opportunities on assigned leads
  - Submit proposals
  - No lead assignment privileges
- **Typical Count**: 5-15+ per partner

---

### **Utility Roles (40-49)**

#### **Role 40: Viewer**
- **Title**: Read-Only User
- **Purpose**: Consultants, auditors, customers
- **Lead Access**: Explicitly assigned leads only
- **Permissions**:
  - View assigned leads (read-only)
  - No editing
  - No assignment
  - Report generation only
- **Typical Count**: Unlimited (external stakeholders)

---

## 🗄️ Database Implementation

### **SQL: Add Missing Roles**

```sql
-- Add missing roles to complete the structure
INSERT INTO roles (role_id, role, created_at, updated_at) VALUES
  -- LAYER 2: Executive (10-15)
  (10, 'President', NOW(), NOW()),
  (11, 'VP Admin', NOW(), NOW()),
  (12, 'VP Engineering', NOW(), NOW()),
  (13, 'VP Sales', NOW(), NOW()),
  
  -- LAYER 3: Department Managers (20-25)
  (20, 'Sales Manager', NOW(), NOW()),
  (21, 'Engineering Manager', NOW(), NOW()),
  (22, 'Partner Manager', NOW(), NOW()),
  
  -- LAYER 4: Team Leads & Specialists (23-29)
  (23, 'Tech Lead', NOW(), NOW()),
  (24, 'Technician 1', NOW(), NOW()),
  (25, 'Technician 2', NOW(), NOW()),
  
  -- LAYER 5: Individual Contributors (30-39)
  (30, 'Sales User', NOW(), NOW()),
  (31, 'Partner Sales', NOW(), NOW()),
  
  -- LAYER 6: Utility Roles (40-49)
  (40, 'Viewer', NOW(), NOW());

-- Verify insertion
SELECT * FROM roles WHERE role_id >= 10 ORDER BY role_id;
```

---

## 📈 Visual Hierarchy Summary

```
┌──────────────────────────────────────────────────────────────┐
│                    COMPLETE ORGANIZATION                     │
└──────────────────────────────────────────────────────────────┘

                        LAYER 1: SYSTEM (1-2)
                    ┌─────────────────────────┐
                    │ Super Admin (1)         │
                    │ Admin (2)               │
                    │ [System Maintenance]    │
                    └──────────┬──────────────┘
                               │
                        ┌──────▼──────┐
                        │ LAYER 2 (10-15)
                        │ EXECUTIVE   │
                        │             │
                        │ President (10)
                        └──────┬──────┘
                    ┌──────────┼──────────┐
                    │          │          │
                ┌───▼──┐  ┌──▼──┐  ┌───▼──┐
                │ (11) │  │(12) │  │ (13) │
                │ VP   │  │ VP  │  │ VP   │
                │Admin │  │Engg │  │Sales │
                └───┬──┘  └──┬──┘  └───┬──┘
                    │        │        │
            ┌───────┼────┬───┴─────┬──┴──────┐
            │       │    │         │         │
        ┌───▼──┐ ┌──▼───┐ ┌──┴──┐ ┌──▼───┐ ┌──▼────┐
        │(20)  │ │(21)  │ │(22) │ │(23)  │ │(24-25)│
        │Sales │ │Eng   │ │Part │ │Tech  │ │Tech   │
        │Mgr   │ │Mgr   │ │Mgr  │ │Lead  │ │1-2    │
        └───┬──┘ └──┬───┘ └──┬──┘ └──┬───┘ └───┬───┘
            │       │        │       │         │
        ┌───▼──┐ ┌──▼───┐ ┌──▼──┐  │      (Reports to
        │(30)  │ │(23)  │ │(31) │  └─ Eng Mgr 21)
        │Sales │ │Tech  │ │Part │
        │User  │ │Lead  │ │Sale │
        └──────┘ └──┬───┘ └─────┘
                 ┌──┴──┐
                 │(24) │ (25)│
                 │Tech1│ Tech2
                 │Senior│Junior
                 └─────┘
```

---

## 🔐 Permission Matrix

| Role          |  ID   | Super Admin | Admin | Pres  | VPAdmin | VPEng | VPSales | SalesMgr | EngMgr | PartMgr | TechLead | Tech1 | Tech2 | SalesUser | PartSales | Viewer |
| ------------- | :---: | :---------: | :---: | :---: | :-----: | :---: | :-----: | :------: | :----: | :-----: | :------: | :---: | :---: | :-------: | :-------: | :----: |
| Create Lead   |   ✅   |      ✅      |   ✅   |   ✅   |    ✅    |   ✅   |    ✅    |    ⚠️     |   ⚠️    |    ✅    |    ⚠️     |   ⚠️   |   ❌   |     ✅     |
| Edit Lead     |   ✅   |      ✅      |   ✅   |   ✅   |    ✅    |   ✅   |    ✅    |    ⚠️     |   ⚠️    |    ✅    |    ⚠️     |   ⚠️   |   ❌   |     ✅     |
| Delete Lead   |   ✅   |      ✅      |   ✅   |   ✅   |    ❌    |   ❌   |    ❌    |    ❌     |   ❌    |    ❌    |    ❌     |   ❌   |   ❌   |     ❌     |
| Assign Lead   |   ✅   |      ✅      |   ✅   |   ✅   |    ✅    |   ✅   |    ✅    |    ✅     |   ⚠️    |    ✅    |    ⚠️     |   ❌   |   ❌   |     ❌     |
| Manage Users  |   ✅   |      ✅      |   ✅   |   ✅   |    ✅    |   ❌   |    ❌    |    ❌     |   ❌    |    ❌    |    ❌     |   ❌   |   ❌   |     ❌     |
| System Config |   ✅   |      ✅      |   ✅   |   ⚠️   |    ⚠️    |   ❌   |    ❌    |    ❌     |   ❌    |    ❌    |    ❌     |   ❌   |   ❌   |     ❌     |
| View Reports  |   ✅   |      ✅      |   ✅   |   ✅   |    ✅    |   ✅   |    ✅    |    ✅     |   ✅    |    ✅    |    ✅     |   ✅   |   ✅   |     ✅     |

Legend: ✅ = Full Permission | ⚠️ = Restricted Permission | ❌ = No Permission

---

## 📋 Internationalization (Language Keys)

Each role needs language support in `/admin/languages/`:

```php
// English
'role_id_1' => 'Super Admin',
'role_id_2' => 'Admin',
'role_id_10' => 'President',
'role_id_11' => 'VP Admin',
'role_id_12' => 'VP Engineering',
'role_id_13' => 'VP Sales',
'role_id_20' => 'Sales Manager',
'role_id_21' => 'Engineering Manager',
'role_id_22' => 'Partner Manager',
'role_id_23' => 'Tech Lead',
'role_id_24' => 'Technician 1',
'role_id_25' => 'Technician 2',
'role_id_30' => 'Sales User',
'role_id_31' => 'Partner Sales',
'role_id_40' => 'Viewer',

// Spanish
'role_id_1' => 'Administrador Super',
'role_id_2' => 'Administrador',
'role_id_10' => 'Presidente',
'role_id_11' => 'VP Administración',
'role_id_12' => 'VP Ingeniería',
'role_id_13' => 'VP Ventas',
// ... etc
```

---

## 🚀 Implementation Roadmap

### **Phase 1: Database Setup (Day 1)**
- [ ] Add new roles via SQL migration
- [ ] Update language files with new role names
- [ ] Verify roles table has all 15 roles

### **Phase 2: Authorization Layer (Days 2-3)**
- [ ] Update Leads model with access control logic
- [ ] Create LeadAccess security helper class
- [ ] Implement role-based access checks

### **Phase 3: Team Assignment System (Days 3-4)**
- [ ] Create sales_teams table
- [ ] Create team_members table
- [ ] Create lead_assignments table
- [ ] Build team management UI

### **Phase 4: UI & Management (Days 4-5)**
- [ ] Create role management interface
- [ ] Build team assignment pages
- [ ] Add user role assignment UI

### **Phase 5: Testing & Validation (Days 5-7)**
- [ ] Comprehensive role access testing
- [ ] Multi-role scenario testing
- [ ] Lead access control verification
- [ ] Partner sales isolation testing

---

## ✅ Backwards Compatibility

- ✅ Roles 1-2 remain unchanged (Super Admin, Admin)
- ✅ Existing permissions maintained for roles 1-2
- ✅ New roles are additive, no breaking changes
- ✅ Database migration is safe (INSERT only, no updates to existing data)
- ✅ Users can be migrated gradually to new roles

---

## 📝 Summary

**Role Structure by Purpose:**

```
System Maintenance:    Roles 1-9   (Reserved for CRM technicians)
Executive Leadership:  Roles 10-15 (C-Suite decision makers)
Department Managers:   Roles 20-25 (Team leadership)
Team Specialists:      Roles 23-29 (Senior individual contributors)
Individual Contributors: Roles 30-39 (Operational staff)
Utility Roles:         Roles 40-49 (Special purpose)
```

**Key Numbers:**
- 14 total active roles
- 5 layers of hierarchy
- 2 preserved system maintenance roles
- 12 organizational hierarchy roles
- Unlimited scalability for future roles

---

## 🔗 Related Documents

- Database schema: `/sql/democrm_democrm_structure.sql`
- Roles model: `/classes/Models/Roles.php`
- Language files: `/admin/languages/`
- Development workflow: `/.zencoder/rules/development-workflow.md`

---

**Status**: 📌 READY FOR REVIEW AND IMPLEMENTATION

---

*Last Updated: 2025-01-15*  
*Version: 2.0 - System Maintenance Clarification*