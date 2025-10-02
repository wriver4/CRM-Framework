# DemoCRM - Customer Relationship Management System

## 🚨 CRITICAL: REMOTE CODING PROJECT

**⚠️ THIS IS A REMOTE CODING PROJECT ⚠️**  
All development is performed on a **remote server via SFTP** at `sftp://159.203.116.150:222/home/democrm`

**Key Remote Development Facts:**
- Changes are **immediately live** on the production server
- File ownership must be `democrm:democrm` (use `ssh wswg "chown democrm:democrm /path/to/file"`)
- Full remote path: `/run/user/1000/gvfs/sftp:host=159.203.116.150,port=222/home/democrm`
- See `.zencoder/rules/repo.md` for complete remote development guidelines

## 🚨 IMPORTANT: READ DOCUMENTATION FIRST

**Before working on this project, you MUST read the comprehensive documentation:**

### 📋 Essential Reading Order:
1. **[Repository Overview](.zencoder/rules/repo.md)** - Start here for project context
2. **[Setup & Installation](.zencoder/rules/setup-installation.md)** - Development environment setup
3. **[Architecture Complete](.zencoder/rules/architecture-complete.md)** - System design patterns
4. **[Development Workflow](.zencoder/rules/development-workflow.md)** - Coding standards and processes

### 📁 Complete Documentation Structure:
```
.zencoder/rules/
├── repo.md                           # 🎯 START HERE - Project overview
├── setup-installation.md             # 🛠️ Development setup guide
├── architecture-complete.md          # 🏗️ System architecture
├── development-workflow.md           # 💻 Development patterns
├── database-operations.md            # 🗄️ Database guidelines
├── testing-complete.md               # 🧪 Testing framework
├── internationalization-complete.md  # 🌍 Multilingual support
├── project-structure.md              # 📂 Directory organization
├── dir_structure.md                  # 📋 Complete file tree
└── core-system.md                    # ⚙️ Core system configuration
```

## Quick Start

### Prerequisites
- PHP 8.3+
- MariaDB 10+
- Apache/Nginx
- Composer

### Installation
```bash
# 1. Install dependencies
composer install

# 2. Configure database in classes/Core/Database.php
# 3. Import database: sql/democrm_democrm_structure.sql  
# 4. Point web server to public_html/ directory
# 5. Verify at: http://localhost/democrm
```

### 🚨 Critical File Ownership
**Files MUST be owned by `democrm:democrm` on server:**
```bash
ssh wswg "chown democrm:democrm /path/to/new/file"
```

## Testing
```bash
# Full test suite
php tests/enhanced_integration_test.php --comprehensive

# Language validation
php tests/language_test.php --comprehensive

# Browser tests
npx playwright test
```

## Project Overview

**Project**: DemoCRM - Customer Relationship Management System  
**Technology Stack**: PHP 8.3+, MariaDB 10+, Bootstrap 5, JavaScript  
**Architecture**: Non-Traditional PHP with custom Database singleton pattern extending most classes  
**Language Support**: English (primary), Spanish (complete translations)  
**Database Schema**: Refer to `/sql/democrm_democrm_structure.sql` for detailed schema information  
**Testing**: Automated testing using PHPUnit, Playwright and manual QA processes

### Key Architectural Features:
- **Database Singleton Pattern**: Most classes extend the Database class for consistent DB access
- **Direct File Routing**: Non-traditional framework with direct file-based routing
- **Template System**: Modular template components (HEADER → NAV → BODY → FOOTER)
- **Security First**: Prepared statements, input validation, audit logging
- **Internationalization**: Complete translation system with no hardcoded text

## 📖 Documentation Philosophy

This project uses a **documentation-first approach**. All critical information is centralized in `.zencoder/rules/` to ensure:

- ✅ **Consistency** across all development work
- ✅ **Security** through proper guidelines
- ✅ **Quality** via established patterns
- ✅ **Efficiency** with clear procedures

**Remember**: The rules exist to maintain system integrity, security, and scalability. Following them prevents issues and saves time.

---

**💡 New to the project?** Start with `.zencoder/rules/repo.md` for complete context and mandatory reading list.