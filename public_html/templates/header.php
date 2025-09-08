<?php

// header("Content-Security-Policy: default-src 'self'");
/**
 * Notes:
 * default-src 'self' cdn.example.com;
 * script-src 'self' js.example.com;
 * style-src 'self' css.example.com;
 * img-src 'self' img.example.com;
 * 
 * connect-src
 * Applies to XMLHttpRequest (AJAX), WebSocket, fetch(), <a ping> or EventSource. If not allowed the browser emulates a 400 HTTP status code.
 *  Example connect-src Policy
 * connect-src 'self';
 * font-src font.example.com;
 * Defines valid sources of plugins, eg <object>, <embed> or <applet>.
 * object-src 'self';
 * Defines valid sources of audio and video, eg HTML5 <audio>, <video> elements.
 * media-src media.example.com;
 * 
 * frame-src
 * Defines valid sources for loading frames. In CSP Level 2 frame-src was deprecated in favor of the child-src directive. CSP Level 3, has undeprecated frame-src and it will continue to defer to child-src if not present.
 * Exapmle frame-src Policyframe-src 'self';
 * 
 * 
 * 
 * 
 */

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title><?php echo DOCSUBDOMAIN . " - " . $title; ?></title>
  <meta name="viewport"
        content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="robots"
        content="noindex,nofollow">
  <link rel="icon"
        type="image/png"
        sizes="16x16"
        href="<?php echo IMG . "/favicon-16x16.png"; ?>">
  <link rel="icon"
        type="image/png"
        sizes="32x32"
        href="<?php echo IMG . "/favicon-32x32.png"; ?>">
  <link rel="apple-touch-icon"
        sizes="180x180"
        href="<?php echo IMG . "/apple-touch-icon.png"; ?>">
  <link rel="icon"
        type="image/png"
        sizes="192x192"
        href="<?php echo IMG . "/android-chrome-192x192.png"; ?>">
  <link rel="icon"
        type="image/png"
        sizes="512x512"
        href="<?php echo IMG . "/android-chrome-512x512.png"; ?>">
  <link rel="icon"
        href="<?php echo IMG . "/logo.svg"; ?>"
        type="image/svg+xml">
  <link rel="mask-icon"
        href="<?php echo IMG . "/safari-pinned-tab.svg"; ?>"
        color="#5bbad5">
  <link rel="icon"
        href="<?php echo IMG . "/favicon.ico"; ?>"
        sizes="any">
  <link rel="shortcut icon"
        href="<?php echo IMG . "/favicon.ico"; ?>">
  <link rel="manifest"
        href="<?php echo IMG . "/site.webmanifest"; ?>">
  <meta name="msapplication-TileColor"
        content="#da532c">
  <meta name="msapplication-config"
        content="<?php echo IMG . "/browserconfig.xml"; ?>">
  <meta name="theme-color"
        content="#ffffff">
  <link rel="stylesheet"
        href="<?php echo CSS . "/bootstrap.min.css"; ?>">
  <link rel="stylesheet"
        href="<?php echo CSS . "/all.css"; ?>">
  <?php if ($page == "login") { ?>
  <link rel="stylesheet"
        href="<?php echo CSS . "/login.css"; ?>">
  <?php } ?>
  <?php if (isset($refresh) && $refresh == true && $_SESSION['refresh'] == true && $dir == "status") { ?>
  <meta http-equiv="refresh"
        content="<?php echo $_SESSION['refresh_time']; ?>">
  <?php } ?>
  <?php if ($table_page == true) { ?>
  <link rel="stylesheet"
        type="text/css"
        href="https://cdn.datatables.net/v/bs5/dt-1.12.1/fh-3.2.4/r-2.3.0/datatables.min.css" />
  <?php } ?>

  <link rel="stylesheet"
        href="<?php echo CSS . "/style.css"; ?>">
  <link rel="stylesheet"
        href="<?php echo CSS . "/forms.css"; ?>">