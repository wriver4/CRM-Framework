<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
// Direct routing variables - these determine page navigation and template inclusion
$dir = 'contacts';
$subdir='';
$page = 'list';

$table_page = true;
$table_header = true;

$search = true;
$button_showall = false;
$button_new = true;
$button_refresh = false;
$button_back = false;
$paginate = true;
require LANG . '/en.php';
$title = $lang['contacts'];
$new_button = $lang['contact_new'];

$title_icon = '<i class="fa-solid fa-address-book"></i><i class="fa-solid fa-address-book"></i>';
$new_icon = '<i class="fa-solid fa-address-book"></i>';

require HEADER;
require BODY;
require NAV;
require LISTOPEN;
require 'get.php';
require LISTCLOSE;
require FOOTER;
