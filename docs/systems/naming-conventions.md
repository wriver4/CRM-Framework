# DemoCRM Framework Naming Conventions

## Document Purpose
This document captures the **actual naming conventions** used in the DemoCRM framework, based on analysis of the Contacts and Leads modules. These patterns should be followed when creating new features.

**Analysis Date:** 2025-01-08  
**Modules Analyzed:** Contacts, Leads  
**Tool Created:** `suggest_naming.php`

---

## 🔑 Translation Keys

### Pattern: `{module}_{element}`

**IMPORTANT:** The framework uses a **simple two-part pattern**, NOT the four-part pattern suggested in theoretical documentation.

### ❌ INCORRECT (Theoretical Pattern)
```php
// DO NOT USE THESE PATTERNS:
$lang['email_template_list_title']        // Too verbose
$lang['email_template_create_button']     // Unnecessary suffix
$lang['email_template_name_label']        // Unnecessary suffix
```

### ✅ CORRECT (Actual Framework Pattern)
```php
// USE THESE PATTERNS:
$lang['lead_new']                  // Action: New Lead
$lang['lead_edit']                 // Action: Edit Lead
$lang['lead_delete']               // Action: Delete Lead
$lang['lead_first_name']           // Field: First Name
$lang['lead_cell_phone']           // Field: Cell Phone
$lang['lead_contact_information']  // Section: Contact Information
$lang['contact_type']              // Field: Contact Type
$lang['contact_call_order']        // Field: Call Order
```

### Common Fields (No Module Prefix)
```php
// Generic fields used across modules - NO prefix needed
$lang['first_name']        // First Name
$lang['family_name']       // Family Name
$lang['fullname']          // Full Name
$lang['cell_phone']        // Cell Phone
$lang['business_phone']    // Business Phone
$lang['alt_phone']         // Alternate Phone
$lang['personal_email']    // Personal Email
$lang['business_email']    // Business Email
$lang['alt_email']         // Alternate Email
$lang['street_address_1']  // Street Address
$lang['street_address_2']  // Street Address 2
$lang['city']              // City
$lang['state']             // State
$lang['postcode']          // Zip Code
$lang['country']           // Country
```

### Action Patterns
```php
'{module}_new'      // e.g., lead_new, contact_new, opportunity_new
'{module}_view'     // e.g., lead_view, contact_view
'{module}_edit'     // e.g., lead_edit, contact_edit
'{module}_delete'   // e.g., lead_delete, contact_delete
```

### Section Patterns
```php
'{module}_contact_information'  // Contact info section
'{module}_property_address'     // Address section
'{module}_details'              // Details section
```

### Field Patterns
```php
'{module}_{field_name}'  // e.g., lead_source, lead_stage, contact_type
```

### Key Insights
- **NO suffixes** like `_label`, `_button`, `_title`, `_placeholder`
- **NO page indicators** like `_list_`, `_edit_`, `_view_`
- **Simple and direct** naming
- **Common fields** don't need module prefix

---

## 🎨 CSS Classes (Bootstrap 5)

### Button Patterns
```html
<!-- Primary Actions (Save, Submit, Create) -->
<button class="btn btn-primary">Save</button>

<!-- Success Actions (Complete, Final Submit) -->
<button class="btn btn-success">Complete</button>

<!-- Secondary Actions (Back, Cancel) -->
<button class="btn btn-secondary">Back</button>

<!-- Info Actions (View Related) -->
<button class="btn btn-info">View Contact</button>

<!-- Danger Actions (Delete) -->
<button class="btn btn-danger">Delete</button>

<!-- Outline Variants (Navigation, Secondary Actions) -->
<button class="btn btn-outline-primary">View Details</button>
<button class="btn btn-outline-secondary">Cancel</button>

<!-- Small Buttons -->
<button class="btn btn-sm btn-primary">Save</button>
```

### Form Patterns
```html
<!-- Form Group -->
<div class="form-group pb-2">
    <!-- Label with padding -->
    <label class="pb-1">First Name</label>
    <!-- or with top/bottom padding -->
    <label class="pb-1 pt-1">Email</label>
    
    <!-- Required indicator -->
    <label class="pb-1 required">Required Field</label>
    
    <!-- Text/Email/Tel Input -->
    <input type="text" class="form-control">
    
    <!-- Select Dropdown -->
    <select class="form-select">
        <option>Choose...</option>
    </select>
    
    <!-- Checkbox -->
    <div class="form-check">
        <input type="checkbox" class="form-check-input" id="check1">
        <label class="form-check-label" for="check1">Option</label>
    </div>
</div>
```

