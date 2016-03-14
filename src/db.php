<?php

/**
 * Class Db
 * Manage connection of databases
 */
class Db {
    private $host     = "localhost";
    private $user     = "user";
    private $password = "password";
    private $database = "db";
    protected $dbConnection;

    function __construct() {
        try {
            if(!$this->dbConnection) {
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
