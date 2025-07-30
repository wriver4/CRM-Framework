<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$dir = 'security';
$subdir = 'permissions';
$page = 'list';

$table_page = true;
$table_header = true;

$search = true;
$button_showall = true;
$button_new = true;
$button_refresh = false;
$button_back = false;
$paginate = true;

require_once LANG . '/en.php';
$title = $lang['permissions'];
$new_button = $lang['permission_new'];

$title_icon = '<i class="fa-solid fa-user-shield" aria-hidden="true"></i><i class="fa-solid fa-user-shield" aria-hidden="true"></i>';
$new_icon = '<i class="fa-solid fa-user-shield" aria-hidden="true"></i>';

require HEADER;
require BODY;
require NAV;
require LISTOPEN;
require 'get.php';
require LISTCLOSE;
require FOOTER;
