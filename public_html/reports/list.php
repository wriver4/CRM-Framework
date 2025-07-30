<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$dir = 'reports';
$page = 'list';

$table_page = true;
$table_header = true;

$search = true;
$button_new = false;
$button_showall = true;
$refresh = false;
$paginate = false;

require LANG . '/en.php';
$title = $lang['reports'];


$title_icon = '<i class="fa-solid fa-lightbulb"></i><i class="fa-regular fa-lightbulb"></i>';
$new_icon = '<i class="fa-solid fa-lightbulb"></i>';

require HEADER;
require BODY;
require NAV;
require LISTOPEN;
// require 'get.php';
echo '<h4 class="text-center"> Future Home of ' . $title . ' Management</h4>';
require LISTCLOSE;
require FOOTER;
