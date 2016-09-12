<?php


namespace Utils;


class PoTranslator
{

    static private $instance = null;
    /**
     * @var array
     */
    private $mapping=array();
    private function __construct() { /* ... @return Singleton */ }  // Защищаем от создания через new Singleton
    private function __clone() { /* ... @return Singleton */ }  // Защищаем от создания через клонирование
    private function __wakeup() { /* ... @return Singleton */ }  // Защищаем от создания через unserialize

    static public function getInstance() {
        return
            self::$instance===null
                ? self::$instance = new static()//new self()
                : self::$instance;
    }


    public function setPath($path){
        if(!is_file($path)) throw  new \Exception('File '.$path.' not found');
        $this->path = $path;

        $content = file_get_contents($path);
        $poMsgIdAndMsgStrRegex = '/^#\s*(.+?)\nmsgid "(.+?)"\nmsgstr "(.+?)"/m';

        if(preg_match_all($poMsgIdAndMsgStrRegex,$content,$matches)){
            foreach ($matches[2] as $id=>$tag){
                if(isset($matches[3][$id])){
                    $this->mapping[$tag]=$matches[3][$id];
                }
            }
        }
    }


    public function translate($msgId){
        return isset($this->mapping[$msgId])? $this->mapping[$msgId]:$msgId;
    }
}