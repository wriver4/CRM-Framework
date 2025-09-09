<?php
$not->loggedin();

// Load user's language preference or fall back to browser/default
$languagesModel = new Languages();
$userLanguage = null;

if (Sessions::isLoggedIn()) {
    $userLanguage = $languagesModel->getUserLanguage(Sessions::getUserId());
} else {
    $userLanguage = $languagesModel->getBrowserLanguage();
}

// Set language in session if not already set
if ($userLanguage && (!Sessions::getLanguageId() || Sessions::getLanguageId() != $userLanguage['id'])) {
    Sessions::setLanguage($userLanguage['id'], $userLanguage['iso_code'], $userLanguage['file_name']);
}

// Load the language file
$languageFile = LANG . '/' . ($userLanguage['file_name'] ?? 'en.php');
if (file_exists($languageFile)) {
    require_once $languageFile;
} else {
    require_once LANG . '/en.php';
}
require_once 'nav_start.php';
require_once 'nav_item_leads_new.php';
require_once 'nav_item_leads_list.php';
//require_once 'nav_item_leads_view.php';
//require_once 'nav_item_leads_edit.php';
//require_once 'nav_item_leads_delete.php';
require_once 'nav_item_contacts.php';
require_once 'nav_item_reports.php';
?>
        </ul>
        <?php require_once 'nav_item_profile.php'; ?>
<?php
require_once 'nav_end.php';