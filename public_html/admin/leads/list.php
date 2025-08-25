<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$dir = 'admin';
$subdir = 'leads';
$page = 'list';

$table_page = true;
$table_header = true;

$search = true;
$paginate = true;
$button_new = false;  // No new button in admin view
$button_showall = false;
$button_back = false;
$button_refresh = true;

require LANG . '/en.php';
$title = 'Admin Leads Management';
$title_icon = '<i class="fa-solid fa-user-shield" aria-hidden="true"></i>';

require HEADER;
require BODY;
require NAV;
require LISTOPEN;
require 'get.php';
require LISTCLOSE;
require FOOTER;
?>