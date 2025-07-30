<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
$dir = basename(dirname(__FILE__));
$page = substr(basename(__FILE__), 0, strrpos(basename(__FILE__), '.'));

$table_page = false;

$firethreatmessage = '';
$systemmessage = '';

require LANG . '/en.php';
$title = 'Welcome to Administration!';

$title_icon = '<i class="fa-solid fa-bars-progress" aria-hidden="true"></i>';

require HEADER;
require BODY;
require NAV;
require SECTIONOPEN;
?>
<div class="container">
  <div class="row">
    <h4 class="text-danger">Fire Threat Map</h4>
  </div>
  <div class="row">
    <div class="col-12">
      <div class="clearfix"
           style="height:70vh;">
        <iframe width="100%"
                style="height:100%;"
                src="https://wfca.com/fire-map?lng=-104.84297526073&lat=39.5844205374117&zoom=8.00"
                title="WFCA Fire Map">
        </iframe>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-12">
      <h4 class="text-danger">System Messages</h4>
      <p>Future Home for System Messages</p>
    </div>
  </div>
</div>
<?php
require SECTIONCLOSE;
require FOOTER;
