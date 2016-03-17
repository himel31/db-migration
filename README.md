# Db Migration
Simple database migration script.

## Database configuration
Open src/config.ini in your favorate editor and put your database information there. Here is an example : 
```
[database]
host = localhost
user = root
password = password
database = dbname
```

## Step-by-step guide

* Open the terminal.
* cd path/to/root/directory/of/project.
* Execute following command -
```
php migration.php
```
  you can see the summary of instructions in terminal screen :
```
Usage:
        To check status of available (default) or migrated version files:
            php migration.php status [--migrated]
        To create a file for new version:
            php migration.php new
        To migrate your database:
            php migration.php migrate [--sql]

 
```

## Details of commands

##### 1.  php migration.php status [--migrated]

Run it to list out:

  * Migrated version files available/ready to migrate (default behavior without any parameter passing ). Here is a sample look:

```
Available version files for migration : 
    Version20160314100322.php
```
  * Already migrated files (need to pass optional '--migrated' parameter )

##### 2. php migration.php new

This command will create a new migration version, tilted :
```
version<YEAR><MONTH><DATE><HOUR><MINUTE><SECOND>.php
```
This file is a kind of template file containing a class with some empty methods.
```
<?php
class Version20160314100322  extends DbHandler {
    /**
     * Write php scripts to run the sql alter query(s) here
     *
     * sample code :
     * $this->query("Insert sql here");
     */
    public function up() {

    }
    public function down() {

    }
}
?>
```
Developers have to write the scripts to run the sql alter query in up() method, they also can write the rollback query in down() method.

 
##### 3. php migration.php migrate [--sql]

This command will execute migration operation on available version php files one by one.

The system also support sql type migrations, for that, optional '--sql' parameter need to pass with the command and the name of sql file must start with 'Version'. Please ensure that the sql version files are correctly sorted, otherwise error may occur and the process will be aborted.