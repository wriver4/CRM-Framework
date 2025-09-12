<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
// Direct routing variables - these determine page navigation and template inclusion
$dir = 'prospects';
$subdir = '';
$page = 'list';

$table_page = true;
$table_header = true;

$search = true;
$paginate = true;
$button_new = false; // Prospects are created from leads, not directly
$button_showall = false;
$button_back = false;
$button_refresh = true;

require LANG . '/en.php';
$title = $lang['prospects'] ?? 'Prospects';   
$new_button = $lang['prospect_new'] ?? 'New Prospect';

$title_icon = '<i class="fa-solid fa-bullseye" aria-hidden="true"></i>';
$new_icon = '<i class="fa-solid fa-plus" aria-hidden="true"></i>';

require HEADER;
require BODY;
require NAV;
require LISTOPEN;
require 'get.php';
require LISTCLOSE;
require FOOTER;