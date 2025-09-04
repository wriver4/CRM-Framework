<?php
require_once '../../config/system.php'; // Loads autoloader
$communications = new Communications();
$list = $communications->listCommunications($leadId);

// Render communication list
