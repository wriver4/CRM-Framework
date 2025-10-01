<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

// Define constant to prevent multiple inclusions
// define('SYSTEM_LOADED', true);

// --- SESSION SECURITY CONFIGURATION ---
// These settings enhance session security but are commented out for now
// to avoid breaking existing functionality. Uncomment and test carefully.

// Prevents session fixation attacks by rejecting uninitialized session IDs
// ini_set('session.use_strict_mode', 1);

// Increases session ID entropy - longer IDs are harder to guess/brute force
// ini_set('session.sid_length', 32);

// More bits per character in session ID increases randomness
// ini_set('session.sid_bits_per_character', 6);

// Prevents JavaScript access to session cookies (XSS protection)
// ini_set('session.cookie_httponly', 1);

// Only send session cookies over HTTPS connections (prevents interception)
// ini_set('session.cookie_secure', 1);

// Prevents session ID from being passed in URLs (only use cookies)
// ini_set('session.use_only_cookies', 1);

// Marks session cookie as SameSite=Strict (CSRF protection)
// ini_set('session.cookie_samesite', 'Strict');

// Only start session if not running in CLI mode
if (php_sapi_name() !== 'cli') {
    session_start();
}

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
    /*
    $file = __DIR__ . '/../classes/' . $class_name . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
    */
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
define("DOCROOT", dirname(__DIR__));
define("CONFIGROOT", DOCROOT . '/config');
define("CLASSES", DOCROOT . "/classes/");

// Publicly accessible paths
define("DOCPUBLIC", DOCROOT . '/public_html');
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
define("URL", $protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
define("TEMPLATES", URL . "/templates");
define("ASSETS", URL . "/assets");
define("IMG", ASSETS . '/img');
define("CSS", ASSETS . '/css');
define("JS", ASSETS . '/js');
define("SECURITY", URL . "/security");

// --- MODULE URLS ---
// Performance-optimized constants for module navigation
define("PROSPECTING", URL . "/prospecting");
define("LEADS", URL . "/leads");
define("REFERRALS", URL . "/referrals");
define("PROSPECTS", URL . "/prospects");
define("CONTRACTING", URL . "/contracting");
define("CONTACTS", URL . "/contacts");
define("CALENDAR", URL . "/calendar");
define("ADMIN", URL . "/admin");
define("REPORTS", URL . "/reports");

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
    $dbcrm = (new Database())->dbcrm();
    $not = $users = new Users();
    $audit = new Audit();
    $helper = new Helpers();
    $roles = new Roles();
    $permissions = new Permissions();
    $rolesperms = new RolesPermissions();
    $nonce = new Nonce();
    $security = new Security();
    
} catch (\Throwable $e) {
    // The Logit handler has already logged the detailed error via Whoops.
    // Now, we can stop execution gracefully with a user-friendly message.
    if (php_sapi_name() !== 'cli') {
        http_response_code(503); // Service Unavailable
    }
    die("A critical application service could not be started. Please check the error logs.");
}

// Note: Helper functions have been moved to the Helpers class
// Use $helper->get_client_ip(), $helper->country_by_ip(), $helper->isValidSessionId() instead