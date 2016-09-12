<?php


namespace Utils;


class Database
{
    static private $instance = null;
    /**
     * @var \mysqli
     */
    private $mysqli;
    private function __construct() {  }
    private function __clone() {  }
    private function __wakeup() {  }

    static public function getInstance() {
        return
            self::$instance===null
                ? self::$instance = new static()//new self()
                : self::$instance;
    }

    public function getMysqli(){
        return $this->mysqli;
    }

    public function setMysqli(\mysqli $mysqli){
        $this->mysqli = $mysqli;
    }
}