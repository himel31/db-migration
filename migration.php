<?php
require_once 'src/migration_manager.php';

class Migration extends MigrationManager {

    private $argv = array();

    function __construct($_argv = array()) {

        parent::__construct();
        $this->argv = $_argv;
    }

    function run() {

        if (isset($this->argv[1]) && $this->argv[1] == 'new') {

            $this->create_template();
        }
        else if (isset($this->argv[1]) && $this->argv[1] == 'migrate') {

            if(isset($this->argv[2]) && in_array($this->argv[2], $this->allowed_file_type))
                $this->FILE_TYPE = $this->argv[2];

            $this->migrate();
        }
        else if (isset($this->argv[1]) && $this->argv[1] == 'status') {

            if(isset($this->argv[2]) && $this->argv[2] == '--migrated')
                $this->show_migrated_files();
            else if(!isset($this->argv[2]))
                $this->current_status();
        }
        else {

            echo "Usage:
        To check status of available (default) or migrated version files:
            php migration.php status [--migrated]
        To create a file for new version:
            php migration.php new
        To migrate your database:
            php migration.php migrate [--sql]
         \n";
            exit;
        }
    }

}

$migration = new Migration($argv);
$migration->run();