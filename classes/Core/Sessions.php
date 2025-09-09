<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

/**
 * Sessions class for managing user sessions
 * 
 * Works with the global session started in system.php.
 * Provides convenient static methods for session management.
 */
class Sessions extends Database
{
    public function __construct()
    {
        parent::__construct();
        // Note: Session is already started in system.php
    }

    /**
     * Regenerate session ID for security
     * Note: Session is already started in system.php
     */
    public function regenerate($deleteOld = true)
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return session_regenerate_id($deleteOld);
        }
        return false;
    }

    /**
     * Check if user is logged in
     * @return bool
     */
    public static function isLoggedIn()
    {
        return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
    }

    /**
     * Get current user ID
     * @return int|null
     */
    public static function getUserId()
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get user's full name
     * @return string|null
     */
    public static function getUserName()
    {
        return $_SESSION['full_name'] ?? null;
    }

    /**
     * Get user's permissions array
     * @return array
     */
    public static function getPermissions()
    {
        return $_SESSION['permissions'] ?? [];
    }

    /**
     * Get user's language preference
     * @return string
     */
    public static function getLanguage()
    {
        return $_SESSION['lang'] ?? 'en';
    }

    /**
     * Get user's language ID
     * @return int|null
     */
    public static function getLanguageId()
    {
        return $_SESSION['language_id'] ?? null;
    }

    /**
     * Get user's language file name
     * @return string
     */
    public static function getLanguageFile()
    {
        return $_SESSION['language_file'] ?? 'en.php';
    }

    /**
     * Set user's language preference in session
     * @param int $languageId
     * @param string $langCode
     * @param string $fileName
     */
    public static function setLanguage($languageId, $langCode, $fileName)
    {
        $_SESSION['language_id'] = $languageId;
        $_SESSION['lang'] = $langCode;
        $_SESSION['language_file'] = $fileName;
    }

    /**
     * Check if session is still valid (not expired)
     * @param int $timeout_minutes Default 30 minutes
     * @return bool
     */
    public static function isValid($timeout_minutes = 30)
    {
        if (!isset($_SESSION['last_activity'])) {
            return false;
        }
        
        $timeout_seconds = $timeout_minutes * 60;
        return (time() - $_SESSION['last_activity']) < $timeout_seconds;
    }

    /**
     * Update last activity timestamp
     */
    public static function updateActivity()
    {
        $_SESSION['last_activity'] = time();
    }

    /**
     * Set session data after successful login
     * @param array $userData User data from database
     * @param array $permissions User permissions
     */
    public static function create($userData, $permissions)
    {
        $_SESSION['ua'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 509);
        $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['full_name'] = $userData['full_name'];
        $_SESSION['permissions'] = $permissions;
        $_SESSION['lang'] = $userData['lang'] ?? 'en';
        $_SESSION['loggedin'] = true;
        $_SESSION['refresh'] = true;
        $_SESSION['refresh_time'] = 60;
        $_SESSION['last_activity'] = time();
    }

    /**
     * Clean destroy session and cookies
     */
    public static function destroyClean()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            @setcookie(session_name(), "", time() - 3600, '/');
            @session_unset();
            @session_destroy();
        }
    }

    /**
     * Get session value by key
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set session value
     * @param string $key
     * @param mixed $value
     */
    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Check if session key exists
     * @param string $key
     * @return bool
     */
    public static function has($key)
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session key
     * @param string $key
     */
    public static function remove($key)
    {
        unset($_SESSION[$key]);
    }
}