<?php
/**
 * Autoloaded Classes Viewer
 * 
 * Shows all autoloaded classes in the CRM system for debugging and development
 * Only accessible to super administrators
 * 
 * @author CRM Framework
 * @version 1.0
 */

require_once dirname(__DIR__, 3) . '/config/system.php';

// Check if user is logged in
$not->loggedin();

// Check if user has admin permissions (permission 1030 - admin.access)
if (!in_array(1030, $_SESSION['permissions'] ?? [])) {
    header('Location: ' . URL . '/index');
    exit;
}

// Direct routing variables
$dir = basename(dirname(__FILE__));
$page = substr(basename(__FILE__), 0, strrpos(basename(__FILE__), '.'));

$table_page = false;
$system_message = '';

require LANG . '/en.php';
$title = $lang['tools_autoloaded_classes'] ?? 'Autoloaded Classes';
$title_icon = '<i class="fa-solid fa-code" aria-hidden="true"></i>';

require HEADER;
require BODY;
require NAV;
require SECTIONOPEN;

// Get all declared classes
$allClasses = get_declared_classes();

// Filter to get only our custom classes (exclude PHP built-in classes)
$customClasses = [];
$frameworkClasses = [];
$vendorClasses = [];

foreach ($allClasses as $className) {
    $reflection = new ReflectionClass($className);
    $filename = $reflection->getFileName();
    
    if ($filename === false) {
        // Built-in PHP class, skip
        continue;
    }
    
    // Categorize based on file path
    if (strpos($filename, DOCROOT) === 0) {
        // Our framework classes
        $relativePath = str_replace(DOCROOT, '', $filename);
        $frameworkClasses[] = [
            'name' => $className,
            'file' => $relativePath,
            'full_path' => $filename,
            'parent' => $reflection->getParentClass() ? $reflection->getParentClass()->getName() : null,
            'interfaces' => $reflection->getInterfaceNames(),
            'methods_count' => count($reflection->getMethods()),
            'properties_count' => count($reflection->getProperties()),
            'is_abstract' => $reflection->isAbstract(),
            'is_final' => $reflection->isFinal(),
            'namespace' => $reflection->getNamespaceName()
        ];
    } else {
        // Vendor/external classes
        $vendorClasses[] = [
            'name' => $className,
            'file' => $filename,
            'parent' => $reflection->getParentClass() ? $reflection->getParentClass()->getName() : null,
            'namespace' => $reflection->getNamespaceName()
        ];
    }
}

// Sort classes by name
usort($frameworkClasses, function($a, $b) {
    return strcmp($a['name'], $b['name']);
});

usort($vendorClasses, function($a, $b) {
    return strcmp($a['name'], $b['name']);
});

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><?= $title_icon ?> <?= $title ?></h1>
                <div>
                    <button class="btn btn-outline-primary btn-sm" onclick="refreshData()">
                        <i class="fa fa-refresh"></i> Refresh
                    </button>
                </div>
            </div>
            
            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title"><?= count($frameworkClasses) ?></h4>
                                    <p class="card-text">Framework Classes</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fa fa-code fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title"><?= count($vendorClasses) ?></h4>
                                    <p class="card-text">Vendor Classes</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fa fa-cube fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title"><?= count($allClasses) ?></h4>
                                    <p class="card-text">Total Classes</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fa fa-list fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs" id="classTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="framework-tab" data-bs-toggle="tab" data-bs-target="#framework" type="button" role="tab">
                        <i class="fa fa-code"></i> Framework Classes (<?= count($frameworkClasses) ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="vendor-tab" data-bs-toggle="tab" data-bs-target="#vendor" type="button" role="tab">
                        <i class="fa fa-cube"></i> Vendor Classes (<?= count($vendorClasses) ?>)
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="classTabContent">
                <!-- Framework Classes Tab -->
                <div class="tab-pane fade show active" id="framework" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="mb-0">Framework Classes</h5>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control form-control-sm" id="frameworkSearch" placeholder="Search framework classes...">
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0" id="frameworkTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Class Name</th>
                                            <th>File Path</th>
                                            <th>Parent Class</th>
                                            <th>Interfaces</th>
                                            <th>Methods</th>
                                            <th>Properties</th>
                                            <th>Flags</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($frameworkClasses as $class): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($class['name']) ?></strong>
                                                <?php if ($class['namespace']): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($class['namespace']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <code><?= htmlspecialchars($class['file']) ?></code>
                                            </td>
                                            <td>
                                                <?php if ($class['parent']): ?>
                                                <span class="badge bg-secondary"><?= htmlspecialchars($class['parent']) ?></span>
                                                <?php else: ?>
                                                <span class="text-muted">None</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($class['interfaces'])): ?>
                                                    <?php foreach ($class['interfaces'] as $interface): ?>
                                                    <span class="badge bg-info me-1"><?= htmlspecialchars($interface) ?></span>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                <span class="text-muted">None</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge bg-primary"><?= $class['methods_count'] ?></span></td>
                                            <td><span class="badge bg-success"><?= $class['properties_count'] ?></span></td>
                                            <td>
                                                <?php if ($class['is_abstract']): ?>
                                                <span class="badge bg-warning">Abstract</span>
                                                <?php endif; ?>
                                                <?php if ($class['is_final']): ?>
                                                <span class="badge bg-danger">Final</span>
                                                <?php endif; ?>
                                                <?php if (!$class['is_abstract'] && !$class['is_final']): ?>
                                                <span class="text-muted">Normal</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vendor Classes Tab -->
                <div class="tab-pane fade" id="vendor" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="mb-0">Vendor Classes</h5>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control form-control-sm" id="vendorSearch" placeholder="Search vendor classes...">
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0" id="vendorTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Class Name</th>
                                            <th>File Path</th>
                                            <th>Parent Class</th>
                                            <th>Namespace</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($vendorClasses as $class): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($class['name']) ?></strong></td>
                                            <td><code><?= htmlspecialchars($class['file']) ?></code></td>
                                            <td>
                                                <?php if ($class['parent']): ?>
                                                <span class="badge bg-secondary"><?= htmlspecialchars($class['parent']) ?></span>
                                                <?php else: ?>
                                                <span class="text-muted">None</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($class['namespace']): ?>
                                                <code><?= htmlspecialchars($class['namespace']) ?></code>
                                                <?php else: ?>
                                                <span class="text-muted">Global</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('frameworkSearch').addEventListener('keyup', function() {
    filterTable('frameworkTable', this.value);
});

document.getElementById('vendorSearch').addEventListener('keyup', function() {
    filterTable('vendorTable', this.value);
});

function filterTable(tableId, searchTerm) {
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const cells = row.getElementsByTagName('td');
        let found = false;
        
        for (let j = 0; j < cells.length; j++) {
            if (cells[j].textContent.toLowerCase().includes(searchTerm.toLowerCase())) {
                found = true;
                break;
            }
        }
        
        row.style.display = found ? '' : 'none';
    }
}

function refreshData() {
    location.reload();
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?php
require SECTIONCLOSE;
require FOOTER;
?>