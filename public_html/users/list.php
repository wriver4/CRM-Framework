<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$dir = 'users';
$subdir='';
$page = 'list';

$table_page = true;
$table_header = true;

$search = true;
$paginate = true;
$button_new = true;
$button_showall = false;
$button_back = false;
$button_refresh = false;

require LANG . '/en.php';
$title = $lang['users'];   
$new_button = $lang['user_new'];

$title_icon = '<i class="fa fa-users" aria-hidden="true"></i>';
$new_icon = '<i class="fa fa-user" aria-hidden="true"></i>';

require HEADER;
require BODY;
require NAV;
require LISTOPEN;
require 'get.php';
require LISTCLOSE;
require FOOTER;
