<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();

$dir = 'admin';
$subdir = 'security';
$sub_subdir = 'permission_approvals';
$sub_sub_subdir = '';
$page = 'list';

$table_page = true;
$table_header = true;

$search = true;
$button_showall = true;
$button_new = false;
$button_refresh = false;
$button_back = false;
$paginate = true;

require LANG . '/en.php';
$title = $lang['permission_approvals'] ?? 'Permission Approvals';
$new_button = $lang['approval_new'] ?? 'New Approval';

$title_icon = '<i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>';
$new_icon = '<i class="fa-solid fa-plus" aria-hidden="true"></i>';

require HEADER;
require BODY;
require NAV;
require LISTOPEN;
require 'get.php';
require LISTCLOSE;
require FOOTER;
