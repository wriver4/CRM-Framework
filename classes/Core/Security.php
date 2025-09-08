<?php
class Security extends Database
{
    Public function __construct()
    {
        parent::__construct();
    }
    
    /** put below code in User.php ?
     * Hash the password
     * @param string $password
     * @return string
     */
    public static function hash($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function verify($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Check if user is logged in and redirect if not
     */
    public function check_user_login()
    {
        if (!isset($_SESSION) || !isset($_SESSION['loggedin'])) {
            header("Location: /login");
            exit;
        }
    }

    /**
     * Check user permissions for specific module and action
     * @param string $module - The module to check (e.g., 'leads', 'admin', 'contacts')
     * @param string $action - The action to check (e.g., 'read', 'write', 'delete')
     * @param bool $redirect - Whether to redirect on failure (default: true)
     * @return bool - Returns true if user has permission, false otherwise
     */
    public function check_user_permissions($module, $action, $redirect = true)
    {
        // First ensure user is logged in
        if (!isset($_SESSION) || !isset($_SESSION['loggedin'])) {
            if ($redirect) {
                header("Location: /login");
                exit;
            }
            return false;
        }

        // For now, implement basic permission check
        // This can be expanded to check against a permissions table
        $user_id = $_SESSION['user_id'] ?? null;
        $user_role = $_SESSION['user_role'] ?? 'user';

        // Basic permission logic - can be expanded based on requirements
        $hasPermission = true; // Default to allow access for logged-in users
        
        // Admin users have access to everything
        if ($user_role === 'admin' || $user_role === 'administrator') {
            $hasPermission = true;
        }
        // Regular users have read access to most modules
        elseif ($action === 'read') {
            $hasPermission = true;
        }
        // Write/delete actions may require specific permissions
        elseif (in_array($action, ['write', 'delete', 'create', 'update'])) {
            // For now, allow write access to regular users
            // This can be refined based on specific business rules
            $hasPermission = true;
        }

        // If permission denied and redirect is enabled
        if (!$hasPermission && $redirect) {
            header("Location: /access_denied.php");
            exit;
        }

        return $hasPermission;
    }
}
