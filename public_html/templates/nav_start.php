<?php
$browser_language = "en";
$not->loggedin();
if (file_exists(LANG . '/' . $browser_language . '.php')) {
  require_once LANG . '/' . $browser_language . '.php';
} else {
  require_once LANG . '/en.php';
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
