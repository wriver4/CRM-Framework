<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
// Direct routing variables - these determine page navigation and template inclusion
$dir = 'prospecting';
$subdir = '';
$page = 'list';

$table_page = true;
$table_header = true;

$search = true;
$paginate = true;
$button_new = true; // Prospecting can create new leads
$button_showall = false;
$button_back = false;
$button_refresh = true;

require LANG . '/en.php';
$title = $lang['prospecting'] ?? 'Prospecting';   
$new_button = $lang['lead_new'] ?? 'New Lead';

$title_icon = '<i class="fa-solid fa-search" aria-hidden="true"></i>';
$new_icon = '<i class="fa-solid fa-user-plus" aria-hidden="true"></i>';

require HEADER;
require BODY;
require NAV;
require LISTOPEN;
require 'get.php';
require LISTCLOSE;
require FOOTER;