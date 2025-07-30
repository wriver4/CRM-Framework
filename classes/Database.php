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
        $this->wr_host = 'localhost';
        $this->wr_database = 'serverw_serverw';
        $this->wr_username = 'serverw_serverw';
        $this->wr_password = 'TJza3DrfUZS0';


        // admin database connection information
        $this->admin_host = 'localhost';
        $this->admin_database = 'adminrnd_primary'; //adminrnd_primary fY5JTVs38CPW
        $this->admin_username = 'adminrnd_primary';
        $this->admin_password = 'fY5JTVs38CPW';

        // secure connection information encrypted
        $this->secure_host = 'localhost';
        $this->secure_database = 'newwgadmin_secure';
        $this->secure_username = 'root';
        $this->secure_password = 'root';

        $this->character_set = 'utf8mb4';
        $this->options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];
    }
    public function dbwr()
    {
        static $DBWR = null;
        if (is_null($DBWR)) {
            $dsn = 'mysql:host=' . $this->wr_host . ';dbname=' . $this->wr_database . ';charset=' . $this->character_set;
            try {
                $pdo = new \PDO($dsn, $this->wr_username, $this->wr_password, $this->options);
            } catch (\PDOException $e) {
                throw new \PDOException($e->getMessage(), (int)$e->getCode());
            }
            $DBWR = $pdo;
        }
        return $DBWR;
    }
    public function dbadmin()
    {
        static $DBADMIN = null;
        if (is_null($DBADMIN)) {
            $dsn = 'mysql:host=' . $this->admin_host . ';dbname=' . $this->admin_database . ';charset=' . $this->character_set;
            try {
                $pdo = new \PDO($dsn, $this->admin_username, $this->admin_password, $this->options);
            } catch (\PDOException $e) {
                throw new \PDOException($e->getMessage(), (int)$e->getCode());
            }
            $DBADMIN = $pdo;
        }
        return $DBADMIN;
    }

    public function dbsecure()
    {
        static $DBS = null;
        if (is_null($DBS)) {
            $dsn = 'mysql:host=' . $this->secure_host . ';dbname=' . $this->secure_database . ';charset=' . $this->character_set;
            try {
                $pdo = new \PDO($dsn, $this->secure_username, $this->secure_password, $this->options);
            } catch (\PDOException $e) {
                throw new \PDOException($e->getMessage(), (int)$e->getCode());
            }
            $DBS = $pdo;
        }
        return $DBS;
    }
}
