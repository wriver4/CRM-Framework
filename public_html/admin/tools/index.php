<?php
/**
 * Admin Tools Index
 * 
 * Landing page for administrative tools
 * Only accessible to super administrators
 * 
 * @author CRM Framework
 * @version 1.0
 */

require_once dirname(__DIR__, 3) . '/config/system.php';

// Check if user is logged in
$not->loggedin();

// Check if user has admin permissions (permission 8)
if (!in_array(8, $_SESSION['permissions'] ?? [])) {
    header('Location: ' . URL . '/index');
    exit;
}

// Direct routing variables
$dir = basename(dirname(__FILE__));
$page = substr(basename(__FILE__), 0, strrpos(basename(__FILE__), '.'));

$table_page = false;
$system_message = '';

require LANG . '/en.php';
$title = $lang['navbar_tools'] ?? 'Admin Tools';
$title_icon = '<i class="fa-solid fa-tools" aria-hidden="true"></i>';

require HEADER;
require BODY;
require NAV;
require SECTIONOPEN;
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><?= $title_icon ?> <?= $title ?></h1>
            </div>
            
            <div class="row">
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <i class="fa fa-code fa-2x text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="card-title mb-0"><?= $lang['tools_autoloaded_classes'] ?></h5>
                                </div>
                            </div>
                            <p class="card-text">View all autoloaded classes in the CRM system, including framework classes and vendor libraries. Useful for debugging and development.</p>
                            <div class="d-grid">
                                <a href="<?= URL ?>/admin/tools/autoloaded_classes" class="btn btn-primary">
                                    <i class="fa fa-eye me-2"></i>View Classes
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Placeholder for future tools -->
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-dashed">
                        <div class="card-body text-center text-muted">
                            <i class="fa fa-plus-circle fa-3x mb-3"></i>
                            <h5>More Tools Coming Soon</h5>
                            <p>Additional administrative tools will be added here as needed.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle me-2"></i>
                        <strong>Note:</strong> These tools are only accessible to users with administrative permissions. 
                        Use these tools carefully as they provide deep system insights and may contain sensitive information.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-dashed {
    border: 2px dashed #dee2e6 !important;
}

.border-dashed .card-body {
    opacity: 0.7;
}
</style>

<?php
require SECTIONCLOSE;
require FOOTER;
?>