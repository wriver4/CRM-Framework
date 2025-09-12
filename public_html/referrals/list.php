<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
// Direct routing variables - these determine page navigation and template inclusion
$dir = 'referrals';
$subdir = '';
$page = 'list';

$table_page = true;
$table_header = true;

$search = true;
$paginate = true;
$button_new = false; // Referrals are created from leads, not directly
$button_showall = false;
$button_back = false;
$button_refresh = true;

require LANG . '/en.php';
$title = $lang['referrals'] ?? 'Referrals';   
$new_button = $lang['referral_new'] ?? 'New Referral';

$title_icon = '<i class="fa-solid fa-share-nodes" aria-hidden="true"></i>';
$new_icon = '<i class="fa-solid fa-plus" aria-hidden="true"></i>';

require HEADER;
require BODY;
require NAV;
require LISTOPEN;
require 'get.php';
require LISTCLOSE;
require FOOTER;