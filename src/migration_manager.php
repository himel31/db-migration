<?php
require "db_handler.php";

/**
 * Class Migration
 *
 * @author Himel
 *
 * Improvement area of script :
 *
 *      @todo 1. Add log writing in important points/terns of code
 *      @todo 2. Add migration rollback feature ( run down() method in version files)
 */
class MigrationManager extends DbHandler{

    private $MIGRATE_FILE_PREFIX =  'Version';
    private $MIGRATIONS_DIR = '/versions/';
    private $MIGRATIONS_TEMPLATE = '/template-version.php';

    protected $FILE_TYPE = 'php';
    public $allowed_file_type = array('php','--sql');

    private $_all_migration_files = array();

    /*utility functions >>>>>>>>>>>>>>>>>>>*/

    protected function migration_full_path() {
        return Utility::current_dir() . '/..' .$this->MIGRATIONS_DIR;
    }

    /**
     * check whether any non migrated files exist, if no, then nothing to migrate
     */
    private function is_database_up_to_date() {
        if(empty($this->_non_migrated_files)) {
            echo "Your database is up-to-date. No migration needed.\n";
            exit;
        }
    }

    /**
     * check whether MIGRATIONS_DIR folder is empty
     */
    private function is_non_migration_file_exist() {
        if(empty($this->_all_migration_files)) {
            echo 'No migration version file exist in ' . $this->MIGRATIONS_DIR .  " directory.\n";
            exit;
        }
    }
    /*utility functions <<<<<<<<<<<<<<<<<<<<<<*/

    /**
     * execute migration operation on available migration versions
     */
    private function execute() {
        $this->is_database_up_to_date();

        echo "files going to migrate : \n";
        Utility::show_list($this->_non_migrated_files);

        if($this->FILE_TYPE == '--sql')
            $exe_function = 'execute_sql_file';
        else
            $exe_function = 'execute_php_file';

        // run the available (non migrated) migration files
        foreach($this->_non_migrated_files as $file) {
            echo "Running: $file\n";
            $this->$exe_function($file);
        }
    }

    /**
     * compare with migrated_version in DB and listing available (non migrated ) files in array
     */
    private function filter_nonmigrated_files() {
        if($this->is_table_exist()) {
            $migrated_version_list = $this->get_migrated_version_list();
            $this->_non_migrated_files =  array_diff($this->_all_migration_files, $migrated_version_list);
        } else {
            // no version in db, all files add need to migrate
            $this->_non_migrated_files =  $this->_all_migration_files;
        }

    }

    /**
     * Find all the migration files in the directory
     * and return the sorted.
     */
    private function scan_migration_files() {
        $this->_all_migration_files = array();

        if (is_dir($this->migration_full_path())) {
            if($dir = opendir($this->migration_full_path())) {
                while (false !== ($file = readdir($dir))) {
                    // consider the files that start with $this->MIGRATE_FILE_PREFIX
                    if (substr($file, 0, strlen($this->MIGRATE_FILE_PREFIX)) == $this->MIGRATE_FILE_PREFIX) {
                        $this->_all_migration_files[] = $file;
                    }
                }
            } else {
                echo 'Unable to open ' . $this->migration_full_path();
                exit;
            }
        } else {
            echo 'No directory found in ' . $this->migration_full_path();
            exit;
        }
        asort($this->_all_migration_files);
    }

    /**
     * main function of migration operations
     *
     * > php migration.php migrate [--sql]
     */
    public function migrate() {

        $this->scan_migration_files();
        $this->is_non_migration_file_exist();
        $this->filter_nonmigrated_files();

        $this->execute();
    }

    /**
     * show available (non migrated) files , stored in MIGRATIONS_DIR
     *
     * > php migration.php status
     */
    public function current_status() {

        $this->scan_migration_files();
        $this->is_non_migration_file_exist();
        $this->filter_nonmigrated_files();

        $this->is_database_up_to_date();

        echo "Available version files for migration : \n";
        Utility::show_list($this->_non_migrated_files);
    }

    /**
     * show migrated files , stored in db
     *
     * > php migration.php status [--migrated]
     */
    public function show_migrated_files() {

        if($this->is_table_exist()) {

            $migrated_version_list = $this->get_migrated_version_list();

            if(empty($migrated_version_list)) {
                echo "No migration executed so far. \n";
            }
            Utility::show_list($this->get_migrated_version_list());
        }
    }

    /**
     * create new file titled migration-Ymdhms.php
     *
     * > php migration.php new
     */
    public function create_template() {

        $new_file_name = $this->MIGRATE_FILE_PREFIX . date('Ymdhms');
        $template = file_get_contents(Utility::current_dir() . $this->MIGRATIONS_TEMPLATE);
        if(empty($template)) {
            echo "Template file failed to load!\n";
            exit;
        }
        $template = str_replace("VERSION", $new_file_name, $template);
        $path = $this->migration_full_path() . $new_file_name .'.'. $this->FILE_TYPE;
        echo "Adding a new migration version file ...\n";

        $f = @fopen($path, 'w');
        if ($f) {
            fputs($f, $template);
            fclose($f);
            echo "Done.\nYou can now write php scripts to run the sql alter query(s) in the up() method of $this->MIGRATIONS_DIR" . $new_file_name .'.'. $this->FILE_TYPE . ". \n";
        }
        else {
            echo "Failed.\n";
        }
    }

}