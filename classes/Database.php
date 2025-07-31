<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */
/** Warning - Warning
 * This File will show errors that are not errors following $this->
 * In VSCode not sure about other editors
 */

class Database
{
    public function __construct()
    {
        // server database connection information
        $this->crm_host = 'localhost';
        $this->crm_database = 'democrm_democrm';
        $this->crm_username = 'democrm_democrm';
        $this->crm_password = 'b3J2sy5T4JNm60';

        $this->character_set = 'utf8mb4';
        $this->options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];
    }
    public function crm()
    {
        static $CRM = null;
        if (is_null($CRM)) {
            $dsn = 'mysql:host=' . $this->crm_host . ';dbname=' . $this->crm_database . ';charset=' . $this->character_set;
            try {
                $pdo = new \PDO($dsn, $this->crm_username, $this->crm_password, $this->options);
            } catch (\PDOException $e) {
                throw new \PDOException($e->getMessage(), (int)$e->getCode());
            }
            $CRM = $pdo;
        }
        return $CRM;
    }
    
}