### Layout Patterns
```html
<!-- Rows -->
<div class="row">...</div>
<div class="row align-items-start">...</div>

<!-- Columns -->
<div class="col">Auto width</div>
<div class="col-6">Half width</div>

<!-- Cards -->
<div class="card">
    <div class="card-header">Header</div>
    <div class="card-body">Content</div>
</div>

<!-- Alerts -->
<div class="alert alert-success alert-dismissible fade show">
    Success message
</div>

<!-- Accordion -->
<div class="accordion">
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button">Title</button>
        </h2>
        <div class="accordion-collapse">Content</div>
    </div>
</div>

<!-- Tables -->
<table class="table table-striped table-hover">
    <thead>...</thead>
    <tbody>...</tbody>
</table>
```

---

## 💾 Database Column Naming

### Address Field Prefixes

The framework uses **consistent prefixes** for address-related fields:

```sql
-- Personal Address (p_ prefix)
p_street_1
p_street_2
p_city
p_state
p_postcode
p_country

-- Business Address (b_ prefix)
b_street_1
b_street_2
b_city
b_state
b_postcode
b_country

-- Mailing Address (m_ prefix)
m_street_1
m_street_2
m_city
m_state
m_postcode
m_country

-- Form Data (form_ prefix)
form_street_1
form_street_2
form_city
form_state
form_postcode
form_country
```

### Common Column Names
```sql
-- IDs
id
contact_id
lead_id
user_id
property_id

-- Names
first_name
family_name
full_name

-- Contact Info
cell_phone
business_phone
alt_phone
personal_email
business_email
alt_email

-- Metadata
status
timezone
created_at
updated_at
created_by
updated_by
deleted_at
```

### Naming Rules
- Use `snake_case` for all column names
- Use descriptive prefixes for related fields
- Keep names consistent across tables
- Avoid abbreviations unless standard (e.g., `id`, `alt`)

---

## 📝 Variable Naming

### Database Results
```php
// Multiple rows
$results = $stmt->fetchAll();

// Single row
$result = $stmt->fetch();

// Row in loop
foreach ($results as $row) {
    // ...
}
```

### Class Instances (Plural Pattern)
```php
// Framework uses PLURAL names for class instances
$contacts = new Contacts($pdo);
$leads = new Leads($pdo);
$users = new Users($pdo);
$helpers = new Helpers();
```

### Lists (Descriptive Plural)
```php
$property_contacts = [];
$lead_contacts = [];
$user_roles = [];
$active_leads = [];
```

### Field Extraction (Direct Column Mapping)
```php
// Use exact column name as variable name
$first_name = $result['first_name'];
$cell_phone = $result['cell_phone'];
$p_city = $result['p_city'];
$b_street_1 = $result['b_street_1'];
```

### Prefixed Variables
```php
// Personal address fields
$p_street_1 = $result['p_street_1'];
$p_city = $result['p_city'];

// Business address fields
$b_street_1 = $result['b_street_1'];
$b_city = $result['b_city'];

// Form fields
$form_city = $_POST['form_city'];
$form_state = $_POST['form_state'];
```

---

## 📁 File Structure

### CRUD Files
```
{module}/
├── list.php      # List/index page
├── view.php      # View single record
├── new.php       # Create new record form
├── edit.php      # Edit existing record form
├── get.php       # AJAX GET endpoint
├── post.php      # AJAX POST endpoint (create/update)
└── delete.php    # AJAX DELETE endpoint
```

### API Endpoints
```php
// get.php - Handles AJAX GET requests
// Typically includes action parameter routing

// post.php - Handles AJAX POST requests
// Create and update operations

// delete.php - Handles AJAX DELETE requests
// Soft or hard delete operations
```

### File Naming Rules
- Use lowercase
- Use underscores for multi-word names
- Follow standard CRUD naming
- Keep API endpoints separate from page files

---

## 🔧 Page Structure Pattern

