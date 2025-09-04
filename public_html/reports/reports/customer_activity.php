<?php
require_once '../../config/system.php'; // Loads autoloader
$communications = new Communications();
$activity = $communications->listCommunications($leadId);

// Render lead activity report
