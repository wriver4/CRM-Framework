<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();

// Check admin permissions
if (!in_array(8, $_SESSION['permissions'] ?? [])) {
    header("Location: " . URL . "/dashboard");
    exit;
}

$dir = 'admin/languages';
$subdir = 'list';
$page = 'list';

$languagesModel = new Languages();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'activate':
                if (isset($_POST['language_id'])) {
                    $languagesModel->activateLanguage((int)$_POST['language_id']);
                    $success = "Language activated successfully.";
                }
                break;
                
            case 'deactivate':
                if (isset($_POST['language_id'])) {
                    if ($languagesModel->deactivateLanguage((int)$_POST['language_id'])) {
                        $success = "Language deactivated successfully.";
                    } else {
                        $error = "Cannot deactivate the default language.";
                    }
                }
                break;
                
            case 'set_default':
                if (isset($_POST['language_id'])) {
                    if ($languagesModel->setDefaultLanguage((int)$_POST['language_id'])) {
                        $success = "Default language updated successfully.";
                    } else {
                        $error = "Failed to update default language.";
                    }
                }
                break;
        }
    }
}

$allLanguages = $languagesModel->getAllLanguages();

// Load language file
require LANG . '/en.php';
$title = $lang['manage_languages'] ?? 'Manage Languages';
$title_icon = '<i class="fa fa-globe" aria-hidden="true"></i>';

$table_page = true;
$table_header = true;

require HEADER;
require BODY;
require NAV;
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <?= $title_icon; ?>&ensp;<?= $title; ?>
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="<?= URL; ?>/admin/languages/add" class="btn btn-sm btn-outline-secondary">
                            <i class="fa fa-plus me-1"></i>
                            <?= $lang['add_language'] ?? 'Add Language'; ?>
                        </a>
                    </div>
                </div>
            </div>
                
                <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa fa-check-circle me-2"></i>
                    <?= htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fa fa-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <?= $lang['system_languages'] ?? 'System Languages'; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="languagesTable" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th><?= $lang['language'] ?? 'Language'; ?></th>
                                        <th><?= $lang['locale_code'] ?? 'Locale Code'; ?></th>
                                        <th><?= $lang['file_name'] ?? 'File Name'; ?></th>
                                        <th><?= $lang['status'] ?? 'Status'; ?></th>
                                        <th><?= $lang['file_exists'] ?? 'File Exists'; ?></th>
                                        <th><?= $lang['actions'] ?? 'Actions'; ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($allLanguages as $language): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <strong><?= htmlspecialchars($language['name_native']); ?></strong>
                                                    <?php if ($language['name_english'] !== $language['name_native']): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($language['name_english']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if ($language['is_default']): ?>
                                                <span class="badge bg-primary ms-2">
                                                    <?= $lang['default'] ?? 'Default'; ?>
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <code><?= htmlspecialchars($language['locale_code']); ?></code>
                                        </td>
                                        <td>
                                            <code><?= htmlspecialchars($language['file_name']); ?></code>
                                        </td>
                                        <td>
                                            <?php if ($language['is_active']): ?>
                                            <span class="badge bg-success">
                                                <i class="fa fa-check me-1"></i>
                                                <?= $lang['active'] ?? 'Active'; ?>
                                            </span>
                                            <?php else: ?>
                                            <span class="badge bg-secondary">
                                                <i class="fa fa-times me-1"></i>
                                                <?= $lang['inactive'] ?? 'Inactive'; ?>
                                            </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $fileExists = $languagesModel->validateLanguageFile($language['file_name']);
                                            if ($fileExists): ?>
                                            <span class="badge bg-success">
                                                <i class="fa fa-check me-1"></i>
                                                <?= $lang['yes'] ?? 'Yes'; ?>
                                            </span>
                                            <?php else: ?>
                                            <span class="badge bg-danger">
                                                <i class="fa fa-times me-1"></i>
                                                <?= $lang['no'] ?? 'No'; ?>
                                            </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <?php if (!$language['is_default']): ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="set_default">
                                                    <input type="hidden" name="language_id" value="<?= $language['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-primary" 
                                                            title="<?= $lang['set_as_default'] ?? 'Set as Default'; ?>"
                                                            onclick="return confirm('<?= $lang['confirm_set_default'] ?? 'Set this as the default language?'; ?>')">
                                                        <i class="fa fa-star"></i>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                                
                                                <?php if ($language['is_active']): ?>
                                                <?php if (!$language['is_default']): ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="deactivate">
                                                    <input type="hidden" name="language_id" value="<?= $language['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-warning"
                                                            title="<?= $lang['deactivate'] ?? 'Deactivate'; ?>"
                                                            onclick="return confirm('<?= $lang['confirm_deactivate'] ?? 'Deactivate this language?'; ?>')">
                                                        <i class="fa fa-pause"></i>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                                <?php else: ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="activate">
                                                    <input type="hidden" name="language_id" value="<?= $language['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-success"
                                                            title="<?= $lang['activate'] ?? 'Activate'; ?>">
                                                        <i class="fa fa-play"></i>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                                
                                                <a href="<?= URL; ?>/admin/languages/edit/<?= $language['id']; ?>" 
                                                   class="btn btn-sm btn-outline-secondary"
                                                   title="<?= $lang['edit'] ?? 'Edit'; ?>">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fa fa-info-circle me-2"></i>
                                    <?= $lang['language_management_info'] ?? 'Language Management Information'; ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li><?= $lang['language_info_1'] ?? 'Language files must exist in the admin/languages/ directory'; ?></li>
                                    <li><?= $lang['language_info_2'] ?? 'Only active languages are available for user selection'; ?></li>
                                    <li><?= $lang['language_info_3'] ?? 'The default language cannot be deactivated'; ?></li>
                                    <li><?= $lang['language_info_4'] ?? 'Setting a new default language will automatically activate it'; ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fa fa-chart-bar me-2"></i>
                                    <?= $lang['language_statistics'] ?? 'Language Statistics'; ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $activeCount = count(array_filter($allLanguages, fn($l) => $l['is_active']));
                                $totalCount = count($allLanguages);
                                $filesExist = count(array_filter($allLanguages, fn($l) => $languagesModel->validateLanguageFile($l['file_name'])));
                                ?>
                                <div class="row text-center">
                                    <div class="col-4">
                                        <h4 class="text-primary"><?= $totalCount; ?></h4>
                                        <small><?= $lang['total_languages'] ?? 'Total Languages'; ?></small>
                                    </div>
                                    <div class="col-4">
                                        <h4 class="text-success"><?= $activeCount; ?></h4>
                                        <small><?= $lang['active_languages'] ?? 'Active Languages'; ?></small>
                                    </div>
                                    <div class="col-4">
                                        <h4 class="text-info"><?= $filesExist; ?></h4>
                                        <small><?= $lang['files_available'] ?? 'Files Available'; ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#languagesTable').DataTable({
            "pageLength": 25,
            "order": [[ 3, "desc" ], [ 0, "asc" ]],
            "columnDefs": [
                { "orderable": false, "targets": 5 }
            ]
        });
    });
</script>

<?php require FOOTER; ?>