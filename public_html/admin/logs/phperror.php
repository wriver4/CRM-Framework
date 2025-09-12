<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
// Direct routing variables - these determine page navigation and template inclusion
$dir = "logs";
$subdir = '';
$page = "view";

$table_page = false;

require_once LANG . '/en.php';
$title = $lang['phperror_view'];

$title_icon = '<i class="fa-brands fa-php"></i>';

//require 'phperror_get.php';

require HEADER;
require BODY;
require NAV;
require SECTIONOPEN;

echo '<h4 class="text-center"> Future Home of ' . $title . ' Management</h4>';

require SECTIONCLOSE;
require FOOTER;
