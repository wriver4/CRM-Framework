<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
//require_once '../classes/Leads.php';
//require_once '../classes/Sales.php';
$page = 'dashboard';

echo $page;
//$leads = new Leads();
//$sales = new Sales();

// Fetch data for dashboard
//$leadCount = count($leads->listLeads());
//$salesPipeline = $sales->listDeals();

// Render dashboard