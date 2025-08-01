<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

session_start();

if (
  isset($_SERVER['HTTPS']) &&
  ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
  isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
  $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'
) {
  $protocol = 'https://';
} else {
  // 404 error or redirect to https
  $protocol = 'http://';
}

define("DOCROOT", dirname($_SERVER['DOCUMENT_ROOT']));
define("DOCSUBDOMAIN", basename(DOCROOT));
define("TABTITLEPREFIX", ucfirst(substr(basename(DOCROOT), 0, -2)));
define("CONFIGROOT", DOCROOT . '/config');
define("DOCPUBLIC", $_SERVER['DOCUMENT_ROOT']);
define("DOCTEMPLATES", DOCPUBLIC . '/templates');
define("LANG", DOCTEMPLATES . '/languages');
define("LOGINLANG", LANG . '/login');
define("HEADER", DOCTEMPLATES . '/header.php');
define("BODY", DOCTEMPLATES . '/body.php');
define("NAV", DOCTEMPLATES . '/nav.php');
define("LISTOPEN", DOCTEMPLATES . '/list_open.php');
define("LISTCLOSE", DOCTEMPLATES . '/list_close.php');
define("SECTIONOPEN", DOCTEMPLATES . '/section_open.php');
define("SECTIONCLOSE", DOCTEMPLATES . '/section_close.php');
define("FOOTER", DOCTEMPLATES . '/footer.php');
define("CLASSES", DOCROOT . '/classes/');

define("URL", $protocol . $_SERVER['HTTP_HOST']);
define("SECURITY", URL . "/security");
define("TEMPLATES", URL . "/templates");
define("IMG", TEMPLATES . '/img');
define("CSS", TEMPLATES . '/css');
define("JS", TEMPLATES . '/js');



define("VALIDEMAIL", "(?![_.-])((?![_.-][_.-])[a-zA-Z\d_.-]){2,63}[a-zA-Z\d]@((?!-)((?!--)[a-zA-Z\d-]){2,63}[a-zA-Z\d]\.){1,2}([a-zA-Z]{2,14}\.)?[a-zA-Z]{2,14}");


define('NONCE_SECRET', 'pHAx1YhL_q/ed&$M)_X2zi!rzn?@au');

// require_once 'ftpconfig.php';

$systemToEmailAddress = "mark@waveguardco.com";
$programLog = DOCROOT . '/logs/program.log';
$programLogSubject = "admin.waveguardco.com program log entry";
$programLogMailTo = $systemToEmailAddress . '';
$agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';

function my_autoload($class_name)
{
  include CLASSES . $class_name . '.php';
}
//function monolog_autoload($class_name)
//{
//  include CLASSES .'Monolog/'. $class_name . '.php';
//}
spl_autoload_extensions('.php');
spl_autoload_register('my_autoload');
//spl_autoload_register('monolog_autoload');
//use Monolog\Logger; 
//use Monolog\Handler\StreamHandler; // send message to log file

if (class_exists('Database')) {
  $db = new Database();
  $dbcrm = $db->dbcrm();
} else {
  echo 'Class Database does not exist';
}
//echo "System configuration loaded. db exists<br>";
//exit();
//or do it during login
if (class_exists('Users')) {
  $not = $users = new Users();
} else {
  echo 'Class Users does not exist';
}

if (class_exists('Audit')) {
  $audit = new Audit();
} else {
  echo 'Class Audit does not exist';
}

if (class_exists('Helpers')) {
  $helper = new Helpers();
} else {
  echo 'Class Helpers does not exist';
}

if (class_exists('RolesPermissions')) {
  $rolesperms = new RolesPermissions();
} else {
  echo 'Class RolesPermissions does not exist';
}

if (class_exists('Nonce')) {
  $nonce = new Nonce();
} else {
  echo 'Class Nonce does not exist';
}

/*
$systemToEmailAddress = "mark@waveguard.com";
$programLog = $basedir . '/logs/program.log';
$programLogSubject = "admin.waveguardco.com program log entry";
$programLogMailTo = $systemToEmailAddress . '';
$agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
*/


require_once 'helpers.php';