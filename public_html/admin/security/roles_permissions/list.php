<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
// Direct routing variables - these determine page navigation and template inclusion
$dir = 'admin';
$subdir = 'security';
$sub_subdir = 'roles_permissions';
$sub_sub_subdir = '';
$page = 'list';

$table_page = true;
$table_header = true;

$search = false;
$button_showall = true;
$button_new = false;
$button_refresh = false;
$button_back = false;
$paginate = true;

require LANG . '/en.php';
$title = $lang['roles_permissions'];
$new_button = $lang['roles_permissions_new'];

$title_icon = '<i class="fa-solid fa-user-shield" aria-hidden="true"></i><i class="fa-solid fa-user-lock"></i>';
$new_icon = '<i class="fa-solid fa-user-lock" aria-hidden="true"></i>';

require HEADER;
require BODY;
require NAV;
require_once LISTOPEN;
require 'get.php';
require LISTCLOSE;
require FOOTER;
