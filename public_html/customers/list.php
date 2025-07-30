<?php
require_once '../../classes/Leads.php';
$leads = new Leads();
$list = $leads->listLeads();

// Render lead list
?>
