<?php
require_once '../classes/Leads.php';
require_once '../classes/Sales.php';

$leads = new Leads();
$sales = new Sales();

// Fetch data for dashboard
$leadCount = count($leads->listLeads());
$salesPipeline = $sales->listDeals();

// Render dashboard
?>