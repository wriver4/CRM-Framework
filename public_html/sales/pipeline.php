<?php
require_once '../../classes/Sales.php';
$sales = new Sales();
$pipeline = $sales->listDeals();

// Render sales pipeline
