<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$dir = "leads";
$page = "new";

$table_page = false;

#$last_user_id = $users->last_row_id() + 1;

require LANG . '/en.php';
$title = $lang['lead_new'];

$title_icon = '<i class="fa-solid fa-pencil"></i><i class="fa-solid fa-pencil"></i>';

require HEADER;
require BODY;
require NAV;
require SECTIONOPEN;
?>





<?php
require SECTIONCLOSE;
require FOOTER;