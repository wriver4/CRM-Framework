<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$dir = 'contracting';
$subdir = '';
$page = 'list';

$table_page = true;
$table_header = true;

$search = true;
$paginate = true;
$button_new = false; // Contracting entries are created from prospects, not directly
$button_showall = false;
$button_back = false;
$button_refresh = true;

require LANG . '/en.php';
$title = $lang['contracting'] ?? 'Contracting';   
$new_button = $lang['contracting_new'] ?? 'New Contract';

$title_icon = '<i class="fa-solid fa-file-contract" aria-hidden="true"></i>';
$new_icon = '<i class="fa-solid fa-plus" aria-hidden="true"></i>';

require HEADER;
require BODY;
require NAV;
require LISTOPEN;
require 'get.php';
require LISTCLOSE;
require FOOTER;