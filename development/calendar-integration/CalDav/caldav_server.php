<?php
// caldav/server.php - Main CalDAV server endpoint
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/backends/CalendarBackend.php';
require_once __DIR__ . '/backends/PrincipalBackend.php';

use Sabre\DAV;
use Sabre\CalDAV;
use Sabre\DAVACL;
use CRM\CalDAV\CalendarBackend;
use CRM\CalDAV\PrincipalBackend;

// Error handling and logging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to client
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/caldav_errors.log');

// Custom error handler for CalDAV
function caldavErrorHandler($errno, $errstr, $errfile, $errline) {
    $errorMessage = "CalDAV Error: $errstr in $errfile on line $errline";
    error_log($errorMessage);
    
    // For fatal errors, return proper DAV response
    if ($errno === E_ERROR || $errno === E_PARSE || $errno === E_CORE_ERROR) {
        http_response_code(500);
        header('Content-Type: application/xml');
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<d:error xmlns:d="DAV:"><d:description>Internal Server Error</d:description></d:error>';
        exit;
    }
}

set_error_handler('caldavErrorHandler');

// Exception handler
function caldavExceptionHandler($exception) {
    error_log("CalDAV Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
    
    http_response_code(500);
    header('Content-Type: application/xml');
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<d:error xmlns:d="DAV:"><d:description>Internal Server Error</d:description></d:error>';
    exit;
}

set_exception_handler('caldavExceptionHandler');

try {
    // Initialize database connection
    $database = initializeCalDAVDatabase();
    $pdo = getDB();
    
    // Create backends
    $principalBackend = new PrincipalBackend($pdo);
    $calendarBackend = new CalendarBackend($pdo);
    
    // Create directory tree
    $tree = [
        new DAVACL\PrincipalCollection($principalBackend),
        new CalDAV\CalendarRoot($principalBackend, $calendarBackend)
    ];
    
    // Initialize the server
    $server = new DAV\Server($tree);
    
    // Set the base URI
    $baseUri = '/caldav/';
    if (isset($_SERVER['REQUEST_URI'])) {
        $requestUri = $_SERVER['REQUEST_URI'];
        if (strpos($requestUri, '/caldav/') !== false) {
            $baseUri = '/caldav/';
        }
    }
    $server->setBaseUri($baseUri);
    
    // Add essential plugins
    
    // 1. Authentication plugin
    $authBackend = new CalDAVAuthBackend($pdo);
    $authPlugin = new DAV\Auth\Plugin($authBackend);
    $server->addPlugin($authPlugin);
    
    // 2. ACL (Access Control List) plugin
    $aclPlugin = new DAVACL\Plugin();
    $aclPlugin->allowUnauthenticatedAccess = false;
    $server->addPlugin($aclPlugin);
    
    // 3. CalDAV plugin
    $caldavPlugin = new CalDAV\Plugin();
    $server->addPlugin($caldavPlugin);
    
    // 4. Sync support plugin
    $server->addPlugin(new DAV\Sync\Plugin());
    
    // 5. Scheduling plugin (for invitations)
    $server->addPlugin(new CalDAV\Schedule\Plugin());
    
    // 6. Browser plugin (for debugging)
    if (isset($_GET['debug']) && $_GET['debug'] === '1') {
        $server->addPlugin(new DAV\Browser\Plugin());
    }
    
    // 7. Property storage plugin
    $propertyBackend = new CalDAVPropertyBackend($pdo);
    $server->addPlugin(new DAV\PropertyStorage\Plugin($propertyBackend));
    
    // 8. Locks plugin (for exclusive access)
    $locksBackend = new CalDAVLocksBackend($pdo);
    $server->addPlugin(new DAV\Locks\Plugin($locksBackend));
    
    // CORS support for web clients
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        $allowedOrigins = [
            'https://nextcloud.yourdomain.com',
            'http://localhost:3000', // For development
        ];
        
        $origin = $_SERVER['HTTP_ORIGIN'];
        if (in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: $origin");
        }
        
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PROPFIND, PROPPATCH, REPORT, MKCALENDAR');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, Depth, User-Agent, X-File-Size, X-Requested-With, If-Modified-Since, X-File-Name, Cache-Control, Prefer, Brief');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
    
    // Rate limiting
    $rateLimiter = new CalDAVRateLimiter($pdo);
    if (!$rateLimiter->checkLimit()) {
        http_response_code(429);
        header('Content-Type: application/xml');
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<d:error xmlns:d="DAV:"><d:description>Rate limit exceeded</d:description></d:error>';
        exit;
    }
    
    // Log the request
    logCalDAVRequest();
    
    // Execute the request
    $server->exec();
    
} catch (Exception $e) {
    error_log("Fatal CalDAV error: " . $e->getMessage());
    
    http_response_code(500);
    header('Content-Type: application/xml');
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<d:error xmlns:d="DAV:"><d:description>Internal Server Error</d:description></d:error>';
}

/**
 * Custom authentication backend
 */
class CalDAVAuthBackend implements DAV\Auth\Backend\BackendInterface {
    private $pdo;
    private $principalBackend;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->principalBackend = new PrincipalBackend($pdo);
    }
    
    public function authenticate(DAV\Server $server, $realm) {
        $auth = new DAV\Auth\Backend\BasicAuth();
        $auth->setRealm($realm);
        
        $userpass = $auth->getCredentials();
        if (!$userpass) {
            return false;
        }
        
        list($username, $password) = $userpass;
        
        // Validate credentials
        if ($this->principalBackend->validateCredentials($username, $password)) {
            return 'principals/users/' . $username;
        }
        
        return false;
    }
    
    public function challenge(DAV\Server $server) {
        $server->httpResponse->addHeader('WWW-Authenticate', 'Basic realm="CRM CalDAV Server"');
        $server->httpResponse->setStatus(401);
    }
}

