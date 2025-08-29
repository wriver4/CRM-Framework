<?php
require_once '../../classes/Communications.php';
$communications = new Communications();
$list = $communications->listCommunications($leadId);

// Render communication list
