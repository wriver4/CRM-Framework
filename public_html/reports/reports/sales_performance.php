<?php
require_once '../../classes/Sales.php';
$sales = new Sales();
$performance = $sales->listDeals();

// Render sales performance report
