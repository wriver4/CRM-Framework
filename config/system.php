<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

session_start();

// --- AUTOLOADING ---
// Autoloaders should be registered first to ensure classes are available
// throughout the application's bootstrap process.

// 1. Custom autoloader for the /classes directory.
// This autoloader only handles non-namespaced classes to avoid
// conflicts with Composer's autoloader.
spl_autoload_register(function ($class_name) {
    // Ignore namespaced classes (which are handled by Composer)
    if (strpos($class_name, '\\') !== false) {
        return;
    }
    
    // Search in organized subdirectories
    $directories = ['Core', 'Models', 'Views', 'Utilities', 'Logging'];
    
    foreach ($directories as $dir) {
        $file = __DIR__ . '/../classes/' . $dir . '/' . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
    
    // Fallback to root classes directory for backward compatibility
    $file = __DIR__ . '/../classes/' . $class_name . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// 2. Composer's autoloader for vendor packages.
require_once __DIR__ . '/../vendor/autoload.php';

// --- DEBUG CONFIGURATION ---
// SQL debugging - set to true to enable detailed SQL execution logging
// WARNING: This will log all SQL queries and parameters - only enable for debugging
define('DEBUG_SQL', false);

// Enable SQL error logging (always enabled for error tracking)
define('ENABLE_SQL_ERROR_LOGGING', true);

// Determine if the connection is secure. The `HTTP_X_FORWARDED_PROTO`
// check is important for applications behind a reverse proxy or load balancer.
$isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

// Skipped for command-line interface (CLI) scripts.
if (!$isSecure && php_sapi_name() !== 'cli') {
    $redirectUrl = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $redirectUrl);
    exit();
}

// Define the protocol based on the secure connection status.
$protocol = 'https://';

// --- FILE SYSTEM PATHS ---
// Core application paths
define("DOCROOT", dirname($_SERVER['DOCUMENT_ROOT']));
define("CONFIGROOT", DOCROOT . '/config');
define("CLASSES", DOCROOT . '/classes/');

// Publicly accessible paths
define("DOCPUBLIC", $_SERVER['DOCUMENT_ROOT']);
define("DOCTEMPLATES", DOCPUBLIC . '/templates');

// Specific template file paths for includes
define("HEADER", DOCTEMPLATES . '/header.php');
define("BODY", DOCTEMPLATES . '/body.php');
define("NAV", DOCTEMPLATES . '/nav.php');
define("FOOTER", DOCTEMPLATES . '/footer.php');
define("LISTOPEN", DOCTEMPLATES . '/list_open.php');
define("LISTBUTTONS", DOCTEMPLATES . '/list_buttons.php');
define("LISTCLOSE", DOCTEMPLATES . '/list_close.php');
define("SECTIONOPEN", DOCTEMPLATES . '/section_open.php');
define("SECTIONCLOSE", DOCTEMPLATES . '/section_close.php');

// --- URLS & BROWSER PATHS ---
define("URL", $protocol . $_SERVER['HTTP_HOST']);
define("TEMPLATES", URL . "/templates");
define("ASSETS", URL . "/assets");
define("IMG", ASSETS . '/img');
define("CSS", ASSETS . '/css');
define("JS", ASSETS . '/js');
define("SECURITY", URL . "/security");

// --- APPLICATION & UI SETTINGS ---
define("DOCSUBDOMAIN", basename(DOCROOT));
define("TABTITLEPREFIX", ucfirst(substr(basename(DOCROOT), 0, -2)));
define("LANG", DOCPUBLIC . '/admin/languages');
define("LOGINLANG", LANG . '/login');
define("VALIDEMAIL", "(?![_.-])((?![_.-][_.-])[a-zA-Z\d_.-]){2,63}[a-zA-Z\d]@((?!-)((?!--)[a-zA-Z\d-]){2,63}[a-zA-Z\d]\.){1,2}([a-zA-Z]{2,14}\.)?[a-zA-Z]{2,14}");


define('NONCE_SECRET', 'pHAx1YhL_q/ed&$M)_X2zi!rzn?@au');

// require_once 'ftpconfig.php';

$systemToEmailAddress = "mark@waveguardco.com";
$programLog = DOCROOT . '/logs/program.log';
$programLogSubject = "admin.waveguardco.com program log entry";
$programLogMailTo = $systemToEmailAddress . '';
$agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';

// --- ERROR & EXCEPTION HANDLING (Whoops & Monolog) ---
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Whoops\Run as WhoopsRun;
use Whoops\Handler\PrettyPageHandler;

// Set up Monolog to log errors to a file
$log = new Logger('app_logger');
$log->pushHandler(new StreamHandler(DOCROOT . '/logs/php_errors.log', Level::Error));

// Set up Whoops to provide detailed error pages for development
$whoops = new WhoopsRun();
$whoops->pushHandler(new PrettyPageHandler());

// Add our custom Logit handler to the Whoops stack.
$whoops->pushHandler(new Logit($log)); // The 'my_autoload' function will load /classes/Logit.php

// Register Whoops to take over PHP's error handling.
$whoops->register();

// --- CORE SERVICES INITIALIZATION ---
// Instantiate core application classes. If any of these fail, the autoloader and
// Whoops will catch the fatal error, log it, and display a detailed error page
// for debugging, which is more effective than the previous class_exists checks.
try {
    $db = new Database();
    $dbcrm = $db->dbcrm();
    $not = $users = new Users();
    $audit = new Audit();
    $helper = new Helpers();
    $rolesperms = new RolesPermissions();
    $nonce = new Nonce();
} catch (\Throwable $e) {
    // The Logit handler has already logged the detailed error via Whoops.
    // Now, we can stop execution gracefully with a user-friendly message.
    http_response_code(503); // Service Unavailable
    die("A critical application service could not be started. Please check the error logs.");
}

require_once 'helpers.php';