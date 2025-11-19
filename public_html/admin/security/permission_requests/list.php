<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();

$dir = 'admin';
$subdir = 'security';
$sub_subdir = 'permission_requests';
$sub_sub_subdir = '';
$page = 'list';

$table_page = true;
$table_header = true;

$search = true;
$button_showall = true;
$button_new = true;
$button_refresh = false;
$button_back = false;
$paginate = true;

require LANG . '/en.php';
$title = $lang['permission_requests'] ?? 'Permission Requests';
$new_button = $lang['request_new'] ?? 'New Request';

$title_icon = '<i class="fa-solid fa-file-contract" aria-hidden="true"></i>';
$new_icon = '<i class="fa-solid fa-plus" aria-hidden="true"></i>';

require HEADER;
require BODY;
require NAV;
require LISTOPEN;
require 'get.php';
require LISTCLOSE;
require FOOTER;
