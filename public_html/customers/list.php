<?php
require_once '../../config/system.php';
$leads = new Leads();
$list = $leads->listLeads();

// Render lead list