/**
 * Property storage backend
 */
class CalDAVPropertyBackend implements DAV\PropertyStorage\Backend\BackendInterface {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function propFind($path, array $propNames) {
        $stmt = $this->pdo->prepare('SELECT name, value, valuetype FROM caldav_properties WHERE path = ?');
        $stmt->execute([$path]);
        
        $properties = [];
        while ($row = $stmt->fetch()) {
            $value = $row['value'];
            if ($row['valuetype'] == 2) {
                $value = base64_decode($value);
            }
            $properties[$row['name']] = $value;
        }
        
        return $properties;
    }
    
    public function propPatch($path, array $propPatch) {
        foreach ($propPatch as $propName => $propValue) {
            if ($propValue === null) {
                // Delete property
                $stmt = $this->pdo->prepare('DELETE FROM caldav_properties WHERE path = ? AND name = ?');
                $stmt->execute([$path, $propName]);
            } else {
                // Set property
                $valueType = is_string($propValue) ? 1 : 2;
                $value = $valueType === 2 ? base64_encode($propValue) : $propValue;
                
                $stmt = $this->pdo->prepare('REPLACE INTO caldav_properties (path, name, value, valuetype) VALUES (?, ?, ?, ?)');
                $stmt->execute([$path, $propName, $value, $valueType]);
            }
        }
        
        return true;
    }
    
    public function delete($path) {
        $stmt = $this->pdo->prepare('DELETE FROM caldav_properties WHERE path = ? OR path LIKE ?');
        $stmt->execute([$path, $path . '/%']);
        
        return true;
    }
    
    public function move($source, $destination) {
        $stmt = $this->pdo->prepare('UPDATE caldav_properties SET path = REPLACE(path, ?, ?) WHERE path = ? OR path LIKE ?');
        $stmt->execute([$source, $destination, $source, $source . '/%']);
        
        return true;
    }
}

/**
 * Locks backend for WebDAV locking
 */
class CalDAVLocksBackend implements DAV\Locks\Backend\BackendInterface {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getLocks($uri, $returnChildLocks) {
        $stmt = $this->pdo->prepare('SELECT * FROM caldav_locks WHERE uri = ?');
        $stmt->execute([$uri]);
        
        $locks = [];
        while ($row = $stmt->fetch()) {
            $locks[] = new DAV\Locks\LockInfo([
                'owner' => $row['owner'],
                'token' => $row['token'],
                'timeout' => $row['timeout'],
                'created' => $row['created_at'],
                'scope' => $row['scope'],
                'depth' => $row['depth'],
                'uri' => $row['uri']
            ]);
        }
        
        return $locks;
    }
    
    public function lock($uri, DAV\Locks\LockInfo $lockInfo) {
        $stmt = $this->pdo->prepare('INSERT INTO caldav_locks (owner, timeout, created_at, token, scope, depth, uri) VALUES (?, ?, ?, ?, ?, ?, ?)');
        
        return $stmt->execute([
            $lockInfo->owner,
            $lockInfo->timeout,
            time(),
            $lockInfo->token,
            $lockInfo->scope,
            $lockInfo->depth,
            $uri
        ]);
    }
    
    public function unlock($uri, DAV\Locks\LockInfo $lockInfo) {
        $stmt = $this->pdo->prepare('DELETE FROM caldav_locks WHERE uri = ? AND token = ?');
        return $stmt->execute([$uri, $lockInfo->token]);
    }
}

/**
 * Rate limiter for CalDAV requests
 */
class CalDAVRateLimiter {
    private $pdo;
    private $maxRequests = 100; // per hour
    private $timeWindow = 3600; // 1 hour in seconds
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function checkLimit() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $currentTime = time();
        $windowStart = $currentTime - $this->timeWindow;
        
        // Clean old entries
        $stmt = $this->pdo->prepare('DELETE FROM caldav_rate_limit WHERE timestamp < ?');
        $stmt->execute([$windowStart]);
        
        // Count requests in current window
        $stmt = $this->pdo->prepare('SELECT COUNT(*) as count FROM caldav_rate_limit WHERE ip = ? AND timestamp > ?');
        $stmt->execute([$ip, $windowStart]);
        $result = $stmt->fetch();
        
        if ($result['count'] >= $this->maxRequests) {
            return false;
        }
        
        // Log this request
        $stmt = $this->pdo->prepare('INSERT INTO caldav_rate_limit (ip, timestamp) VALUES (?, ?)');
        $stmt->execute([$ip, $currentTime]);
        
        return true;
    }
}

/**
 * Log CalDAV requests for monitoring
 */
function logCalDAVRequest() {
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
        'uri' => $_SERVER['REQUEST_URI'] ?? 'UNKNOWN',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'auth_user' => $_SERVER['PHP_AUTH_USER'] ?? 'anonymous'
    ];
    
    $logLine = json_encode($logEntry) . "\n";
    
    $logFile = __DIR__ . '/../logs/caldav_access.log';
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }
    
    file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
}

// Create rate limiting table if it doesn't exist
try {
    $pdo = getDB();
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS caldav_rate_limit (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip VARCHAR(45) NOT NULL,
            timestamp INT NOT NULL,
            INDEX idx_ip_timestamp (ip, timestamp)
        )
    ");
} catch (Exception $e) {
    error_log("Could not create rate limit table: " . $e->getMessage());
}
?>