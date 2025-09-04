<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

/**
 * Sessions class for managing user sessions
 * 
 * Handles session management with security features like
 * session regeneration, timeout handling, and secure configuration.
 */
class Sessions extends Database
{
    public function __construct()
    {
        parent::__construct();
        $this->configureSession();
    }

    /**
     * Configure session settings for security
     */
    private function configureSession()
    {
        // Only configure if session hasn't started yet
        if (session_status() === PHP_SESSION_NONE) {
            // Security settings
            ini_set('session.use_strict_mode', 1);
            ini_set('session.sid_length', 32);
            ini_set('session.sid_bits_per_character', 4);
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', 1);
            ini_set('session.use_only_cookies', 1);
        }
    }

    /**
     * Start a new session
     */
    public function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            return session_start();
        }
        return true;
    }

    /**
     * Destroy current session
     */
    public function destroy()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
            return true;
        }
        return false;
    }

    /**
     * Regenerate session ID for security
     */
    public function regenerate($deleteOld = true)
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return session_regenerate_id($deleteOld);
        }
        return false;
    }
}