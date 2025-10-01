<?php
// caldav/backends/PrincipalBackend.php
namespace CRM\CalDAV;

require_once __DIR__ . '/../../vendor/autoload.php';

use Sabre\DAVACL\PrincipalBackend\AbstractBackend;
use PDO;

class PrincipalBackend extends AbstractBackend {
    
    private $pdo;
    private $tableNameUsers = 'users';
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get list of principals by prefix
     */
    public function getPrincipalsByPrefix($prefixPath) {
        $principals = [];
        
        switch ($prefixPath) {
            case 'principals/users':
                $stmt = $this->pdo->prepare("
                    SELECT username, email, display_name 
                    FROM {$this->tableNameUsers} 
                    WHERE username IS NOT NULL 
                    ORDER BY username
                ");
                $stmt->execute();
                
                while ($row = $stmt->fetch()) {
                    $principals[] = [
                        'uri' => 'principals/users/' . $row['username'],
                        '{DAV:}displayname' => $row['display_name'] ?: $row['username'],
                        '{http://sabredav.org/ns}email-address' => $row['email'],
                        '{urn:ietf:params:xml:ns:caldav}calendar-user-type' => 'INDIVIDUAL'
                    ];
                }
                break;
                
            case 'principals/groups':
                // You can implement group support here if needed
                break;
        }
        
        return $principals;
    }
    
    /**
     * Get a specific principal by path
     */
    public function getPrincipalByPath($path) {
        if (strpos($path, 'principals/users/') !== 0) {
            return null;
        }
        
        $username = substr($path, strlen('principals/users/'));
        
        $stmt = $this->pdo->prepare("
            SELECT username, email, display_name, timezone
            FROM {$this->tableNameUsers} 
            WHERE username = ?
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return null;
        }
        
        return [
            'uri' => $path,
            '{DAV:}displayname' => $user['display_name'] ?: $user['username'],
            '{http://sabredav.org/ns}email-address' => $user['email'],
            '{urn:ietf:params:xml:ns:caldav}calendar-user-type' => 'INDIVIDUAL',
            '{urn:ietf:params:xml:ns:caldav}calendar-user-address-set' => 
                new \Sabre\CalDAV\Xml\Property\EmailAddressSet([$user['email']]),
            '{urn:ietf:params:xml:ns:caldav}calendar-home-set' => 
                new \Sabre\DAV\Xml\Property\Href('calendars/' . $username . '/'),
            '{urn:ietf:params:xml:ns:caldav}schedule-inbox-URL' => 
                new \Sabre\DAV\Xml\Property\Href('calendars/' . $username . '/inbox/'),
            '{urn:ietf:params:xml:ns:caldav}schedule-outbox-URL' => 
                new \Sabre\DAV\Xml\Property\Href('calendars/' . $username . '/outbox/')
        ];
    }
    
    /**
     * Update principal properties
     */
    public function updatePrincipal($path, \Sabre\DAV\PropPatch $propPatch) {
        if (strpos($path, 'principals/users/') !== 0) {
            return false;
        }
        
        $username = substr($path, strlen('principals/users/'));
        
        $supportedProperties = [
            '{DAV:}displayname',
            '{http://sabredav.org/ns}email-address'
        ];
        
        $propPatch->handle($supportedProperties, function($mutations) use ($username) {
            $updates = [];
            $values = [];
            
            foreach ($mutations as $property => $value) {
                switch ($property) {
                    case '{DAV:}displayname':
                        $updates[] = 'display_name = ?';
                        $values[] = $value;
                        break;
                    case '{http://sabredav.org/ns}email-address':
                        $updates[] = 'email = ?';
                        $values[] = $value;
                        break;
                }
            }
            
            if ($updates) {
                $values[] = $username;
                $stmt = $this->pdo->prepare("
                    UPDATE {$this->tableNameUsers} 
                    SET " . implode(', ', $updates) . " 
                    WHERE username = ?
                ");
                return $stmt->execute($values);
            }
            
            return false;
        });
    }
    
    /**
     * Search for principals
     */
    public function searchPrincipals($prefixPath, array $searchProperties, $test = 'allof') {
        if ($prefixPath !== 'principals/users') {
            return [];
        }
        
        $whereConditions = [];
        $params = [];
        
        foreach ($searchProperties as $property => $value) {
            switch ($property) {
                case '{DAV:}displayname':
                    $whereConditions[] = "(display_name LIKE ? OR username LIKE ?)";
                    $params[] = '%' . $value . '%';
                    $params[] = '%' . $value . '%';
                    break;
                case '{http://sabredav.org/ns}email-address':
                    $whereConditions[] = "email LIKE ?";
                    $params[] = '%' . $value . '%';
                    break;
            }
        }
        
        if (empty($whereConditions)) {
            return [];
        }
        
        $whereClause = implode($test === 'anyof' ? ' OR ' : ' AND ', $whereConditions);
        
        $stmt = $this->pdo->prepare("
            SELECT username 
            FROM {$this->tableNameUsers} 
            WHERE {$whereClause}
            ORDER BY username
        ");
        $stmt->execute($params);
        
        $principals = [];
        while ($row = $stmt->fetch()) {
            $principals[] = 'principals/users/' . $row['username'];
        }
        
        return $principals;
    }
    
    /**
     * Find principals by their URI
     */
    public function findByUri($uri, $principalPrefix) {
        // Handle email addresses
        if (filter_var($uri, FILTER_VALIDATE_EMAIL)) {
            $stmt = $this->pdo->prepare("
                SELECT username 
                FROM {$this->tableNameUsers} 
                WHERE email = ?
            ");
            $stmt->execute([$uri]);
            $user = $stmt->fetch();
            
            if ($user) {
                return $principalPrefix . '/' . $user['username'];
            }
        }
        
        // Handle mailto: URIs
        if (strpos($uri, 'mailto:') === 0) {
            $email = substr($uri, 7);
            return $this->findByUri($email, $principalPrefix);
        }
        
        return null;
    }
    
    /**
     * Get group membership for a principal
     */
    public function getGroupMembership($principal) {
        // Basic implementation - you can extend this for actual group support
        return [];
    }
    
    /**
     * Get group members
     */
    public function getGroupMembers($principal) {
        // Basic implementation - you can extend this for actual group support
        return [];
    }
    
    /**
     * Set group membership
     */
    public function setGroupMembership($principal, array $members) {
        // Basic implementation - you can extend this for actual group support
        throw new \Sabre\DAV\Exception\Forbidden('Group management not implemented');
    }
    
    /**
     * Create a new user (principal)
     */
    public function createUser($username, $password, $email, $displayName = null) {
        // Check if user already exists
        $stmt = $this->pdo->prepare("
            SELECT id FROM {$this->tableNameUsers} 
            WHERE username = ? OR email = ?
        ");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            throw new \Exception('User already exists');
        }
        
        // Create new user
        $stmt = $this->pdo->prepare("
            INSERT INTO {$this->tableNameUsers} (username, password, email, display_name) 
            VALUES (?, ?, ?, ?)
        ");
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        return $stmt->execute([
            $username,
            $hashedPassword,
            $email,
            $displayName ?: $username
        ]);
    }
    
    /**
     * Update user password
     */
    public function updateUserPassword($username, $newPassword) {
        $stmt = $this->pdo->prepare("
            UPDATE {$this->tableNameUsers} 
            SET password = ? 
            WHERE username = ?
        ");
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $stmt->execute([$hashedPassword, $username]);
    }
    
    /**
     * Delete a user
     */
    public function deleteUser($username) {
        // First, delete all calendars and tasks for this user
        $stmt = $this->pdo->prepare("
            DELETE t FROM tasks t 
            INNER JOIN caldav_calendars c ON t.calendar_id = c.id 
            WHERE c.user_id = ?
        ");
        $stmt->execute([$username]);
        
        // Delete calendars
        $stmt = $this->pdo->prepare("
            DELETE FROM caldav_calendars WHERE user_id = ?
        ");
        $stmt->execute([$username]);
        
        // Delete user
        $stmt = $this->pdo->prepare("
            DELETE FROM {$this->tableNameUsers} WHERE username = ?
        ");
        
        return $stmt->execute([$username]);
    }
    
    /**
     * Validate user credentials
     */
    public function validateCredentials($username, $password) {
        $stmt = $this->pdo->prepare("
            SELECT password FROM {$this->tableNameUsers} 
            WHERE username = ?
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return false;
        }
        
        return password_verify($password, $user['password']);
    }
    
    /**
     * Get user information
     */
    public function getUserInfo($username) {
        $stmt = $this->pdo->prepare("
            SELECT username, email, display_name, timezone, created_at
            FROM {$this->tableNameUsers} 
            WHERE username = ?
        ");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }
    
    /**
     * List all users (for admin purposes)
     */
    public function getAllUsers() {
        $stmt = $this->pdo->prepare("
            SELECT username, email, display_name, created_at
            FROM {$this->tableNameUsers} 
            ORDER BY username
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Check if user exists
     */
    public function userExists($username) {
        $stmt = $this->pdo->prepare("
            SELECT 1 FROM {$this->tableNameUsers} WHERE username = ?
        ");
        $stmt->execute([$username]);
        return (bool) $stmt->fetch();
    }
    
    /**
     * Get user by email
     */
    public function getUserByEmail($email) {
        $stmt = $this->pdo->prepare("
            SELECT username, email, display_name
            FROM {$this->tableNameUsers} 
            WHERE email = ?
        ");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    /**
     * Update user timezone
     */
    public function updateUserTimezone($username, $timezone) {
        $stmt = $this->pdo->prepare("
            UPDATE {$this->tableNameUsers} 
            SET timezone = ? 
            WHERE username = ?
        ");
        return $stmt->execute([$timezone, $username]);
    }
}
?>