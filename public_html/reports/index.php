<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();

$status = new Status();
$dir = "reports";
$subdir = "status";
$page = "index";

$table_page = true;
$table_header = true;

$search = true;
$button_new = false;
$button_showall = false;
$button_refresh = true;
$button_back = false;
$paginate = false;

require LANG . '/en.php';
$title = $lang['reports'];

$title_icon = '<i class="fa-regular fa-copy"></i>';

require HEADER;
require BODY;
require NAV;
require SECTIONOPEN;
?>
<p></p>
<div class="container">
  <div class="row">
    <div class="col-4">
      <span><b>Client App</b></span><br>
      <span class="text-success">Status</span><br>
      <span class="text-success">Users</span><br>
      <span class="text-success">Systems</span><br>
      <span class="text-success">Security Roles</span><br>
      <span class="text-success">Security Permissions</span><br>
      <span class="text-success">Security Roles-Permissions</span><br>
      <span class="text-success">Properties</span><br>
      <span class="text-success">Contacts</span><br>
      <span class="text-success">Auditing</span><br>
      <span class="text-success">Session Management Part 1</span><br>
    </div>
    <div class="col-8">
      <!-- Client App -->
      <span class="text-success"><b>https://clientrnd.waveguardco.net/ ask Mark for a UN and PW</b></span><br>
      <!-- Status -->
      <span class="text-success">Operational</span><br>
      <!-- Users -->
      <span class="text-success">Operational - Fix JavaScript</span><br>
      <!-- Systems -->
      <span class="text-success">Operational - Fix Javascript</span><br>
      <!-- Security Roles -->
      <span class="text-success">Operational - Some features Manually Implemented</span><br>
      <!-- Security Permissions -->
      <span class="text-success">Operational - Some features Manually Implemented - Incomplete Data</span><br>
      <!-- Security Roles-Permissions -->
      <span class="text-success">Operational - Some features Manually Implemented - Incomplete Data</span><br>
      <!-- Properties -->
      <span class="text-success">Operational</span><span> - Some data needs clarification to be Added</span><br>
      <!-- Contacts -->
      <span class="text-success">Operational</span><span> - Some data needs clarification to be Added</span><br>
      <!-- Auditing -->
      <span class="text-success">Operational - Some features Manually Implemented</span><br>
      <!-- Session Management Part 1 -->
      <span class="text-success">Operational</span><br>
    </div>
  </div>
  <hr>
  <div class="row">
    <div class="col-4">
      <span><b>Administration App</b></span><br>
      <span class="text-success">Status</span><br>
      <span class="text-success">Tickets</span><br>
      <span class="text-success">Properties</span><br>
      <span class="text-success">Contacts</span><br>
      <span class="text-success">Systems</span><br>
      <span class="text-success">Users</span><br>
      <span class="text-success">Testing</span><br>
      <span class="text-success">Reports</span><br>
      <span class="text-success">Security</span><br>
      <span class="text-success">Help</span><br>
      <span class="text-success">Messages</span>
    </div>
    <div class="col-8">
      <!-- Administration App -->
      <span class="text-success"></span><br>
      <!-- Status -->
      <span class="text-success">Fully Functional</span><br>
      <!-- Tickets -->
      <span class="text-danger">Work in progress - Target Next Week</span><br>
      <!-- Properties -->
      <span class="text-success">Fully Functional</span><br>
      <!-- Contacts-->
      <span class="text-success">Fully Functional - <span class="text-danger">Validation Needs User
          Input</span></span><br>
      <!-- Systems -->
      <span class="text-success">Fully Functional - <span class="text-danger">Needs Data Entry</span></span><br>
      <!-- Users -->
      <span class="text-success">Fully Functional</span><br>
      <!-- Testing -->
      <span class="text-success">Fully Functional</span><br>
      <!-- Reports -->
      <span class="text-success">Work in Progress</span><br>
      <!-- Security -->
      <span class="text-danger">Work in progress - <span class="text-success">Implemented at Role
          level</span></span><br>
      <!-- Help -->
      <span class="text-success">Fully Functional</span><br>
      <!-- Messages -->
      <span class="text-danger">Work in progress - <span class="text-success">Fire Map Implemented</span></span>
    </div>
  </div>
</div>
</div>
<?php
require SECTIONCLOSE;
require FOOTER;
