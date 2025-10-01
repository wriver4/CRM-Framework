# DemoCRM - Customer Relationship Management System

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
- **Technology**: PHP 8.3+, MariaDB, Bootstrap 5
- **Architecture**: Non-traditional framework with direct file routing
- **Languages**: English (primary), Spanish (complete)
- **Testing**: PHPUnit, Playwright, Enhanced Integration Framework

## 📖 Documentation Philosophy

This project uses a **documentation-first approach**. All critical information is centralized in `.zencoder/rules/` to ensure:

- ✅ **Consistency** across all development work
- ✅ **Security** through proper guidelines
- ✅ **Quality** via established patterns
- ✅ **Efficiency** with clear procedures

**Remember**: The rules exist to maintain system integrity, security, and scalability. Following them prevents issues and saves time.

---

**💡 New to the project?** Start with `.zencoder/rules/repo.md` for complete context and mandatory reading list.