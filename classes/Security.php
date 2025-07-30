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
}
