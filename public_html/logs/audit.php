<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$dir = "logs";
$subdir = '';
$page = "view";

$table_page = false;

require_once LANG . '/en.php';
$title = $lang['audit_view'];

$title_icon = '<i class="fa-solid fa-clipboard-check"></i>';

// require 'audit_get.php';

require HEADER;
require BODY;
require NAV;
require SECTIONOPEN;

echo '<h4 class="text-center"> Future Home of ' . $title . ' Management</h4>';

require SECTIONCLOSE;
require FOOTER;
