#!/usr/bin/env php
<?php

/* MIGRATE SCRIPT */
class Migrate {

    var $cmd;
    var $upList;
    var $downList;
    var $fileList;
    var $position;
    var $settings;
    var $connect;

    function __construct() {
        $this->upList = array();
        $this->downList = array();
        $this->fileList = array();
        $this->position = -1;
    }

    function run() {
        $this->loadInfo();
        $this->loadMigrations();
        $this->loadSettings();

        $this->parseArgs();
    }

    function parseArgs() {
        global $argv, $argc;

        $args = array();
        if (!empty($argv)) {
            $args = array_slice($argv, 0);
            // remove command from start of array
            $this->cmd = array_shift($args);
        }


        if ($args[0] == 'update') {
            $this->moveToLatest();
        } else if ($args[0] == 'revert') {
            $this->moveBackOne();
        } else if ($args[0] == 'info') {
            $this->showInfo();
        } else if (preg_match('/^[0-9]+/', $args[0])) {
            $this->moveToFile($args[0]);
        } else {
            $this->showHelp();
        }
    }

    function loadMigrations() {
        global $migrate;

        $migrationsPath = 'Utils/migrations';
        if (is_dir($migrationsPath)) {
            foreach (glob($migrationsPath.'/[0-1]*.php') as $migrationFile) {
                $this->fileList[] = basename($migrationFile);
                include $migrationFile;
            }
        }
    }

    function loadInfo() {
        // if there is no .migrations file skip trying to read it
        if (!file_exists('.migrations')) {
            return;
        }

        $serializedInfo = file_get_contents('.migrations');
        if ($serializedInfo === false) {
            $this->showError('Could not read .migrations file');
        }

        $info = unserialize($serializedInfo);
        if ($info === false) {
            $this->showError('Did not understand contents of .migrations file');
        }

        $this->position = $this->getPositionOfFile($info['lastFile']);
    }

    function saveInfo() {

        $info = array();
        $info['lastFile'] = $this->fileList[$this->position];

        $serializedInfo = serialize($info);

        $fileHandle = fopen('.migrations', 'w');
        if ($fileHandle !== false) {
            if (fwrite($fileHandle, $serializedInfo) === false) {
                // fail
                $this->showError('Could not write .migrations file');
            }

            fclose($fileHandle);
        } else {
            $this->showError('Could not open .migrations file');
        }
    }

    function showError($msg) {
        echo 'ERROR: '.$msg."\n";
        exit;
    }

    function showHelp() {
        $usage = <<<EOF
Usage:
migrate.php update|revert|info|(filename)
List of Commands:
update - migrates to latest migration
revert - reverts back one migration
Example:
php migrate.php 005
This migrates the database to the point in the file in "migrations/" starting with "005"
EOF;
    }

    function showInfo() {
        echo 'Current Position: '.$this->lastPosition."\n";
        echo 'Files:'."\n";
        foreach ($this->fileList as $file) {
            echo $file . "\n";
        }
    }

    function moveToFile($migrationName) {
        $targetPosition = $this->getPositionOfFile($migrationName);

        if ($targetPosition >= 0) {
            $this->moveToPosition($targetPosition);
        } else {
            // fail
            showError('Did not understand version name: \''.$migrationName.'\'');
            return;
        }
    }

    function getPositionOfFile($migrationName) {
        $matches = preg_grep('/^'.preg_quote($migrationName,'/').'/i', $this->fileList);
        if (!empty($matches)) {
            return key($matches);
        } else {
            return -1;
        }
    }

    function moveToPosition($targetPosition) {
        if ($targetPosition > $this->position) {
            for ($i = $this->position + 1; $i <= $targetPosition; $i++) {
                if (array_key_exists($i, $this->upList)) {
                    call_user_func($this->upList[$i]);
                }
            }

            $this->position = $targetPosition;
            $this->saveInfo();

        } else if ($targetPosition < $this->position) {
            for ($i = $this->position; $i > $targetPosition; $i--) {
                if (array_key_exists($i, $this->downList)) {
                    call_user_func($this->downList[$i]);
                }
            }

            $this->position = $targetPosition;
            $this->saveInfo();
        }
    }

    function moveToLatest() {
        $latestFile = end($this->fileList);
        reset($this->fileList);

        if ($latestFile !== false) {
            $this->moveToFile($latestFile);
        }
    }

    function moveBackOne() {
        if ($this->position >= 0 && array_key_exists($this->position, $this->fileList)) {
            $targetPosition = $this->position - 1;
            $this->moveToPosition($targetPosition);
        }
    }

    function up($callback) {
        $this->upList[] = $callback;
    }

    function down($callback) {
        $this->downList[] = $callback;
    }

    function query($sql) {
        echo $sql."\n";
        if ($this->connect->query($sql) === false) {
            $this->showError($this->connect->error());
        }
    }

    function loadSettings() {
        $self_config = '/etc/stalker_jsonapi.ini';

        $stalker_path='/var/www/stalker_portal/';

        if(file_exists($self_config)){
            $override = parse_ini_file($self_config);
            if(isset($override['stalker_host'])) $stalker_host   = $override['stalker_host'];
            if(isset($override['stalker_path'])) $stalker_path   = $override['stalker_path'];
            if(isset($override['debug']))        $debug          = $override['debug'];
        }

        $conf = array();
        if(!file_exists($stalker_path)) throw  new Exception("Work directory of stalker portal is not exist",500);
        $config_path = $stalker_path.'/server/config.ini';
        $custom_path = $stalker_path.'/server/custom.ini';


        if(file_exists($config_path)) {
            $conf   = parse_ini_file($config_path);
        }else{
            throw  new \Exception('File '.$config_path.' must be present', 500);
        }

        if(file_exists($custom_path)){
            $custom = parse_ini_file($custom_path);
            $conf = array_merge($conf,$custom);
        }
        $conf = array_merge($conf,array('stalker_path'=>$stalker_path));

        $dbhost = isset($conf['mysql_host']) ? $conf['mysql_host']:'localhost';

        $settings = array(
            'host' => $dbhost,
            'user' => $conf["mysql_user"],
            'pass' => $conf["mysql_pass"],
            'database' => $conf["db_name"]
        );

        $dsn = 'mysql:host='.$dbhost.';dbname='.$conf["db_name"].';charset=utf8';

        $opt = array(
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        );

        $this->connect = new PDO($dsn,$conf["mysql_user"], $conf["mysql_pass"], $opt);

        $this->settings = $settings;
        //mysql_connect($this->settings['host'], $this->settings['user'], $this->settings['pass']);
        //mysql_select_db($this->settings['database']);
    }
}
$migrate = new Migrate();

// require 'settings.php';
$migrate->run();