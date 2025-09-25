<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
// Direct routing variables - these determine page navigation and template inclusion
$dir = 'leads';
$subdir = '';
$page = 'list';

$table_page = true;
$table_header = true;

$search = true;
$paginate = true;
$button_new = true;
$button_showall = false;
$button_back = false;
$button_refresh = true;

require LANG . '/en.php';
$title = $lang['leads'] ?? 'Leads';   
$new_button = $lang['lead_new'] ?? 'New Lead';

$title_icon = '<i class="fa-solid fa-users-line" aria-hidden="true"></i>';
$new_icon = '<i class="fa-solid fa-user-plus" aria-hidden="true"></i>';

require HEADER;
require BODY;
require NAV;

// Check for stage change notification
if (isset($_SESSION['stage_moved'])) {
    $stage_info = $_SESSION['stage_moved'];
    echo '<div class="container-fluid mt-3">';
    echo '<div class="alert alert-info alert-dismissible fade show" role="alert">';
    echo '<div class="d-flex align-items-center">';
    echo '<i class="fa-solid fa-info-circle fa-2x me-3 text-info"></i>';
    echo '<div class="flex-grow-1">';
    echo '<h5 class="alert-heading mb-2">Lead Moved to ' . htmlspecialchars($stage_info['stage_name']) . '</h5>';
    echo '<p class="mb-3">' . htmlspecialchars($stage_info['message']) . '</p>';
    echo '<div class="d-flex gap-2">';
    echo '<a href="' . htmlspecialchars($stage_info['url']) . '" class="btn btn-info btn-sm">';
    echo '<i class="fa-solid fa-external-link-alt me-1"></i>Go to ' . htmlspecialchars($stage_info['stage_name']);
    echo '</a>';
    echo '<button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="alert">';
    echo '<i class="fa-solid fa-times me-1"></i>Stay Here';
    echo '</button>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div>';
    echo '</div>';
    
    // Clear the notification so it doesn't show again
    unset($_SESSION['stage_moved']);
}

require LISTOPEN;
require 'get.php';
require LISTCLOSE;
require FOOTER;
