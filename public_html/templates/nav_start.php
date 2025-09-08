<?php
$browser_language = "en";
$not->loggedin();

// Load language file - ensure $lang is properly initialized
if (!isset($lang) || !is_array($lang)) {
    if (file_exists(LANG . '/' . $browser_language . '.php')) {
        $lang = include LANG . '/' . $browser_language . '.php';
    } else {
        $lang = include LANG . '/en.php';
    }
    
    // Fallback if language file doesn't return an array
    if (!is_array($lang)) {
        $lang = [
            'navbar_tooltip_title' => 'Home',
            'navbar_status' => 'Status',
            'navbar_contacts' => 'Contacts',
            'navbar_leads_new' => 'New Lead Entry',
            'navbar_leads_list' => 'Leads List',
            'navbar_users' => 'Users',
            'navbar_reports' => 'Reports',
            'navbar_admin_leads' => 'Admin Leads',
            'navbar_security' => 'Security',
            'navbar_maintenance' => 'Maintenance',
            'navbar_help' => 'Help',
            'navbar_logout' => 'Logout'
        ];
    }
}
?>
<div class="container">
  <nav class="navbar fixed-top navbar-expand-lg navbar-light bg-primary">
    <div class="container mx-auto overflow-visible">
      <a class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-light text-decoration-none"
         data-toggle="tooltip"
         data-placement="right"
         title="<?= $lang['navbar_tooltip_title']; ?>"
         href="/index"
         tabindex="-1">
        <img class="pr-5 overflow-visible"
             width="35"
             height="35"
             src="<?= IMG . "/logo.svg" ?>">
        <span class="fs-5">&ensp;waveGUARD&trade;&nbsp;</span>
      </a>
      <button class="navbar-toggler"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#navbarNavDropdown"
              aria-controls="navbarNavDropdown"
              aria-expanded="false"
              aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span></button>
      <div class="collapse navbar-collapse"
           id="navbarNavDropdown">
        <ul class="nav col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
