<?php
require_once '../../config/system.php'; // Loads autoloader
$sales = new Sales();
$pipeline = $sales->listDeals();

// Render sales pipeline
