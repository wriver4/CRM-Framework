<?php
$browser_language = "en";
$not->loggedin();
if (file_exists(LANG . '/' . $browser_language . '.php')) {
  require_once LANG . '/' . $browser_language . '.php';
} else {
  require_once LANG . '/en.php';
}
require_once 'nav_start.php';
require_once 'nav_item_status.php';
require_once 'nav_item_tickets.php';
require_once 'nav_item_properties.php';
require_once 'nav_item_contacts.php';
require_once 'nav_item_systems.php';
require_once 'nav_item_users.php';
require_once 'nav_item_testing.php';
require_once 'nav_item_develop.php';
require_once 'nav_item_reports.php';
?>
</ul>
<?php
require_once 'nav_item_profile.php';
require_once 'nav_end.php';
