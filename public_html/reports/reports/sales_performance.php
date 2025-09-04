<?php
require_once '../../config/system.php'; // Loads autoloader
$sales = new Sales();
$performance = $sales->listDeals();

// Render sales performance report
