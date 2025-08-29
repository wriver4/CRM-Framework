<?php
require_once '../../classes/Communications.php';
$communications = new Communications();
$activity = $communications->listCommunications($leadId);

// Render lead activity report
