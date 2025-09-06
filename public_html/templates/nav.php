<?php
$browser_language = "en";
$not->loggedin();
if (file_exists(LANG . '/' . $browser_language . '.php')) {
  require_once LANG . '/' . $browser_language . '.php';
} else {
  require_once LANG . '/en.php';
}
require_once 'nav_start.php';
require_once 'nav_item_leads_new.php';
require_once 'nav_item_leads_list.php';
require_once 'nav_item_email_import.php';
//require_once 'nav_item_leads_view.php';
//require_once 'nav_item_leads_edit.php';
//require_once 'nav_item_leads_delete.php';
require_once 'nav_item_contacts.php';
require_once 'nav_item_reports.php';
?>
</ul>
<?php
require_once 'nav_item_profile.php';
require_once 'nav_end.php';