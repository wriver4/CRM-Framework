<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();

// Direct routing variables - these determine page navigation and template inclusion
$dir = 'profile';
$subdir = 'language';
$page = 'language';

$languagesModel = new Languages();
$usersModel = new Users();
$currentUserId = Sessions::getUserId();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['language_id'])) {
    $newLanguageId = (int)$_POST['language_id'];
    
    // Validate the language exists and is active
    $selectedLanguage = $languagesModel->getLanguageById($newLanguageId);
    if ($selectedLanguage && $selectedLanguage['is_active'] == 1) {
        // Update user's language preference
        if ($usersModel->updateLanguagePreference($currentUserId, $newLanguageId)) {
            // Update session with new language
            Sessions::setLanguage($selectedLanguage['id'], $selectedLanguage['iso_code'], $selectedLanguage['file_name']);
            
            // Redirect to refresh the page with new language
            header("Location: " . URL . "/profile/language?updated=1");
            exit;
        } else {
            $error = "Failed to update language preference. Please try again.";
        }
    } else {
        $error = "Invalid language selection.";
    }
}

// Get current user language and active languages
$currentLanguage = $languagesModel->getUserLanguage($currentUserId);
$activeLanguages = $languagesModel->getActiveLanguages();

// Load language file
require LANG . '/en.php';
$title = $lang['language_preferences'] ?? 'Language Preferences';
$title_icon = '<i class="fa fa-language" aria-hidden="true"></i>';

$table_page = false;

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
            </div>
            
            <?php if (isset($_GET['updated']) && $_GET['updated'] == '1'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa fa-check-circle me-2"></i>
                <?= $lang['language_updated_successfully'] ?? 'Language preference updated successfully!'; ?>
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
            
            <div class="row">
                <div class="col-md-8 col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fa fa-globe me-2"></i>
                                <?= $lang['select_language'] ?? 'Select Your Preferred Language'; ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="language_id" class="form-label">
                                        <?= $lang['preferred_language'] ?? 'Preferred Language'; ?>
                                    </label>
                                    <select class="form-select" id="language_id" name="language_id" required>
                                        <?php foreach ($activeLanguages as $language): ?>
                                        <option value="<?= $language['id']; ?>" 
                                                <?= ($currentLanguage['id'] == $language['id']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($language['name_native']); ?>
                                            <?php if ($language['name_english'] !== $language['name_native']): ?>
                                                (<?= htmlspecialchars($language['name_english']); ?>)
                                            <?php endif; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">
                                        <?= $lang['language_help_text'] ?? 'This will change the language for all interface elements.'; ?>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <h6><?= $lang['current_language'] ?? 'Current Language'; ?>:</h6>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-primary me-2">
                                            <?= htmlspecialchars($currentLanguage['locale_code'] ?? 'en-US'); ?>
                                        </span>
                                        <span>
                                            <?= htmlspecialchars($currentLanguage['name_native'] ?? 'English (United States)'); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="<?= URL; ?>/profile/edit" class="btn btn-secondary">
                                        <?= $lang['back'] ?? '<i class="fa fa-arrow-left" aria-hidden="true"></i>&ensp;Back'; ?>
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-save me-2"></i>
                                        <?= $lang['save_language_preference'] ?? 'Save Language Preference'; ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fa fa-info-circle me-2"></i>
                                <?= $lang['language_info'] ?? 'Language Information'; ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    <strong><?= $lang['available_languages'] ?? 'Available Languages'; ?>:</strong>
                                    <ul class="list-unstyled mt-2">
                                        <?php foreach ($activeLanguages as $language): ?>
                                        <li class="mb-1">
                                            <span class="badge bg-light text-dark me-2">
                                                <?= htmlspecialchars($language['locale_code']); ?>
                                            </span>
                                            <?= htmlspecialchars($language['name_native']); ?>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <div class="col-sm-6">
                                    <strong><?= $lang['language_notes'] ?? 'Notes'; ?>:</strong>
                                    <ul class="mt-2">
                                        <li><?= $lang['language_note_1'] ?? 'Language changes take effect immediately'; ?></li>
                                        <li><?= $lang['language_note_2'] ?? 'Your preference is saved to your user profile'; ?></li>
                                        <li><?= $lang['language_note_3'] ?? 'Contact administrator to request additional languages'; ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require FOOTER; ?>