### Standard Page Sequence
```php
<?php
// 1. System includes
require_once '../../includes/system.php';

// 2. Authentication check
$not->loggedin();

// 3. Routing variables
$dir = 'admin';
$subdir = 'module_name';
$page = 'list';

// 4. Language file
require LANG . '/en.php';

// 5. Page configuration
$ui->assign('_page', array(
    'TITLE' => $lang['module_title'],
    'BODY' => array('class' => 'module-page'),
    'ACTIVE' => 'module'
));

// 6. Template sequence
$ui->display('HEADER');
$ui->display('NAV');
$ui->display('LISTOPEN');
?>

<!-- 7. Page content -->

<?php
$ui->display('LISTCLOSE');
$ui->display('FOOTER');
?>
```

---

## 🛠️ Using the Naming Tool

The `suggest_naming.php` tool implements all these patterns:

```bash
# Suggest translation key
php admin/tools/suggest_naming.php --type=translation_key --text="First Name" --module=contact

# Suggest CSS class
php admin/tools/suggest_naming.php --type=css_class --context="primary save button"

# Suggest variable name
php admin/tools/suggest_naming.php --type=variable --context="database results for leads"

# Suggest database column
php admin/tools/suggest_naming.php --type=db_column --context="personal street address"

# Suggest file name
php admin/tools/suggest_naming.php --type=file --context="list page" --module=opportunities

# JSON output
php admin/tools/suggest_naming.php --json --type=translation_key --text="Create New" --module=opportunity
```

---

## 📊 Pattern Comparison

### Translation Keys: Theory vs Reality

| Aspect             | Theoretical Pattern                | Actual Framework Pattern |
| ------------------ | ---------------------------------- | ------------------------ |
| **Structure**      | `{module}_{page}_{element}_{type}` | `{module}_{element}`     |
| **Example**        | `email_template_list_title`        | `email_template_title`   |
| **Suffixes**       | `_label`, `_button`, `_title`      | None                     |
| **Page Indicator** | `_list_`, `_edit_`, `_view_`       | None                     |
| **Complexity**     | 4 parts                            | 2 parts                  |
| **Verbosity**      | High                               | Low                      |

### Why the Simple Pattern?
1. **Easier to remember** - Less mental overhead
2. **Shorter code** - More readable
3. **Flexible** - Same key works across pages
4. **Consistent** - Matches existing codebase
5. **Maintainable** - Fewer keys to manage

---

## ✅ Best Practices

### DO:
- ✅ Use simple `{module}_{element}` pattern for translation keys
- ✅ Use common field names without module prefix when appropriate
- ✅ Use consistent address prefixes (p_, b_, m_, form_)
- ✅ Use plural names for class instances
- ✅ Use Bootstrap 5 standard classes
- ✅ Follow CRUD file naming conventions
- ✅ Map variables directly to column names

### DON'T:
- ❌ Add unnecessary suffixes to translation keys
- ❌ Use page indicators in translation keys
- ❌ Mix naming patterns within the same module
- ❌ Abbreviate unless it's a standard abbreviation
- ❌ Use camelCase for database columns
- ❌ Create custom CSS classes when Bootstrap has them

---

## 📚 Reference Examples

### Complete Form Field Example
```php
<!-- Translation Key: contact_first_name -->
<div class="form-group pb-2">
    <label class="pb-1 required"><?php echo $lang['first_name']; ?></label>
    <input type="text" 
           name="first_name" 
           class="form-control" 
           value="<?php echo htmlspecialchars($first_name); ?>"
           required>
</div>
```

### Complete Button Example
```php
<!-- Translation Key: contact_new -->
<a href="new.php" class="btn btn-primary">
    <?php echo $lang['contact_new']; ?>
</a>
```

### Complete Database Query Example
```php
// Variable: $results (plural for multiple rows)
$sql = "SELECT 
    id,
    first_name,
    family_name,
    cell_phone,
    p_city,
    p_state
FROM contacts
WHERE status = ?
ORDER BY family_name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute(['active']);
$results = $stmt->fetchAll();

foreach ($results as $row) {
    $first_name = $row['first_name'];
    $p_city = $row['p_city'];
    // ...
}
```

---

**Document Created:** 2025-01-08  
**Based On:** Contacts and Leads module analysis  
**Tool:** `/admin/tools/suggest_naming.php`  
**Status:** Active Reference