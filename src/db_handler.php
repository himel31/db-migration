<?php
require_once 'db.php';
require_once 'utility.php';
/**
 * Class DbHandler
 *
 * Handle database related tasks
 */
class DbHandler extends Db {
    protected $MIGRATIONS_TABLE = 'migrations';

    /**
     * check whether table $this->MIGRATIONS_TABLE exist
     * if not exist , create it and return false
     * @return bool
     */
    protected function is_table_exist() {
        $val = $this->dbConnection->query("select 1 from $this->MIGRATIONS_TABLE LIMIT 1");
        if($val !== FALSE) {
            //table exists!
            return true;
        } else {
            //create the table and return false
            $result = $this->dbConnection->query("CREATE TABLE IF NOT EXISTS $this->MIGRATIONS_TABLE (
                                `version` varchar(50) NOT NULL,
                              UNIQUE KEY `version_UNIQUE` (`version`)
                            ) DEFAULT CHARSET=utf8");

            if(! $result ) {
                echo "Could not create table: $this->MIGRATIONS_TABLE : ";
                print_r($this->dbConnection->errorInfo());
                exit;
            }
            return false;
        }

    }

    protected function query($query) {

        $result = $this->dbConnection->query($query);

        if (!$result) {
            echo "Query execution failed: ";
            print_r($this->dbConnection->errorInfo());
            echo "\n Aborting.\n";
            exit;
        }
        return $result;
    }

    /**
     * Fetch all version list from database MIGRATIONS_TABLE and return as array
     * @return array
     */
    protected function get_migrated_version_list() {
        $sql = "select version from $this->MIGRATIONS_TABLE order by version";
        $result = $this->dbConnection->query("$sql");
        $migrated_version_list = array();

        while ($row = $result->fetch(PDO::FETCH_OBJ)) {
            $migrated_version_list[] = $row->version;
        }
        return $migrated_version_list;
    }

    /**
     * @param $file
     * execute shell mysql command to manage sql migration version files.
     * then insert the vertion name to MIGRATIONS_TABLE
     */
    protected function execute_sql_file($file) {

        if(Utility::get_file_type($file) != 'sql') {
            echo $file." is not a sql file \n";
            return false;
        }

        $command = 'mysql '
            . $this->get_db_param()
            . ' < ' . $this->migration_full_path().$file;

        $this->dbConnection->beginTransaction();
        $stmt = $this->dbConnection->prepare("INSERT INTO $this->MIGRATIONS_TABLE VALUE (?)");
        $result = $stmt->execute(array($file));

        if(!$result) {

            echo "Aborting due to some error in migration tracking\n";
            $this->dbConnection->rollback();
            exit;
        }

        $output = array();
        exec($command,$output,$worked);
        switch($worked){
            case 0:
                echo "$file successfully imported \n";
                $this->dbConnection->commit();

                break;
            case 1:
                echo 'There was an error during import ' . $file ."\n";
                echo "Aborted \n";
                $this->dbConnection->rollback();
                $this->current_status();
                exit;
                break;
        }
    }

    /**
     * Include version class file and run up() method,
     * Alter query(s) should be define and executed on that up() method.
     * then insert the vertion name to MIGRATIONS_TABLE
     *
     * @param $file
     */
    protected function execute_php_file($file){

        if(Utility::get_file_type($file) != 'php') {
            echo $file." is not a php file \n";
            return false;
        }

        include_once(($this->migration_full_path() . $file));
        //@todo check if include successfull
        $class_name = Utility::get_class_name($file);

        if(class_exists($class_name)) {
            $this->dbConnection->beginTransaction();
            $stmt = $this->dbConnection->prepare("INSERT INTO $this->MIGRATIONS_TABLE VALUE (?)");
            $result = $stmt->execute(array($file));

            if(!$result) {

                echo "Aborting due to some error in migration tracking\n";
                $this->dbConnection->rollback();
                exit;
            }

            try {

                $migration_file = new $class_name();
                $migration_file->up();
                $this->dbConnection->commit();
                echo "Executed successfully. \n";

            } catch (Exception $e) {

                $this->dbConnection->rollback();
                echo "Aborting due to some error in version file : " .$file;
                echo "\n" . $e->getMessage() . "\n";
                exit;
            }
        } else {
            echo "Aborting due to some error in class in version file : " .$file;
            echo "\n";
            exit;
        }
    }
}