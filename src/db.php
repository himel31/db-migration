<?php

/**
 * Class Db
 * Manage connection of databases
 */
class Db {
    private $host     = null;
    private $user     = null;
    private $password = null;
    private $database = null;
    protected $dbConnection;

    function __construct() {
        try {
            if(!$this->dbConnection) {
                $config = parse_ini_file('config.ini');

                $this->host = $config['host'];
                $this->user = $config['user'];
                $this->password = $config['password'];
                $this->database = $config['database'];

                $this->dbConnection = new PDO("mysql:dbname=$this->database;host=$this->host", $this->user, $this->password);
            }

        } catch(PDOException $e) {
            echo "Connect failed: ".$e->getMessage();
            die;
        }
    }

    protected function get_db_param() {
        return ' --host=' . $this->host
        . ' --user=' . $this->user
        . ' --password=' . $this->password
        . ' --database=' . $this->database;
    }
}
